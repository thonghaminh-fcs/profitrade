// =============================================================================
// Variation swatch handler
// =============================================================================
// Each .zp-variation has either:
//   - .zp-swatches (color or button swatches) + a hidden <input>
//   - <select> (fallback for non-color/size attributes)
//
// On click, swatches mirror their value into the hidden input, then trigger
// `change` so WC's wc-add-to-cart-variation script picks it up and populates
// .single_variation_wrap with price + add-to-cart.
//
// We also surface stock + sale info from the matched variation into our
// custom .zp-stock pill, and update the price block in the summary.
// =============================================================================

import { initQuantity } from "./quantity.js";

export function initVariations() {
	const form = document.querySelector(".variations_form");
	if (!form) return;

	// 0. Defensively force-init WC's variation form. WC binds .variations_form
	//    on document.ready, but FSE shortcode rendering can sometimes run after
	//    that — so re-initing is a no-op when already wired, and a fix when not.
	if (typeof window.jQuery === "function" && typeof window.jQuery.fn.wc_variation_form === "function") {
		const $form = window.jQuery(form);
		if (!$form.data("wc-variation-form-initialized")) {
			$form.wc_variation_form();
			$form.data("wc-variation-form-initialized", true);
			// Re-evaluate current selection so disabled state updates
			$form.trigger("check_variations");
		}
	}

	// 1. Wire up swatches to mirror into the hidden <select> that WC's
	//    variation script watches (.variations select via $attributeFields).
	form.querySelectorAll(".zp-variation").forEach((wrap) => {
		const swatches = wrap.querySelectorAll(".zp-swatch");
		// For swatch rows: sync into the visually-hidden <select>
		// For select rows: the <select> is already visible and WC handles it
		const hiddenSelect  = wrap.querySelector("select.zp-variation__select--hidden");
		const visibleSelect = wrap.querySelector("select.zp-variation__select:not(.zp-variation__select--hidden)");
		const selectedLabel = wrap.querySelector("[data-selected-label]");

		swatches.forEach((btn) => {
			btn.addEventListener("click", () => {
				const val   = btn.dataset.value;
				const label = btn.dataset.label;
				// Toggle if same value, else select
				const wasSelected = btn.classList.contains("is-selected");
				wrap.querySelectorAll(".zp-swatch").forEach((s) => {
					s.classList.remove("is-selected");
					s.setAttribute("aria-checked", "false");
				});
				if (!wasSelected) {
					btn.classList.add("is-selected");
					btn.setAttribute("aria-checked", "true");
					if (hiddenSelect) hiddenSelect.value = val;
					if (selectedLabel) selectedLabel.textContent = label;
				} else {
					if (hiddenSelect) hiddenSelect.value = "";
					if (selectedLabel) selectedLabel.textContent = "";
				}
				// Trigger change on the <select> so WC's variation script picks it up
				if (hiddenSelect) hiddenSelect.dispatchEvent(new Event("change", { bubbles: true }));
			});
		});

		// Initialize selectedLabel from any pre-selected swatch
		const initial = wrap.querySelector(".zp-swatch.is-selected");
		if (initial && selectedLabel) selectedLabel.textContent = initial.dataset.label || "";

		// Visible selects (non-swatch attributes): surface selected label
		if (visibleSelect && selectedLabel) {
			const sync = () => {
				const opt = visibleSelect.options[visibleSelect.selectedIndex];
				selectedLabel.textContent = opt && opt.value ? opt.text : "";
			};
			visibleSelect.addEventListener("change", sync);
			sync();
		}
	});

	// 2. When WC finds a matching variation, surface stock into our custom pill
	// Stock pill lives in the price-row above the form, not inside it.
	// Fall back to inside-form search for backward compatibility.
	const stockPill = document.querySelector(".zp-stock--variation")
		|| form.querySelector(".zp-stock--variation");
	const summary   = document.querySelector(".zp-summary");
	const priceEl   = summary && summary.querySelector(".zp-summary__price");

	form.addEventListener("found_variation", (e, variation) => {
		// jQuery-style event — payload is on detail or arg
		const v = variation || (e && e.detail);
		if (!v) return;
		updateStockPill(stockPill, v);
		updatePrice(priceEl, v);
		// Re-init quantity stepper if WC swapped the form
		initQuantity();
	});

	form.addEventListener("reset_data", () => {
		if (stockPill) stockPill.hidden = true;
	});

	// jQuery bridge: WC dispatches via jQuery, listen on $(form) too
	if (typeof window.jQuery === "function") {
		window.jQuery(form)
			.on("found_variation", (_e, v) => {
				updateStockPill(stockPill, v);
				updatePrice(priceEl, v);
				updateBuyNow(v);
				initQuantity();
			})
			.on("reset_data", () => {
				if (stockPill) stockPill.hidden = true;
				resetBuyNow();
			});
	}
}

/**
 * Enable Buy Now and rewrite its href to point at the chosen variation.
 * On variable products the button starts disabled (.is-disabled class) and
 * gets activated only when a valid variation is matched.
 */
function updateBuyNow(variation) {
	const buyNow = document.querySelector("[data-buy-now]");
	if (!buyNow || !variation) return;

	if (variation.is_in_stock === false || variation.is_purchasable === false) {
		buyNow.classList.add("is-disabled");
		return;
	}

	const productId = buyNow.dataset.productId;
	const url = new URL(buyNow.href, window.location.origin);
	url.searchParams.set("add-to-cart", productId);
	url.searchParams.set("variation_id", variation.variation_id);

	if (variation.attributes && typeof variation.attributes === "object") {
		for (const [key, value] of Object.entries(variation.attributes)) {
			if (value) url.searchParams.set(key, value);
		}
	}

	buyNow.href = url.toString();
	buyNow.classList.remove("is-disabled");
}

function resetBuyNow() {
	const buyNow = document.querySelector("[data-buy-now]");
	if (!buyNow) return;
	buyNow.classList.add("is-disabled");
	const productId = buyNow.dataset.productId;
	const url = new URL(buyNow.href, window.location.origin);
	url.searchParams.set("add-to-cart", productId);
	url.searchParams.delete("variation_id");
	[...url.searchParams.keys()].forEach((k) => {
		if (k.startsWith("attribute_")) url.searchParams.delete(k);
	});
	buyNow.href = url.toString();
}

function updateStockPill(el, v) {
	if (!el) return;

	// Preserve non-state classes (e.g. zp-stock--inline) when toggling state.
	const stateClasses = ["zp-stock--in", "zp-stock--low", "zp-stock--out"];
	const setState = (state, text) => {
		stateClasses.forEach((c) => el.classList.remove(c));
		el.classList.add("zp-stock", "zp-stock--variation", state);
		el.textContent = text;
		el.hidden = false;
	};

	if (v.is_in_stock === false) {
		setState("zp-stock--out", "Out of stock");
		return;
	}
	const qty = v.max_qty;
	if (typeof qty === "number" && qty > 0 && qty <= 5) {
		setState("zp-stock--low", `Only ${qty} left in stock!`);
		return;
	}
	setState("zp-stock--in", "In stock");
}

function updatePrice(el, v) {
	if (!el || !v.price_html) return;
	// Preserve our discount pill if the variation isn't on sale
	const discount = el.querySelector(".zp-summary__discount");
	el.innerHTML = v.price_html;
	if (discount && v.display_regular_price > v.display_price) {
		const pct = Math.round(((v.display_regular_price - v.display_price) / v.display_regular_price) * 100);
		el.insertAdjacentHTML("beforeend", `<span class="zp-summary__discount">-${pct}%</span>`);
	}
}
