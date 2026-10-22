import { useCallback } from "react";

function formatPrice(priceStr, decimals = 2) {
	const num = parseInt(priceStr, 10) / 100;
	return `$${num.toFixed(decimals)}`;
}

export default function CartRow({ item, busy, onUpdateQty, onRemove }) {
	const img = item.images?.[0];
	const name = item.name;
	const variation = item.variation
		?.map((v) => v.value)
		.filter(Boolean)
		.join(", ");
	const lineTotal = formatPrice(item.totals.line_total);
	const qty = item.quantity;
	const maxQty = item.quantity_limits?.maximum ?? 99;
	const minQty = item.quantity_limits?.minimum ?? 1;

	const decrement = useCallback(() => {
		if (qty > minQty) onUpdateQty(item.key, qty - 1);
	}, [qty, minQty, item.key, onUpdateQty]);

	const increment = useCallback(() => {
		if (qty < maxQty) onUpdateQty(item.key, qty + 1);
	}, [qty, maxQty, item.key, onUpdateQty]);

	const remove = useCallback(() => {
		onRemove(item.key);
	}, [item.key, onRemove]);

	return (
		<div className={`zc-row${busy ? " is-busy" : ""}`}>
			{/* Image */}
			<div className="zc-row__img">
				{img ? (
					<img
						src={img.thumbnail || img.src}
						alt={img.alt || name}
						loading="lazy"
					/>
				) : (
					<div className="zc-row__img-placeholder" />
				)}
			</div>

			{/* Product info */}
			<div className="zc-row__info">
				<span className="zc-row__name">{name}</span>
				{variation && <span className="zc-row__variant">{variation}</span>}
			</div>

			{/* Quantity */}
			<div className="zc-row__qty">
				<button
					className="zc-row__qty-btn"
					onClick={decrement}
					disabled={busy || qty <= minQty}
					aria-label="Decrease quantity"
				>
					&ndash;
				</button>
				<span className="zc-row__qty-val">{qty}</span>
				<button
					className="zc-row__qty-btn"
					onClick={increment}
					disabled={busy || qty >= maxQty}
					aria-label="Increase quantity"
				>
					+
				</button>
			</div>

			{/* Price */}
			<span className="zc-row__price">{lineTotal}</span>

			{/* Remove */}
			<button
				className="zc-row__remove"
				onClick={remove}
				disabled={busy}
				aria-label={`Remove ${name}`}
			>
				&times;
			</button>
		</div>
	);
}
