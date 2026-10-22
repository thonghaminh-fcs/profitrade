<?php

namespace AiZippy\Cart;

defined('ABSPATH') || exit;

/**
 * Keep a useful header cart button on Cart and Checkout pages, where
 * WooCommerce intentionally suppresses the interactive Mini-Cart drawer.
 *
 * Compatibility shim. This filters woocommerce/mini-cart, so it only runs on a
 * site whose header still uses the core block. The ai-zippy/mini-cart block
 * covers the same case itself by rendering a plain cart link on those two pages
 * (see src/blocks/mini-cart/render.php), which makes this a no-op there.
 *
 * It is kept rather than deleted because existing client sites and any header
 * customised in the Site Editor may still hold the core block, and losing the
 * button would be a silent regression for them. It can go once no site ships
 * woocommerce/mini-cart.
 */
class HeaderCartButton
{
    public static function register(): void
    {
        add_filter('render_block_woocommerce/mini-cart', [self::class, 'render'], 20, 2);
    }

    public static function render(string $content, array $block): string
    {
        if (!is_cart() && !is_checkout()) {
            return $content;
        }

        $count = function_exists('WC') && WC()->cart
            ? WC()->cart->get_cart_contents_count()
            : 0;

        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
        $is_cart_page = is_cart();

        return sprintf(
            '<div class="wc-block-mini-cart wp-block-woocommerce-mini-cart ai-zippy-header-cart">'
            . '<button class="wc-block-mini-cart__button ai-zippy-header-cart__button" type="button" '
            . 'data-cart-url="%1$s" data-is-cart-page="%2$s" aria-label="%3$s">'
            . '<span class="wc-block-mini-cart__quantity-badge">'
            . '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="wc-block-mini-cart__icon" viewBox="0 0 32 32" aria-hidden="true">'
            . '<circle cx="12.667" cy="24.667" r="2"/><circle cx="23.333" cy="24.667" r="2"/>'
            . '<path fill-rule="evenodd" d="M9.285 10.036a1 1 0 0 1 .776-.37h15.272a1 1 0 0 1 .99 1.142l-1.333 9.333A1 1 0 0 1 24 21H12a1 1 0 0 1-.98-.797L9.083 10.87a1 1 0 0 1 .203-.834m2.005 1.63L12.814 19h10.319l1.047-7.333z" clip-rule="evenodd"/>'
            . '<path fill-rule="evenodd" d="M5.667 6.667a1 1 0 0 1 1-1h2.666a1 1 0 0 1 .984.82l.727 4a1 1 0 1 1-1.967.359l-.578-3.18H6.667a1 1 0 0 1-1-1" clip-rule="evenodd"/>'
            . '</svg>'
            . '<span class="wc-block-mini-cart__badge" aria-hidden="true">%4$d</span>'
            . '</span></button></div>',
            esc_url($cart_url),
            $is_cart_page ? 'true' : 'false',
            esc_attr(sprintf(_n('%d item in cart', '%d items in cart', $count, 'ai-zippy'), $count)),
            $count
        );
    }
}
