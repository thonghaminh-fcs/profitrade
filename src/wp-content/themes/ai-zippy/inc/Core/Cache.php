<?php

namespace AiZippy\Core;

defined('ABSPATH') || exit;

/**
 * Centralized cache key management.
 *
 * All transient keys are defined here as constants.
 * To find what a cache stores or where it's used, start here.
 */
class Cache
{
    /** Filter options: categories, attributes, price range */
    const FILTER_OPTIONS = 'ai_zippy_filter_options';

    /** TTL: 5 minutes */
    const FILTER_OPTIONS_TTL = 5 * MINUTE_IN_SECONDS;

    /**
     * Get a cached value.
     */
    public static function get(string $key): mixed
    {
        return get_transient($key);
    }

    /**
     * Set a cached value.
     */
    public static function set(string $key, mixed $value, int $ttl = 300): void
    {
        set_transient($key, $value, $ttl);
    }

    /**
     * Delete a cached value.
     */
    public static function delete(string $key): void
    {
        delete_transient($key);
    }

    /**
     * Clear all product-related caches.
     */
    public static function clearProductCaches(): void
    {
        self::delete(self::FILTER_OPTIONS);
    }
}
