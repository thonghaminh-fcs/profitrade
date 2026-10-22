// Countdown for cards flagged `countdown` (docs/build/blocks/home-promotions.md,
// logic.js:57,115-117,132): rewrite the [data-sm-countdown] sub every second
// with the HH:MM:SS left until the next local midnight. PHP prints the same
// format as the no-JS state, so the first paint never jumps.
const pad = (n) => String(n).padStart(2, "0");

function render(el) {
	const now = new Date();
	const end = new Date(now);
	end.setHours(24, 0, 0, 0);
	const left = Math.max(0, Math.floor((end - now) / 1000));
	el.textContent = `Ends in ${pad(Math.floor(left / 3600))}:${pad(
		Math.floor((left % 3600) / 60)
	)}:${pad(left % 60)}`;
}

const els = document.querySelectorAll("[data-sm-countdown]");
if (els.length) {
	els.forEach(render);
	setInterval(() => els.forEach(render), 1000);
}
