export const WISHLIST_STORAGE_KEY = "shop-wishlist";
export const WISHLIST_CHANGE_EVENT = "ai-zippy:wishlist-change";

const MAX_ITEMS = 100;

function normalizeIds(ids) {
	if (!Array.isArray(ids)) return [];

	return [...new Set(ids.map(Number).filter((id) => Number.isInteger(id) && id > 0))]
		.slice(0, MAX_ITEMS);
}

export function readWishlistIds() {
	try {
		return normalizeIds(JSON.parse(localStorage.getItem(WISHLIST_STORAGE_KEY) || "[]"));
	} catch {
		return [];
	}
}

export function writeWishlistIds(ids) {
	const normalized = normalizeIds(ids);

	try {
		localStorage.setItem(WISHLIST_STORAGE_KEY, JSON.stringify(normalized));
	} catch {
		// Keep the in-memory UI responsive when storage is unavailable.
	}

	window.dispatchEvent(
		new CustomEvent(WISHLIST_CHANGE_EVENT, { detail: { ids: normalized } }),
	);

	return normalized;
}

export function toggleWishlistId(productId) {
	const id = Number(productId);
	const current = readWishlistIds();
	const next = current.includes(id)
		? current.filter((storedId) => storedId !== id)
		: [...current, id];

	return writeWishlistIds(next);
}

export function subscribeToWishlist(callback) {
	const handleChange = (event) => {
		if (event.type === "storage" && event.key !== WISHLIST_STORAGE_KEY) return;
		callback(readWishlistIds());
	};

	window.addEventListener(WISHLIST_CHANGE_EVENT, handleChange);
	window.addEventListener("storage", handleChange);

	return () => {
		window.removeEventListener(WISHLIST_CHANGE_EVENT, handleChange);
		window.removeEventListener("storage", handleChange);
	};
}
