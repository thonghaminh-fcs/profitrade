const steps = [
	{ num: 1, label: "Shopping cart" },
	{ num: 2, label: "Checkout details" },
	{ num: 3, label: "Order complete" },
];

export default function CartSteps({ current }) {
	return (
		<div className="zc-steps">
			{steps.map((step, i) => (
				<div
					key={step.num}
					className={`zc-steps__item${step.num === current ? " is-active" : ""}${step.num < current ? " is-done" : ""}`}
				>
					<span className="zc-steps__num">{step.num}</span>
					<span className="zc-steps__label">{step.label}</span>
					{i < steps.length - 1 && <span className="zc-steps__line" />}
				</div>
			))}
		</div>
	);
}
