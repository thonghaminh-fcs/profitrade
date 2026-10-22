<?php

namespace AiZippy\Core;

defined('ABSPATH') || exit;

/**
 * Vite manifest reader and asset enqueue helper.
 */
class ViteAssets
{
    private static ?array $manifest = null;
    private static ?array $adminManifest = null;

    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueueTheme']);
        add_filter('script_loader_tag', [self::class, 'addModuleType'], 10, 2);
    }

    /**
     * Enqueue the main theme JS + CSS.
     *
     * Runtime attaches the frontend config (including the WC Store API nonce)
     * to this handle at priority 15.
     */
    public static function enqueueTheme(): void
    {
        self::enqueue('ai-zippy-theme', 'src/wp-content/themes/ai-zippy/src/js/frontend/theme.js');
    }

    /**
     * Add type="module" to Vite-built scripts.
     */
    public static function addModuleType(string $tag, string $handle): string
    {
        if (str_starts_with($handle, 'ai-zippy-')) {
            return str_replace(' src=', ' type="module" src=', $tag);
        }
        return $tag;
    }

    /**
     * Get the Vite manifest.
     */
    public static function getManifest(): array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }

        $path = AI_ZIPPY_THEME_DIR . '/assets/dist/.vite/manifest.json';

        if (!file_exists($path)) {
            return self::$manifest = [];
        }

        self::$manifest = json_decode(file_get_contents($path), true);

        return self::$manifest ?: [];
    }

    /**
     * Enqueue a Vite-built asset by its source entry key.
     */
    public static function enqueue(string $handle, string $entry): void
    {
        $manifest = self::getManifest();

        if (empty($manifest[$entry])) {
            return;
        }

        $asset = $manifest[$entry];
        $dist_uri = AI_ZIPPY_THEME_URI . '/assets/dist';
        $dist_dir = AI_ZIPPY_THEME_DIR . '/assets/dist';

        // Enqueue JS
        if (!empty($asset['file']) && str_ends_with($asset['file'], '.js')) {
            $file_path = $dist_dir . '/' . $asset['file'];
            $version = file_exists($file_path) ? filemtime($file_path) : AI_ZIPPY_THEME_VERSION;

            wp_enqueue_script(
                $handle,
                $dist_uri . '/' . $asset['file'],
                [],
                $version,
                true
            );
        }

        // CSS-only entry (no JS, file is .css directly)
        if (!empty($asset['file']) && str_ends_with($asset['file'], '.css') && empty($asset['css'])) {
            $file_path = $dist_dir . '/' . $asset['file'];
            $version = file_exists($file_path) ? filemtime($file_path) : AI_ZIPPY_THEME_VERSION;

            wp_enqueue_style(
                $handle,
                $dist_uri . '/' . $asset['file'],
                [],
                $version
            );
        }

        // Enqueue associated CSS (bundled with JS entries)
        if (!empty($asset['css'])) {
            foreach ($asset['css'] as $index => $css_file) {
                $file_path = $dist_dir . '/' . $css_file;
                $version = file_exists($file_path) ? filemtime($file_path) : AI_ZIPPY_THEME_VERSION;

                wp_enqueue_style(
                    $handle . '-css-' . $index,
                    $dist_uri . '/' . $css_file,
                    [],
                    $version
                );
            }
        }
    }

    /**
     * Read the admin Vite manifest (separate bundle, built from
     * vite.config.admin.js → assets/dist-admin/).
     */
    public static function getAdminManifest(): array
    {
        if (self::$adminManifest !== null) {
            return self::$adminManifest;
        }

        $path = AI_ZIPPY_THEME_DIR . '/assets/dist-admin/.vite/manifest.json';
        if (!file_exists($path)) {
            return self::$adminManifest = [];
        }

        $decoded = json_decode(file_get_contents($path), true);
        return self::$adminManifest = is_array($decoded) ? $decoded : [];
    }

    /**
     * Enqueue a Vite-built admin asset. Admin bundles are UMD and consume
     * WordPress's bundled React + @wordpress/components via globals, so we
     * declare the relevant WP script deps here.
     *
     * @param string   $handle    WP script handle
     * @param string   $entry     Manifest key (full path from repo root)
     * @param string[] $extraDeps Additional WP script deps (e.g. 'wp-data')
     */
    public static function enqueueAdmin(string $handle, string $entry, array $extraDeps = []): void
    {
        $manifest = self::getAdminManifest();
        if (empty($manifest[$entry])) {
            return;
        }

        $asset    = $manifest[$entry];
        $dist_uri = AI_ZIPPY_THEME_URI . '/assets/dist-admin';
        $dist_dir = AI_ZIPPY_THEME_DIR . '/assets/dist-admin';

        // WP packages we always pull in for admin apps. Keeping this list
        // minimal; additional deps can be passed via $extraDeps.
        $wp_deps = array_unique(array_merge([
            'wp-element',
            'wp-components',
            'wp-i18n',
            'wp-api-fetch',
        ], $extraDeps));

        // @wordpress/components ships a stylesheet — enqueue it.
        wp_enqueue_style('wp-components');

        // JS entry
        if (!empty($asset['file']) && str_ends_with($asset['file'], '.js')) {
            $file_path = $dist_dir . '/' . $asset['file'];
            $version   = file_exists($file_path) ? filemtime($file_path) : AI_ZIPPY_THEME_VERSION;

            wp_enqueue_script(
                $handle,
                $dist_uri . '/' . $asset['file'],
                $wp_deps,
                $version,
                true
            );

            // Set script translations target so wp-i18n works if we ever ship .po/.mo files.
            if (function_exists('wp_set_script_translations')) {
                wp_set_script_translations($handle, 'ai-zippy');
            }
        }

        // Bundled CSS (from imported .scss in the entry)
        if (!empty($asset['css'])) {
            foreach ($asset['css'] as $index => $css_file) {
                $file_path = $dist_dir . '/' . $css_file;
                $version   = file_exists($file_path) ? filemtime($file_path) : AI_ZIPPY_THEME_VERSION;

                wp_enqueue_style(
                    $handle . '-css-' . $index,
                    $dist_uri . '/' . $css_file,
                    ['wp-components'],
                    $version
                );
            }
        }
    }
}
