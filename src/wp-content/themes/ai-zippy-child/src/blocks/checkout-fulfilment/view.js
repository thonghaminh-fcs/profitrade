// Checkout Fulfilment Picker — frontend behaviour.
// Reads the shared {slot, express} delivery state owned by child.js
// (sessionStorage "sm-delivery", "sm:delivery" event) and keeps the two option
// cards, the hidden checkout inputs, the sm_* cookies (read by the Woo fee /
// order-meta hooks in render.php) and the Woo shipping-address visibility in
// sync with the current choice.
const DELIVERY_KEY = "sm-delivery";
const DEFAULT_SLOT = 2;
const SLOTS = [
	"8:00 – 11:00 AM",
	"11:00 AM – 2:00 PM",
	"2:00 – 5:00 PM",
	"5:00 – 8:00 PM",
	"8:00 – 11:00 PM",
];

// viewScript only enqueues when the block is rendered, so the root exists.
const root = document.querySelector(".sm-fulfilment");

const options = [...root.querySelectorAll("[data-sm-fulfil-option]")];
const deliveryTitle = root.querySelector("[data-sm-fulfil-delivery-title]");
const deliverySub = root.querySelector("[data-sm-fulfil-delivery-sub]");
const inputs = {
	fulfilment: root.querySelector("[data-sm-fulfilment-input]"),
	express: root.querySelector("[data-sm-express-input]"),
	slot: root.querySelector("[data-sm-slot-input]"),
};
const feeLabel = root.dataset.expressFee || "$20.00";
const feeValue = parseFloat(String(feeLabel).replace(/[^0-9.]/g, "")) || 20;

let fulfilment =
	options.find((el) => el.classList.contains("is-selected"))?.dataset
		.smFulfilOption || "delivery";

function getDelivery() {
	try {
		const raw = sessionStorage.getItem(DELIVERY_KEY);
		if (raw) {
			const parsed = JSON.parse(raw);
			if (typeof parsed.slot === "number" && SLOTS[parsed.slot]) {
				return { slot: parsed.slot, express: !!parsed.express };
			}
		}
	} catch (_) {
		/* fall through to default */
	}
	return { slot: DEFAULT_SLOT, express: false };
}

function setCookie(name, value) {
	document.cookie = `${name}=${encodeURIComponent(value)}; path=/; SameSite=Lax`;
}

// Woo classic checkout recalculates totals (incl. our express fee) on this
// event; jQuery handlers pick up natively dispatched events.
let refreshTimer = 0;
function refreshTotals() {
	clearTimeout(refreshTimer);
	refreshTimer = setTimeout(() => {
		document.body.dispatchEvent(new Event("update_checkout"));
	}, 150);
}

function syncCookiesAndInputs(state) {
	if (inputs.fulfilment) inputs.fulfilment.value = fulfilment;
	if (inputs.express)
		inputs.express.value = state.express && fulfilment === "delivery" ? "1" : "0";
	if (inputs.slot) inputs.slot.value = SLOTS[state.slot];
	setCookie("sm_fulfilment", fulfilment);
	setCookie("sm_express", state.express && fulfilment === "delivery" ? "1" : "0");
	setCookie("sm_slot", SLOTS[state.slot]);
	setCookie("sm_express_fee", String(feeValue));
}

// Step-2 heading: "Shipping details" flips to "Pickup details" (logic.js:156).
// Best-effort: any checkout heading that currently says "Shipping details".
function syncDetailsTitle(isPickup) {
	document
		.querySelectorAll(
			".sm-page-checkout h2, .sm-page-checkout h3, [data-sm-details-title]"
		)
		.forEach((el) => {
			const text = el.textContent.trim();
			if (isPickup && /^(2\s*·\s*)?Shipping details$/i.test(text)) {
				el.textContent = text.replace(/Shipping details/i, "Pickup details");
			} else if (!isPickup && /^(2\s*·\s*)?Pickup details$/i.test(text)) {
				el.textContent = text.replace(/Pickup details/i, "Shipping details");
			}
		});
}

function render(state) {
	const express = !!state.express;

	if (deliveryTitle)
		deliveryTitle.textContent = express ? "1-Hour Express" : "Delivery";
	if (deliverySub)
		deliverySub.textContent = express
			? `Within 60 min · ${feeLabel}`
			: `Today, ${SLOTS[state.slot]} · Free`;

	const isPickup = fulfilment === "pickup";
	root.classList.toggle("is-pickup", isPickup);
	document.body.classList.toggle("sm-fulfilment-pickup", isPickup);

	options.forEach((el) => {
		const selected = el.dataset.smFulfilOption === fulfilment;
		el.classList.toggle("is-selected", selected);
		el.setAttribute("aria-checked", selected ? "true" : "false");
		el.tabIndex = selected ? 0 : -1;
	});

	syncCookiesAndInputs(state);
	syncDetailsTitle(isPickup);
}

function select(next) {
	if (next === fulfilment) return;
	fulfilment = next;
	render(getDelivery());
	refreshTotals();
}

// The hidden inputs must post with the Woo checkout <form>, which the block
// sits OUTSIDE of — associate them through the HTML form attribute.
function attachToCheckoutForm() {
	const form = document.querySelector(
		"form.checkout, form.woocommerce-checkout, form[name='checkout']"
	);
	if (!form) return;
	if (!form.id) form.id = "sm-checkout-form";
	Object.values(inputs).forEach((input) => {
		if (input) input.setAttribute("form", form.id);
	});
}

options.forEach((el) => {
	el.addEventListener("click", () => select(el.dataset.smFulfilOption));
	el.addEventListener("keydown", (e) => {
		if (e.key === "Enter" || e.key === " ") {
			e.preventDefault();
			select(el.dataset.smFulfilOption);
		}
		const arrow =
			e.key === "ArrowRight" || e.key === "ArrowDown"
				? 1
				: e.key === "ArrowLeft" || e.key === "ArrowUp"
					? -1
					: 0;
		if (arrow) {
			e.preventDefault();
			const i = options.indexOf(el);
			const nextEl = options[(i + arrow + options.length) % options.length];
			nextEl.focus();
			select(nextEl.dataset.smFulfilOption);
		}
	});
});

document.addEventListener("sm:delivery", (e) => {
	const prevExpress = inputs.express ? inputs.express.value : "0";
	render(e.detail || getDelivery());
	if (inputs.express && inputs.express.value !== prevExpress) refreshTotals();
});

attachToCheckoutForm();
render(getDelivery());
