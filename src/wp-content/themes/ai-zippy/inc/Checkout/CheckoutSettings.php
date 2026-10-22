<?php

namespace AiZippy\Checkout;

defined('ABSPATH') || exit;

/**
 * Adds a checkout template setting under WooCommerce > Settings > Advanced.
 *
 * Options:
 * - "react"       → Custom React checkout app
 * - "woocommerce" → Default WooCommerce checkout block/shortcode
 */
class CheckoutSettings
{
    public const OPTION_KEY = 'ai_zippy_checkout_template';
    public const DEFAULT    = 'react';

    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_filter('woocommerce_get_settings_advanced', [self::class, 'addSetting'], 10, 2);
    }

    /**
     * Get the current checkout template choice.
     */
    public static function getTemplate(): string
    {
        return get_option(self::OPTION_KEY, self::DEFAULT);
    }

    /**
     * Check if React checkout is active.
     */
    public static function isReact(): bool
    {
        return self::getTemplate() === 'react';
    }

    /**
     * Add our setting to WooCommerce > Settings > Advanced.
     */
    public static function addSetting(array $settings, string $currentSection): array
    {
        // Only add to the main Advanced section (no subsection)
        if (!empty($currentSection)) {
            return $settings;
        }

        // Find the section end to insert before it
        $insertIndex = count($settings);
        foreach ($settings as $i => $setting) {
            if (isset($setting['type']) && $setting['type'] === 'sectionend' && isset($setting['id']) && $setting['id'] === 'checkout_process_options') {
                $insertIndex = $i;
                break;
            }
        }

        $customSettings = [
            [
                'title'    => __('Checkout template', 'ai-zippy'),
                'desc'     => __('Choose which checkout template to use on the frontend.', 'ai-zippy'),
                'id'       => self::OPTION_KEY,
                'type'     => 'select',
                'default'  => self::DEFAULT,
                'options'  => [
                    'react'       => __('AI Zippy React Checkout', 'ai-zippy'),
                    'woocommerce' => __('WooCommerce Default Checkout', 'ai-zippy'),
                ],
                'desc_tip' => true,
            ],
        ];

        array_splice($settings, $insertIndex, 0, $customSettings);

        return $settings;
    }
}
