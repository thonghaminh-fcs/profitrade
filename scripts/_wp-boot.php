<?php
// Shared bootstrap for the seeders: load WordPress and read the JSON spec given as argv[1].
if (!defined('ABSPATH')) {
    define('WP_USE_THEMES', false);
    require_once '/var/www/html/wp-load.php';
}
function spec_arg(): array
{
    global $argv;
    $p = $argv[1] ?? '';
    $d = $p && is_file($p) ? json_decode(file_get_contents($p), true) : null;
    if (!is_array($d)) {
        fwrite(STDERR, "FAIL: spec json missing or invalid\n");
        exit(2);
    }
    return $d;
}
