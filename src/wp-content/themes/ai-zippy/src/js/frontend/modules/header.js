/**
 * Sticky header — adds shadow on scroll.
 *
 * The element is found through the selector contract (window.aiZippy.selectors)
 * so a client theme can render its own header markup without losing behaviour.
 */

import { azSelector, azSetting } from "../core/config.js";

export function initHeader() {
	const selector = azSelector("header", ".az-header, header.wp-block-group");
	const header = document.querySelector(selector);
	if (!header) return;

	const offset = azSetting("stickyOffset", 10);

	window.addEventListener(
		"scroll",
		() => {
			header.classList.toggle("is-scrolled", window.scrollY > offset);
		},
		{ passive: true },
	);
}
