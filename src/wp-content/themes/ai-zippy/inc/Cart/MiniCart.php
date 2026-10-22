<?php

namespace AiZippy\Cart;

use AiZippy\Core\Features;
use AiZippy\Core\ViteAssets;

defined('ABSPATH') || exit;

/**
 * Server-side data for the ai-zippy/mini-cart block.
 *
 * The block replaces woocommerce/mini-cart. The core block ships its own React
 * bundle and internal .wc-block-* class names, which the theme used to restyle
 * with ~465 lines of CSS — a WooCommerce release could break every client site
 * at once. This class owns the data instead, so the markup and the class names
 * belong to us.
 *
 * Everything here tolerates WooCommerce being inactive: the block renders
 * nothing rather than fataling.
 *
 * Filters:
 *   ai_zippy_mini_cart_free_shipping_threshold (float)
 *   ai_zippy_mini_cart_cross_sell_ids          (int[])
 *   ai_zippy_mini_cart_icons                   (array<string, string>)
 *   ai_zippy_legacy_mini_cart_css              (bool)
 *   ai_zippy_unhook_core_mini_cart             (bool)
 */
class MiniCart
{
    /** Vite entry holding the deprecated core mini-cart overrides. */
    private const LEGACY_ENTRY = 'src/wp-content/themes/ai-zippy/src/scss/mini-cart-legacy-entry.scss';

    public static function register(): void
    {
        // Priority 20: after ViteAssets has registered the theme handle.
        add_action('wp_enqueue_scripts', [self::class, 'maybeEnqueueLegacyStyles'], 20);

        // Priority 10: WooCommerce hooks its own block in at 9.
        add_filter('hooked_block_types', [self::class, 'unhookCoreMiniCart'], 10, 4);
    }

    /**
     * Stop WooCommerce auto-inserting its mini cart next to the navigation.
     *
     * WooCommerce registers woocommerce/mini-cart as a hooked block anchored to
     * core/navigation with no area restriction, so it is injected into the
     * resolved header whether or not the theme asked for it. With our block also
     * in the header a site renders two cart controls, and the legacy stylesheet
     * gate below can never switch off.
     *
     * Removing it here rather than adding ignoredHookedBlocks to parts/header.html
     * covers headers customised in the Site Editor too, and survives a client
     * rebuilding their header from scratch.
     *
     * @param string[]                          $hooked_blocks
     * @param string                            $position
     * @param string                            $anchor
     * @param array|\WP_Post|\WP_Block_Template $context
     * @return string[]
     */
    public static function unhookCoreMiniCart($hooked_blocks, $position, $anchor, $context): array
    {
        $hooked_blocks = (array) $hooked_blocks;

        /**
         * Filter whether the theme suppresses WooCommerce's hooked mini cart.
         *
         * Defaults to true unconditionally: we never want WooCommerce's native,
         * unstyled mini cart injected into the header. When the `mini_cart`
         * feature is off our own block renders nothing, so the intended "off"
         * state is no cart UI at all — not core's block taking over. Return false
         * only if a site deliberately wants to keep core's mini cart.
         *
         * @param bool $unhook
         */
        if (!apply_filters('ai_zippy_unhook_core_mini_cart', true)) {
            return $hooked_blocks;
        }

        return array_values(array_diff($hooked_blocks, ['woocommerce/mini-cart']));
    }

    /**
     * Whether the block can render at all.
     */
    public static function available(): bool
    {
        return Features::enabled('mini_cart') && self::cart() !== null;
    }

    /**
     * The live cart, or null when WooCommerce is inactive or not booted yet.
     */
    private static function cart(): ?\WC_Cart
    {
        if (!function_exists('WC')) {
            return null;
        }

        $cart = WC()->cart;

        return $cart instanceof \WC_Cart ? $cart : null;
    }

    /**
     * Initial state rendered into the markup so the drawer is usable before —
     * and without — JavaScript.
     *
     * `subtotal` is display HTML from WooCommerce (currency symbol, tax
     * settings, decimal separators all respected). `subtotalRaw` is the plain
     * number, for the free-shipping progress bar.
     *
     * @return array{count:int, subtotal:string, subtotalRaw:float}
     */
    public static function state(): array
    {
        $cart = self::cart();

        if ($cart === null) {
            return ['count' => 0, 'subtotal' => '', 'subtotalRaw' => 0.0];
        }

        return [
            'count'       => (int) $cart->get_cart_contents_count(),
            'subtotal'    => (string) $cart->get_cart_subtotal(),
            'subtotalRaw' => (float) $cart->get_displayed_subtotal(),
        ];
    }

    /**
     * Cart line items, flattened for rendering.
     *
     * @return array<int, array{key:string, name:string, permalink:string, image:string, quantity:int, price:string}>
     */
    public static function items(): array
    {
        $cart = self::cart();

        if ($cart === null) {
            return [];
        }

        $items = [];

        foreach ($cart->get_cart() as $key => $line) {
            $product = $line['data'] ?? null;

            // A line whose product was deleted survives in the session.
            if (!$product instanceof \WC_Product) {
                continue;
            }

            $items[] = [
                'key'       => (string) $key,
                'name'      => $product->get_name(),
                'permalink' => $product->is_visible() ? $product->get_permalink() : '',
                'image'     => self::thumbnail($product),
                'quantity'  => (int) ($line['quantity'] ?? 0),
                'price'     => (string) $cart->get_product_subtotal($product, $line['quantity'] ?? 0),
            ];
        }

        return $items;
    }

    /**
     * A product thumbnail URL, falling back to the WooCommerce placeholder.
     */
    private static function thumbnail(\WC_Product $product): string
    {
        $id = $product->get_image_id();

        if ($id) {
            $src = wp_get_attachment_image_url((int) $id, 'woocommerce_thumbnail');

            if ($src) {
                return $src;
            }
        }

        return function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '';
    }

    /**
     * The lowest "free shipping over X" amount configured in WooCommerce.
     *
     * Reads every shipping zone (including "Locations not covered", zone 0) and
     * takes the smallest minimum order amount, since that is the first
     * threshold a customer can reach. Returns 0.0 when no zone offers it.
     */
    public static function freeShippingThreshold(): float
    {
        $threshold = 0.0;

        if (class_exists(\WC_Shipping_Zones::class)) {
            $zone_ids = array_column(\WC_Shipping_Zones::get_zones(), 'zone_id');
            // Zone 0 is "Locations not covered by your other zones" and is not
            // part of get_zones().
            $zone_ids[] = 0;

            foreach ($zone_ids as $zone_id) {
                $zone = \WC_Shipping_Zones::get_zone((int) $zone_id);

                // get_zone() returns false for an id that no longer exists.
                if (!$zone instanceof \WC_Shipping_Zone) {
                    continue;
                }

                foreach ($zone->get_shipping_methods(true) as $method) {
                    if ($method->id !== 'free_shipping') {
                        continue;
                    }

                    // "A minimum order amount" must actually be part of the
                    // requirement, otherwise min_amount is a leftover value.
                    if (!in_array($method->get_option('requires'), ['min_amount', 'either', 'both'], true)) {
                        continue;
                    }

                    $amount = (float) $method->get_option('min_amount');

                    if ($amount > 0 && ($threshold === 0.0 || $amount < $threshold)) {
                        $threshold = $amount;
                    }
                }
            }
        }

        /**
         * Filter the free-shipping threshold shown by the mini cart.
         *
         * @param float $threshold 0.0 disables the progress bar.
         */
        return (float) apply_filters('ai_zippy_mini_cart_free_shipping_threshold', $threshold);
    }

    /**
     * Cross-sell product IDs for the items currently in the cart.
     *
     * @return int[]
     */
    public static function crossSellIds(int $limit = 3): array
    {
        $cart = self::cart();
        $ids  = $cart !== null ? array_map('intval', $cart->get_cross_sells()) : [];

        /**
         * Filter the cross-sell products offered in the mini cart.
         *
         * @param int[] $ids
         * @param int   $limit
         */
        $ids = (array) apply_filters('ai_zippy_mini_cart_cross_sell_ids', $ids, $limit);

        return array_slice(array_values(array_unique(array_map('intval', $ids))), 0, max(0, $limit));
    }

    /**
     * Toggle icons, keyed by the block's iconStyle attribute.
     *
     * Inline SVG rather than an icon font or sprite: the toggle is above the
     * fold in the header and must paint without a second request. `stroke` uses
     * currentColor so the child theme controls the colour.
     *
     * @return array<string, string>
     */
    public static function icons(): array
    {
        $stroke = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"';

        $icons = [
            'bag' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" ' . $stroke . '>'
                . '<path d="M6 7h12l-1 13H7L6 7Z"/><path d="M9 7V5a3 3 0 0 1 6 0v2"/></svg>',

            'cart' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" ' . $stroke . '>'
                . '<path d="M3 4h2l2.4 11h10.2L20 7H6"/><circle cx="9" cy="19" r="1.6"/><circle cx="17" cy="19" r="1.6"/></svg>',

            'basket' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" ' . $stroke . '>'
                . '<path d="M4 9h16l-1.5 10h-13L4 9Z"/><path d="M9 9 12 3l3 6"/><path d="M10 13v3M14 13v3"/></svg>',
        ];

        /**
         * Filter the mini cart icon set. Values are raw SVG markup and are
         * echoed unescaped, so only add trusted markup.
         *
         * @param array<string, string> $icons
         */
        return (array) apply_filters('ai_zippy_mini_cart_icons', $icons);
    }

    /**
     * Load the deprecated core mini-cart overrides only where they still apply.
     *
     * The 465-line override sheet used to be part of style.scss and therefore
     * shipped to every page. It is now a separate entry, loaded only while a
     * site still has woocommerce/mini-cart in its header — which stops being
     * true as soon as the site adopts ai-zippy/mini-cart.
     */
    public static function maybeEnqueueLegacyStyles(): void
    {
        /**
         * Filter whether to load the deprecated core mini-cart stylesheet.
         *
         * @param bool $needed
         */
        if (!apply_filters('ai_zippy_legacy_mini_cart_css', self::legacyStylesNeeded())) {
            return;
        }

        ViteAssets::enqueue('ai-zippy-mini-cart-legacy', self::LEGACY_ENTRY);
    }

    /**
     * Whether the header still renders the core WooCommerce mini cart.
     *
     * Checks the resolved header template part, so a part customised in the
     * Site Editor (stored in the database) is read instead of the theme file.
     */
    private static function legacyStylesNeeded(): bool
    {
        if (!function_exists('get_block_template')) {
            return false;
        }

        $part = get_block_template(get_stylesheet() . '//header', 'wp_template_part');

        if (!$part || empty($part->content)) {
            return false;
        }

        return has_block('woocommerce/mini-cart', $part->content);
    }
}
