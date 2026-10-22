# Rules: FSE Templates & WooCommerce Overrides

## FSE Templates
Located in `templates/` and `parts/`. Pure HTML block markup.

### Critical Rules

### 1. Template naming
WordPress matches templates by slug:
- `page-{slug}.html` — specific page (e.g. `page-checkout.html`)
- `page.html` — all pages
- `single.html` — single posts
- `archive-product.html` — WooCommerce shop
- `single-product.html` — WooCommerce product

### 2. Every page template needs `wp:post-content`
Without it, users can't add blocks to the page in the editor:

```html
<!-- wp:post-title {"level":1} /-->
<!-- wp:post-content {"layout":{"type":"constrained"}} /-->
```

### 3. No HTML comments inside blocks
Plain `<!-- comments -->` inside `wp:group` cause block validation errors. Use `wp:html` block if you need custom HTML.

### 4. Shortcodes in FSE templates
Use `<!-- wp:shortcode -->` block. But be aware: WordPress applies `wpautop` to shortcode output, injecting `<p>` and `<br>` tags.

**Fix:** Collapse whitespace in PHP before returning:
```php
$html = preg_replace('/>\s+</', '><', $html);
```

### 5. Template priority in block themes
FSE template (`templates/page-checkout.html`) takes priority over WC PHP templates. If you want WC's `thankyou.php` to render, use a shortcode in the FSE template that calls it via PHP.

## WooCommerce Template Overrides
Located in `woocommerce/` directory (theme root level).

```
woocommerce/
└── checkout/
    ├── form-checkout.php   # Custom checkout layout
    └── thankyou.php        # Custom thank you page
```

### Rules for WC overrides

### 1. Keep WC hooks
Always preserve `do_action()` hooks for plugin compatibility, unless they output unwanted content (then skip specific ones).

### 2. Version tag
Include the `@version` tag matching the WC template version you're overriding. WC warns when versions mismatch.

### 3. wpautop in shortcode context
When WC templates render inside a `[shortcode]` in FSE, `wpautop` mangles the HTML. Buffer early and collapse whitespace:

```php
// In template_redirect (early)
ob_start();
wc_get_template('checkout/thankyou.php', ['order' => $order]);
$html = preg_replace('/>\s+</', '><', ob_get_clean());
```
