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
// Feature includes — hooks that must run on EVERY request (Store-API checkout
// order persistence, express fee, contact-form AJAX handler). Block render.php
// files only run when the block renders, so they cannot own these.
// =============================================================================

require get_stylesheet_directory() . '/inc/contact-form.php';
require get_stylesheet_directory() . '/inc/checkout-fulfilment.php';

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

    // Depend on the parent theme's style so child CSS loads after it —
    // but only when that handle actually exists (it is registered by the
    // parent's own Vite loader and may be absent in some contexts).
    $css_deps = wp_style_is('ai-zippy-theme-css-0', 'registered') ? ['ai-zippy-theme-css-0'] : [];

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
        wp_enqueue_style($handle, $dist_uri . '/' . $asset['file'], $css_deps, $version);
    }

    // CSS bundled with JS entry
    if (!empty($asset['css'])) {
        foreach ($asset['css'] as $i => $css_file) {
            $file_path = $dist_dir . '/' . $css_file;
            $version   = file_exists($file_path) ? filemtime($file_path) : false;
            wp_enqueue_style(
                $handle . '-css-' . $i,
                $dist_uri . '/' . $css_file,
                $css_deps,
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

    // Runtime data for child.js (image fallback resolver, site urls).
    if (wp_script_is('ai-zippy-child', 'registered')) {
        wp_add_inline_script(
            'ai-zippy-child',
            'window.smAssets=' . wp_json_encode([
                'fallbackImg' => ai_zippy_child_img('product-fallback.jpg'),
                'homeUrl'     => home_url('/'),
                'shopUrl'     => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'),
            ]) . ';',
            'before'
        );
    }
}, 20);

// =============================================================================
// Theme setup — editor styles (CSS parity in the block editor)
// =============================================================================

add_action('after_setup_theme', function (): void {
    add_theme_support('editor-styles');
    add_editor_style('assets/dist/css/child-style.css');
});

// =============================================================================
// Image resolver — Media Library first (option pfx_media_map), theme asset fallback
// =============================================================================

/**
 * Resolve an image URL by its semantic name (docs/build/images.json).
 * Reads option `pfx_media_map` (name => attachment ID, written by
 * scripts/upload-images.php); falls back to the theme's assets/images/ copy.
 */
function ai_zippy_child_img(string $name): string
{
    $map = (array) get_option('pfx_media_map', []);
    if (!empty($map[$name])) {
        $url = wp_get_attachment_image_url((int) $map[$name], 'large');
        if ($url) {
            return $url;
        }
    }
    return get_stylesheet_directory_uri() . '/assets/images/' . rawurlencode($name);
}

/**
 * [sm_img] shortcode — used by parts/header.html and parts/footer.html,
 * where PHP cannot run inside template markup.
 *   [sm_img name="logo-smart.jpg" alt="S.Mart" class="sm-header__logo" width="52" height="52"]
 */
add_shortcode('sm_img', function (array $atts): string {
    $atts = shortcode_atts([
        'name'    => '',
        'alt'     => '',
        'class'   => '',
        'width'   => '',
        'height'  => '',
        'loading' => 'lazy',
    ], $atts, 'sm_img');

    if ($atts['name'] === '') {
        return '';
    }

    $attr = [
        'class'    => $atts['class'],
        'alt'      => $atts['alt'],
        'loading'  => $atts['loading'],
        'decoding' => 'async',
    ];
    if ($atts['width'] !== '') {
        $attr['width'] = (int) $atts['width'];
    }
    if ($atts['height'] !== '') {
        $attr['height'] = (int) $atts['height'];
    }

    $map = (array) get_option('pfx_media_map', []);
    if (!empty($map[$atts['name']])) {
        $id  = (int) $map[$atts['name']];
        $img = wp_get_attachment_image($id, 'large', false, $attr);
        if ($img !== '') {
            return $img;
        }
    }

    $src = get_stylesheet_directory_uri() . '/assets/images/' . rawurlencode($atts['name']);
    $out = '<img src="' . esc_url($src) . '"';
    foreach ($attr as $key => $value) {
        $out .= ' ' . $key . '="' . esc_attr($value) . '"';
    }
    return $out . '>';
});

// =============================================================================
// Block category for S.Mart child blocks
// =============================================================================

add_filter('block_categories_all', function (array $categories): array {
    array_unshift($categories, [
        'slug'  => 'sm-sections',
        'title' => __('S.Mart Sections', 'ai-zippy-child'),
        'icon'  => null,
    ]);
    return $categories;
}, 10, 1);

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
