import { useState, useEffect, useCallback } from "react";
import { getCart, updateCustomer, selectShippingRate, placeOrder, applyCoupon, removeCoupon, updateItemQty, removeItem } from "./api.js";
import { validateContact, validateAddress, validateCheckout } from "./validators.js";
import CartSteps from "../cart/components/CartSteps.jsx";
import CheckoutSection from "./components/CheckoutSection.jsx";
import ContactForm from "./components/ContactForm.jsx";
import AddressForm from "./components/AddressForm.jsx";
import ShippingMethods from "./components/ShippingMethods.jsx";
import PaymentMethods from "./components/PaymentMethods.jsx";
import OrderSummary from "./components/OrderSummary.jsx";
import OrderConfirmation from "./components/OrderConfirmation.jsx";

// WC config from PHP
const wcConfig = window.aiZippyCheckout || {};
const shippingEnabled = wcConfig.shippingEnabled ?? true;
const shipTo = wcConfig.shipToDestination || "billing_only";
const allowDifferentShipping = shippingEnabled && shipTo === "shipping";

const EMPTY_ADDRESS = {
	first_name: "",
	last_name: "",
	company: "",
	address_1: "",
	address_2: "",
	city: "",
	state: "",
	postcode: "",
	country: "SG",
	phone: "",
};

/**
 * Split "John Doe" into { first_name, last_name } for WC.
 * WC Store API requires last_name to be non-empty.
 * Single-word names: first_name = name, last_name = name (duplicate is fine).
 */
function splitFullName(fullName) {
	const parts = fullName.trim().split(/\s+/);
	if (parts.length === 0) return { first_name: "", last_name: "" };
	if (parts.length === 1) return { first_name: parts[0], last_name: parts[0] };
	const last_name = parts.pop();
	return { first_name: parts.join(" "), last_name };
}

/**
 * Join first + last into display name.
 */
function joinName(first, last) {
	return [first, last].filter(Boolean).join(" ");
}

export default function CheckoutApp({ cartUrl, shopUrl }) {
	const [cart, setCart] = useState(null);
	const [loading, setLoading] = useState(true);
	const [placing, setPlacing] = useState(false);
	const [error, setError] = useState(null);

	// Section: "contact" | "billing" | "payment"
	const [activeSection, setActiveSection] = useState("contact");

	// Form state
	const [fullName, setFullName] = useState("");
	const [email, setEmail] = useState("");
	const [phone, setPhone] = useState("");
	const [billing, setBilling] = useState({ ...EMPTY_ADDRESS });
	const [shipping, setShipping] = useState({ ...EMPTY_ADDRESS });
	const [shipToDifferent, setShipToDifferent] = useState(false);
	const [paymentMethod, setPaymentMethod] = useState("cod");
	const [customerNote, setCustomerNote] = useState("");
	const [couponCode, setCouponCode] = useState("");
	const [couponError, setCouponError] = useState(null);
	const [couponNotice, setCouponNotice] = useState(null);
	const [fieldErrors, setFieldErrors] = useState({});
	const [busyKeys, setBusyKeys] = useState(new Set());

	useEffect(() => {
		if (cart) window.dispatchEvent(new CustomEvent("ai-zippy-cart-updated", { detail: cart }));
	}, [cart]);

	// Track which sections have been completed
	const [completedSections, setCompletedSections] = useState(new Set());

	// Order result
	const [orderResult, setOrderResult] = useState(null);

	// Load cart on mount
	useEffect(() => {
		getCart()
			.then((c) => {
				setCart(c);
				const ba = c.billing_address || {};
				const sa = c.shipping_address || {};

				if (ba.email) setEmail(ba.email);
				if (ba.phone) setPhone(ba.phone);
				if (ba.first_name) {
					setFullName(joinName(ba.first_name, ba.last_name));
					setBilling((prev) => ({ ...prev, ...ba }));
				}
				if (sa.first_name) {
					setShipping((prev) => ({ ...prev, ...sa }));
				}

				// If user already has contact info, mark as completed and jump ahead
				if (ba.email && ba.first_name) {
					setCompletedSections((prev) => new Set([...prev, "contact"]));
					if (ba.address_1 && ba.city && ba.country) {
						setCompletedSections((prev) => new Set([...prev, "contact", "billing"]));
						setActiveSection("payment");
					} else {
						setActiveSection("billing");
					}
				}
			})
			.catch(() => setError("Failed to load cart"))
			.finally(() => setLoading(false));
	}, []);

	// Listen for the Zippy CRM points-tender widget. When the user applies or
	// removes points, the widget dispatches `zippy-crm:tender-changed`; we
	// refetch the cart so the new fee line shows up in the OrderSummary.
	useEffect(() => {
		const refresh = () => {
			getCart()
				.then((c) => setCart(c))
				.catch(() => { /* surface via existing error path on next user action */ });
		};
		window.addEventListener("zippy-crm:tender-changed", refresh);
		return () => window.removeEventListener("zippy-crm:tender-changed", refresh);
	}, []);

	// Dismiss errors
	useEffect(() => {
		if (!error) return;
		const t = setTimeout(() => setError(null), 5000);
		return () => clearTimeout(t);
	}, [error]);

	// Section state helper
	function sectionState(name) {
		if (activeSection === name) return "editing";
		if (completedSections.has(name)) return "completed";
		return "upcoming";
	}

	// --- Section: Contact ---
	const handleContactContinue = useCallback(() => {
		const errors = validateContact({ fullName, email, phone });
		if (errors) {
			setFieldErrors(errors);
			return;
		}
		setFieldErrors({});

		// Sync name into billing
		const { first_name, last_name } = splitFullName(fullName);
		setBilling((prev) => ({ ...prev, first_name, last_name }));
		setShipping((prev) => ({ ...prev, first_name, last_name }));

		setCompletedSections((prev) => new Set([...prev, "contact"]));
		setActiveSection("billing");
	}, [fullName, email, phone]);

	// --- Section: Billing ---
	const handleBillingCountryChange = useCallback(async (country) => {
		setBilling((prev) => ({ ...prev, country }));
		try {
			const addr = { ...billing, country };
			const updated = await updateCustomer(addr, shipToDifferent ? shipping : addr);
			setCart(updated);
		} catch { /* ignore */ }
	}, [billing, shipping, shipToDifferent]);

	const handleShippingCountryChange = useCallback(async (country) => {
		setShipping((prev) => ({ ...prev, country }));
		try {
			const updated = await updateCustomer(billing, { ...shipping, country });
			setCart(updated);
		} catch { /* ignore */ }
	}, [billing, shipping]);

	const handleBillingContinue = useCallback(() => {
		const errors = validateAddress(billing);
		if (Object.keys(errors).length) {
			setFieldErrors((prev) => ({ ...prev, billing: errors }));
			return;
		}
		setFieldErrors((prev) => ({ ...prev, billing: undefined }));
		setCompletedSections((prev) => new Set([...prev, "billing"]));
		setActiveSection("payment");
	}, [billing]);

	const handleSelectShipping = useCallback(async (packageId, rateId) => {
		try {
			const updated = await selectShippingRate(packageId, rateId);
			setCart(updated);
		} catch {
			setError("Failed to select shipping method");
		}
	}, []);

	// --- Coupon ---
	// The Store API's apply-coupon / remove-coupon endpoints return the COUPON
	// object, not the cart. We must refetch the cart afterwards to get the
	// updated totals + coupon list. Also surface a success message so the user
	// sees their coupon was actually applied.
	const handleApplyCoupon = useCallback(async () => {
		if (!couponCode.trim()) return;
		setCouponError(null);
		setCouponNotice(null);
		try {
			await applyCoupon(couponCode.trim());
			const refreshed = await getCart();
			setCart(refreshed);
			setCouponNotice(`Coupon "${couponCode.trim()}" applied`);
			setCouponCode("");
		} catch (err) {
			setCouponError(err.message);
		}
	}, [couponCode]);

	const handleRemoveCoupon = useCallback(async (code) => {
		try {
			await removeCoupon(code);
			const refreshed = await getCart();
			setCart(refreshed);
			setCouponNotice(null);
		} catch {
			setError("Failed to remove coupon");
		}
	}, []);

	// --- Cart item quantity ---
	const markBusy = useCallback((key, busy) => {
		setBusyKeys((prev) => {
			const next = new Set(prev);
			busy ? next.add(key) : next.delete(key);
			return next;
		});
	}, []);

	const handleUpdateQty = useCallback(async (key, qty) => {
		markBusy(key, true);
		try {
			const updated = await updateItemQty(key, qty);
			setCart(updated);
		} catch {
			setError("Failed to update quantity");
		} finally {
			markBusy(key, false);
		}
	}, [markBusy]);

	const handleRemoveItem = useCallback(async (key) => {
		markBusy(key, true);
		try {
			const updated = await removeItem(key);
			setCart(updated);
		} catch {
			setError("Failed to remove item");
		} finally {
			markBusy(key, false);
		}
	}, [markBusy]);

	// --- Place order ---
	const handlePlaceOrder = useCallback(async () => {
		setFieldErrors({});
		const { first_name, last_name } = splitFullName(fullName);
		const billingData = { ...billing, first_name, last_name, email, phone };
		const shippingData = shipToDifferent ? { ...shipping, first_name, last_name } : billingData;

		const errors = validateCheckout({
			fullName,
			email,
			phone,
			billing: billingData,
			shipping: shippingData,
			shipToDifferent,
		});

		if (errors) {
			setFieldErrors(errors);
			setError("Please fix the errors below");
			// Jump to first section with errors
			if (errors.fullName || errors.email || errors.phone) setActiveSection("contact");
			else if (errors.billing) setActiveSection("billing");
			return;
		}

		setPlacing(true);
		setError(null);

		try {
			const result = await placeOrder({
				billing: billingData,
				shipping: shippingData,
				paymentMethod,
				customerNote,
			});
			setOrderResult(result);
		} catch (err) {
			setError(err.message || "Failed to place order");
		} finally {
			setPlacing(false);
		}
	}, [billing, shipping, fullName, email, phone, shipToDifferent, paymentMethod, customerNote]);

	// --- Summaries ---
	const contactSummary = (
		<div className="zk__box-summary-lines">
			<span>{fullName}</span>
			<span>{email}</span>
			<span>{phone}</span>
			<span>{billing.country}</span>
		</div>
	);

	const billingSummary = (
		<div className="zk__box-summary-lines">
			<span>{[billing.address_1, billing.address_2].filter(Boolean).join(", ")}</span>
			<span>{[billing.city, billing.state, billing.postcode].filter(Boolean).join(", ")}</span>
			<span>{billing.country}</span>
		</div>
	);

	// --- Render ---
	if (loading && !cart) {
		return (
			<div className="zk">
				<CartSteps current={2} />
				<div className="zk__skeleton">
					<div className="zk__skeleton-form">
						{[1, 2, 3].map((i) => <div key={i} className="zk__skeleton-row" />)}
					</div>
					<div className="zk__skeleton-sidebar" />
				</div>
			</div>
		);
	}

	if (!cart?.items?.length && !orderResult) {
		return (
			<div className="zk">
				<CartSteps current={2} />
				<div className="zk__empty">
					<h2>Your cart is empty</h2>
					<p>Add some products before checking out.</p>
					<a href={shopUrl} className="zk__btn zk__btn--primary">Continue shopping</a>
				</div>
			</div>
		);
	}

	if (orderResult) {
		return (
			<div className="zk">
				<CartSteps current={3} />
				<OrderConfirmation order={orderResult} />
			</div>
		);
	}

	return (
		<div className="zk">
			{error && <div className="zk__error">{error}</div>}

			<CartSteps current={2} />

			<div className="zk__layout">
				<div className="zk__form">
					{/* 1. Contact */}
					<CheckoutSection
						number={1}
						title="Contact information"
						state={sectionState("contact")}
						summary={contactSummary}
						onEdit={() => setActiveSection("contact")}
					>
						<ContactForm
							fullName={fullName}
							onFullNameChange={setFullName}
							email={email}
							onEmailChange={setEmail}
							phone={phone}
							onPhoneChange={setPhone}
							country={billing.country}
							onCountryChange={(c) => {
								setBilling((prev) => ({ ...prev, country: c }));
								handleBillingCountryChange(c);
							}}
							errors={fieldErrors}
						/>
						<button
							className="zk__btn zk__btn--primary zk__btn--continue"
							onClick={handleContactContinue}
							type="button"
						>
							Continue
						</button>
					</CheckoutSection>

					{/* 2. Billing */}
					<CheckoutSection
						number={2}
						title="Billing address"
						state={sectionState("billing")}
						summary={billingSummary}
						onEdit={() => setActiveSection("billing")}
					>
						<AddressForm
							address={billing}
							onChange={setBilling}
							errors={fieldErrors.billing}
						/>

						{allowDifferentShipping && (
							<div className="zk__ship-toggle">
								<label className="zk__checkbox">
									<input
										type="checkbox"
										checked={shipToDifferent}
										onChange={(e) => setShipToDifferent(e.target.checked)}
									/>
									<span>Ship to a different address</span>
								</label>
							</div>
						)}

						{shipToDifferent && (
							<AddressForm
								address={shipping}
								onChange={setShipping}
								errors={fieldErrors.shipping}
							/>
						)}

						{shippingEnabled && cart.shipping_rates?.length > 0 && (
							<ShippingMethods
								packages={cart.shipping_rates}
								onSelect={handleSelectShipping}
							/>
						)}

						<button
							className="zk__btn zk__btn--primary zk__btn--continue"
							onClick={handleBillingContinue}
							type="button"
						>
							Continue
						</button>
					</CheckoutSection>

					{/* 3. Payment */}
					<CheckoutSection
						number={3}
						title="Payment"
						state={sectionState("payment")}
						onEdit={() => setActiveSection("payment")}
					>
						{/*
						  Mount point for the Zippy CRM points-tender widget.
						  The plugin's checkout bundle hydrates this div.
						  Empty when guest, when balance < min_redemption, or
						  when the plugin isn't installed.
						*/}
						<div id="zippy-crm-checkout-points" className="zippy-crm-mount" />

						<PaymentMethods
							selected={paymentMethod}
							onSelect={setPaymentMethod}
						/>

						<div className="zk__notes">
							<label className="zk__label">Order notes (optional)</label>
							<textarea
								className="zk__textarea"
								value={customerNote}
								onChange={(e) => setCustomerNote(e.target.value)}
								placeholder="Notes about your order, e.g. delivery instructions"
								rows={3}
							/>
						</div>

					</CheckoutSection>

					<a href={cartUrl} className="zk__back-link">
						&larr; Back to cart
					</a>
				</div>

				<OrderSummary
					cart={cart}
					couponCode={couponCode}
					couponError={couponError}
					couponNotice={couponNotice}
					onCouponChange={setCouponCode}
					onApplyCoupon={handleApplyCoupon}
					onRemoveCoupon={handleRemoveCoupon}
					onUpdateQty={handleUpdateQty}
					onRemoveItem={handleRemoveItem}
					busyKeys={busyKeys}
					placeOrderButton={
						<button
							className="zk__btn zk__btn--primary zk__btn--place"
							onClick={handlePlaceOrder}
							disabled={placing}
						>
							{placing ? (
								<>
									<span className="zk__spinner" />
									Processing...
								</>
							) : (
								"Place order"
							)}
						</button>
					}
				/>
			</div>
		</div>
	);
}
