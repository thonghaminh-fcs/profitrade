export default function OrderConfirmation({ order }) {
	return (
		<div className="zk__confirmed">
			<div className="zk__confirmed-icon">✓</div>
			<h2 className="zk__confirmed-title">Thank you for your order!</h2>
			<p className="zk__confirmed-text">
				Order <strong>#{order.order_id}</strong> has been placed successfully.
			</p>

			{order.status && (
				<p className="zk__confirmed-status">
					Status: <strong>{order.status}</strong>
				</p>
			)}

			{order.payment_result?.redirect_url && (
				<a
					href={order.payment_result.redirect_url}
					className="zk__btn zk__btn--primary"
				>
					View order details
				</a>
			)}
		</div>
	);
}
