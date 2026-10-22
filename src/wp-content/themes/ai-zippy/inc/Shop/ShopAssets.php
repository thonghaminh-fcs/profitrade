<?php

namespace AiZippy\Shop;

defined('ABSPATH') || exit;

/**
 * Enqueue shop filter React app on WooCommerce pages.
 */
class ShopAssets
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    /**
     * Enqueue shop filter assets.
     */
    public static function enqueue(): void
    {
        if (!is_shop() && !is_product_taxonomy()) {
            return;
        }

        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-shop-filter',
            'src/wp-content/themes/ai-zippy/src/js/frontend/shop-filter/index.jsx'
        );

        // Values only the server knows, merged into the container's data-config
        // before index.jsx reads it. The container is static HTML inside
        // templates/archive-product.html, so an inline script is the way in.
        $server_config = [
            'wishlist_enabled' => \AiZippy\Admin\ThemeOptions::isWishlistEnabled(),
            // Built from rest_url() so the React app hits the right endpoint
            // whatever the permalink setting: the pretty /wp-json/… form, or the
            // …/index.php?rest_route=/… form on Plain permalinks. Hardcoding the
            // pretty path in JS left the grid stuck loading on Plain-permalink
            // sites, where WP answers /wp-json/… with the homepage HTML.
            'rest_url'         => esc_url_raw(rest_url('ai-zippy/v1')),
        ];

        // On a product category/tag page, pre-seed the filter with the current term.
        if (is_product_taxonomy()) {
            $term = get_queried_object();
            if ($term instanceof \WP_Term) {
                $server_config['initial_category'] = $term->slug;
            }
        }

        wp_add_inline_script(
            'ai-zippy-shop-filter',
            '(function(){' .
                'var el=document.getElementById("ai-zippy-shop-filter");' .
                'if(!el)return;' .
                'var cfg=JSON.parse(el.dataset.config||"{}");' .
                'Object.assign(cfg,' . wp_json_encode($server_config) . ');' .
                'el.dataset.config=JSON.stringify(cfg);' .
            '})();',
            'before'
        );
    }
}
