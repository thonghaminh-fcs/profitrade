export default function CartEmpty({ shopUrl }) {
	return (
		<div className="zc-empty">
			<div className="zc-empty__icon">
				<svg
					width="80"
					height="80"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					strokeWidth="1.5"
				>
					<circle cx="9" cy="21" r="1" />
					<circle cx="20" cy="21" r="1" />
					<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
				</svg>
			</div>
			<h2 className="zc-empty__title">Your cart is empty</h2>
			<p className="zc-empty__text">
				Looks like you haven't added anything yet. Browse our menu and find
				something you love!
			</p>
			<div className="zc-empty__actions">
				<a href={shopUrl} className="zc-empty__btn zc-empty__btn--primary">
					Browse Menu
				</a>
				<a href="/" className="zc-empty__btn zc-empty__btn--secondary">
					Back to Home
				</a>
			</div>
		</div>
	);
}
