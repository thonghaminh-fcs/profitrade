<?php

use AiZippy\Shop\Wishlist;

defined('ABSPATH') || exit;

echo Wishlist::renderApp(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by renderer.
