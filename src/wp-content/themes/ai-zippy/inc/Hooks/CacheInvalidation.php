<?php

namespace AiZippy\Hooks;

use AiZippy\Core\Cache;

defined('ABSPATH') || exit;

/**
 * Cache invalidation hooks.
 *
 * Clears relevant caches when WooCommerce data changes.
 * All cache keys are defined in Core\Cache — check there first.
 */
class CacheInvalidation
{
    public static function register(): void
    {
        // Product changes
        add_action('woocommerce_update_product', [self::class, 'onProductChange']);
        add_action('woocommerce_new_product', [self::class, 'onProductChange']);
        add_action('woocommerce_delete_product', [self::class, 'onProductChange']);

        // Category changes
        add_action('edited_product_cat', [self::class, 'onProductChange']);
        add_action('created_product_cat', [self::class, 'onProductChange']);
        add_action('delete_product_cat', [self::class, 'onProductChange']);
    }

    public static function onProductChange(): void
    {
        Cache::clearProductCaches();
    }
}
