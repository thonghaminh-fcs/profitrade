<?php

/**
 * AI Zippy Child Theme Functions
 *
 * Project-specific customizations live here.
 * The parent theme (ai-zippy) handles core assets, REST APIs, cart/checkout, etc.
 * This file adds the child's own Vite-built assets and auto-registers any
 * client-specific Gutenberg blocks from assets/blocks/.
 */

defined('ABSPATH') || exit;

// =============================================================================
// Vite manifest reader — child's own assets/dist/.vite/manifest.json
// Mirrors the parent's AiZippy\Core\ViteAssets but scoped to this theme.
// =============================================================================

/**
 * Read the child theme's Vite manifest (cached per-request).
 */
function ai_zippy_child_vite_manifest(): array
{
    static $manifest = null;
    if ($manifest !== null) {
        return $manifest;
    }

    $path = get_stylesheet_directory() . '/assets/dist/.vite/manifest.json';
    if (!file_exists($path)) {
        return $manifest = [];
    }

    $decoded = json_decode(file_get_contents($path), true);
    return $manifest = is_array($decoded) ? $decoded : [];
}

/**
 * Enqueue a child-theme Vite asset by its source entry key.
 * Entry keys are the full path from repo root, e.g.:
 *   src/wp-content/themes/ai-zippy-child/src/js/child.js
 */
function ai_zippy_child_enqueue_vite(string $handle, string $entry, array $js_deps = []): void
{
    $manifest = ai_zippy_child_vite_manifest();
    if (empty($manifest[$entry])) {
        return;
    }

    $asset    = $manifest[$entry];
    $dist_uri = get_stylesheet_directory_uri() . '/assets/dist';
    $dist_dir = get_stylesheet_directory() . '/assets/dist';

    // JS entry — skip stub files emitted by Vite when an SCSS entry has no content yet
    if (!empty($asset['file']) && str_ends_with($asset['file'], '.js')) {
        $file_path = $dist_dir . '/' . $asset['file'];
        $is_scss_entry = !empty($asset['src']) && str_ends_with($asset['src'], '.scss');
        $is_tiny_stub  = file_exists($file_path) && filesize($file_path) < 100;

        if (!($is_scss_entry && $is_tiny_stub)) {
            $version = file_exists($file_path) ? filemtime($file_path) : false;
            wp_enqueue_script($handle, $dist_uri . '/' . $asset['file'], $js_deps, $version, true);
        }
    }

    // CSS-only entry (manifest file is .css directly)
    if (!empty($asset['file']) && str_ends_with($asset['file'], '.css') && empty($asset['css'])) {
        $file_path = $dist_dir . '/' . $asset['file'];
        $version   = file_exists($file_path) ? filemtime($file_path) : false;
        // Depend on the parent theme's style so child CSS loads after it.
        wp_enqueue_style($handle, $dist_uri . '/' . $asset['file'], ['ai-zippy-theme-css-0'], $version);
    }

    // CSS bundled with JS entry
    if (!empty($asset['css'])) {
        foreach ($asset['css'] as $i => $css_file) {
            $file_path = $dist_dir . '/' . $css_file;
            $version   = file_exists($file_path) ? filemtime($file_path) : false;
            wp_enqueue_style(
                $handle . '-css-' . $i,
                $dist_uri . '/' . $css_file,
                ['ai-zippy-theme-css-0'],
                $version
            );
        }
    }
}

/**
 * Mark child theme scripts as ES modules (Vite outputs ESM).
 */
add_filter('script_loader_tag', function (string $tag, string $handle): string {
    if (str_starts_with($handle, 'ai-zippy-child')) {
        return str_replace(' src=', ' type="module" src=', $tag);
    }
    return $tag;
}, 10, 2);

/**
 * Enqueue child theme assets (after parent so overrides work).
 */
add_action('wp_enqueue_scripts', function (): void {
    $base = 'src/wp-content/themes/ai-zippy-child/src';

    ai_zippy_child_enqueue_vite('ai-zippy-child',       $base . '/js/child.js');
    ai_zippy_child_enqueue_vite('ai-zippy-child-style', $base . '/scss/style.scss');
}, 20);

// =============================================================================
// Auto-register child theme blocks (wp-scripts build output)
// =============================================================================

add_action('init', function (): void {
    $blocks_dir = get_stylesheet_directory() . '/assets/blocks';
    if (!is_dir($blocks_dir)) {
        return;
    }
    foreach (glob($blocks_dir . '/*/block.json') as $block_json) {
        register_block_type(dirname($block_json));
    }
});
