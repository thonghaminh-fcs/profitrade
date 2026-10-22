// =============================================================================
// Quantity stepper — ± buttons next to <input type="number">
// =============================================================================

/**
 * Bind the ± buttons inside every .zp-qty wrapper.
 *
 * Safe to call repeatedly: variations.js re-runs this on every found_variation
 * in case WooCommerce swapped the form. WooCommerce leaves the existing buttons
 * in place, so without a guard each call stacked another pair of listeners and
 * one click applied once per variation switch — the stepper jumped by 2, 3, 4…
 */
export function initQuantity() {
	document.querySelectorAll(".zp-qty").forEach((wrap) => {
		const input = wrap.querySelector(".zp-qty__input");
		const minus = wrap.querySelector(".zp-qty__btn--minus");
		const plus  = wrap.querySelector(".zp-qty__btn--plus");
		if (!input || !minus || !plus) return;

		if (wrap.dataset.qtyBound === "1") return;
		wrap.dataset.qtyBound = "1";

		// Read the bounds per click rather than caching them: WooCommerce
		// rewrites min/max on the qty input when a variation is selected, and
		// it writes an empty max for unlimited stock — which must read as
		// Infinity, not NaN, or the clamp would blank the field.
		const attr = (name, fallback) => {
			const parsed = parseFloat(input.getAttribute(name));
			return Number.isFinite(parsed) ? parsed : fallback;
		};

		const stepBy = (direction) => {
			const step = attr("step", 1) || 1;
			const min  = attr("min", 0);
			const max  = attr("max", Infinity);
			const current = parseFloat(input.value) || 0;

			input.value = Math.max(min, Math.min(max, current + direction * step));
			input.dispatchEvent(new Event("change", { bubbles: true }));
		};

		minus.addEventListener("click", () => stepBy(-1));
		plus.addEventListener("click", () => stepBy(1));
	});
}
