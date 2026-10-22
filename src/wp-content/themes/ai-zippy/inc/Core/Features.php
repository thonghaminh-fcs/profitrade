<?php

namespace AiZippy\Core;

use AiZippy\Admin\ThemeOptions;

defined('ABSPATH') || exit;

/**
 * Core feature registry.
 *
 * The parent theme ships behaviour, the child theme ships design. A client site
 * must therefore be able to switch a core feature off without forking the
 * parent, so every feature backed by a frontend module or a block is declared
 * here and resolved in this order:
 *
 *   DEFAULTS  →  stored option (ai_zippy_feature_{key})  →  filters
 *
 * Filters:
 *   ai_zippy_features       (array) the whole resolved map
 *   ai_zippy_feature_{key}  (bool)  one feature
 *
 * Example — disable the sticky header from a child theme:
 *   add_filter('ai_zippy_feature_sticky_header', '__return_false');
 */
class Features
{
    /**
     * Prefix for per-feature options in wp_options. Nothing writes these yet;
     * they exist so a settings screen can override a default later without
     * another round of plumbing.
     */
    private const OPTION_PREFIX = 'ai_zippy_feature_';

    /**
     * Every core feature and its out-of-the-box state.
     *
     * Keys stay snake_case and are exposed to JS verbatim via Runtime, so a
     * feature is referenced by the exact same name in PHP and in JS.
     */
    public const DEFAULTS = [
        'sticky_header'      => true,
        'mini_cart'          => true,
        'header_cart_button' => true,
        'search_typeahead'   => true,
        'ajax_add_to_cart'   => true,
        'scroll_to_top'      => true,
        'shop_view_toggle'   => true,
        'wishlist'           => true,
        'loading_page'       => false,
    ];

    /**
     * The resolved map, keyed by feature name.
     */
    public static function all(): array
    {
        $features = [];

        foreach (self::DEFAULTS as $key => $default) {
            $features[$key] = self::resolve($key, $default);
        }

        /**
         * Filter the whole feature map.
         *
         * @param array<string, bool> $features
         */
        $features = (array) apply_filters('ai_zippy_features', $features);

        // A filter returning junk must not turn every check truthy.
        return array_map('boolval', $features);
    }

    /**
     * Whether a single feature is on. Unknown keys are off — a typo should fail
     * closed rather than silently enable something.
     */
    public static function enabled(string $key): bool
    {
        if (!array_key_exists($key, self::DEFAULTS)) {
            return false;
        }

        $features = self::all();

        return (bool) ($features[$key] ?? false);
    }

    /**
     * Resolve one feature: legacy option first (so settings saved before this
     * registry existed still apply), then the generic option, then the
     * per-feature filter.
     */
    private static function resolve(string $key, bool $default): bool
    {
        $value = $default;

        $legacy = self::legacyResolvers()[$key] ?? null;
        if ($legacy !== null && is_callable($legacy)) {
            $value = (bool) call_user_func($legacy);
        } else {
            $stored = get_option(self::OPTION_PREFIX . $key, null);
            if ($stored !== null) {
                $value = rest_sanitize_boolean($stored);
            }
        }

        /**
         * Filter a single feature.
         *
         * @param bool $value
         */
        return (bool) apply_filters('ai_zippy_feature_' . $key, $value);
    }

    /**
     * Features whose state already lives in an existing ThemeOptions option.
     * These keep reading their original option so saved settings survive.
     *
     * @return array<string, callable>
     */
    private static function legacyResolvers(): array
    {
        return [
            'wishlist'     => [ThemeOptions::class, 'isWishlistEnabled'],
            'loading_page' => [ThemeOptions::class, 'isEnabled'],
        ];
    }
}
