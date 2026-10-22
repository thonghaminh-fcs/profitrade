<?php

/**
 * Dynamic URL detection for local dev + tunnel support.
 * Loaded from wp-config.php via WORDPRESS_CONFIG_EXTRA.
 */

if (defined('WP_HOME') || !isset($_SERVER['HTTP_HOST'])) {
    return;
}

$scheme = 'http';

if (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
) {
    $scheme = 'https';
}

$dynamic_url = $scheme . '://' . $_SERVER['HTTP_HOST'];

define('WP_HOME', $dynamic_url);
define('WP_SITEURL', $dynamic_url);
