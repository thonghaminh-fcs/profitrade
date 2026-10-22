/**
 * WooCommerce Store API — Checkout operations.
 *
 * Uses /wc/store/v1/checkout for placing orders
 * and /wc/store/v1/cart for reading cart data.
 */

const STORE_API = "/wp-json/wc/store/v1";

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

/**
 * Get current cart (items, totals, shipping, etc.)
 */
export function getCart() {
	return storeApiFetch(`${STORE_API}/cart`);
}

/**
 * Get available shipping methods for the cart.
 */
export function getShippingRates() {
	return storeApiFetch(`${STORE_API}/cart/shipping-rates`);
}

/**
 * Update customer shipping/billing address to get live rates.
 */
export function updateCustomer(billing, shipping) {
	return storeApiFetch(`${STORE_API}/cart/update-customer`, {
		method: "POST",
		body: JSON.stringify({
			billing_address: billing,
			shipping_address: shipping,
		}),
	});
}

/**
 * Select a shipping rate.
 */
export function selectShippingRate(packageId, rateId) {
	return storeApiFetch(`${STORE_API}/cart/select-shipping-rate`, {
		method: "POST",
		body: JSON.stringify({
			package_id: packageId,
			rate_id: rateId,
		}),
	});
}

/**
 * Apply a coupon code.
 */
export function applyCoupon(code) {
	return storeApiFetch(`${STORE_API}/cart/apply-coupon`, {
		method: "POST",
		body: JSON.stringify({ code }),
	});
}

/**
 * Remove a coupon code.
 */
export function removeCoupon(code) {
	return storeApiFetch(`${STORE_API}/cart/remove-coupon`, {
		method: "POST",
		body: JSON.stringify({ code }),
	});
}

/**
 * Place the order.
 */
export function placeOrder({ billing, shipping, paymentMethod, customerNote }) {
	return storeApiFetch(`${STORE_API}/checkout`, {
		method: "POST",
		body: JSON.stringify({
			billing_address: billing,
			shipping_address: shipping,
			payment_method: paymentMethod,
			customer_note: customerNote || "",
		}),
	});
}

/**
 * Update cart item quantity.
 */
export function updateItemQty(key, quantity) {
	return storeApiFetch(`${STORE_API}/cart/update-item`, {
		method: "POST",
		body: JSON.stringify({ key, quantity }),
	});
}

/**
 * Remove cart item.
 */
export function removeItem(key) {
	return storeApiFetch(`${STORE_API}/cart/remove-item`, {
		method: "POST",
		body: JSON.stringify({ key }),
	});
}

/**
 * Get available payment gateways (passed from PHP via wp_localize_script).
 */
export function getPaymentGateways() {
	const gateways = window.aiZippyCheckout?.paymentGateways || [];
	return Promise.resolve(gateways);
}
