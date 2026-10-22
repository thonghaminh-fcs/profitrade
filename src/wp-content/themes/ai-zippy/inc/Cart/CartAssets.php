<?php

namespace AiZippy\Cart;

defined('ABSPATH') || exit;

/**
 * Enqueue cart React app on WooCommerce cart page.
 */
class CartAssets
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    /**
     * Enqueue cart assets.
     */
    public static function enqueue(): void
    {
        if (!is_cart() && !is_page('cart')) {
            return;
        }

        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-cart',
            'src/wp-content/themes/ai-zippy/src/js/frontend/cart/index.jsx'
        );
    }
}
