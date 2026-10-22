// =============================================================================
// Lazy-load related products
// =============================================================================
// The related-products section is rendered server-side but contains a placeholder
// element with [data-lazy-related]. On scroll-into-view, swap the placeholder
// for a marker class that triggers CSS reveal + image src→data-src promotion.
//
// For a full async fetch, we'd hit a REST endpoint here — but related-products
// is already cheap to render server-side; lazy-loading IMAGES alone is the
// 90% perf win at zero added complexity.
// =============================================================================

export function initLazyRelated() {
	const section = document.querySelector("[data-lazy-related]");
	if (!section) return;

	// Hide the entire section if there are no actual products to show.
	// product-collection emits an empty wrapper when no related products exist —
	// showing the heading + empty space looks broken.
	if (!hasProducts(section)) {
		section.style.display = "none";
		return;
	}

	// Promote any data-src images to actual src lazily
	const lazyImages = section.querySelectorAll("img[data-src]");
	const reveal = () => {
		lazyImages.forEach((img) => {
			if (img.dataset.src) {
				img.src = img.dataset.src;
				img.removeAttribute("data-src");
			}
		});
		section.classList.add("is-loaded");
	};

	if (!("IntersectionObserver" in window)) {
		reveal();
		return;
	}

	const io = new IntersectionObserver(
		([entry]) => {
			if (entry.isIntersecting) {
				reveal();
				io.disconnect();
			}
		},
		{ rootMargin: "200px 0px" },
	);
	io.observe(section);
}

/**
 * Check if the related-products container has any actual product entries.
 * Our shortcode renders slim cards as `.sf__card--slim`. WC product-collection
 * fallbacks kept for resilience on themes that swap the renderer.
 */
function hasProducts(section) {
	const items = section.querySelectorAll(
		".sf__card, .wc-block-product-template > li, .wp-block-post-template > li, .wc-block-grid__product",
	);
	return items.length > 0;
}
