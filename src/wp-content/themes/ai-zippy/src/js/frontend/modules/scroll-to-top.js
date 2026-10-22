// =============================================================================
// Scroll-to-top button
// =============================================================================
// Floating button that appears in the bottom-right when the user has scrolled
// past 400px. Click smooth-scrolls to top (uses the native smooth-scroll
// behavior set on <html> in _base.scss).
//
// Mobile: positioned higher when the product page's sticky add-to-cart bar
// is visible, so the two don't overlap.
// =============================================================================

const SHOW_AT = 400; // pixels scrolled before button shows

export function initScrollToTop() {
	const btn = createButton();
	document.body.appendChild(btn);

	let visible = false;

	const update = () => {
		const shouldShow = window.scrollY > SHOW_AT;
		if (shouldShow === visible) return;
		visible = shouldShow;
		btn.classList.toggle("is-visible", shouldShow);
		btn.setAttribute("aria-hidden", shouldShow ? "false" : "true");
		btn.tabIndex = shouldShow ? 0 : -1;
	};

	// Throttle scroll handler with rAF for smoothness
	let ticking = false;
	window.addEventListener("scroll", () => {
		if (ticking) return;
		ticking = true;
		requestAnimationFrame(() => {
			update();
			ticking = false;
		});
	}, { passive: true });

	// Initial state
	update();

	btn.addEventListener("click", () => {
		window.scrollTo({ top: 0, behavior: "smooth" });
	});
}

function createButton() {
	const btn = document.createElement("button");
	btn.type = "button";
	btn.className = "az-scroll-top";
	btn.setAttribute("aria-label", "Scroll to top");
	btn.setAttribute("aria-hidden", "true");
	btn.tabIndex = -1;
	btn.innerHTML = `
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<polyline points="18 15 12 9 6 15"/>
		</svg>
	`;
	return btn;
}
