# Rules: SCSS & CSS

## Design Tokens — Single Source of Truth
All colors and breakpoints come from `theme.json`. SCSS variables in `_variables.scss` map to CSS custom properties.

```scss
$c-primary: var(--wp--preset--color--primary);    // #1a1a1a
$c-accent:  var(--wp--preset--color--accent);      // #c8a97e
$c-border:  var(--wp--preset--color--border);      // #e5e5e5
```

**Never hardcode colors.** Use `$c-*` variables or `var(--wp--preset--color--*)`.

## Critical Rules

### 1. Breakpoint mixins
Use `@include from()` (mobile-first) and `@include until()` (desktop-first):

```scss
@include from(md)  { ... }   // min-width: 768px
@include until(lg) { ... }   // max-width: 1023px
```

Breakpoints: `sm` (480), `md` (768), `lg` (1024), `xl` (1200), `xxl` (1400)

### 2. CSS class prefixes
Each app/block has its own prefix to avoid conflicts:

| Prefix | Scope |
|--------|-------|
| `az-`  | Theme-level (checkout override, thank you page) |
| `zc-`  | Cart React app (`zc__layout`, `zc-steps`) |
| `zk-`  | Checkout React app (`zk__sidebar`, `zk__input`) |
| `sf__` | Shop filter React app |
| `ps__` | Product showcase block |
| `bi__` | Brand intro block |

### 3. Vite SCSS entries — naming convention
Entry files and partials must have different names to avoid Sass ambiguity:

```
wc-checkout-entry.scss   ← Vite entry (imports the partial)
_wc-checkout.scss        ← Partial with actual styles
```

**WRONG:** `wc-checkout.scss` + `_wc-checkout.scss` in the same directory → Sass error.

### 4. Conditional CSS loading
CSS that only applies to one template should be a separate Vite entry, loaded conditionally in PHP:

```php
// Only load WC checkout CSS when WC default template is selected
\AiZippy\Core\ViteAssets::enqueue('handle', 'entry-key');
```

### 5. Block SCSS — style.scss vs editor.scss
- `style.scss` — loads on BOTH frontend and editor
- `editor.scss` — loads ONLY in editor

If you need to override `style.scss` behavior in the editor, use doubled class selector in `editor.scss` for higher specificity:

```scss
.wp-block-ai-zippy-name.wp-block-ai-zippy-name { ... }
```

### 6. Transition mixin — multiple properties
Wrap multiple properties in parentheses:

```scss
// WRONG
@include transition(color, opacity);

// CORRECT
@include transition((color, opacity));
```

### 7. React app SCSS
Each React app bundles its own SCSS (imported in the app's entry):
- `src/js/cart/cart.scss`
- `src/js/checkout/checkout.scss`
- `src/js/shop-filter/shop-filter.scss`

These use local `$variables` (not `@use "variables"`) since they're separate Vite entries.
