/**
 * Mini Cart (S.Mart drawer) — frontend behaviour.
 *
 * render.php prints the full initial state, so this module never builds the
 * drawer from scratch — it keeps it in sync after a cart change (Woo Store
 * API) and wires the delivery picker into the shared {slot, express} state.
 *
 * Contracts it honours (do not rename their hooks):
 * - child.js owns drawer open/close ([data-sm-drawer-open/close], .is-open on
 *   .sm-drawer) and the delivery state in sessionStorage "sm-delivery"; state
 *   changes broadcast "sm:delivery" on document. Flipping express delegates to
 *   the delivery-bar [data-sm-express] toggle so every consumer re-renders.
 * - the parent theme bundle broadcasts "ai-zippy-cart-updated" (Store API cart
 *   in event.detail) after any cart write it performs.
 * - WooCommerce fires "wc-blocks_added_to_cart" and the jQuery "added_to_cart"
 *   / "removed_from_cart" events after classic AJAX writes.
 */

// Must match the slot list in render.php and delivery-bar/render.php.
const SLOTS = [
	"8:00 – 11:00 AM",
	"11:00 AM – 2:00 PM",
	"2:00 – 5:00 PM",
	"5:00 – 8:00 PM",
	"8:00 – 11:00 PM",
];

const DELIVERY_KEY = "sm-delivery";
const DEFAULT_SLOT = 2;
const FREE_LABEL = "Free";

/**
 * Format a Store API amount. Totals arrive as integer minor units plus the
 * currency shape, so site separators and symbol placement are respected
 * (mirrors formatPrice() in the parent's modules/mini-cart.js).
 */
function formatPrice(minorAmount, currency = {}) {
	const minorUnit = Number.isFinite(currency.currency_minor_unit)
		? currency.currency_minor_unit
		: 2;
	const value = Number(minorAmount || 0) / 10 ** minorUnit;

	if (!Number.isFinite(value)) return "";

	const [whole, fraction = ""] = value.toFixed(minorUnit).split(".");
	const thousands = currency.currency_thousand_separator ?? ",";
	const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
	const decimals = fraction
		? `${currency.currency_decimal_separator ?? "."}${fraction}`
		: "";

	return `${currency.currency_prefix ?? ""}${grouped}${decimals}${currency.currency_suffix ?? ""}`;
}

function getDelivery() {
	try {
		const parsed = JSON.parse(sessionStorage.getItem(DELIVERY_KEY));
		if (parsed && typeof parsed.slot === "number") {
			return { slot: parsed.slot, express: !!parsed.express };
		}
	} catch (_) {
		/* fall through to default */
	}
	return { slot: DEFAULT_SLOT, express: false };
}

function setup(root) {
	const toggle = root.querySelector(".sm-cart-button");
	const drawer = root.querySelector(".sm-cart-drawer");
	const list = root.querySelector(".sm-cart-drawer__items");
	const emptyState = root.querySelector(".sm-cart-drawer__empty");
	const footer = root.querySelector(".sm-cart-drawer__footer");

	const fee = parseFloat(root.dataset.expressFee || "20") || 0;
	const feeLabel = root.dataset.expressFeeLabel || `$${fee.toFixed(2)}`;

	const homeUrl = (window.smAssets && window.smAssets.homeUrl) || "/";
	const restBase = `${homeUrl.replace(/\/?$/, "/")}wp-json/wc/store/v1/`;
	const headers = () => ({
		"Content-Type": "application/json",
		Nonce:
			(window.wcBlocksMiddlewareConfig &&
				window.wcBlocksMiddlewareConfig.storeApiNonce) ||
			"",
	});

	// Subtotal in Store API minor units; null until the first cart arrives, so
	// the server-rendered totals stay on screen for guests with an empty cart.
	let subtotalMinor = null;
	let currency = {};

	const money = (minor) => formatPrice(minor, currency);

	function renderTotals() {
		const state = getDelivery();

		const feeEl = root.querySelector("[data-sm-delivery-fee]");
		if (feeEl) feeEl.textContent = state.express ? feeLabel : FREE_LABEL;

		if (subtotalMinor === null) return;

		const minorUnit = Number.isFinite(currency.currency_minor_unit)
			? currency.currency_minor_unit
			: 2;
		const feeMinor = state.express ? Math.round(fee * 10 ** minorUnit) : 0;
		const total = money(subtotalMinor + feeMinor);

		const subtotalEl = root.querySelector("[data-sm-subtotal]");
		const totalEl = root.querySelector("[data-sm-total]");
		const buttonTotal = root.querySelector("[data-sm-cart-total]");
		if (subtotalEl) subtotalEl.textContent = money(subtotalMinor);
		if (totalEl) totalEl.textContent = total;
		if (buttonTotal) buttonTotal.textContent = total;
	}

	function renderDelivery(state) {
		const pickSlot = root.querySelector("[data-sm-pick-slot]");
		const pickExpress = root.querySelector("[data-sm-pick-express]");
		const slotSub = root.querySelector("[data-sm-slot-sub]");

		if (pickSlot) {
			pickSlot.classList.toggle("is-selected", !state.express);
			pickSlot.setAttribute("aria-pressed", state.express ? "false" : "true");
		}
		if (pickExpress) {
			pickExpress.classList.toggle("is-selected", state.express);
			pickExpress.setAttribute("aria-pressed", state.express ? "true" : "false");
		}
		if (slotSub) {
			const label = SLOTS[state.slot] ?? SLOTS[DEFAULT_SLOT];
			slotSub.textContent = `Today, ${label} · ${FREE_LABEL}`;
		}
		renderTotals();
	}

	/** One cart row. Mirrors the <li> markup in render.php. */
	function renderItem(item) {
		const name = item.name || "";

		const li = document.createElement("li");
		li.className = "sm-cart-item";
		li.dataset.key = item.key;

		const image = item.images?.[0]?.thumbnail || item.images?.[0]?.src || "";
		if (image) {
			const img = document.createElement("img");
			img.className = "sm-cart-item__image";
			img.src = image;
			img.alt = "";
			img.width = 64;
			img.height = 64;
			img.loading = "lazy";
			li.append(img);
		}

		const body = document.createElement("div");
		body.className = "sm-cart-item__body";

		const title = document.createElement(item.permalink ? "a" : "span");
		title.className = "sm-cart-item__name";
		title.textContent = name;
		if (item.permalink) title.href = item.permalink;
		body.append(title);

		const variant = (item.variation || [])
			.map((v) => v.value)
			.filter(Boolean)
			.join(", ");
		const meta = document.createElement("span");
		meta.className = "sm-cart-item__meta";
		meta.textContent = [variant, formatPrice(item.prices?.price, item.prices || {})]
			.filter(Boolean)
			.join(" · ");
		body.append(meta);

		const controls = document.createElement("div");
		controls.className = "sm-cart-item__controls";

		const qty = document.createElement("div");
		qty.className = "sm-cart-item__qty";

		const down = document.createElement("button");
		down.type = "button";
		down.className = "sm-cart-item__qty-down";
		down.textContent = "\u2212";
		down.setAttribute("aria-label", `Decrease quantity of ${name}`);

		const value = document.createElement("span");
		value.className = "sm-cart-item__qty-value";
		value.textContent = String(item.quantity ?? 0);

		const up = document.createElement("button");
		up.type = "button";
		up.className = "sm-cart-item__qty-up";
		up.textContent = "+";
		up.setAttribute("aria-label", `Increase quantity of ${name}`);

		qty.append(down, value, up);

		const remove = document.createElement("button");
		remove.type = "button";
		remove.className = "sm-cart-item__remove";
		remove.textContent = "Remove";
		remove.setAttribute("aria-label", `Remove ${name} from cart`);

		controls.append(qty, remove);
		body.append(controls);
		li.append(body);

		const line = document.createElement("span");
		line.className = "sm-cart-item__line";
		line.textContent = formatPrice(
			item.totals?.line_total ?? item.totals?.line_subtotal,
			item.totals || {}
		);
		li.append(line);

		return li;
	}

	function apply(cart) {
		if (!cart) return;

		currency = cart.totals || {};

		const count = Number.isFinite(cart.items_count)
			? cart.items_count
			: (cart.items || []).reduce((total, item) => total + (item.quantity || 0), 0);
		subtotalMinor = Number(cart.totals?.total_items || 0);

		const countEl = root.querySelector("[data-sm-cart-count]");
		const titleCountEl = root.querySelector("[data-sm-cart-count-title]");
		if (countEl) countEl.textContent = String(count);
		if (titleCountEl) titleCountEl.textContent = `(${count})`;
		if (toggle) {
			toggle.setAttribute(
				"aria-label",
				count === 1 ? `${count} item in cart` : `${count} items in cart`
			);
		}

		if (list) list.replaceChildren(...(cart.items || []).map(renderItem));
		if (list) list.hidden = count === 0;
		if (emptyState) emptyState.hidden = count > 0;
		if (footer) footer.hidden = count === 0;

		renderTotals();
	}

	function fetchCart() {
		fetch(`${restBase}cart`, { headers: headers() })
			.then((res) => (res.ok ? res.json() : null))
			.then(apply)
			.catch(() => {});
	}

	/** Run a Store API write, then repaint from its response. */
	async function mutate(route, payload) {
		root.classList.add("is-busy");
		try {
			const res = await fetch(`${restBase}${route}`, {
				method: "POST",
				headers: headers(),
				body: JSON.stringify(payload),
			});
			if (!res.ok) throw new Error("cart write failed");
			apply(await res.json());
		} catch (_) {
			// Leave the drawer as-is; the next read reconciles it.
		} finally {
			root.classList.remove("is-busy");
		}
	}

	/**
	 * Flip the shared express flag. child.js owns the state, so delegate to
	 * the delivery-bar toggle when it is on the page — its click handler
	 * writes sessionStorage and broadcasts "sm:delivery" for everyone.
	 */
	function setExpress(on) {
		if (getDelivery().express === on) return;
		const barToggle = document.querySelector("[data-sm-express]");
		if (barToggle) {
			barToggle.click();
			return;
		}
		const next = { ...getDelivery(), express: on };
		try {
			sessionStorage.setItem(DELIVERY_KEY, JSON.stringify(next));
		} catch (_) {
			/* storage unavailable — event below still repaints this drawer */
		}
		document.dispatchEvent(new CustomEvent("sm:delivery", { detail: next }));
	}

	if (list) {
		list.addEventListener("click", (event) => {
			const row = event.target.closest(".sm-cart-item");
			if (!row) return;

			const key = row.dataset.key;
			const qty = Number(
				row.querySelector(".sm-cart-item__qty-value")?.textContent || 0
			);

			if (event.target.closest(".sm-cart-item__remove")) {
				mutate("cart/remove-item", { key });
			} else if (event.target.closest(".sm-cart-item__qty-up")) {
				mutate("cart/update-item", { key, quantity: qty + 1 });
			} else if (event.target.closest(".sm-cart-item__qty-down")) {
				// Reaching zero removes the line, matching the cart page.
				if (qty <= 1) mutate("cart/remove-item", { key });
				else mutate("cart/update-item", { key, quantity: qty - 1 });
			}
		});
	}

	root.addEventListener("click", (event) => {
		if (event.target.closest("[data-sm-pick-express]")) setExpress(true);
		else if (event.target.closest("[data-sm-pick-slot]")) setExpress(false);
	});

	document.addEventListener("sm:delivery", (event) => {
		renderDelivery(event.detail || getDelivery());
	});

	// Cart writes performed by the parent theme bundle (add-to-cart, its own
	// mini-cart instance) arrive pre-fetched in the event detail.
	window.addEventListener("ai-zippy-cart-updated", (event) => {
		if (event.detail) apply(event.detail);
		else fetchCart();
	});

	document.body.addEventListener("wc-blocks_added_to_cart", () => fetchCart());
	if (window.jQuery) {
		window.jQuery(document.body).on("added_to_cart removed_from_cart", () =>
			fetchCart()
		);
	}

	// Keep aria-expanded truthful: child.js toggles .is-open on the drawer.
	if (drawer && toggle && "MutationObserver" in window) {
		new MutationObserver(() => {
			toggle.setAttribute(
				"aria-expanded",
				drawer.classList.contains("is-open") ? "true" : "false"
			);
		}).observe(drawer, { attributes: true, attributeFilter: ["class"] });
	}

	renderDelivery(getDelivery());
	fetchCart();
}

function initMiniCart() {
	document.querySelectorAll(".sm-mini-cart").forEach(setup);
}

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", initMiniCart);
} else {
	initMiniCart();
}
