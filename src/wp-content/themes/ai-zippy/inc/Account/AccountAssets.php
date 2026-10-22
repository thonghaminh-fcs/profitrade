<?php

namespace AiZippy\Account;

defined('ABSPATH') || exit;

/**
 * My Account — redirect guests to /login/ and enqueue no extra assets
 * (account styles are bundled in the main style.scss).
 */
class AccountAssets
{
    public static function register(): void
    {
        add_action('template_redirect', [self::class, 'redirectGuests']);
        add_filter('woocommerce_account_menu_items', [self::class, 'filterMenuItems']);
    }

    /**
     * Remove "Downloads" from the account menu — clients don't sell downloadable products.
     */
    public static function filterMenuItems(array $items): array
    {
        unset($items['downloads']);
        return $items;
    }

    /**
     * Redirect unauthenticated users away from /my-account/ to /login/.
     * WooCommerce normally shows its own login form there; we redirect instead.
     */
    public static function redirectGuests(): void
    {
        if (!is_account_page() || is_user_logged_in()) {
            return;
        }

        // Allow WC endpoints that don't need auth (lost-password, reset-password)
        $endpoint = WC()->query->get_current_endpoint();
        $open_endpoints = ['lost-password', 'reset-password'];
        if (in_array($endpoint, $open_endpoints, true)) {
            return;
        }

        $login_page = get_page_by_path('login');
        if ($login_page) {
            wp_safe_redirect(get_permalink($login_page->ID));
            exit;
        }
    }
}
