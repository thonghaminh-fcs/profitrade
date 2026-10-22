<?php

namespace AiZippy\Core;

defined('ABSPATH') || exit;

/**
 * Rate limiter using WordPress transients.
 *
 * Usage:
 *   if (RateLimiter::isLimited('product-filter', 30, 60)) {
 *       return new \WP_Error('rate_limited', 'Too many requests', ['status' => 429]);
 *   }
 */
class RateLimiter
{
    /**
     * Check if the current IP has exceeded the rate limit.
     *
     * @param string $action  Unique identifier for the endpoint.
     * @param int    $limit   Max requests within the window.
     * @param int    $window  Time window in seconds.
     */
    public static function isLimited(string $action, int $limit = 30, int $window = 60): bool
    {
        $ip = self::getClientIp();
        $key = 'rl_' . md5($action . '_' . $ip);

        $data = get_transient($key);

        if ($data === false) {
            set_transient($key, ['count' => 1, 'start' => time()], $window);
            return false;
        }

        if (time() - $data['start'] >= $window) {
            set_transient($key, ['count' => 1, 'start' => time()], $window);
            return false;
        }

        $data['count']++;
        set_transient($key, $data, $window);

        return $data['count'] > $limit;
    }

    /**
     * Get client IP (supports proxies, Cloudflare, tunnels).
     */
    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}
