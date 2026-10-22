import { azText, azUrl } from "../core/config.js";
import { getCart, updateCartItem, removeCartItem } from "./cart-api.js";
import { showToast } from "./toast.js";

/**
 * Mini cart drawer.
 *
 * render.php already prints the current cart, so this module never builds the
 * drawer from scratch on load — it only keeps it in sync after a change and
 * handles opening, closing and focus.
 *
 * Every string comes from azText() and every URL from the runtime config, so a
 * client site can translate or relocate them without touching this bundle.
 */

/** Matches the transition duration in style.scss. */
const CLOSE_DELAY = 240;

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * The focusables the browser will actually move to.
 *
 * The visibility filter is load-bearing: an empty cart hides the footer, but
 * querySelectorAll still returns the View cart and Checkout links inside it. A
 * trap anchored on an unfocusable `last` never fires, and Tab walks straight out
 * of the dialog into the page behind.
 */
function focusableIn(panel) {
	return [...panel.querySelectorAll(FOCUSABLE)].filter((el) => el.getClientRects().length > 0);
}

/**
 * Format a Store API amount. Totals arrive as integer minor units plus the
 * currency shape, so the site's separators and symbol placement are respected
 * without guessing a locale.
 */
function formatPrice(minorAmount, currency = {}) {
	const minorUnit = Number.isFinite(currency.currency_minor_unit) ? currency.currency_minor_unit : 2;
	const value = Number(minorAmount || 0) / 10 ** minorUnit;

	if (!Number.isFinite(value)) return "";

	const [whole, fraction = ""] = value.toFixed(minorUnit).split(".");
	const thousands = (currency.currency_thousand_separator ?? ",");
	const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
	const decimals = fraction ? `${currency.currency_decimal_separator ?? "."}${fraction}` : "";

	return `${currency.currency_prefix ?? ""}${grouped}${decimals}${currency.currency_suffix ?? ""}`;
}

/** The item count label, picking the plural form in the browser. */
function countLabel(count) {
	return count === 1
		? azText("minicart.items_one", "%s item in cart", count)
		: azText("minicart.items_many", "%s items in cart", count);
}

/**
 * One cart row. Mirrors ai_zippy_mini_cart_item_row() in render.php — keep both
 * in step when the structure changes.
 */
function renderItem(item) {
	const li = document.createElement("li");
	li.className = "az-mc__item";
	li.dataset.key = item.key;

	const image = item.images?.[0]?.thumbnail || item.images?.[0]?.src || "";
	const price = formatPrice(item.totals?.line_subtotal, item.totals);
	const name = item.name || "";

	// textContent everywhere below: product names are author-controlled and must
	// never be parsed as HTML here.
	if (image) {
		const img = document.createElement("img");
		img.className = "az-mc__item-image";
		img.src = image;
		img.alt = "";
		img.width = 64;
		img.height = 64;
		img.loading = "lazy";
		li.append(img);
	}

	const body = document.createElement("div");
	body.className = "az-mc__item-body";

	const title = document.createElement(item.permalink ? "a" : "span");
	title.className = "az-mc__item-name";
	title.textContent = name;
	if (item.permalink) title.href = item.permalink;
	body.append(title);

	const priceEl = document.createElement("span");
	priceEl.className = "az-mc__item-price";
	priceEl.textContent = price;
	body.append(priceEl);

	const qty = document.createElement("div");
	qty.className = "az-mc__qty";
	qty.innerHTML = `
		<button class="az-mc__qty-down" type="button">&minus;</button>
		<input class="az-mc__qty-input" type="number" inputmode="numeric" min="0" step="1" />
		<button class="az-mc__qty-up" type="button">+</button>
	`;
	qty.querySelector(".az-mc__qty-down").setAttribute("aria-label", azText("minicart.decrease", "Decrease quantity of %s", name));
	qty.querySelector(".az-mc__qty-up").setAttribute("aria-label", azText("minicart.increase", "Increase quantity of %s", name));

	const input = qty.querySelector(".az-mc__qty-input");
	input.value = String(item.quantity ?? 0);
	input.setAttribute("aria-label", azText("minicart.quantity", "Quantity of %s", name));

	body.append(qty);
	li.append(body);

	const remove = document.createElement("button");
	remove.className = "az-mc__remove";
	remove.type = "button";
	remove.innerHTML = "&times;";
	remove.setAttribute("aria-label", azText("minicart.remove", "Remove %s from cart", name));
	li.append(remove);

	return li;
}

function setupInstance(root) {
	const toggle = root.querySelector(".az-mc__toggle");
	const drawer = root.querySelector(".az-mc__drawer");

	// Link mode (cart and checkout pages) has no drawer — the anchor is enough.
	if (!toggle || !drawer) return;

	const panel = drawer.querySelector(".az-mc__panel");
	const list = drawer.querySelector(".az-mc__items");
	const empty = drawer.querySelector(".az-mc__empty");
	const footer = drawer.querySelector(".az-mc__footer");
	const countEl = root.querySelector(".az-mc__count");
	const subtotalEls = root.querySelectorAll(".az-mc__subtotal, .az-mc__totals-value");
	const shipping = drawer.querySelector(".az-mc__shipping");
	const threshold = Number(root.dataset.threshold || 0);

	let lastFocused = null;
	let closeTimer = 0;
	let refreshTimer = 0;
	let broadcasting = false;
	// Set when add-to-cart.js hands us the write's own cart in the event detail.
	// The coalesced refresh() below sees this and skips its redundant Store API GET.
	let freshUntil = 0;

	const isOpen = () => root.classList.contains("az-mc--open");

	function open() {
		if (isOpen()) return;

		window.clearTimeout(closeTimer);
		lastFocused = document.activeElement;

		// On mobile (≤600px) WP's admin bar is absolute, not fixed — it scrolls
		// away. Measure whatever portion is still visible and push the fixed
		// drawer down by that amount so it never overlaps the bar at page top
		// yet sits flush once the bar has been scrolled past.
		if (document.body.classList.contains("admin-bar") && window.innerWidth <= 600) {
			const bar = document.getElementById("wpadminbar");
			const visible = bar ? Math.max(0, bar.getBoundingClientRect().bottom) : 0;
			drawer.style.setProperty("--az-mc-top", `${visible}px`);
		}

		drawer.hidden = false;
		// Next frame, so the browser has a painted "closed" state to animate from.
		requestAnimationFrame(() => root.classList.add("az-mc--open"));

		toggle.setAttribute("aria-expanded", "true");
		document.body.classList.add("az-mc-locked");

		(drawer.querySelector(".az-mc__close") || panel)?.focus();
	}

	function close() {
		if (!isOpen()) return;

		root.classList.remove("az-mc--open");
		toggle.setAttribute("aria-expanded", "false");
		document.body.classList.remove("az-mc-locked");

		// Keep it in the tree until the slide-out has finished.
		closeTimer = window.setTimeout(() => {
			drawer.hidden = true;
		}, CLOSE_DELAY);

		if (lastFocused instanceof HTMLElement) lastFocused.focus();
		lastFocused = null;
	}

	/** Keep Tab inside the panel while it is open. */
	function trapFocus(event) {
		const focusable = focusableIn(panel);
		if (focusable.length === 0) return;

		const first = focusable[0];
		const last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	/** Repaint the parts of the drawer that depend on cart contents. */
	function apply(cart) {
		if (!cart) return;

		const count = Number.isFinite(cart.items_count)
			? cart.items_count
			: (cart.items || []).reduce((total, item) => total + (item.quantity || 0), 0);

		if (countEl) {
			countEl.textContent = String(count);
			countEl.dataset.empty = count === 0 ? "true" : "false";
		}

		toggle.setAttribute("aria-label", countLabel(count));

		const subtotal = formatPrice(cart.totals?.total_items, cart.totals);
		subtotalEls.forEach((el) => {
			el.textContent = subtotal;
		});

		if (list) {
			// replaceChildren destroys whichever row the user is working in. Without
			// restoring focus a keyboard user lands on <body> — outside the open
			// dialog — after every + or −, and cannot press the same control twice.
			const active = document.activeElement;
			const activeRow = active instanceof HTMLElement ? active.closest(".az-mc__item") : null;
			const restoreKey = activeRow?.dataset.key;
			const restoreClass = activeRow
				? ["az-mc__qty-up", "az-mc__qty-down", "az-mc__qty-input", "az-mc__remove"].find((name) =>
					active.classList.contains(name)
				)
				: undefined;

			list.replaceChildren(...(cart.items || []).map(renderItem));

			// Only when focus was already inside a row: apply() also runs on add to
			// cart, and stealing focus from the product page would be worse.
			if (restoreKey) {
				const row = list.querySelector(`.az-mc__item[data-key="${CSS.escape(restoreKey)}"]`);
				// The row is gone when the change emptied that line, so fall back to a
				// control that is certain to still be in the panel.
				const target = (restoreClass && row?.querySelector(`.${restoreClass}`)) || drawer.querySelector(".az-mc__close");
				target?.focus();
			}
		}

		if (empty) empty.hidden = count > 0;
		if (footer) footer.hidden = count === 0;

		if (shipping && threshold > 0) {
			const minorUnit = Number.isFinite(cart.totals?.currency_minor_unit) ? cart.totals.currency_minor_unit : 2;
			const spent = Number(cart.totals?.total_items || 0) / 10 ** minorUnit;
			const remaining = Math.max(0, threshold - spent);
			const percent = Math.min(100, Math.round((spent / threshold) * 100));

			shipping.dataset.reached = remaining <= 0 ? "true" : "false";

			const text = shipping.querySelector(".az-mc__shipping-text");
			if (text) {
				text.textContent = remaining <= 0
					? azText("minicart.ship_reached", "You have earned free shipping")
					: azText(
						"minicart.ship_progress",
						"Spend %s more to get free shipping",
						formatPrice(Math.round(remaining * 10 ** minorUnit), cart.totals)
					);
			}

			const track = shipping.querySelector(".az-mc__shipping-track");
			const bar = shipping.querySelector(".az-mc__shipping-bar");
			if (track) track.setAttribute("aria-valuenow", String(percent));
			if (bar) bar.style.width = `${percent}%`;
		}
	}

	/** Run a Store API write, then repaint from its response. */
	async function mutate(action) {
		root.classList.add("az-mc--busy");

		try {
			const cart = await action();
			apply(cart);

			// header-cart-button.js and any client script listen for this. The flag
			// stops our own listener repainting what we just painted.
			broadcasting = true;
			window.dispatchEvent(new CustomEvent("ai-zippy-cart-updated", { detail: cart }));
			broadcasting = false;
		} catch {
			// A toast, not `.az-mc__shipping-text`: that element only exists when the
			// free-shipping bar is on, so writing the error there showed nothing on
			// the common default. The toast is the same one add-to-cart.js uses.
			showToast(azText("minicart.update_failed", "Could not update the cart"), "error");
		} finally {
			root.classList.remove("az-mc--busy");
		}
	}

	/**
	 * Re-read the cart after something outside the drawer changed it.
	 *
	 * Coalesced on a timer on purpose. add-to-cart.js fires the block event and
	 * the jQuery one for the same write, so a listener per event would send two
	 * identical GETs for every add.
	 *
	 * @param {boolean} reveal Open the drawer afterwards, if the block asks for it.
	 */
	function refresh(reveal) {
		window.clearTimeout(refreshTimer);
		refreshTimer = window.setTimeout(() => {
			// add-to-cart.js just applied the write's own cart — the GET would
			// only re-read what we already painted, so reveal and bail.
			if (Date.now() < freshUntil) {
				if (reveal && root.dataset.openOnAdd === "true") open();
				return;
			}
			getCart()
				.then((cart) => {
					apply(cart);
					if (reveal && root.dataset.openOnAdd === "true") open();
				})
				.catch(() => {});
		}, 50);
	}

	toggle.addEventListener("click", (event) => {
		event.preventDefault();
		isOpen() ? close() : open();
	});

	drawer.addEventListener("click", (event) => {
		if (event.target.closest("[data-az-mc-close]")) close();
	});

	drawer.addEventListener("keydown", (event) => {
		if (event.key === "Escape") {
			event.stopPropagation();
			close();
		} else if (event.key === "Tab") {
			trapFocus(event);
		}
	});

	// Quantity and removal, delegated so re-rendered rows keep working.
	list?.addEventListener("click", (event) => {
		const row = event.target.closest(".az-mc__item");
		if (!row) return;

		const key = row.dataset.key;
		const input = row.querySelector(".az-mc__qty-input");
		const current = Number(input?.value || 0);

		if (event.target.closest(".az-mc__remove")) {
			mutate(() => removeCartItem(key));
		} else if (event.target.closest(".az-mc__qty-up")) {
			mutate(() => updateCartItem(key, current + 1));
		} else if (event.target.closest(".az-mc__qty-down")) {
			// Reaching zero removes the line, matching the cart page.
			mutate(() => (current <= 1 ? removeCartItem(key) : updateCartItem(key, current - 1)));
		}
	});

	list?.addEventListener("change", (event) => {
		const input = event.target.closest(".az-mc__qty-input");
		if (!input) return;

		const row = input.closest(".az-mc__item");
		const quantity = Math.max(0, Math.floor(Number(input.value) || 0));

		mutate(() => (quantity === 0 ? removeCartItem(row.dataset.key) : updateCartItem(row.dataset.key, quantity)));
	});

	// add-to-cart.js fires this after a successful Store API write. When it
	// carries the write's own cart, paint from that and mark the next coalesced
	// refresh as redundant so we don't spend a second GET on the site's most
	// frequent action. The jQuery-only path below (no detail) still refetches.
	document.body.addEventListener("wc-blocks_added_to_cart", (event) => {
		const cart = event.detail?.cart;
		if (cart) {
			apply(cart);
			freshUntil = Date.now() + 1000;
			if (root.dataset.openOnAdd === "true") open();
		} else {
			refresh(true);
		}
	});

	// WooCommerce's own archive-loop AJAX only fires the jQuery events, so a site
	// that keeps the core add-to-cart button still updates the drawer.
	if (window.jQuery) {
		window.jQuery(document.body).on("added_to_cart", () => refresh(true));
		window.jQuery(document.body).on("removed_from_cart", () => refresh(false));
	}

	// Another instance, or client code, changed the cart.
	window.addEventListener("ai-zippy-cart-updated", (event) => {
		if (!broadcasting && event.detail) apply(event.detail);
	});
}

export function initMiniCart() {
	document.querySelectorAll(".az-mc--drawer").forEach(setupInstance);

	// Link mode still needs the cart URL to survive a permalink change.
	document.querySelectorAll(".az-mc--link .az-mc__toggle").forEach((link) => {
		if (!link.getAttribute("href")) link.href = azUrl("cart", "/");
	});
}
