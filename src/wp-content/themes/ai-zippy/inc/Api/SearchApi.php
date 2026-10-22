<?php

namespace AiZippy\Api;

use AiZippy\Core\RateLimiter;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

defined('ABSPATH') || exit;

/**
 * REST API: Unified Search
 *
 * GET /wp-json/ai-zippy/v1/search
 *
 * Params:
 *   q          string   Search query (name, SKU, ID, post title)
 *   scope      string   "products" | "posts" | "both"  (default: products)
 *   per_page   int      Max results to return (default: 8, max: 20)
 */
class SearchApi
{
    const NAMESPACE  = 'ai-zippy/v1';
    const RATE_LIMIT = 60; // per minute

    public static function register(): void
    {
        register_rest_route(self::NAMESPACE, '/search', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'handle'],
            'permission_callback' => [self::class, 'checkPermission'],
            'args'                => [
                'q'        => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => ''],
                'scope'    => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => 'products'],
                'per_page' => ['type' => 'integer', 'sanitize_callback' => 'absint',              'default' => 8],
            ],
        ]);
    }

    public static function checkPermission(WP_REST_Request $request): bool|WP_Error
    {
        if (RateLimiter::isLimited('search-api', self::RATE_LIMIT, 60)) {
            return new WP_Error('rate_limited', 'Too many requests.', ['status' => 429]);
        }
        return true;
    }

    public static function handle(WP_REST_Request $request): WP_REST_Response
    {
        $q        = trim($request->get_param('q'));
        $scope    = $request->get_param('scope');
        $per_page = min(20, max(1, $request->get_param('per_page')));

        if (strlen($q) < 2) {
            return new WP_REST_Response(['results' => []], 200);
        }

        $results = [];

        if (in_array($scope, ['products', 'both'], true)) {
            $results = array_merge($results, self::searchProducts($q, $per_page));
        }

        if (in_array($scope, ['posts', 'both'], true)) {
            $remaining = $per_page - count($results);
            if ($remaining > 0) {
                $results = array_merge($results, self::searchPosts($q, $remaining));
            }
        }

        // Trim to per_page
        $results = array_slice($results, 0, $per_page);

        return new WP_REST_Response(['results' => $results], 200);
    }

    // -----------------------------------------------------------------------
    // Product search: name + SKU + numeric ID
    // -----------------------------------------------------------------------

    private static function searchProducts(string $q, int $limit): array
    {
        if (!function_exists('wc_get_products')) {
            return [];
        }

        $ids = [];

        // 1. Exact numeric ID
        if (ctype_digit($q)) {
            $ids[] = (int) $q;
        }

        // 2. SKU lookup (partial match)
        $sku_ids = self::searchBySku($q);
        $ids     = array_unique(array_merge($ids, $sku_ids));

        $results = [];

        // 3. Name / title search via wc_get_products
        $name_products = wc_get_products([
            'status'   => 'publish',
            'limit'    => $limit,
            's'        => $q,
            'return'   => 'objects',
            'orderby'  => 'relevance',
        ]);

        foreach ($name_products as $product) {
            $results[$product->get_id()] = self::formatProduct($product);
        }

        // 4. Merge SKU / ID results (may add products not in name results)
        if (!empty($ids)) {
            $extra = wc_get_products([
                'status'  => 'publish',
                'include' => $ids,
                'limit'   => count($ids),
                'return'  => 'objects',
            ]);
            foreach ($extra as $product) {
                if (!isset($results[$product->get_id()])) {
                    $results[$product->get_id()] = self::formatProduct($product);
                }
            }
        }

        return array_values(array_slice($results, 0, $limit));
    }

    private static function searchBySku(string $search): array
    {
        global $wpdb;

        $like = '%' . $wpdb->esc_like($search) . '%';

        if (
            class_exists('Automattic\WooCommerce\Utilities\OrderUtil') &&
            get_option('woocommerce_custom_orders_table_enabled') === 'yes'
        ) {
            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT product_id FROM {$wpdb->prefix}wc_product_meta_lookup WHERE sku LIKE %s",
                $like
            ));
        } else {
            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta}
                 WHERE meta_key = '_sku' AND meta_value LIKE %s",
                $like
            ));
        }

        return array_map('intval', $ids);
    }

    private static function formatProduct(\WC_Product $product): array
    {
        $image_id = $product->get_image_id();
        $cats     = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
        $cat_name = (!is_wp_error($cats) && !empty($cats)) ? wp_specialchars_decode($cats[0], ENT_QUOTES) : '';

        return [
            'type'       => 'product',
            'id'         => $product->get_id(),
            'title'      => $product->get_name(),
            'url'        => $product->get_permalink(),
            'image'      => $image_id
                                ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail')
                                : wc_placeholder_img_src(),
            'price_html' => $product->get_price_html(),
            'sku'        => $product->get_sku(),
            'on_sale'    => $product->is_on_sale(),
            'in_stock'   => $product->is_in_stock(),
            'category'   => $cat_name,
        ];
    }

    // -----------------------------------------------------------------------
    // Post search: title + content
    // -----------------------------------------------------------------------

    private static function searchPosts(string $q, int $limit): array
    {
        $posts = get_posts([
            'post_type'      => 'post',
            'post_status'    => 'publish',
            's'              => $q,
            'posts_per_page' => $limit,
            'orderby'        => 'relevance',
        ]);

        return array_map(function (\WP_Post $post) {
            $thumb = get_the_post_thumbnail_url($post->ID, 'thumbnail');
            return [
                'type'     => 'post',
                'id'       => $post->ID,
                'title'    => $post->post_title,
                'url'      => get_permalink($post),
                'image'    => $thumb ?: '',
                'excerpt'  => wp_trim_words($post->post_excerpt ?: $post->post_content, 12, '…'),
                'date'     => get_the_date('M j, Y', $post),
                'category' => '',
            ];
        }, $posts);
    }
}
