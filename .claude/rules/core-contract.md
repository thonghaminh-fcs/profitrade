# Rules: Core Contract (Parent ↔ Child)

The parent theme `ai-zippy` is a **core feature library**: behaviour, data and
configuration. The child theme is **design**. Every client site has its own
blocks and styling, so nothing in the parent may assume how the markup looks or
what the text says.

That rule is about *client* design, not about shipping something usable. A core
feature must work out of the box, so a parent block may carry a **neutral
default skin** — plain greys, theme.json presets with fallbacks, no brand voice.
The test is whether a child theme can override it with ordinary CSS. What the
parent may not do is encode one client's look and expect the next to inherit it.

Three classes implement the contract:

| Class | Responsibility |
|---|---|
| `AiZippy\Core\Features` | Which core features are on |
| `AiZippy\Core\L10n` | Every string rendered by frontend JS |
| `AiZippy\Core\Runtime` | Publishes both, plus URLs/nonces/selectors, as `window.aiZippy` |

## Hard rules for parent code

1. **No hardcoded user-facing text in JS.** Add the string to `L10n::strings()`
   and read it with `azText('key', 'English fallback')`.
2. **No hardcoded URLs or paths.** Permalinks differ per site — use
   `azUrl('shop')` / `azUrlWithQuery('shop', { s: query })`.
3. **No selectors that depend on someone's design.** `header.wp-block-group`
   only exists because the default part happens to be a Group block. Read the
   selector from the contract: `azSelector('header', fallback)`.
4. **Every new core behaviour gets a feature flag** in `Features::DEFAULTS` and
   is gated in `theme.js` with `azFeature('my_feature')`.
5. **Every decision a client may want to change gets a filter**, named
   `ai_zippy_{feature}_{thing}`.

## `window.aiZippy`

Printed by `Runtime::inject()` on `wp_enqueue_scripts` priority 15, attached to
the `ai-zippy-theme` handle.

```js
{
  rest:      { url, nonce },
  endpoints: { search },
  storeApi:  { nonce, timestamp },   // WC Store API
  urls:      { home, shop, cart, checkout, account, search },
  selectors: { header, footer },
  features:  { sticky_header: true, mini_cart: true, ... },
  settings:  { stickyOffset, searchDebounce, searchMinChars, searchMaxResults, toastDuration },
  i18n:      { "cart.added": "Added!", ... }
}
```

Read it only through `src/js/frontend/core/config.js`:

```js
import { azText, azUrl, azUrlWithQuery, azFeature, azSetting, azSelector,
         azEndpoint, azNonce, azRestUrl, azConfig } from "../core/config.js";
```

`azRestUrl('wc/store/v1/cart')` resolves a route against `rest.url`. Never write
`/wp-json/...` by hand: with plain permalinks the REST base is
`/?rest_route=/`, so a literal path 404s.

Every accessor takes a fallback and survives a missing config — the bundle also
runs in the editor preview, where PHP never printed it.

## Filters

| Filter | Type | Purpose |
|---|---|---|
| `ai_zippy_features` | array | The whole feature map |
| `ai_zippy_feature_{key}` | bool | One feature |
| `ai_zippy_i18n_strings` | array | Add / replace frontend strings |
| `ai_zippy_runtime_config` | array | The whole payload, applied last |
| `ai_zippy_selectors` | array | DOM contract |
| `ai_zippy_runtime_settings` | array | Numeric tunables |
| `ai_zippy_mini_cart_icons` | array | Toggle icon set (raw SVG, echoed unescaped) |
| `ai_zippy_mini_cart_free_shipping_threshold` | float | Override the detected threshold |
| `ai_zippy_mini_cart_cross_sell_ids` | array | Cross-sell product ids |
| `ai_zippy_legacy_mini_cart_css` | bool | Force the deprecated core-block stylesheet on/off |

### Child theme examples

```php
// Turn a core feature off.
add_filter('ai_zippy_feature_sticky_header', '__return_false');

// Point core behaviour at this client's markup.
add_filter('ai_zippy_selectors', function (array $selectors): array {
    $selectors['header'] = '.client-topbar';
    return $selectors;
});

// Reword one string without touching translations.
add_filter('ai_zippy_i18n_strings', function (array $strings): array {
    $strings['cart.add_success'] = __('Added to your basket', 'client-theme');
    return $strings;
});
```

## Feature flags

Declared in `Features::DEFAULTS`, resolved as
`DEFAULTS → stored option → filter`. Unknown keys resolve to `false`, so a typo
fails closed.

Two flags read a pre-existing option instead of `ai_zippy_feature_{key}` so
settings saved before the registry existed still apply:

| Flag | Legacy source |
|---|---|
| `wishlist` | `ThemeOptions::isWishlistEnabled()` |
| `loading_page` | `ThemeOptions::isEnabled()` |

## Adding a string

1. Add `'namespace.name' => __('English', 'ai-zippy')` to `L10n::strings()`.
2. Use `azText('namespace.name', 'English')` in JS.
3. Placeholders are `%s`, filled positionally: `azText(key, fallback, value)`.

Strings are inserted into `innerHTML` in a few render paths — escape any
**user** value passed as a placeholder (`escHtml()`), never the template.

## i18n

`load_theme_textdomain('ai-zippy', .../languages)` runs in
`ThemeSetup::setup()`. All PHP, block and `L10n` strings use the `ai-zippy`
textdomain. Regenerate the POT file with the command in
`src/wp-content/themes/ai-zippy/languages/README.md`.

## Replacing a WooCommerce block

A core block that overrides WooCommerce's internal `.wc-block-*` class names is a
liability: those names are not a public API, so one Woo release breaks every
client site at once. When the parent needs that behaviour, it ships its own block
and owns the markup.

`ai-zippy/mini-cart` is the reference implementation of that swap:

| Piece | File |
|---|---|
| Server data + filters | `inc/Cart/MiniCart.php` |
| Block + server render | `src/blocks/mini-cart/` |
| Drawer behaviour | `src/js/frontend/modules/mini-cart.js` |
| Strings | `L10n::strings()`, `minicart.*` |

The class names are the contract with the child theme — `az-mc`, `az-mc__toggle`,
`az-mc__count`, `az-mc__drawer`, `az-mc__panel`, `az-mc__items`, `az-mc__item`,
`az-mc__footer`, `az-mc__shipping`. State lives in `az-mc--open`, `az-mc--busy`
and `data-side`, so design can hook either.

`src/blocks/mini-cart/style.scss` carries what makes the drawer work —
positioning, the slide transform, the scroll area, the scroll lock, hidden
states — plus a neutral default skin so the feature is usable on a site that
adds no CSS of its own. Colours resolve from theme.json presets with hard-coded
fallbacks. A child theme restyles any `az-mc__*` rule.

The skin exists because it had to. Replacing `woocommerce/mini-cart` orphaned
its 470-line override sheet, which targeted `wc-block-mini-cart__*` class names
that are no longer rendered, and structure-only CSS left the drawer as default
HTML: blue links, no spacing, buttons touching. Behaviour passed every test while
the feature was unusable.

Two traps that CSS shows up as a working drawer:

- `[hidden]` loses to any `display` declaration. `.az-mc__drawer` and
  `.az-mc__empty` both set one, so both restate `[hidden] { display: none }`.
  Without it the empty message sits next to a full cart.
- The closed-position rules are qualified by `[data-side]`. The open-state rule
  must match that specificity, hence `.az-mc.az-mc--open .az-mc__panel`. A single
  class there leaves the panel parked off-screen while the class, `aria-expanded`,
  the body lock and focus all say it is open.

Behaviour lives in `modules/mini-cart.js` (bundled into `theme.js`) rather than a
block `view.js`, because it needs `cart-api.js`, `azText` and `azUrl`, which are
already in that bundle. A separate `view.js` entry would ship a second copy.

### Retiring the old stylesheet

The overrides it replaces do not get deleted in the same change — existing sites
would lose their styling on update. Instead they become their own Vite entry,
enqueued only where they still apply:
`_mini-cart.scss` → `mini-cart-legacy-entry.scss`, enqueued by
`MiniCart::maybeEnqueueLegacyStyles()` while the **resolved** header part (so a
Site Editor customisation in the database wins over the theme file) still
contains `woocommerce/mini-cart`.

`inc/Cart/HeaderCartButton.php` stays for the same reason: it filters
`render_block_woocommerce/mini-cart`, so it is a no-op once a site adopts the new
block, which covers Cart and Checkout itself by rendering a plain cart link.

## Back-compat

`window.aiZippySearch` is still printed and still read as a fallback in
`search-bar.js`. It exists only so a site running a JS bundle built before this
contract keeps working. Remove both once every site is rebuilt.
