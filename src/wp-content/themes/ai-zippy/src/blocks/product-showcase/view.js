/**
 * Frontend script for Product Showcase block.
 * Initializes Swiper for slider mode and thumbnail hover for all cards.
 */

document.addEventListener("DOMContentLoaded", () => {
	// Reveal blocks once ready
	document
		.querySelectorAll(".wp-block-ai-zippy-product-showcase")
		.forEach((block) => {
			block.classList.add("is-loaded");
		});

	// Thumbnail hover — switch main image. Targets ALL .sf__card instances on
	// the page so the showcase block + any other server-rendered shop cards
	// share the same interactivity. The shop-filter React app handles its own
	// cards internally, so its data-images attribute remains unused there.
	document.querySelectorAll(".sf__card[data-images]").forEach((card) => {
		const images = JSON.parse(card.dataset.images || "[]");
		const mainImg = card.querySelector(".sf__card-image > a img");
		const thumbs = card.querySelectorAll(".sf__card-thumb[data-index]");

		if (!mainImg || images.length <= 1) return;

		thumbs.forEach((thumb) => {
			thumb.addEventListener("mouseenter", () => {
				const idx = parseInt(thumb.dataset.index, 10);
				if (images[idx]) {
					mainImg.src = images[idx];
					thumbs.forEach((t) => t.classList.remove("is-active"));
					thumb.classList.add("is-active");
				}
			});
		});
	});

	// Wishlist toggle, persisted the same way the shop-filter React app does
	// (see useWishlist.js) — same localStorage key, so a product wishlisted
	// from either surface shows as active on the other.
	const WISHLIST_KEY = "shop-wishlist";

	const readWishlist = () => {
		try {
			const raw = JSON.parse(localStorage.getItem(WISHLIST_KEY) || "[]");
			return Array.isArray(raw) ? raw : [];
		} catch {
			return [];
		}
	};

	const setButtonState = (btn, isWishlisted) => {
		btn.classList.toggle("is-active", isWishlisted);
		btn.setAttribute("aria-pressed", String(isWishlisted));
		const svg = btn.querySelector("svg");
		if (svg) svg.setAttribute("fill", isWishlisted ? "currentColor" : "none");
	};

	document.querySelectorAll(".sf__card-wish[data-product-id]").forEach((btn) => {
		const productId = Number(btn.dataset.productId);
		setButtonState(btn, readWishlist().includes(productId));

		btn.addEventListener("click", (e) => {
			e.preventDefault();
			const ids = readWishlist();
			const index = ids.indexOf(productId);
			if (index === -1) {
				ids.push(productId);
			} else {
				ids.splice(index, 1);
			}
			localStorage.setItem(WISHLIST_KEY, JSON.stringify(ids));
			window.dispatchEvent(
				new CustomEvent("ai-zippy:wishlist-change", { detail: { ids } }),
			);
			setButtonState(btn, index === -1);
		});
	});

	// Swiper init for slider blocks
	initSwipers();
});

async function initSwipers() {
	const sliders = document.querySelectorAll(
		".ps--slider[data-swiper-config]",
	);
	if (sliders.length === 0) return;

	// Dynamically import Swiper (loaded via CDN for caching)
	const [{ default: Swiper }, { Navigation, Pagination, Autoplay }] =
		await Promise.all([
			import("https://cdn.jsdelivr.net/npm/swiper@11/swiper.min.mjs"),
			import("https://cdn.jsdelivr.net/npm/swiper@11/modules/index.min.mjs"),
		]);

	// Inject Swiper CSS
	if (!document.querySelector('link[href*="swiper"]')) {
		const link = document.createElement("link");
		link.rel = "stylesheet";
		link.href =
			"https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css";
		document.head.appendChild(link);
	}

	sliders.forEach((el) => {
		const config = JSON.parse(el.dataset.swiperConfig);
		const swiperEl = el.querySelector(".ps__swiper");
		if (!swiperEl) return;

		const options = {
			modules: [Navigation, Pagination, Autoplay],
			slidesPerView: 2,
			spaceBetween: 20,
			loop: true,
			pagination: {
				el: el.querySelector(".ps__pagination"),
				clickable: true,
			},
			navigation: {
				prevEl: el.querySelector(".ps__nav-prev"),
				nextEl: el.querySelector(".ps__nav-next"),
			},
			breakpoints: {
				480: { slidesPerView: 2 },
				768: { slidesPerView: Math.min(3, config.columns) },
				1024: { slidesPerView: config.columns },
			},
		};

		if (config.autoplay) {
			options.autoplay = {
				delay: config.autoplayDelay || 5000,
				disableOnInteraction: false,
			};
		}

		new Swiper(swiperEl, options);
	});
}
