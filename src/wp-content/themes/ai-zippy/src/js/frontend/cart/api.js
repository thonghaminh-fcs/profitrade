/**
 * WooCommerce Store API helpers for Cart operations.
 *
 * The Store API requires a Nonce header for all mutating requests.
 * The nonce is provided via wp_add_inline_script in functions.php
 * and refreshed from response headers after each request.
 */

const BASE = "/wp-json/wc/store/v1/cart";

function getNonce() {
	return window.wcBlocksMiddlewareConfig?.storeApiNonce || "";
}

function refreshNonce(res) {
	const newNonce = res.headers.get("Nonce");
	if (newNonce && window.wcBlocksMiddlewareConfig) {
		window.wcBlocksMiddlewareConfig.storeApiNonce = newNonce;
	}
}

function reqHeaders() {
	return {
		"Content-Type": "application/json",
		Nonce: getNonce(),
	};
}

async function storeApiFetch(url, options = {}) {
	const res = await fetch(url, {
		...options,
		headers: { ...reqHeaders(), ...options.headers },
	});
	refreshNonce(res);

	if (!res.ok) {
		const data = await res.json().catch(() => ({}));
		throw new Error(data.message || `Request failed (${res.status})`);
	}

	return res.json();
}

export function getCart() {
	return storeApiFetch(BASE);
}

export function updateItemQty(key, quantity) {
	return storeApiFetch(`${BASE}/update-item`, {
		method: "POST",
		body: JSON.stringify({ key, quantity }),
	});
}

export function removeItem(key) {
	return storeApiFetch(`${BASE}/remove-item`, {
		method: "POST",
		body: JSON.stringify({ key }),
	});
}

export function applyCoupon(code) {
	return storeApiFetch(`${BASE}/apply-coupon`, {
		method: "POST",
		body: JSON.stringify({ code }),
	});
}

export function removeCoupon(code) {
	return storeApiFetch(`${BASE}/remove-coupon`, {
		method: "POST",
		body: JSON.stringify({ code }),
	});
}

export async function clearCart(items) {
	for (const item of items) {
		await removeItem(item.key);
	}
	return getCart();
}
