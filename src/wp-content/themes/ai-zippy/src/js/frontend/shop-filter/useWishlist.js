import { useCallback, useEffect, useState } from "react";
import {
	readWishlistIds,
	subscribeToWishlist,
	toggleWishlistId,
	writeWishlistIds,
} from "../core/wishlist-storage.js";

/**
 * Wishlist state backed by the shared browser storage used across product cards,
 * the header count and the dedicated wishlist page.
 */
export default function useWishlist() {
	const [ids, setIds] = useState(readWishlistIds);

	useEffect(() => subscribeToWishlist(setIds), []);

	const toggle = useCallback((productId) => {
		const next = toggleWishlistId(productId);
		setIds(next);
		return next.includes(Number(productId));
	}, []);

	const remove = useCallback((productId) => {
		const id = Number(productId);
		const next = writeWishlistIds(
			readWishlistIds().filter((storedId) => storedId !== id),
		);
		setIds(next);
	}, []);

	const clear = useCallback(() => {
		setIds(writeWishlistIds([]));
	}, []);

	const isWishlisted = useCallback(
		(productId) => ids.includes(Number(productId)),
		[ids],
	);

	return { ids, isWishlisted, toggle, remove, clear };
}
