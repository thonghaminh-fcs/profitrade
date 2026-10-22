export default function ShippingMethods({ packages, onSelect }) {
	if (!packages?.length) return null;

	return (
		<fieldset className="zk__section">
			<legend className="zk__section-title">Shipping method</legend>

			{packages.map((pkg, pkgIdx) => (
				<div key={pkg.package_id ?? pkgIdx} className="zk__shipping-pkg">
					{packages.length > 1 && (
						<div className="zk__shipping-pkg-name">{pkg.name || `Package ${pkgIdx + 1}`}</div>
					)}

					<div className="zk__radio-group">
						{pkg.shipping_rates?.map((rate) => {
							const selected = rate.selected;
							const price = formatPrice(rate.price, rate.currency_code);

							return (
								<label
									key={rate.rate_id}
									className={`zk__radio-option${selected ? " is-selected" : ""}`}
								>
									<input
										type="radio"
										name={`shipping-${pkg.package_id ?? pkgIdx}`}
										checked={selected}
										onChange={() => onSelect(pkg.package_id ?? pkgIdx, rate.rate_id)}
										className="zk__radio-input"
									/>
									<div className="zk__radio-body">
										<span className="zk__radio-label">{rate.name}</span>
										{rate.delivery_time?.value && (
											<span className="zk__radio-desc">{rate.delivery_time.value}</span>
										)}
									</div>
									<span className="zk__radio-price">
										{price === "$0.00" || price === "Free" ? "Free" : price}
									</span>
								</label>
							);
						})}
					</div>
				</div>
			))}
		</fieldset>
	);
}

function formatPrice(priceInCents, currency = "USD") {
	const amount = parseInt(priceInCents, 10) / 100;
	if (amount === 0) return "Free";

	try {
		return new Intl.NumberFormat("en-US", {
			style: "currency",
			currency,
		}).format(amount);
	} catch {
		return `$${amount.toFixed(2)}`;
	}
}
