import { azText, azUrl } from "../core/config.js";
import { getCart } from "./cart-api.js";

/**
 * Cart/Checkout header button. WooCommerce does not load the Mini-Cart drawer
 * on these pages, so this control links to Cart and maintains its own count.
 *
 * Only present while the header still uses the core woocommerce/mini-cart block
 * (see AiZippy\Cart\HeaderCartButton). The ai-zippy/mini-cart block handles the
 * same pages itself, so this module simply finds nothing there.
 */
export function initHeaderCartButton() {
	const button = document.querySelector(".ai-zippy-header-cart__button");
	if (!button) return;

	const update = (cart) => {
		if (!cart) return;
		const count = Number.isFinite(cart.items_count)
			? cart.items_count
			: (cart.items || []).reduce((total, item) => total + (item.quantity || 0), 0);
		const badge = button.querySelector(".wc-block-mini-cart__badge");
		if (badge) badge.textContent = String(count);

		// Shares the mini cart's string table so a site translates the count once.
		button.setAttribute(
			"aria-label",
			count === 1
				? azText("minicart.items_one", "%s item in cart", count)
				: azText("minicart.items_many", "%s items in cart", count)
		);
	};

	button.addEventListener("click", () => {
		if (button.dataset.isCartPage !== "true") {
			window.location.assign(button.dataset.cartUrl || azUrl("cart", "/"));
		}
	});

	const refresh = () => getCart().then(update).catch(() => {});
	window.addEventListener("ai-zippy-cart-updated", (event) => update(event.detail));
	document.body.addEventListener("wc-blocks_added_to_cart", refresh);

	// Classic WooCommerce checkout uses jQuery events after quantity/totals updates.
	if (typeof jQuery !== "undefined") {
		jQuery(document.body).on("updated_checkout added_to_cart removed_from_cart", refresh);
	}

	refresh();
}
