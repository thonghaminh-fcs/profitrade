<?php

namespace AiZippy\Product;

defined('ABSPATH') || exit;

/**
 * [ai_zippy_related_products] — renders related products on a single product page.
 *
 * Output: a CSS grid of slim product cards (image + title + price, no Add to Cart),
 * styled to match the rest of the site's product card aesthetic.
 *
 * Returns empty string when no related products exist — the wrapping
 * `data-lazy-related` block handles its own visibility via JS.
 *
 * Attributes:
 *   per_page  int  default 4   how many products to show
 *   columns   int  default 4   desktop column count
 *
 * Usage in FSE templates:
 *   <!-- wp:shortcode -->
 *   [ai_zippy_related_products per_page="4" columns="4"]
 *   <!-- /wp:shortcode -->
 */
class RelatedProductsShortcode
{
    public static function register(): void
    {
        add_shortcode('ai_zippy_related_products', [self::class, 'render']);
    }

    /**
     * @param array|string $atts
     */
    public static function render($atts = []): string
    {
        global $product;
        if (!$product instanceof \WC_Product) {
            $product = wc_get_product(get_queried_object_id());
        }
        if (!$product instanceof \WC_Product) {
            return '';
        }

        $atts = shortcode_atts([
            'per_page' => 4,
            'columns'  => 4,
        ], $atts, 'ai_zippy_related_products');

        $per_page = max(1, min(12, (int) $atts['per_page']));
        $columns  = max(1, min(6,  (int) $atts['columns']));

        $related_ids = wc_get_related_products($product->get_id(), $per_page);
        if (empty($related_ids)) {
            return '';
        }

        $products = array_filter(
            array_map('wc_get_product', $related_ids),
            fn($p) => $p instanceof \WC_Product && $p->is_visible()
        );
        if (empty($products)) {
            return '';
        }

        ob_start();
        ?>
        <div class="zp-related" data-columns="<?php echo (int) $columns; ?>">
            <div class="zp-related__grid" style="grid-template-columns: repeat(<?php echo (int) $columns; ?>, 1fr);">
                <?php foreach ($products as $p) : ?>
                    <?php echo Cards::render($p, 'slim'); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        $html = (string) ob_get_clean();

        // Strip newlines so wpautop doesn't paragraph-wrap any of our markup
        // (same trick used by ProductShortcode).
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        $html = preg_replace('/[\r\n\t]+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        return $html;
    }
}
