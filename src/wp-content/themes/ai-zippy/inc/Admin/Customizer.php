<?php

namespace AiZippy\Admin;

defined('ABSPATH') || exit;

/**
 * Customizer: logo + favicon (site icon) settings.
 *
 * - Logo:    Appearance → Customize → Site Identity → Logo
 *            Also editable via Site Editor (wp:site-logo block in header).
 * - Favicon: Appearance → Customize → Site Identity → Site Icon
 *            WordPress outputs the <link rel="icon"> tags automatically.
 */
class Customizer
{
    public static function register(): void
    {
        add_action('after_setup_theme', [self::class, 'addThemeSupport']);
        add_action('customize_register', [self::class, 'registerControls']);
    }

    /**
     * Declare theme supports so WP enables the controls in Customizer.
     */
    public static function addThemeSupport(): void
    {
        add_theme_support('custom-logo', [
            'height'               => 80,
            'width'                => 200,
            'flex-height'          => true,
            'flex-width'           => true,
            'unlink-homepage-logo' => true,
        ]);

        // Site icon (favicon) is always available in WP core —
        // declaring this support makes it show up in Customizer.
        add_theme_support('site-icon');
    }

    /**
     * Move Site Identity section to the top of the Customizer panel.
     */
    public static function registerControls(\WP_Customize_Manager $wp_customize): void
    {
        $section = $wp_customize->get_section('title_tagline');
        if ($section) {
            $section->priority = 1;
            $section->title    = __('Site Identity (Logo & Favicon)', 'ai-zippy');
        }
    }
}
