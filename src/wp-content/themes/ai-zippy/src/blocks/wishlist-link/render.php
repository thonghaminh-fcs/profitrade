<?php

use AiZippy\Shop\Wishlist;

defined('ABSPATH') || exit;

echo Wishlist::renderHeaderLink(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by renderer.
