<?php

namespace AiZippy\Api;

use AiZippy\Core\Cache;
use AiZippy\Core\RateLimiter;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WC_Product;

defined('ABSPATH') || exit;

/**
 * REST API: Advanced Product Filter
 *
 * Endpoints:
 *   GET /wp-json/ai-zippy/v1/products        — Search & filter products
 *   GET /wp-json/ai-zippy/v1/filter-options   — Get available filter options
 */
class ProductFilterApi
{
    const NAMESPACE = 'ai-zippy/v1';
    const PRODUCTS_RATE_LIMIT = 60;    // per minute
    const OPTIONS_RATE_LIMIT = 120;    // per minute; response is server-cached

    /**
     * Register REST routes.
     */
    public static function register(): void
    {
        register_rest_route(self::NAMESPACE, '/products', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'getProducts'],
            'permission_callback' => [self::class, 'checkPermission'],
            'args'                => self::getProductArgs(),
        ]);

        register_rest_route(self::NAMESPACE, '/filter-options', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'getFilterOptions'],
            'permission_callback' => [self::class, 'checkPermission'],
        ]);
    }

    /**
     * Permission check with rate limiting.
     */
    public static function checkPermission(WP_REST_Request $request): bool|WP_Error
    {
        $route = $request->get_route();
        $isOptions = str_contains($route, 'filter-options');

        $limit = $isOptions ? self::OPTIONS_RATE_LIMIT : self::PRODUCTS_RATE_LIMIT;
        $action = 'shop-api-' . ($isOptions ? 'options' : 'products');

        if (RateLimiter::isLimited($action, $limit, 60)) {
            return new WP_Error(
                'rate_limited',
                'Too many requests. Please try again later.',
                ['status' => 429]
            );
        }

        return true;
    }

    /**
     * Define accepted query parameters.
     */
    private static function getProductArgs(): array
    {
        return [
            'search'       => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => ''],
            'include'      => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => ''],
            'category'     => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => ''],
            'min_price'    => ['type' => 'number',  'sanitize_callback' => fn($v) => (float) $v,  'default' => 0],
            'max_price'    => ['type' => 'number',  'sanitize_callback' => fn($v) => (float) $v,  'default' => 0],
            'attributes'   => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => ''],
            'stock_status' => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => ''],
            'orderby'      => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => 'menu_order'],
            'order'        => ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => 'ASC'],
            'page'         => ['type' => 'integer', 'sanitize_callback' => 'absint',              'default' => 1],
            'per_page'     => ['type' => 'integer', 'sanitize_callback' => 'absint',              'default' => 12],
        ];
    }

    // ---------------------------------------------------------------
    // GET /products
    // ---------------------------------------------------------------

    public static function getProducts(WP_REST_Request $request): WP_REST_Response
    {
        $search       = $request->get_param('search');
        $include      = trim((string) $request->get_param('include'));
        $category     = $request->get_param('category');
        $min_price    = $request->get_param('min_price');
        $max_price    = $request->get_param('max_price');
        $attributes   = $request->get_param('attributes');
        $stock_status = $request->get_param('stock_status');
        $orderby      = $request->get_param('orderby');
        $order        = $request->get_param('order');
        $page         = max(1, $request->get_param('page'));
        $per_page     = min(100, max(1, $request->get_param('per_page')));

        $args = [
            'status'   => 'publish',
            'limit'    => $per_page,
            'page'     => $page,
            'paginate' => true,
            'orderby'  => $orderby,
            'order'    => strtoupper($order),
        ];

        if ($include !== '') {
            $include_ids = array_values(array_unique(array_filter(
                array_map('absint', explode(',', $include))
            )));

            if (empty($include_ids)) {
                return new WP_REST_Response([
                    'products' => [],
                    'total'    => 0,
                    'pages'    => 0,
                    'page'     => $page,
                    'per_page' => $per_page,
                ], 200);
            }

            $args['include'] = array_slice($include_ids, 0, 100);
        }

        // Search by name OR SKU
        if (!empty($search)) {
            $sku_ids = self::searchBySku($search);

            if (!empty($sku_ids)) {
                $title_args = array_merge($args, [
                    's'        => $search,
                    'paginate' => false,
                    'limit'    => -1,
                    'return'   => 'ids',
                ]);
                $title_ids = wc_get_products($title_args);
                $merged_ids = array_unique(array_merge($sku_ids, $title_ids));

                if (!empty($merged_ids)) {
                    $args['include'] = $merged_ids;
                } else {
                    return new WP_REST_Response([
                        'products' => [],
                        'total' => 0,
                        'pages' => 0,
                    ], 200);
                }
            } else {
                $args['s'] = $search;
            }
        }

        // Category filter (comma-separated slugs)
        if (!empty($category)) {
            $args['category'] = array_map('trim', explode(',', $category));
        }

        // Price range — wc_get_products() ignores min_price/max_price directly;
        // must filter via meta_query on _price.
        if ($min_price > 0 || $max_price > 0) {
            $price_clause = ['key' => '_price', 'type' => 'DECIMAL(10,2)'];

            if ($min_price > 0 && $max_price > 0) {
                $price_clause['value']   = [$min_price, $max_price];
                $price_clause['compare'] = 'BETWEEN';
            } elseif ($min_price > 0) {
                $price_clause['value']   = $min_price;
                $price_clause['compare'] = '>=';
            } else {
                $price_clause['value']   = $max_price;
                $price_clause['compare'] = '<=';
            }

            $args['meta_query'] = [$price_clause];
        }

        // Stock status
        if (!empty($stock_status)) {
            $args['stock_status'] = $stock_status;
        }

        // Attributes: "pa_color:red,blue|pa_size:large"
        if (!empty($attributes)) {
            $tax_query = self::parseAttributeQuery($attributes);
            if (!empty($tax_query)) {
                $args['tax_query'] = $tax_query;
            }
        }

        // wc_get_products() does not reliably pass meta_query through to WP_Query.
        // When price filtering is active, run a raw WP_Query to get matching IDs,
        // then constrain wc_get_products() to those IDs.
        if (!empty($args['meta_query'])) {
            $price_meta_query = $args['meta_query'];
            unset($args['meta_query']);

            // Query both simple products and variable product variations.
            // Variations store _price on their own post (post_type=product_variation),
            // so we fetch their parent IDs too.
            $id_query = new \WP_Query([
                'post_type'      => ['product', 'product_variation'],
                'post_status'    => 'publish',
                'fields'         => 'ids',
                'posts_per_page' => -1,
                'meta_query'     => $price_meta_query,
            ]);

            $price_ids = [];
            foreach ($id_query->posts as $id) {
                $post = get_post($id);
                // If it's a variation, use the parent product ID
                $price_ids[] = ($post && $post->post_type === 'product_variation')
                    ? $post->post_parent
                    : $id;
            }
            $price_ids = array_unique(array_map('intval', $price_ids));

            if (empty($price_ids)) {
                return new WP_REST_Response([
                    'products' => [],
                    'total' => 0,
                    'pages' => 0,
                    'page' => $page,
                    'per_page' => $per_page,
                ], 200);
            }

            // Intersect with any existing include constraint (e.g. from SKU search)
            $args['include'] = isset($args['include'])
                ? array_intersect($args['include'], $price_ids)
                : $price_ids;

            if (empty($args['include'])) {
                return new WP_REST_Response([
                    'products' => [],
                    'total' => 0,
                    'pages' => 0,
                    'page' => $page,
                    'per_page' => $per_page,
                ], 200);
            }
        }

        $results = wc_get_products($args);

        $products = array_map(
            [self::class, 'formatProduct'],
            $results->products
        );

        return new WP_REST_Response([
            'products' => $products,
            'total'    => (int) $results->total,
            'pages'    => (int) $results->max_num_pages,
            'page'     => $page,
            'per_page' => $per_page,
        ], 200);
    }

    // ---------------------------------------------------------------
    // GET /filter-options
    // ---------------------------------------------------------------

    public static function getFilterOptions(WP_REST_Request $request): WP_REST_Response
    {
        $cached = Cache::get(Cache::FILTER_OPTIONS);

        if ($cached !== false) {
            $response = new WP_REST_Response($cached, 200);
            $response->header('X-Cache', 'HIT');
            return $response;
        }

        $data = [
            'categories'  => self::getCategories(),
            'attributes'  => self::getAttributes(),
            'price_range' => self::getPriceRange(),
        ];

        Cache::set(Cache::FILTER_OPTIONS, $data, Cache::FILTER_OPTIONS_TTL);

        $response = new WP_REST_Response($data, 200);
        $response->header('X-Cache', 'MISS');
        return $response;
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

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
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s",
                $like
            ));
        }

        return array_map('intval', $ids);
    }

    private static function parseAttributeQuery(string $attributes): array
    {
        $tax_query = [];

        foreach (explode('|', $attributes) as $group) {
            $parts = explode(':', $group);
            if (count($parts) === 2) {
                $tax_query[] = [
                    'taxonomy' => sanitize_text_field($parts[0]),
                    'field'    => 'slug',
                    'terms'    => array_map('trim', explode(',', $parts[1])),
                    'operator' => 'IN',
                ];
            }
        }

        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
        }

        return $tax_query;
    }

    /**
     * Decode HTML entities for JSON output.
     *
     * WordPress stores term names HTML-encoded, and imported data often carries
     * numeric entities such as &#8217;. The frontend renders these values as plain
     * React text nodes, which do not interpret entities, so they must be fully
     * decoded here — wp_specialchars_decode() only covers & < > " and '.
     */
    private static function decodeName(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function formatProduct(WC_Product $product): array
    {
        $image_id = $product->get_image_id();

        $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'all']);
        $cat_list = array_map(fn($cat) => [
            'id'   => $cat->term_id,
            'name' => self::decodeName($cat->name),
            'slug' => $cat->slug,
        ], $categories);

        $attrs = [];
        foreach ($product->get_attributes() as $attr) {
            if ($attr->is_taxonomy()) {
                $terms = wc_get_product_terms($product->get_id(), $attr->get_name(), ['fields' => 'all']);
                $attrs[] = [
                    'name'    => self::decodeName(wc_attribute_label($attr->get_name())),
                    'slug'    => $attr->get_name(),
                    'options' => array_map(
                        fn($t) => ['name' => self::decodeName($t->name), 'slug' => $t->slug],
                        $terms
                    ),
                ];
            }
        }

        return [
            'id'                => $product->get_id(),
            'name'              => $product->get_name(),
            'slug'              => $product->get_slug(),
            'type'              => $product->get_type(),
            'permalink'         => $product->get_permalink(),
            'sku'               => $product->get_sku(),
            'price'             => $product->get_price(),
            'regular_price'     => $product->get_regular_price(),
            'sale_price'        => $product->get_sale_price(),
            'price_html'        => $product->get_price_html(),
            'on_sale'           => $product->is_on_sale(),
            'stock_status'      => $product->get_stock_status(),
            'stock_quantity'    => $product->get_stock_quantity(),
            'average_rating'    => (float) $product->get_average_rating(),
            'rating_count'      => (int) $product->get_rating_count(),
            'short_description' => wp_strip_all_tags($product->get_short_description()),
            'categories'        => $cat_list,
            'attributes'        => $attrs,
            'image'             => $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : wc_placeholder_img_src(),
            'gallery'           => array_map(
                fn($id) => wp_get_attachment_image_url($id, 'woocommerce_thumbnail'),
                $product->get_gallery_image_ids()
            ),
            'add_to_cart_url'   => $product->add_to_cart_url(),
        ];
    }

    private static function getCategories(): array
    {
        $categories = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'orderby'    => 'name',
        ]);

        if (is_wp_error($categories)) {
            return [];
        }

        // get_terms() post-filters empty terms in PHP for hierarchical taxonomies
        // (see WP_Term_Query "Make sure we show empty categories that have children"),
        // which leaves gaps in the array keys. Reindex so this always encodes as a
        // JSON array — a JSON object would break `categories.length` on the frontend.
        return array_values(array_map(fn($cat) => [
            'id'     => $cat->term_id,
            'name'   => self::decodeName($cat->name),
            'slug'   => $cat->slug,
            'count'  => $cat->count,
            'parent' => $cat->parent,
        ], $categories));
    }

    private static function getAttributes(): array
    {
        $result = [];

        foreach (wc_get_attribute_taxonomies() as $attribute) {
            $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);
            $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true]);

            if (!is_wp_error($terms) && !empty($terms)) {
                $result[] = [
                    'name'    => self::decodeName($attribute->attribute_label),
                    'slug'    => $taxonomy,
                    'type'    => $attribute->attribute_type,
                    'options' => array_map(fn($t) => [
                        'name' => self::decodeName($t->name),
                        'slug' => $t->slug,
                        'count' => $t->count,
                    ], $terms),
                ];
            }
        }

        return $result;
    }

    private static function getPriceRange(): array
    {
        global $wpdb;

        $row = $wpdb->get_row(
            "SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price,
                    MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_price' AND meta_value != ''
             AND post_id IN (
                 SELECT ID FROM {$wpdb->posts}
                 WHERE post_type = 'product' AND post_status = 'publish'
             )"
        );

        return [
            'min' => (float) ($row->min_price ?? 0),
            'max' => (float) ($row->max_price ?? 100),
        ];
    }
}
