export default function AddressForm({ address, onChange, errors = {} }) {
	function update(key, value) {
		onChange({ ...address, [key]: value });
	}

	return (
		<div className="zk__section-fields">
			<div className="zk__field">
				<label className="zk__label">Company (optional)</label>
				<input
					className="zk__input"
					value={address.company}
					onChange={(e) => update("company", e.target.value)}
					autoComplete="organization"
				/>
			</div>

			<div className="zk__field">
				<label className="zk__label zk__label--required">Address</label>
				<input
					className={`zk__input${errors?.address_1 ? " is-error" : ""}`}
					value={address.address_1}
					onChange={(e) => update("address_1", e.target.value)}
					placeholder="Street address"
					autoComplete="address-line1"
				/>
				{errors?.address_1 && <span className="zk__field-error">{errors.address_1}</span>}
			</div>

			<div className="zk__field">
				<input
					className="zk__input"
					value={address.address_2}
					onChange={(e) => update("address_2", e.target.value)}
					placeholder="Apartment, suite, unit, etc. (optional)"
					autoComplete="address-line2"
				/>
			</div>

			<div className="zk__row">
				<div className="zk__field zk__field--half">
					<label className="zk__label zk__label--required">City</label>
					<input
						className={`zk__input${errors?.city ? " is-error" : ""}`}
						value={address.city}
						onChange={(e) => update("city", e.target.value)}
						autoComplete="address-level2"
					/>
					{errors?.city && <span className="zk__field-error">{errors.city}</span>}
				</div>
				<div className="zk__field zk__field--half">
					<label className="zk__label">Postal code</label>
					<input
						className="zk__input"
						value={address.postcode}
						onChange={(e) => update("postcode", e.target.value)}
						autoComplete="postal-code"
					/>
				</div>
			</div>

			<div className="zk__field">
				<label className="zk__label">State / Province (optional)</label>
				<input
					className="zk__input"
					value={address.state}
					onChange={(e) => update("state", e.target.value)}
					autoComplete="address-level1"
				/>
			</div>
		</div>
	);
}
