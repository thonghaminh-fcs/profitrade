# Rules: PHP Classes & Architecture

## PSR-4 Namespace
All PHP logic lives in `inc/` under the `AiZippy\` namespace.

```
AiZippy\Core\ViteAssets      → inc/Core/ViteAssets.php
AiZippy\Api\ProductFilterApi → inc/Api/ProductFilterApi.php
AiZippy\Checkout\CheckoutAssets → inc/Checkout/CheckoutAssets.php
```

## Critical Rules

### 1. Never put logic in functions.php
`functions.php` only contains constants and the loader require. All logic goes in `inc/` classes.

```php
// functions.php — this is ALL it should contain
defined('ABSPATH') || exit;
define('AI_ZIPPY_THEME_VERSION', '4.0.0');
define('AI_ZIPPY_THEME_DIR', get_template_directory());
define('AI_ZIPPY_THEME_URI', get_template_directory_uri());
require_once AI_ZIPPY_THEME_DIR . '/inc/loader.php';
```

### 2. Class pattern — static register()
Every class must have a `register()` method that hooks into WordPress. Called from `loader.php`.

```php
class MyFeature
{
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void { ... }
}
```

### 3. Loader bootstrap order
Register in `loader.php` grouped by domain:

```php
// Core (always first)
AiZippy\Core\ViteAssets::register();
AiZippy\Core\ThemeSetup::register();

// Domain modules
AiZippy\Shop\ShopAssets::register();
AiZippy\Cart\CartAssets::register();
AiZippy\Checkout\CheckoutSettings::register();
```

### 4. Vite assets — use ViteAssets::enqueue()
Never call `wp_enqueue_script/style` directly for Vite-built assets. Always use:

```php
\AiZippy\Core\ViteAssets::enqueue('handle', 'manifest-key');
```

Manifest keys use full path from root:
```php
'src/wp-content/themes/ai-zippy/src/js/theme.js'
```

### 5. WC Store API nonce — provided globally
The nonce is set once in `ViteAssets::enqueueTheme()`. Do NOT add duplicate nonces in page-specific classes (CartAssets, CheckoutAssets, etc.).

### 6. Conditional page assets
Page-specific assets check conditions before loading:

```php
public static function enqueue(): void
{
    if (!is_checkout() && !is_page('checkout')) {
        return;
    }
    // enqueue only on checkout page
}
```

### 7. Folder structure convention
Group by domain, not by type:

```
inc/
├── Core/           # Framework-level (assets, setup, cache, rate limiter)
├── Api/            # REST API endpoints
├── Hooks/          # WP/WC action/filter hooks
├── Shop/           # Shop page features
├── Cart/           # Cart page features
├── Checkout/       # Checkout + order confirmation
└── setup/          # Early boot procedural files
```
