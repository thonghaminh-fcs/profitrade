<?php

namespace AiZippy\Checkout;

defined('ABSPATH') || exit;

/**
 * [ai_zippy_checkout] shortcode.
 *
 * Renders either the React checkout mount point or the
 * WooCommerce default checkout, based on admin setting.
 */
class CheckoutShortcode
{
    /**
     * Holds the real checkout HTML while an inert placeholder stands in for
     * it, so wpautop() has nothing of ours left to mangle.
     */
    private static string $realOutput = '';

    private const PLACEHOLDER = '<!--AI_ZIPPY_CHECKOUT_PLACEHOLDER-->';

    /**
     * Register the shortcode.
     */
    public static function register(): void
    {
        add_shortcode('ai_zippy_checkout', [self::class, 'render']);

        // page-checkout.html renders this via a wp:shortcode block. WordPress
        // core's render_block_core_shortcode() unconditionally wpautop()s that
        // block's output, and FSE templates render through render_block() on
        // the block tree rather than the_content, so there's no the_content
        // filter to intercept. render() hands back an inert placeholder for
        // wpautop to no-op on; this restores the real HTML one level up, once
        // the wrapping wp:group has finished rendering.
        add_filter('render_block_core/group', [self::class, 'restoreRealOutput'], 20);
    }

    /**
     * Swap the real checkout HTML back in for the placeholder left by render().
     */
    public static function restoreRealOutput(string $content): string
    {
        if (self::$realOutput === '' || !str_contains($content, self::PLACEHOLDER)) {
            return $content;
        }

        // wpautop may have wrapped the placeholder comment in its own <p>.
        $pattern = '/<p>\s*' . preg_quote(self::PLACEHOLDER, '/') . '\s*<\/p>|' . preg_quote(self::PLACEHOLDER, '/') . '/';

        return preg_replace($pattern, self::$realOutput, $content, 1);
    }

    /**
     * Render the checkout output.
     */
    public static function render(): string
    {
        if (CheckoutSettings::isReact()) {
            self::$realOutput = '<div id="ai-zippy-checkout" data-cart-url="/cart" data-shop-url="/shop"></div>';
            return self::PLACEHOLDER;
        }

        // Render the WooCommerce Checkout block (used by block/FSE themes)
        $block_markup = '<!-- wp:woocommerce/checkout /-->';
        $output = do_blocks($block_markup);

        // Fallback to classic shortcode if block output is empty
        if (empty(trim($output))) {
            $output = do_shortcode('[woocommerce_checkout]');
        }

        self::$realOutput = $output;

        return self::PLACEHOLDER;
    }
}
