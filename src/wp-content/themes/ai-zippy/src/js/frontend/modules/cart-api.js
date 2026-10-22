/**
 * WooCommerce Store API — Client-side cart operations.
 *
 * Uses the WC Store API (no nonce needed for public endpoints).
 * Docs: https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/src/StoreApi/docs/
 */

import { azRestUrl } from "../core/config.js";

/** Route, not a URL — azRestUrl() resolves it against this site's REST base. */
const STORE_API = "wc/store/v1";

function getNonce() {
	return window.wcBlocksMiddlewareConfig?.storeApiNonce || "";
}

function headers() {
	return {
		"Content-Type": "application/json",
		Nonce: getNonce(),
	};
}

/**
 * Add a product to the cart.
 *
 * For variable products: pass `id` as the **parent** product ID and supply
 * `variation` as an array of { attribute, value } pairs. The Store API will
 * resolve the right variation server-side. Passing the variation_id directly
 * triggers "Missing attributes for variable product."
 *
 * @param {number} productId
 * @param {number} quantity
 * @param {Array<{attribute:string,value:string}>} variation
 */
export async function addToCart(productId, quantity = 1, variation = []) {
	const body = { id: productId, quantity };
	if (variation && variation.length > 0) {
		body.variation = variation;
	}

	const res = await fetch(azRestUrl(`${STORE_API}/cart/add-item`), {
		method: "POST",
		headers: headers(),
		body: JSON.stringify(body),
	});

	if (!res.ok) {
		const err = await res.json().catch(() => ({}));
		throw new Error(err.message || "Failed to add to cart");
	}

	return res.json();
}

/**
 * Update item quantity in cart.
 */
export async function updateCartItem(itemKey, quantity) {
	const res = await fetch(azRestUrl(`${STORE_API}/cart/update-item`), {
		method: "POST",
		headers: headers(),
		body: JSON.stringify({ key: itemKey, quantity }),
	});

	if (!res.ok) throw new Error("Failed to update item");
	return res.json();
}

/**
 * Remove an item from the cart.
 */
export async function removeCartItem(itemKey) {
	const res = await fetch(azRestUrl(`${STORE_API}/cart/remove-item`), {
		method: "POST",
		headers: headers(),
		body: JSON.stringify({ key: itemKey }),
	});

	if (!res.ok) throw new Error("Failed to remove item");
	return res.json();
}

/**
 * Get current cart.
 */
export async function getCart() {
	const res = await fetch(azRestUrl(`${STORE_API}/cart`), { headers: headers() });
	if (!res.ok) throw new Error("Failed to fetch cart");
	return res.json();
}
