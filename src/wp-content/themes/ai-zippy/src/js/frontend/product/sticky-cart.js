// =============================================================================
// Mobile sticky add-to-cart bar
// =============================================================================
// Appears at the bottom of viewport on screens ≤ 768px when the main add-to-cart
// button scrolls out of view. Tapping the sticky button scrolls back to + clicks
// the real button (so the same form submits, no duplicate handlers).
// =============================================================================

const MOBILE_BREAKPOINT = 768;

export function initStickyCart() {
	if (window.matchMedia(`(min-width: ${MOBILE_BREAKPOINT + 1}px)`).matches) return;

	const summary = document.querySelector(".zp-summary");
	const realBtn = summary && summary.querySelector(".single_add_to_cart_button");
	if (!summary || !realBtn) return;

	const title    = summary.querySelector(".zp-summary__title")?.textContent || "";
	const priceEl  = summary.querySelector(".zp-summary__price");
	const thumbSrc = pickThumb();

	const bar = document.createElement("div");
	bar.className = "zp-sticky-cart";
	bar.innerHTML = `
		${thumbSrc ? `<img class="zp-sticky-cart__thumb" src="${thumbSrc}" alt="" />` : ""}
		<div class="zp-sticky-cart__info">
			<span class="zp-sticky-cart__title">${title}</span>
			<span class="zp-sticky-cart__price">${priceEl ? priceEl.innerHTML : ""}</span>
		</div>
		<button type="button" class="zp-sticky-cart__btn">Add</button>
	`;
	document.body.appendChild(bar);

	bar.querySelector(".zp-sticky-cart__btn").addEventListener("click", () => {
		realBtn.scrollIntoView({ behavior: "smooth", block: "center" });
		// Don't auto-click — user might still need to choose variations
	});

	// Show bar when realBtn is below viewport
	const io = new IntersectionObserver(
		([entry]) => {
			bar.classList.toggle("is-visible", !entry.isIntersecting);
		},
		{ threshold: 0.1 },
	);
	io.observe(realBtn);
}

function pickThumb() {
	const img = document.querySelector(".wc-block-product-image-gallery img, .woocommerce-product-gallery__image img");
	return img ? (img.currentSrc || img.src) : "";
}
