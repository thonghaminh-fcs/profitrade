<?php

namespace AiZippy\Core;

defined('ABSPATH') || exit;

/**
 * Theme setup: supports, blocks, block categories.
 */
class ThemeSetup
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('after_setup_theme', [self::class, 'setup']);
        add_action('init', [self::class, 'registerBlocks']);
        add_filter('block_categories_all', [self::class, 'blockCategories']);
        // Runs on wp_head at priority 7 — after wp_enqueue_scripts (priority 1
        // on wp_head), which triggers both the theme's own priority-1 and
        // core's default priority-10 registrations of wp_enqueue_global_styles,
        // and before wp_print_styles (priority 8) actually prints the inline
        // stylesheet.
        add_action('wp_head', [self::class, 'stripGlobalBlockGap'], 7);
    }

    /**
     * Theme supports.
     */
    public static function setup(): void
    {
        // Translations. Strings use the 'ai-zippy' textdomain across PHP, blocks
        // and the runtime string table, so it has to be loaded before anything
        // calls __().
        load_theme_textdomain('ai-zippy', AI_ZIPPY_THEME_DIR . '/languages');

        add_theme_support('wp-block-styles');
        add_theme_support('editor-styles');
        add_theme_support('woocommerce');
        add_theme_support('responsive-embeds');

        // Feed the built frontend CSS into the block editor so the editor
        // canvas matches the front end.
        self::editorStyles();

        // Single product gallery: slider only — main image with thumbnail
        // strip below. We skip zoom and WC's photoswipe lightbox; our custom
        // lightbox at /js/frontend/product/lightbox.js handles full-screen view.
        add_theme_support('wc-product-gallery-slider');

        // Enable revisions for pages and products.
        add_post_type_support('page', 'revisions');
        add_post_type_support('product', 'revisions');
        add_action('wp_enqueue_scripts', 'wp_enqueue_global_styles', 1);
    }

    /**
     * Register the built theme stylesheet as an editor style.
     *
     * The filenames are content-hashed, so they are resolved from the Vite
     * manifest rather than hardcoded. Paths passed to add_editor_style() are
     * relative to the theme root.
     */
    private static function editorStyles(): void
    {
        $manifest = ViteAssets::getManifest();
        $entry    = 'src/wp-content/themes/ai-zippy/src/js/frontend/theme.js';

        foreach ($manifest[$entry]['css'] ?? [] as $css_file) {
            add_editor_style('assets/dist/' . $css_file);
        }
    }

    /**
     * Register custom blocks from assets/blocks (wp-scripts build output).
     *
     * After registration, re-stamp all block style handles with the content-hash
     * version from index.asset.php so CSS cache-busts automatically on every
     * build — no need to bump block.json version manually.
     */
    public static function registerBlocks(): void
    {
        $blocks_dir = AI_ZIPPY_THEME_DIR . '/assets/blocks';

        if (!is_dir($blocks_dir)) {
            return;
        }

        foreach (glob($blocks_dir . '/*/block.json') as $block_json) {
            $block_dir  = dirname($block_json);
            $asset_file = $block_dir . '/index.asset.php';

            register_block_type($block_dir);

            // Re-stamp style handles with the content-hash version so the
            // browser cache busts on every build without touching block.json.
            if (!file_exists($asset_file)) {
                continue;
            }

            $asset   = require $asset_file;
            $version = $asset['version'] ?? null;

            if (!$version) {
                continue;
            }

            // Derive the block name slug from the directory (e.g. "search-bar")
            // and match the handles WordPress auto-registers: "ai-zippy-{slug}-style"
            // and "ai-zippy-{slug}-editor".
            $slug    = basename($block_dir);
            $handles = [
                "ai-zippy-{$slug}-style",
                "ai-zippy-{$slug}-editor",
            ];

            foreach ($handles as $handle) {
                if (wp_style_is($handle, 'registered')) {
                    wp_styles()->registered[$handle]->ver = $version;
                }
            }
        }
    }

    /**
     * Register custom block category.
     */
    public static function blockCategories(array $categories): array
    {
        array_unshift($categories, [
            'slug'  => 'ai-zippy',
            'title' => 'AI Zippy',
            'icon'  => 'star-filled',
        ]);

        return $categories;
    }

    /**
     * Remove core's `:where(.wp-site-blocks) > *` block-gap rules from the
     * global-styles inline stylesheet, without disturbing the global block-gap
     * value that navigation and other flex layouts depend on.
     *
     * Why not solve this in theme.json:
     *   - `settings.spacing.blockGap: null` gets silently coerced back to
     *     `true` by WP_Theme_JSON::do_opt_in_into_settings() because our
     *     theme opts in via `settings.appearanceTools: true`. _wp_array_get()
     *     used inside the opt-in logic treats a null value the same as an
     *     absent key, so the guard in get_root_layout_rules() still fires and
     *     WP emits the three .wp-site-blocks selectors with core's default
     *     24px value.
     *   - `styles.spacing.blockGap: "0"` zeroes the rule but ALSO zeroes the
     *     `--wp--style--block-gap` custom property that navigation/flex
     *     layouts inherit from, collapsing spacing between nav items.
     *
     * Core exposes no filter on the emitted stylesheet, so we edit the
     * inline data on wp_styles directly after all `wp_enqueue_global_styles`
     * chunks are queued and before `wp_print_styles` writes them out.
     */
    public static function stripGlobalBlockGap(): void
    {
        $styles   = wp_styles();
        $existing = $styles->get_data('global-styles', 'after');

        if (empty($existing) || !is_array($existing)) {
            return;
        }

        $pattern = '/:where\(\.wp-site-blocks\) > (?:\*|:first-child|:last-child) \{[^}]*\}/';
        $changed = false;

        foreach ($existing as $index => $chunk) {
            if (!is_string($chunk) || strpos($chunk, 'wp-site-blocks') === false) {
                continue;
            }

            $cleaned = preg_replace($pattern, '', $chunk);

            if ($cleaned !== $chunk) {
                $existing[$index] = $cleaned;
                $changed          = true;
            }
        }

        if ($changed) {
            $styles->registered['global-styles']->extra['after'] = $existing;
        }
    }
}
