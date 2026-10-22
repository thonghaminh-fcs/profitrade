<?php

namespace AiZippy\Core;

defined('ABSPATH') || exit;

/**
 * Frontend runtime configuration.
 *
 * Single source of truth for everything the frontend JS needs from the server:
 * REST endpoints, nonces, page URLs, DOM selectors, feature flags, tunables and
 * translated strings. Exposed as one global so core modules never hardcode a
 * URL, a piece of text or a design-defined selector.
 *
 *   window.aiZippy = {
 *     rest:      { url, nonce },
 *     endpoints: { search },
 *     storeApi:  { nonce, timestamp },
 *     urls:      { home, shop, cart, checkout, account, search },
 *     selectors: { header, footer },
 *     features:  { ... },
 *     settings:  { ... },
 *     i18n:      { ... },
 *   }
 *
 * Filters:
 *   ai_zippy_runtime_config   (array) the whole payload, applied last
 *   ai_zippy_selectors        (array) DOM contract between core JS and design
 *   ai_zippy_runtime_settings (array) numeric tunables
 */
class Runtime
{
    /** Handle the config is attached to (enqueued by ViteAssets at priority 10). */
    private const HANDLE = 'ai-zippy-theme';

    /**
     * Register hooks. Priority 15 so the theme handle already exists.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'inject'], 15);
    }

    /**
     * Print the config as an inline script before the theme bundle.
     */
    public static function inject(): void
    {
        if (!wp_script_is(self::HANDLE, 'registered')) {
            return;
        }

        $config = self::config();

        wp_add_inline_script(
            self::HANDLE,
            'window.aiZippy = ' . wp_json_encode($config) . ';',
            'before'
        );

        // Read through the filtered payload defensively: ai_zippy_runtime_config
        // is a public filter and a child theme may return a trimmed array.
        $store_api = $config['storeApi'] ?? [];
        $endpoints = $config['endpoints'] ?? [];
        $rest      = $config['rest'] ?? [];

        // WooCommerce Blocks reads this exact global name for Store API writes.
        wp_add_inline_script(
            self::HANDLE,
            'var wcBlocksMiddlewareConfig = wcBlocksMiddlewareConfig || '
                . wp_json_encode([
                    'storeApiNonce'            => $store_api['nonce'] ?? '',
                    'wcStoreApiNonceTimestamp' => $store_api['timestamp'] ?? '',
                ]) . ';',
            'before'
        );

        // Deprecated: kept so a stale JS bundle built before window.aiZippy
        // existed keeps working. Remove once all sites are on 4.1+.
        wp_add_inline_script(
            self::HANDLE,
            'window.aiZippySearch = ' . wp_json_encode([
                'apiUrl' => $endpoints['search'] ?? '',
                'nonce'  => $rest['nonce'] ?? '',
            ]) . ';',
            'before'
        );
    }

    /**
     * Build the config payload.
     */
    public static function config(): array
    {
        $config = [
            'rest' => [
                'url'   => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
            ],
            'endpoints' => [
                'search' => esc_url_raw(rest_url('ai-zippy/v1/search')),
            ],
            'storeApi' => [
                'nonce' => wp_create_nonce('wc_store_api'),
                // String on purpose — matches what WC Blocks expects.
                'timestamp' => (string) time(),
            ],
            'urls'      => self::urls(),
            'selectors' => self::selectors(),
            'features'  => Features::all(),
            'settings'  => self::settings(),
            'i18n'      => L10n::strings(),
        ];

        /**
         * Filter the whole runtime config.
         *
         * @param array $config
         */
        return (array) apply_filters('ai_zippy_runtime_config', $config);
    }

    /**
     * Page URLs resolved at runtime. Permalinks differ per site, so nothing may
     * assume /shop/ or /cart/.
     */
    private static function urls(): array
    {
        return [
            'home'     => esc_url_raw(home_url('/')),
            'shop'     => self::pageUrl('shop'),
            'cart'     => self::pageUrl('cart'),
            'checkout' => self::pageUrl('checkout'),
            'account'  => self::pageUrl('myaccount'),
            // Core search; JS appends the ?s= parameter.
            'search'   => esc_url_raw(home_url('/')),
        ];
    }

    /**
     * A WooCommerce page permalink, falling back to the home page when
     * WooCommerce is inactive or the page is not configured.
     */
    private static function pageUrl(string $page): string
    {
        if (function_exists('wc_get_page_permalink')) {
            $url = wc_get_page_permalink($page, '');

            if (!empty($url)) {
                return esc_url_raw($url);
            }
        }

        return esc_url_raw(home_url('/'));
    }

    /**
     * DOM contract between core behaviour and client markup.
     *
     * The `.az-*` class is what the parent's template parts ship; the block
     * fallback keeps sites that have not adopted the class working.
     */
    private static function selectors(): array
    {
        $selectors = [
            'header' => '.az-header, header.wp-block-group',
            'footer' => '.az-footer, footer.wp-block-group',
        ];

        /**
         * Filter the selector contract.
         *
         * @param array<string, string> $selectors
         */
        return (array) apply_filters('ai_zippy_selectors', $selectors);
    }

    /**
     * Numeric tunables previously buried in JS constants.
     */
    private static function settings(): array
    {
        $settings = [
            'stickyOffset'     => 10,
            'searchDebounce'   => 260,
            'searchMinChars'   => 2,
            'searchMaxResults' => 8,
            'toastDuration'    => 4000,
        ];

        /**
         * Filter the runtime settings.
         *
         * @param array<string, int> $settings
         */
        return (array) apply_filters('ai_zippy_runtime_settings', $settings);
    }
}
