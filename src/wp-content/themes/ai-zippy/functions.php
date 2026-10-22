<?php

/**
 * AI Zippy Theme — Bootstrap
 *
 * Constants + loader. All logic lives in inc/ classes.
 */

defined('ABSPATH') || exit;

define('AI_ZIPPY_THEME_VERSION', '4.0.0');
define('AI_ZIPPY_THEME_DIR', get_template_directory());
define('AI_ZIPPY_THEME_URI', get_template_directory_uri());

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once AI_ZIPPY_THEME_DIR . '/inc/loader.php';
