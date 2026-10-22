import {
	readWishlistIds,
	subscribeToWishlist,
} from "../core/wishlist-storage.js";

export function initWishlist() {
	const links = document.querySelectorAll(".az-header-wishlist");
	if (links.length === 0) return;

	const updateCount = (ids = readWishlistIds()) => {
		links.forEach((link) => {
			const count = link.querySelector("[data-wishlist-count]");
			if (!count) return;

			count.textContent = String(ids.length);
			count.hidden = ids.length === 0;
			link.setAttribute(
				"aria-label",
				ids.length === 1 ? "Wishlist (1 item)" : `Wishlist (${ids.length} items)`,
			);
		});
	};

	updateCount();
	subscribeToWishlist(updateCount);
}
