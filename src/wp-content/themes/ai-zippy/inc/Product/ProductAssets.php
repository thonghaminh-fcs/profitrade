<?php

namespace AiZippy\Product;

defined('ABSPATH') || exit;

/**
 * Enqueue single-product page assets only on actual product pages.
 *
 * Critical context: WooCommerce's WC_Frontend_Scripts::load_scripts() has an
 * explicit `is_product() && ! wp_is_block_theme()` guard that SKIPS enqueueing
 * wc-single-product / wc-add-to-cart-variation / gallery scripts on FSE themes.
 *
 * Since our single-product template uses an FSE template + custom shortcode,
 * we need those scripts ourselves. We force-enqueue them here AND localize
 * their params (which WC normally does inside that same skipped block).
 *
 * Also ships our own product app: lightbox, swatches, sticky bar, lazy-load.
 */
class ProductAssets
{
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
        add_filter('woocommerce_breadcrumb_defaults', [self::class, 'breadcrumbDefaults']);
        add_filter('body_class', [self::class, 'bodyClass']);
    }

    /**
     * Add a body class for featured products so the CSS-driven "Best Seller"
     * ribbon shows on the gallery without needing inline markup.
     */
    public static function bodyClass(array $classes): array
    {
        if (is_product()) {
            $product = wc_get_product(get_queried_object_id());
            if ($product instanceof \WC_Product && $product->is_featured()) {
                $classes[] = 'is-featured-product';
            }
        }
        return $classes;
    }

    /**
     * Replace WooCommerce's default breadcrumb separator with a styled chevron.
     * Wrapping the separator in a span lets us style it independently of the
     * surrounding links. Only applied on single product pages.
     */
    public static function breadcrumbDefaults(array $defaults): array
    {
        if (!is_product()) {
            return $defaults;
        }
        $defaults['delimiter'] = '<span class="zp-bc-sep" aria-hidden="true">&rsaquo;</span>';
        return $defaults;
    }

    public static function enqueue(): void
    {
        if (!is_product()) {
            return;
        }

        // Our product app
        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-product',
            'src/wp-content/themes/ai-zippy/src/js/frontend/product/index.js'
        );

        // FSE themes skip WC's gallery + variation scripts (the
        // `is_product() && ! wp_is_block_theme()` guard in WC_Frontend_Scripts).
        // Force-enqueue both groups here.
        wp_enqueue_script('jquery');

        // Gallery scripts — needed for all products to convert the stacked
        // .woocommerce-product-gallery__image divs into a flexslider carousel
        // with thumbnail strip below.
        if (current_theme_supports('wc-product-gallery-slider')) {
            wp_enqueue_script('flexslider');
        }
        wp_enqueue_script('wc-single-product');

        // Variation scripts — only for variable products.
        global $product;
        if (!$product instanceof \WC_Product) {
            $product = wc_get_product(get_queried_object_id());
        }
        $is_variable = $product instanceof \WC_Product && $product->is_type('variable');

        if ($is_variable) {
            wp_enqueue_script('wc-add-to-cart-variation');
        }

        // Localize the params each script reads at runtime. WC normally does
        // this inside the FSE-skipped branch in WC_Frontend_Scripts.
        wp_localize_script('wc-single-product', 'wc_single_product_params', [
            'i18n_required_rating_text'         => esc_attr__('Please select a rating', 'woocommerce'),
            'i18n_product_gallery_trigger_text' => esc_attr__('View full-screen image gallery', 'woocommerce'),
            'review_rating_required'            => function_exists('wc_review_ratings_required') && wc_review_ratings_required() ? 'yes' : 'no',
            'flexslider'                        => apply_filters('woocommerce_single_product_carousel_options', [
                'rtl'            => is_rtl(),
                'animation'      => 'slide',
                'smoothHeight'   => true,
                'directionNav'   => false,
                'controlNav'     => 'thumbnails',
                'slideshow'      => false,
                'animationSpeed' => 500,
                'animationLoop'  => false,
                'allowOneSlide'  => false,
            ]),
            'zoom_enabled'                      => apply_filters('woocommerce_single_product_zoom_enabled', current_theme_supports('wc-product-gallery-zoom')),
            'zoom_options'                      => apply_filters('woocommerce_single_product_zoom_options', []),
            'photoswipe_enabled'                => apply_filters('woocommerce_single_product_photoswipe_enabled', current_theme_supports('wc-product-gallery-lightbox')),
            'photoswipe_options'                => apply_filters('woocommerce_single_product_photoswipe_options', []),
            'flexslider_enabled'                => apply_filters('woocommerce_single_product_flexslider_enabled', current_theme_supports('wc-product-gallery-slider')),
        ]);

        if ($is_variable) {
            wp_localize_script('wc-add-to-cart-variation', 'wc_add_to_cart_variation_params', [
                'wc_ajax_url'                      => \WC_AJAX::get_endpoint('%%endpoint%%'),
                'i18n_no_matching_variations_text' => esc_attr__('Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce'),
                'i18n_make_a_selection_text'       => esc_attr__('Please select some product options before adding this product to your cart.', 'woocommerce'),
                'i18n_unavailable_text'            => esc_attr__('Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce'),
                'i18n_reset_alert_text'            => esc_attr__('Your selection has been reset. Please select some product options before adding this product to your cart.', 'woocommerce'),
            ]);

            // The variation script needs WC's wp.template embedded — used to
            // render the matched variation's price/image into .single_variation.
            add_action('wp_footer', [self::class, 'printVariationTemplate']);
        }
    }

    /**
     * Output the WC variation wp.template that wc-add-to-cart-variation reads.
     */
    public static function printVariationTemplate(): void
    {
        if (!is_product()) {
            return;
        }
        if (function_exists('wc_get_template')) {
            wc_get_template('single-product/add-to-cart/variation.php');
        }
    }
}
