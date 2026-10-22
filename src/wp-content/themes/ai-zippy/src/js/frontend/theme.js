/**
 * AI Zippy Theme — Main Entry Point
 *
 * This file only imports styles and initializes modules.
 * All logic lives in /modules/*.js
 *
 * Each module is gated on its feature flag (see AiZippy\Core\Features) so a
 * client site can switch core behaviour off from its child theme instead of
 * forking this bundle.
 */

import "@scss/style.scss";

import { azFeature } from "./core/config.js";
import { initHeader } from "./modules/header.js";
import { initShopViewToggle } from "./modules/shop-view-toggle.js";
import { initAddToCart } from "./modules/add-to-cart.js";
import { initScrollToTop } from "./modules/scroll-to-top.js";
import { initSearchBar } from "./modules/search-bar.js";
import { initHeaderCartButton } from "./modules/header-cart-button.js";
import { initMiniCart } from "./modules/mini-cart.js";
import { initWishlist } from "./modules/wishlist.js";

document.addEventListener("DOMContentLoaded", () => {
	if (azFeature("sticky_header")) initHeader();
	if (azFeature("shop_view_toggle")) initShopViewToggle();
	if (azFeature("ajax_add_to_cart")) initAddToCart();
	if (azFeature("scroll_to_top")) initScrollToTop();
	if (azFeature("search_typeahead")) initSearchBar();
	if (azFeature("header_cart_button")) initHeaderCartButton();
	if (azFeature("mini_cart")) initMiniCart();
	if (azFeature("wishlist")) initWishlist();
});
