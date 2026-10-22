export default function OrderSummary({
	cart,
	couponCode,
	couponError,
	couponNotice,
	onCouponChange,
	onApplyCoupon,
	onRemoveCoupon,
	onUpdateQty,
	onRemoveItem,
	busyKeys,
	placeOrderButton,
}) {
	const { items, totals, coupons } = cart;
	const currency = totals.currency_code || "USD";

	return (
		<aside className="zk__sidebar">
			<h3 className="zk__sidebar-title">Order summary</h3>

			<div className="zk__order-items">
				{items.map((item) => {
					const busy = busyKeys?.has(item.key);
					return (
						<div
							key={item.key}
							className="zk__order-item"
							style={busy ? { opacity: 0.5, pointerEvents: "none" } : undefined}
						>
							<div className="zk__order-item-img">
								{item.images?.[0] && (
									<img src={item.images[0].src} alt={item.name} />
								)}
							</div>
							<div className="zk__order-item-detail">
								<div className="zk__order-item-top">
									<span className="zk__order-item-name">{item.name}</span>
									<span className="zk__order-item-total">
										{formatPrice(item.totals?.line_total, currency)}
									</span>
								</div>
								<div className="zk__order-item-bottom">
									<span className="zk__order-item-meta">
										Quantity : {item.quantity}
									</span>
									<div className="zk__order-item-qty">
										<button
											className="zk__qty-btn"
											onClick={() =>
												item.quantity > 1
													? onUpdateQty(item.key, item.quantity - 1)
													: onRemoveItem(item.key)
											}
											aria-label="Decrease"
										>
											−
										</button>
										<span className="zk__qty-val">{item.quantity}</span>
										<button
											className="zk__qty-btn"
											onClick={() => onUpdateQty(item.key, item.quantity + 1)}
											aria-label="Increase"
										>
											+
										</button>
									</div>
								</div>
							</div>
						</div>
					);
				})}
			</div>

			{/* Coupon */}
			<div className="zk__coupon">
				<div className="zk__coupon-row">
					<input
						type="text"
						className="zk__input zk__input--sm"
						value={couponCode}
						onChange={(e) => onCouponChange(e.target.value)}
						placeholder="Coupon code"
						onKeyDown={(e) => e.key === "Enter" && onApplyCoupon()}
					/>
					<button
						className="zk__btn zk__btn--outline zk__btn--sm"
						onClick={onApplyCoupon}
						disabled={!couponCode.trim()}
					>
						Apply
					</button>
				</div>
				{couponError && <span className="zk__field-error">{couponError}</span>}
				{!couponError && couponNotice && (
					<span className="zk__field-success">{couponNotice}</span>
				)}

				{coupons?.length > 0 && (
					<div className="zk__coupon-tags">
						{coupons.map((c) => (
							<span key={c.code} className="zk__coupon-tag">
								{c.code}
								<button
									className="zk__coupon-remove"
									onClick={() => onRemoveCoupon(c.code)}
									aria-label={`Remove coupon ${c.code}`}
								>
									&times;
								</button>
							</span>
						))}
					</div>
				)}
			</div>

			{/* Totals */}
			<div className="zk__totals">
				<div className="zk__totals-row">
					<span>Subtotal</span>
					<span>{formatPrice(totals.total_items, currency)}</span>
				</div>

				{parseInt(totals.total_shipping, 10) > 0 && (
					<div className="zk__totals-row">
						<span>Shipping</span>
						<span>{formatPrice(totals.total_shipping, currency)}</span>
					</div>
				)}

				{parseInt(totals.total_tax, 10) > 0 && (
					<div className="zk__totals-row">
						<span>Tax</span>
						<span>{formatPrice(totals.total_tax, currency)}</span>
					</div>
				)}

				{parseInt(totals.total_discount, 10) > 0 && (
					<div className="zk__totals-row zk__totals-row--discount">
						<span>Discount</span>
						<span>-{formatPrice(totals.total_discount, currency)}</span>
					</div>
				)}

				<div className="zk__totals-row zk__totals-row--total">
					<span>Total</span>
					<span>{formatPrice(totals.total_price, currency)}</span>
				</div>
			</div>

			{placeOrderButton}
		</aside>
	);
}

function formatPrice(priceInCents, currency = "USD") {
	const amount = parseInt(priceInCents || "0", 10) / 100;

	try {
		return new Intl.NumberFormat("en-US", {
			style: "currency",
			currency,
		}).format(amount);
	} catch {
		return `$${amount.toFixed(2)}`;
	}
}
