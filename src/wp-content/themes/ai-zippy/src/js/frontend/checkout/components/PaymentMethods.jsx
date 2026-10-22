import { useState, useEffect } from "react";
import { getPaymentGateways } from "../api.js";

export default function PaymentMethods({ selected, onSelect }) {
	const [methods, setMethods] = useState([]);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		getPaymentGateways()
			.then((gateways) => {
				setMethods(gateways);
				// Auto-select first if current selection is not available
				if (gateways.length && !gateways.find((g) => g.id === selected)) {
					onSelect(gateways[0].id);
				}
			})
			.catch(() => {})
			.finally(() => setLoading(false));
	}, []);

	if (loading) {
		return (
			<fieldset className="zk__section">
				<legend className="zk__section-title">Payment</legend>
				<div className="zk__skeleton-row" style={{ height: 56 }} />
			</fieldset>
		);
	}

	if (!methods.length) return null;

	return (
		<fieldset className="zk__section">
			<legend className="zk__section-title">Payment</legend>

			<div className="zk__radio-group">
				{methods.map((method) => (
					<label
						key={method.id}
						className={`zk__radio-option${selected === method.id ? " is-selected" : ""}`}
					>
						<input
							type="radio"
							name="payment"
							checked={selected === method.id}
							onChange={() => onSelect(method.id)}
							className="zk__radio-input"
						/>
						<div className="zk__radio-body">
							<span className="zk__radio-label">{method.title}</span>
							{selected === method.id && method.description && (
								<span className="zk__radio-desc">{method.description}</span>
							)}
						</div>
					</label>
				))}
			</div>
		</fieldset>
	);
}
