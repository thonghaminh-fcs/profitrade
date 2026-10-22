<?php

namespace AiZippy\Product;

defined('ABSPATH') || exit;

/**
 * Shared product-card renderer.
 *
 * One canonical card markup used across:
 *   - Product Showcase block (variant: 'full')
 *   - Related Products on the single product page (variant: 'slim')
 *
 * Variants
 *   full → image + categories + title + (optional) rating + price + Add to Cart button
 *   slim → image + title + price (no Add to Cart, no thumbs strip, no wishlist)
 *
 * Usage:
 *   echo Cards::render($product);                            // defaults to 'full'
 *   echo Cards::render($product, 'slim');
 *   echo Cards::render($product, 'full', [
 *       'show_sale'   => true,
 *       'show_rating' => false,
 *       'show_cart'   => true,
 *   ]);
 */
class Cards
{
    /**
     * Render a single product card. Returns HTML — call sites can echo directly.
     *
     * @param \WC_Product $product
     * @param string      $variant 'full' | 'slim'
     * @param array       $opts    {
     *     show_sale     : bool   default true   show -XX% sale badge
     *     show_rating   : bool   default true   show star rating (full variant only)
     *     show_cart     : bool   default true   show Add to Cart button (full variant only)
     *     show_wishlist : bool   default from the Enable Wishlist theme option
     * }
     */
    public static function render(\WC_Product $product, string $variant = 'full', array $opts = []): string
    {
        $opts = array_merge([
            'show_sale'     => true,
            'show_rating'   => true,
            'show_cart'     => true,
            'show_wishlist' => \AiZippy\Admin\ThemeOptions::isWishlistEnabled(),
        ], $opts);

        if ($variant === 'slim') {
            return self::renderSlim($product, $opts);
        }
        return self::renderFull($product, $opts);
    }

    // -------------------------------------------------------------------------
    // Full variant — used by Product Showcase block
    // -------------------------------------------------------------------------

    /**
     * Full variant — markup mirrors the React shop card (.sf__card) so block
     * cards and shop-page cards share one CSS source of truth.
     */
    private static function renderFull(\WC_Product $product, array $opts): string
    {
        $image_id    = $product->get_image_id();
        $gallery_ids = $product->get_gallery_image_ids();
        $all_images  = array_values(array_filter(array_merge(
            [$image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : ''],
            array_map(fn($id) => wp_get_attachment_image_url($id, 'woocommerce_thumbnail'), $gallery_ids)
        )));
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
        $cat_name   = !empty($categories) ? $categories[0] : '';

        $on_sale  = $product->is_on_sale();
        $regular  = (float) $product->get_regular_price();
        $sale     = (float) $product->get_sale_price();
        $sale_pct = ($on_sale && $regular > 0 && $sale > 0)
            ? (int) round((($regular - $sale) / $regular) * 100)
            : 0;
        $extra_thumbs = count($all_images) > 3 ? count($all_images) - 3 : 0;

        // Only meaningful when WooCommerce is tracking stock for this product —
        // get_stock_quantity() returns null when "Track stock" is off, which
        // would otherwise read as "always low stock".
        $low_stock_threshold = 10;
        $stock_quantity      = $product->get_stock_quantity();
        $has_low_stock       = $product->get_stock_status() === 'instock'
            && $product->get_manage_stock()
            && is_int($stock_quantity)
            && $stock_quantity > 0
            && $stock_quantity < $low_stock_threshold;

        ob_start();
        ?>
        <div class="sf__card" data-images="<?php echo esc_attr(wp_json_encode($all_images)); ?>">
            <div class="sf__card-image">
                <a href="<?php echo esc_url($product->get_permalink()); ?>">
                    <img src="<?php echo esc_url($all_images[0] ?? wc_placeholder_img_src()); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" loading="lazy" />
                </a>

                <?php if ($opts['show_sale'] && $on_sale) : ?>
                    <span class="sf__badge sf__badge--sale">
                        <?php echo $sale_pct > 0 ? esc_html($sale_pct) . '% OFF' : 'Sale'; ?>
                    </span>
                <?php endif; ?>

                <?php if ($product->get_stock_status() === 'outofstock') : ?>
                    <span class="sf__badge sf__badge--oos">Sold Out</span>
                <?php elseif ($product->get_stock_status() === 'onbackorder') : ?>
                    <span class="sf__badge sf__badge--backorder">On Backorder</span>
                <?php endif; ?>

                <?php if ($opts['show_wishlist']) : ?>
                    <button class="sf__card-wish" type="button" data-product-id="<?php echo (int) $product->get_id(); ?>" aria-label="Add to wishlist" aria-pressed="false">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>
                <?php endif; ?>

                <?php if (count($all_images) > 1) : ?>
                    <div class="sf__card-thumbs">
                        <?php foreach (array_slice($all_images, 0, 3) as $i => $img) : ?>
                            <button class="sf__card-thumb<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button" data-index="<?php echo (int) $i; ?>">
                                <img src="<?php echo esc_url($img); ?>" alt="" />
                            </button>
                        <?php endforeach; ?>
                        <?php if ($extra_thumbs > 0) : ?>
                            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="sf__card-thumb sf__card-thumb--more">
                                +<?php echo (int) $extra_thumbs; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sf__card-info">
                <?php if ($cat_name) : ?>
                    <span class="sf__card-cat"><?php echo esc_html($cat_name); ?></span>
                <?php endif; ?>

                <a href="<?php echo esc_url($product->get_permalink()); ?>" class="sf__card-title">
                    <?php echo esc_html($product->get_name()); ?>
                </a>

                <?php if ($opts['show_rating'] && $product->get_average_rating() > 0) : ?>
                    <div class="sf__card-rating">
                        <?php echo wc_get_rating_html($product->get_average_rating(), $product->get_rating_count()); // phpcs:ignore ?>
                    </div>
                <?php endif; ?>

                <div class="sf__card-pricing">
                    <span class="sf__card-price">
                        <?php echo $product->get_price_html(); // phpcs:ignore ?>
                    </span>
                    <?php if ($has_low_stock) : ?>
                        <p class="sf__card-low-stock">
                            <?php printf(esc_html__('Only %d left', 'ai-zippy'), (int) $stock_quantity); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($opts['show_cart']) : ?>
                    <div class="sf__card-actions">
                        <?php if ($product->get_stock_status() === 'outofstock') : ?>
                            <button class="sf__card-btn" type="button" disabled>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                </svg>
                                <span class="sf__card-btn-label"><?php esc_html_e('Sold Out', 'ai-zippy'); ?></span>
                            </button>
                        <?php else :
                            // Variable / grouped / external products can't be AJAX-added without
                            // a chosen variation, so we link them to the product page instead.
                            $needs_options = !$product->is_type('simple');
                            $btn_classes   = 'sf__card-btn' . ($needs_options ? '' : ' az-add-to-cart');
                            $btn_href      = $needs_options ? $product->get_permalink() : $product->add_to_cart_url();
                            $btn_label     = $needs_options ? __('SELECT OPTIONS', 'ai-zippy') : __('ADD TO CART', 'ai-zippy');
                        ?>
                            <a href="<?php echo esc_url($btn_href); ?>" class="<?php echo esc_attr($btn_classes); ?>" data-product-id="<?php echo (int) $product->get_id(); ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                </svg>
                                <span class="sf__card-btn-label"><?php echo esc_html($btn_label); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // Slim variant — used by Related Products on single product page
    // -------------------------------------------------------------------------

    private static function renderSlim(\WC_Product $product, array $opts): string
    {
        $image_url = $product->get_image_id()
            ? wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail')
            : wc_placeholder_img_src();

        $on_sale  = $product->is_on_sale();
        $regular  = (float) $product->get_regular_price();
        $sale     = (float) $product->get_sale_price();
        $sale_pct = ($on_sale && $regular > 0 && $sale > 0)
            ? (int) round((($regular - $sale) / $regular) * 100)
            : 0;

        ob_start();
        ?>
        <div class="sf__card sf__card--slim">
            <?php /* Everything but the actions row stays inside one link — an
                <a> can't contain a nested <button>/<a> without producing
                invalid, unreliably-clickable HTML, so the Add to Cart button
                below has to sit as a sibling of this link rather than
                inside it. */ ?>
            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="sf__card-slim-link">
                <div class="sf__card-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" loading="lazy" />

                    <?php if ($opts['show_sale'] && $on_sale) : ?>
                        <span class="sf__badge sf__badge--sale">
                            <?php echo $sale_pct > 0 ? esc_html($sale_pct) . '% OFF' : 'Sale'; ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($product->get_stock_status() === 'outofstock') : ?>
                        <span class="sf__badge sf__badge--oos">Sold Out</span>
                    <?php elseif ($product->get_stock_status() === 'onbackorder') : ?>
                        <span class="sf__badge sf__badge--backorder">On Backorder</span>
                    <?php endif; ?>
                </div>

                <div class="sf__card-info">
                    <span class="sf__card-title">
                        <?php echo esc_html($product->get_name()); ?>
                    </span>
                    <div class="sf__card-price">
                        <?php echo $product->get_price_html(); // phpcs:ignore ?>
                    </div>
                </div>
            </a>

            <?php if ($opts['show_cart']) : ?>
                <div class="sf__card-actions sf__card-actions--slim">
                    <?php if ($product->get_stock_status() === 'outofstock') : ?>
                        <button class="sf__card-btn" type="button" disabled>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                            </svg>
                            <span class="sf__card-btn-label"><?php esc_html_e('Sold Out', 'ai-zippy'); ?></span>
                        </button>
                    <?php else :
                        // Variable / grouped / external products can't be AJAX-added without
                        // a chosen variation, so we link them to the product page instead.
                        $needs_options = !$product->is_type('simple');
                        $btn_classes   = 'sf__card-btn' . ($needs_options ? '' : ' az-add-to-cart');
                        $btn_href      = $needs_options ? $product->get_permalink() : $product->add_to_cart_url();
                        $btn_label     = $needs_options ? __('SELECT OPTIONS', 'ai-zippy') : __('ADD TO CART', 'ai-zippy');
                    ?>
                        <a href="<?php echo esc_url($btn_href); ?>" class="<?php echo esc_attr($btn_classes); ?>" data-product-id="<?php echo (int) $product->get_id(); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            <span class="sf__card-btn-label"><?php echo esc_html($btn_label); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
