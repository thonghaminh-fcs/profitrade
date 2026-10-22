import CartRow from "./CartRow.jsx";

export default function CartItems({
	items,
	itemCount,
	busyKeys,
	loading,
	onUpdateQty,
	onRemove,
	onClearCart,
}) {
	return (
		<div className={`zc-items${loading ? " is-loading" : ""}`}>
			{/* Header: title + clear */}
			<div className="zc-items__header">
				<h2 className="zc-items__title">
					Cart{" "}
					<span className="zc-items__count">
						({itemCount} {itemCount === 1 ? "product" : "products"})
					</span>
				</h2>
				<button
					className="zc-items__clear"
					onClick={onClearCart}
					type="button"
				>
					<span aria-hidden="true">&times;</span> Clear cart
				</button>
			</div>

			{/* Column labels */}
			<div className="zc-items__cols">
				<span className="zc-items__col-product">Product</span>
				<span className="zc-items__col-count">Count</span>
				<span className="zc-items__col-price">Price</span>
				<span className="zc-items__col-remove" />
			</div>

			{/* Item rows */}
			<div className="zc-items__list">
				{items.map((item) => (
					<CartRow
						key={item.key}
						item={item}
						busy={busyKeys.has(item.key)}
						onUpdateQty={onUpdateQty}
						onRemove={onRemove}
					/>
				))}
			</div>
		</div>
	);
}
