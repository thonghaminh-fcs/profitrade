/**
 * Checkout form validators.
 */

export function validateEmail(email) {
	if (!email.trim()) return "Email is required";
	if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return "Invalid email address";
	return null;
}

export function validatePhone(phone) {
	if (!phone.trim()) return "Phone number is required";
	const cleaned = phone.replace(/[\s\-().]/g, "");
	if (!/^\+\d{7,15}$/.test(cleaned)) return "Invalid phone number";
	return null;
}

export function validateRequired(value, label) {
	if (!value.trim()) return `${label} is required`;
	return null;
}

/**
 * Validate contact section (step 1).
 */
export function validateContact({ fullName, email, phone }) {
	const errors = {};

	if (!fullName.trim()) errors.fullName = "Full name is required";
	const emailErr = validateEmail(email);
	if (emailErr) errors.email = emailErr;
	const phoneErr = validatePhone(phone);
	if (phoneErr) errors.phone = phoneErr;

	return Object.keys(errors).length ? errors : null;
}

/**
 * Validate an address (step 2).
 */
export function validateAddress(address) {
	const errors = {};

	const required = [
		["address_1", "Address"],
		["city", "City"],
		["country", "Country"],
	];

	required.forEach(([key, label]) => {
		const err = validateRequired(address[key] || "", label);
		if (err) errors[key] = err;
	});

	return errors;
}

/**
 * Full checkout validation before placing order.
 */
export function validateCheckout({ fullName, email, phone, billing, shipping, shipToDifferent }) {
	const errors = {};

	if (!fullName.trim()) errors.fullName = "Full name is required";

	const emailErr = validateEmail(email);
	if (emailErr) errors.email = emailErr;

	const phoneErr = validatePhone(phone);
	if (phoneErr) errors.phone = phoneErr;

	const billingErrors = validateAddress(billing);
	if (Object.keys(billingErrors).length) errors.billing = billingErrors;

	if (shipToDifferent) {
		const shippingErrors = validateAddress(shipping);
		if (Object.keys(shippingErrors).length) errors.shipping = shippingErrors;
	}

	return Object.keys(errors).length ? errors : null;
}
