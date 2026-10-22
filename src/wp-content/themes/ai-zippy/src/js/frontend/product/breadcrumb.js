// =============================================================================
// Breadcrumb mobile back link
// =============================================================================
// On mobile (≤ 768px), CSS hides the full trail and shows a "← Back to {parent}"
// link instead. We build that link from the existing trail at runtime — pulls
// the second-to-last <a> (the immediate parent category) and clones its href +
// text.
// =============================================================================

const MOBILE_BREAKPOINT = 768;

export function initBreadcrumb() {
	const nav = document.querySelector(".woocommerce-breadcrumb");
	if (!nav) return;

	const links = nav.querySelectorAll("a");
	if (links.length < 2) return; // Need at least Home + parent category

	const parentLink = links[links.length - 1]; // last <a> = immediate parent (current page is plain text after)
	const parentText = parentLink.textContent.trim();
	const parentHref = parentLink.href;

	const back = document.createElement("a");
	back.className = "zp-bc-back";
	back.href = parentHref;
	back.innerHTML = `
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<line x1="19" y1="12" x2="5" y2="12"/>
			<polyline points="12 19 5 12 12 5"/>
		</svg>
		<span>${escapeHtml(parentText)}</span>
	`;

	// Insert immediately after the original nav so they share the same parent
	nav.parentNode.insertBefore(back, nav.nextSibling);
}

function escapeHtml(s) {
	return s.replace(/[&<>"']/g, (c) => ({
		"&": "&amp;",
		"<": "&lt;",
		">": "&gt;",
		'"': "&quot;",
		"'": "&#39;",
	}[c]));
}
