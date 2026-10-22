import { PhoneInput } from "react-international-phone";
import "react-international-phone/style.css";

const COUNTRIES = [
	{ code: "SG", label: "Singapore" },
	{ code: "MY", label: "Malaysia" },
	{ code: "VN", label: "Vietnam" },
	{ code: "TH", label: "Thailand" },
	{ code: "ID", label: "Indonesia" },
	{ code: "PH", label: "Philippines" },
	{ code: "US", label: "United States" },
	{ code: "GB", label: "United Kingdom" },
	{ code: "AU", label: "Australia" },
	{ code: "JP", label: "Japan" },
	{ code: "KR", label: "South Korea" },
	{ code: "CN", label: "China" },
	{ code: "IN", label: "India" },
	{ code: "HK", label: "Hong Kong" },
	{ code: "TW", label: "Taiwan" },
];

export default function ContactForm({
	fullName,
	onFullNameChange,
	email,
	onEmailChange,
	phone,
	onPhoneChange,
	country,
	onCountryChange,
	errors,
}) {
	return (
		<div className="zk__section-fields">
			<div className="zk__row">
				<div className="zk__field zk__field--half">
					<label className="zk__label zk__label--required" htmlFor="zk-name">Full name</label>
					<input
						id="zk-name"
						type="text"
						className={`zk__input${errors?.fullName ? " is-error" : ""}`}
						value={fullName}
						onChange={(e) => onFullNameChange(e.target.value)}
						autoComplete="name"
					/>
					{errors?.fullName && <span className="zk__field-error">{errors.fullName}</span>}
				</div>
				<div className="zk__field zk__field--half">
					<label className="zk__label zk__label--required" htmlFor="zk-email">Email address</label>
					<input
						id="zk-email"
						type="email"
						className={`zk__input${errors?.email ? " is-error" : ""}`}
						value={email}
						onChange={(e) => onEmailChange(e.target.value)}
						autoComplete="email"
					/>
					{errors?.email && <span className="zk__field-error">{errors.email}</span>}
				</div>
			</div>

			<div className="zk__row">
				<div className="zk__field zk__field--half">
					<label className="zk__label zk__label--required">Phone number</label>
					<PhoneInput
						defaultCountry="sg"
						preferredCountries={["sg", "my", "vn"]}
						value={phone}
						onChange={onPhoneChange}
						inputClassName={`zk__input${errors?.phone ? " is-error" : ""}`}
						className="zk__phone-input"
					/>
					{errors?.phone && <span className="zk__field-error">{errors.phone}</span>}
				</div>
				<div className="zk__field zk__field--half">
					<label className="zk__label zk__label--required">Country</label>
					<select
						className={`zk__select${errors?.country ? " is-error" : ""}`}
						value={country}
						onChange={(e) => onCountryChange(e.target.value)}
					>
						{COUNTRIES.map((c) => (
							<option key={c.code} value={c.code}>{c.label}</option>
						))}
					</select>
					{errors?.country && <span className="zk__field-error">{errors.country}</span>}
				</div>
			</div>
		</div>
	);
}
