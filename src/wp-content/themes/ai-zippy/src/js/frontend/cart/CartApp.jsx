import { useState, useEffect, useCallback } from "react";
import { getCart, updateItemQty, removeItem, applyCoupon, removeCoupon, clearCart } from "./api.js";
import CartSteps from "./components/CartSteps.jsx";
import CartItems from "./components/CartItems.jsx";
import CartSidebar from "./components/CartSidebar.jsx";
import CartEmpty from "./components/CartEmpty.jsx";

export default function CartApp({ checkoutUrl, shopUrl }) {
	const [cart, setCart] = useState(null);
	const [loading, setLoading] = useState(true);
	const [busyKeys, setBusyKeys] = useState(new Set());
	const [error, setError] = useState(null);

	useEffect(() => {
		if (cart) window.dispatchEvent(new CustomEvent("ai-zippy-cart-updated", { detail: cart }));
	}, [cart]);

	// Load cart
	useEffect(() => {
		getCart()
			.then(setCart)
			.catch(() => setError("Failed to load cart"))
			.finally(() => setLoading(false));
	}, []);

	// Note: the Zippy CRM points-tender listener moved to CheckoutApp in
	// plugin v1.13.0. Cart no longer hosts the redemption widget, so there's
	// nothing for this surface to subscribe to.

	const markBusy = useCallback((key, busy) => {
		setBusyKeys((prev) => {
			const next = new Set(prev);
			busy ? next.add(key) : next.delete(key);
			return next;
		});
	}, []);

	const handleUpdateQty = useCallback(async (key, qty) => {
		markBusy(key, true);
		try {
			const updated = await updateItemQty(key, qty);
			setCart(updated);
		} catch {
			setError("Failed to update quantity");
		} finally {
			markBusy(key, false);
		}
	}, [markBusy]);

	const handleRemove = useCallback(async (key) => {
		markBusy(key, true);
		try {
			const updated = await removeItem(key);
			setCart(updated);
		} catch {
			setError("Failed to remove item");
		} finally {
			markBusy(key, false);
		}
	}, [markBusy]);

	const handleClearCart = useCallback(async () => {
		if (!cart?.items?.length) return;
		setLoading(true);
		try {
			const updated = await clearCart(cart.items);
			setCart(updated);
		} catch {
			setError("Failed to clear cart");
		} finally {
			setLoading(false);
		}
	}, [cart]);

	const handleApplyCoupon = useCallback(async (code) => {
		try {
			const updated = await applyCoupon(code);
			setCart(updated);
			return null;
		} catch (err) {
			return err.message;
		}
	}, []);

	const handleRemoveCoupon = useCallback(async (code) => {
		try {
			const updated = await removeCoupon(code);
			setCart(updated);
		} catch {
			setError("Failed to remove coupon");
		}
	}, []);

	// Dismiss error
	useEffect(() => {
		if (!error) return;
		const t = setTimeout(() => setError(null), 4000);
		return () => clearTimeout(t);
	}, [error]);

	// Loading skeleton
	if (loading && !cart) {
		return (
			<div className="zc">
				<CartSteps current={1} />
				<div className="zc__skeleton">
					<div className="zc__skeleton-items">
						{[1, 2, 3].map((i) => (
							<div key={i} className="zc__skeleton-row" />
						))}
					</div>
					<div className="zc__skeleton-sidebar" />
				</div>
			</div>
		);
	}

	// Empty cart
	if (!cart?.items?.length) {
		return (
			<div className="zc">
				<CartSteps current={1} />
				<CartEmpty shopUrl={shopUrl} />
			</div>
		);
	}

	const itemCount = cart.items.reduce((sum, item) => sum + item.quantity, 0);

	return (
		<div className="zc">
			{error && <div className="zc__error">{error}</div>}

			<CartSteps current={1} />

			<div className="zc__layout">
				<CartItems
					items={cart.items}
					itemCount={itemCount}
					busyKeys={busyKeys}
					loading={loading}
					onUpdateQty={handleUpdateQty}
					onRemove={handleRemove}
					onClearCart={handleClearCart}
				/>
				<CartSidebar
					totals={cart.totals}
					coupons={cart.coupons}
					checkoutUrl={checkoutUrl}
					onApplyCoupon={handleApplyCoupon}
					onRemoveCoupon={handleRemoveCoupon}
				/>
			</div>
		</div>
	);
}
