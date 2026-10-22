// =============================================================================
// Tiny custom lightbox for the WC product image gallery (~2 KB)
// =============================================================================
// Hooks into WC's gallery markup. On image click → opens a full-screen overlay
// with prev/next nav, ESC to close, click-outside to close, swipe on mobile.
// No external library.
// =============================================================================

const SELECTORS = [
	".wc-block-product-image-gallery img",
	".woocommerce-product-gallery__image img",
	".wp-block-woocommerce-product-image-gallery img",
];

export function initLightbox() {
	const images = collectImages();
	if (images.length === 0) return;

	let overlay, imgEl, idx = 0;
	let touchStartX = 0;

	function build() {
		overlay = document.createElement("div");
		overlay.className = "zp-lightbox";
		overlay.setAttribute("role", "dialog");
		overlay.setAttribute("aria-modal", "true");
		overlay.innerHTML = `
			<button class="zp-lightbox__close" aria-label="Close">×</button>
			<button class="zp-lightbox__nav zp-lightbox__nav--prev" aria-label="Previous image">‹</button>
			<button class="zp-lightbox__nav zp-lightbox__nav--next" aria-label="Next image">›</button>
			<div class="zp-lightbox__stage">
				<img class="zp-lightbox__img" alt="" />
			</div>
			<div class="zp-lightbox__counter"></div>
		`;
		document.body.appendChild(overlay);
		imgEl = overlay.querySelector(".zp-lightbox__img");

		overlay.querySelector(".zp-lightbox__close").addEventListener("click", close);
		overlay.querySelector(".zp-lightbox__nav--prev").addEventListener("click", prev);
		overlay.querySelector(".zp-lightbox__nav--next").addEventListener("click", next);

		overlay.addEventListener("click", (e) => {
			if (e.target === overlay) close();
		});

		// Swipe (mobile)
		overlay.addEventListener("touchstart", (e) => {
			touchStartX = e.touches[0].clientX;
		}, { passive: true });
		overlay.addEventListener("touchend", (e) => {
			const dx = e.changedTouches[0].clientX - touchStartX;
			if (Math.abs(dx) > 60) (dx > 0 ? prev : next)();
		}, { passive: true });
	}

	function open(i) {
		idx = i;
		if (!overlay) build();
		render();
		document.body.classList.add("zp-lightbox-open");
		// eslint-disable-next-line no-unused-expressions
		overlay.offsetHeight; // trigger reflow before adding open class
		overlay.classList.add("is-open");
		document.addEventListener("keydown", onKey);
	}

	function close() {
		if (!overlay) return;
		overlay.classList.remove("is-open");
		document.body.classList.remove("zp-lightbox-open");
		document.removeEventListener("keydown", onKey);
	}

	function prev() {
		idx = (idx - 1 + images.length) % images.length;
		render();
	}

	function next() {
		idx = (idx + 1) % images.length;
		render();
	}

	function render() {
		const item = images[idx];
		imgEl.src = item.full || item.src;
		imgEl.alt = item.alt || "";
		const counter = overlay.querySelector(".zp-lightbox__counter");
		if (counter) counter.textContent = `${idx + 1} / ${images.length}`;
		const navs = overlay.querySelectorAll(".zp-lightbox__nav");
		navs.forEach((b) => b.style.display = images.length > 1 ? "" : "none");
	}

	function onKey(e) {
		if (e.key === "Escape") close();
		else if (e.key === "ArrowLeft")  prev();
		else if (e.key === "ArrowRight") next();
	}

	images.forEach((item, i) => {
		item.el.addEventListener("click", (e) => {
			e.preventDefault();
			open(i);
		});
		item.el.style.cursor = "zoom-in";
	});
}

function collectImages() {
	const found = [];
	const seen = new Set();
	for (const sel of SELECTORS) {
		document.querySelectorAll(sel).forEach((img) => {
			if (seen.has(img)) return;
			seen.add(img);
			// Try to get full-size from parent <a href> first; fall back to src
			const link = img.closest("a");
			const full = (link && /\.(jpe?g|png|webp|gif|avif)/i.test(link.href)) ? link.href : null;
			found.push({
				el: img,
				src: img.currentSrc || img.src,
				full,
				alt: img.alt,
			});
		});
		if (found.length) break; // first matching selector wins
	}
	return found;
}
