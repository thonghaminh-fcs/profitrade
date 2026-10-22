import { azSetting, azText } from "../core/config.js";

/**
 * Show a transient toast notification.
 *
 * Shared by add-to-cart.js and the mini cart so a failed cart action gets the
 * same visible feedback wherever it happens. The mini cart used to write its
 * errors into `.az-mc__shipping-text`, which only exists when the free-shipping
 * bar is on, so a failed update on the common default silently showed nothing.
 *
 * @param {string}            message Already-translated text.
 * @param {"success"|"error"} [type]
 */
export function showToast(message, type = "success") {
	// Remove existing toasts
	document
		.querySelectorAll(".az-toast")
		.forEach((t) => t.remove());

	const toast = document.createElement("div");
	toast.className = `az-toast az-toast--${type}`;

	// Built as nodes rather than innerHTML: the message can be a Store API error
	// string, and the close label is translatable.
	const label = document.createElement("span");
	label.textContent = message;

	const closeBtn = document.createElement("button");
	closeBtn.className = "az-toast__close";
	closeBtn.setAttribute("aria-label", azText("common.close", "Close"));
	closeBtn.innerHTML = "&times;";

	// Countdown bar. Decorative, so it is hidden from assistive tech — the
	// message itself is the content. Absolutely positioned, hence appended as a
	// sibling without disturbing the flex row.
	const bar = document.createElement("span");
	bar.className = "az-toast__bar";
	bar.setAttribute("aria-hidden", "true");

	toast.append(label, closeBtn, bar);

	// One duration drives both the bar animation and the dismiss below, so the
	// bar cannot finish early or keep running after the toast has gone.
	const duration = azSetting("toastDuration", 4000);
	toast.style.setProperty("--az-toast-duration", `${duration}ms`);

	document.body.appendChild(toast);

	// Close on click
	closeBtn.addEventListener("click", () => {
		toast.classList.add("is-closing");
		setTimeout(() => toast.remove(), 300);
	});

	// Auto-dismiss
	setTimeout(() => {
		if (toast.parentNode) {
			toast.classList.add("is-closing");
			setTimeout(() => toast.remove(), 300);
		}
	}, duration);
}
