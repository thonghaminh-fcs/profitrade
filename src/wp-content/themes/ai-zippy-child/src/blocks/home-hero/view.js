// Home Hero — keeps the floating "Next slot" card in sync with the delivery
// state owned by child.js (sessionStorage "sm-delivery", "sm:delivery" event).
// The mockup shows the slot time regardless of express (logic.js:118).
const DELIVERY_KEY = "sm-delivery";
const DEFAULT_SLOT = 2;
const SLOTS = [
	"8:00 – 11:00 AM",
	"11:00 AM – 2:00 PM",
	"2:00 – 5:00 PM",
	"5:00 – 8:00 PM",
	"8:00 – 11:00 PM",
];

function currentSlot() {
	try {
		const raw = sessionStorage.getItem(DELIVERY_KEY);
		if (raw) {
			const parsed = JSON.parse(raw);
			if (typeof parsed.slot === "number" && SLOTS[parsed.slot]) {
				return parsed.slot;
			}
		}
	} catch (_) {
		/* fall through to default */
	}
	return DEFAULT_SLOT;
}

function renderHeroSlot() {
	const label = `Today, ${SLOTS[currentSlot()]}`;
	document.querySelectorAll("[data-sm-hero-slot]").forEach((el) => {
		el.textContent = label;
	});
}

renderHeroSlot();
document.addEventListener("sm:delivery", renderHeroSlot);
