import { useState } from "react";

function formatPrice(priceStr, decimals = 2) {
	const num = parseInt(priceStr, 10) / 100;
	return `$${num.toFixed(decimals)}`;
}

export default function CartSidebar({
	totals,
	coupons,
	checkoutUrl,
	onApplyCoupon,
	onRemoveCoupon,
}) {
	const [couponCode, setCouponCode] = useState("");
	const [couponError, setCouponError] = useState(null);
	const [applying, setApplying] = useState(false);

	const subtotal = formatPrice(totals.total_items);
	const discount = parseInt(totals.total_discount, 10);
	const total = formatPrice(totals.total_price);

	const handleApply = async (e) => {
		e.preventDefault();
		const code = couponCode.trim();
		if (!code) return;

		setApplying(true);
		setCouponError(null);
		const err = await onApplyCoupon(code);
		if (err) {
			setCouponError(err);
		} else {
			setCouponCode("");
		}
		setApplying(false);
	};

	return (
		<div className="zc-sidebar">
			{/*
			 * The Zippy CRM "Use your points" widget moved to the checkout
			 * page in plugin v1.13.0 — customers now decide redemption
			 * against the final number (with shipping/tax) rather than the
			 * cart subtotal. See CheckoutApp.jsx for the new mount point.
			 */}

			{/* Promo code */}
			<div className="zc-sidebar__promo">
				<h3 className="zc-sidebar__promo-title">Promo code</h3>
				<form className="zc-sidebar__promo-form" onSubmit={handleApply}>
					<input
						type="text"
						value={couponCode}
						onChange={(e) => setCouponCode(e.target.value)}
						placeholder="Type here..."
						disabled={applying}
					/>
					<button type="submit" disabled={applying || !couponCode.trim()}>
						{applying ? "..." : "Apply"}
					</button>
				</form>
				{couponError && (
					<p className="zc-sidebar__promo-error">{couponError}</p>
				)}
			</div>

			{/* Applied coupons */}
			{coupons?.length > 0 && (
				<div className="zc-sidebar__coupons">
					{coupons.map((c) => (
						<div key={c.code} className="zc-sidebar__coupon-tag">
							<span>{c.code}</span>
							<button
								onClick={() => onRemoveCoupon(c.code)}
								aria-label={`Remove coupon ${c.code}`}
							>
								&times;
							</button>
						</div>
					))}
				</div>
			)}

			{/* Totals */}
			<div className="zc-sidebar__totals">
				<div className="zc-sidebar__row">
					<span>Subtotal</span>
					<span>{subtotal}</span>
				</div>
				{discount > 0 && (
					<div className="zc-sidebar__row zc-sidebar__row--discount">
						<span>Discount</span>
						<span>-{formatPrice(totals.total_discount)}</span>
					</div>
				)}
				<div className="zc-sidebar__row zc-sidebar__row--total">
					<span>Total</span>
					<span>{total}</span>
				</div>
			</div>

			{/* Checkout button */}
			<a href={checkoutUrl} className="zc-sidebar__checkout">
				Continue to checkout
			</a>
		</div>
	);
}
