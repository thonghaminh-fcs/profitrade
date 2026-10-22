# AI Zippy — FSE Theme + WooCommerce

Full Site Editing (FSE) WordPress theme with WooCommerce integration. Ships as a **parent + child theme pair** with two fully independent dev pipelines.

## Build Commands (run from project root)

```bash
# Dev (watch + BrowserSync)
npm run dev          # Parent only — Vite + wp-scripts + BS :3000 (core maintainer)
npm run dev:child    # Child only  — Vite + wp-scripts + BS :3001 (client team)
npm run dev:all      # Both in parallel

# Production
npm run build              # Everything
npm run build:parent       # Parent only
npm run build:child        # Child only
npm run build:blocks       # Parent blocks only
npm run build:blocks:child # Child blocks only
```

> **Note:** User typically runs `npm run dev` in their terminal. Do NOT run build commands unless explicitly asked.

## Project Structure

```
ai_zippy/
├── package.json                  # All scripts
├── vite.config.parent.js         # Parent Vite config
├── vite.config.child.js          # Child Vite config (has @parent alias)
├── bs.config.parent.js           # Parent BS :3000 (UI :3010)
├── bs.config.child.js            # Child BS :3001 (UI :3011)
├── docker-compose.yml            # WordPress + MySQL
├── README.md                     # Team-facing docs
└── src/wp-content/themes/
    │
    ├── ai-zippy/                 # Parent theme (core)
    │   ├── theme.json            # Design tokens (single source of truth)
    │   ├── functions.php         # Constants + loader only (16 lines)
    │   ├── templates/            # FSE page templates (.html)
    │   ├── parts/                # Header, footer (carry .az-header / .az-footer)
    │   ├── languages/            # 'ai-zippy' textdomain (.pot / .po / .mo)
    │   ├── woocommerce/          # WC template overrides
    │   │   ├── checkout/
    │   │   │   ├── form-checkout.php     # Card-based classic checkout
    │   │   │   └── thankyou.php          # Branded thank-you page
    │   │   ├── myaccount/                # Full My Account suite
    │   │   │   ├── my-account.php        # Sidebar + content layout
    │   │   │   ├── navigation.php        # Icon sidebar nav (Downloads removed)
    │   │   │   ├── dashboard.php         # Welcome + stats + recent orders
    │   │   │   ├── orders.php            # Order list with status pills
    │   │   │   ├── view-order.php        # Order detail
    │   │   │   ├── form-login.php        # Tabbed sign-in / register
    │   │   │   ├── form-lost-password.php
    │   │   │   ├── form-reset-password.php
    │   │   │   ├── form-edit-account.php # Profile + password cards
    │   │   │   ├── my-address.php        # Address card grid
    │   │   │   └── form-edit-address.php # Styled WC address form
    │   │   └── emails/
    │   │       └── customer-reset-password.php  # Self-contained branded email
    │   ├── src/
    │   │   ├── js/
    │   │   │   ├── frontend/
    │   │   │   │   ├── theme.js      # Entry — gates every module by feature flag
    │   │   │   │   ├── core/         # config.js — the only reader of window.aiZippy
    │   │   │   │   ├── modules/      # Vanilla JS (header, search-bar, cart-api, add-to-cart)
    │   │   │   │   ├── shop-filter/  # React app: product filtering
    │   │   │   │   ├── cart/         # React app: cart
    │   │   │   │   └── checkout/     # React app: checkout
    │   │   │   └── admin/        # React panels mounted in wp-admin
    │   │   ├── scss/
    │   │   │   ├── style.scss            # Main entry (imports all partials)
    │   │   │   ├── wc-checkout-entry.scss # WC default checkout (conditional)
    │   │   │   ├── mini-cart-legacy-entry.scss # Deprecated core mini-cart CSS (conditional)
    │   │   │   ├── _variables.scss       # Colors, breakpoints, mixins
    │   │   │   ├── _base.scss
    │   │   │   ├── _header.scss
    │   │   │   ├── _shop.scss
    │   │   │   ├── _cart.scss
    │   │   │   ├── _mini-cart.scss        # DEPRECATED — overrides woocommerce/mini-cart
    │   │   │   ├── _add-to-cart.scss     # AJAX add-to-cart + toasts
    │   │   │   ├── _checkout.scss        # WC checkout block styles
    │   │   │   ├── _wc-checkout.scss     # Classic WC checkout (conditional)
    │   │   │   ├── _wc-notices.scss
    │   │   │   ├── _account.scss         # My Account suite (ma__ prefix)
    │   │   │   └── _footer.scss
    │   │   └── blocks/                   # Core Gutenberg blocks
    │   │       ├── hero-section/
    │   │       ├── product-showcase/
    │   │       ├── brand-intro/
    │   │       ├── search-bar/
    │   │       └── mini-cart/            # Replaces woocommerce/mini-cart
    │   ├── assets/
    │   │   ├── dist/             # Vite output
    │   │   └── blocks/           # wp-scripts output
    │   └── inc/                  # PSR-4 classes, AiZippy\ namespace
    │       ├── loader.php        # Autoloader + bootstrap all modules
    │       ├── setup/
    │       │   └── dynamic-url.php       # Auto-detect URL for tunnel/local dev
    │       ├── Core/
    │       │   ├── ViteAssets.php        # Manifest reader + enqueue
    │       │   ├── Runtime.php           # window.aiZippy — the parent↔child contract
    │       │   ├── Features.php          # Feature flag registry
    │       │   ├── L10n.php              # Every string rendered by frontend JS
    │       │   ├── ThemeSetup.php        # Supports, blocks, categories, textdomain
    │       │   ├── Customizer.php        # Logo + site icon support
    │       │   ├── ThemeOptions.php      # "Zippy AI" admin page + revisions limit
    │       │   ├── Cache.php             # Centralized cache keys
    │       │   └── RateLimiter.php       # IP-based rate limiting
    │       ├── Api/
    │       │   └── ProductFilterApi.php  # REST: /ai-zippy/v1/products
    │       ├── Hooks/
    │       │   └── CacheInvalidation.php
    │       ├── Shop/
    │       │   └── ShopAssets.php        # Shop filter React enqueue + taxonomy seed
    │       ├── Cart/
    │       │   ├── CartAssets.php
    │       │   ├── MiniCart.php          # Mini cart data + filters + legacy CSS gate
    │       │   └── HeaderCartButton.php  # Compat shim for the core Woo block
    │       ├── Checkout/
    │       │   ├── CheckoutAssets.php
    │       │   ├── CheckoutSettings.php
    │       │   ├── CheckoutShortcode.php
    │       │   ├── OrderConfirmationShortcode.php
    │       │   └── CheckoutValidation.php
    │       └── Account/
    │           └── AccountAssets.php     # Guest → /login/ redirect + removes Downloads nav
    │
    └── ai-zippy-child/           # Child theme (per-client customizations)
        ├── functions.php         # Vite manifest reader + child block auto-registration
        ├── style.css             # Theme header (Template: ai-zippy)
        ├── theme.json            # Per-client theme.json overrides
        ├── templates/            # Per-client template overrides
        ├── parts/
        ├── src/
        │   ├── js/
        │   │   └── child.js      # Entry — client-specific JS
        │   ├── scss/
        │   │   └── style.scss    # Entry — client overrides (uses @parent-scss/variables)
        │   └── blocks/           # Client-specific Gutenberg blocks
        │       └── .gitkeep
        └── assets/
            ├── dist/             # Child Vite output
            └── blocks/           # Child wp-scripts output
```

## Architecture Decisions

### Parent + Child Theme Pipeline
- **Two fully independent dev processes** — parent and child each have their own Vite config, BrowserSync, and wp-scripts invocation
- **Parent** = core features (cart, checkout, account, shop filter, shared blocks). Maintained by the core maintainer.
- **Child** = per-client customizations (styles, blocks, template overrides). Maintained by the client team.
- **CSS load order**: parent CSS loads first; child CSS is enqueued with `['ai-zippy-theme-css-0']` as a dependency so it overrides cleanly
- **Port split**: parent BS on 3000 (UI 3010), child BS on 3001 (UI 3011) — can run simultaneously
- **Shared SCSS**: child uses `@use "@parent-scss/variables" as *;` — zero duplication of tokens/mixins
- **Child block auto-registration**: `ai-zippy-child/functions.php` scans its own `assets/blocks/*/block.json` on `init`

### Core Contract (Parent ↔ Child)
The parent is a **feature library** — behaviour, data, configuration. The child is **design**. Every client has its own blocks and styling, so parent code may never assume how the markup looks or what the text says.

- `AiZippy\Core\Features` — feature flag registry. Resolution order: `DEFAULTS` → `ai_zippy_feature_{key}` option → filter. Unknown keys resolve to `false` so a typo fails closed.
- `AiZippy\Core\L10n` — every string rendered by frontend JS, wrapped in `__()`.
- `AiZippy\Core\Runtime` — publishes all of it as `window.aiZippy` on `wp_enqueue_scripts` priority 15 (after `ViteAssets` at 10, so the `ai-zippy-theme` handle exists): `rest`, `endpoints`, `storeApi`, `urls`, `selectors`, `features`, `settings`, `i18n`.
- JS reads it **only** through `src/js/frontend/core/config.js` (`azText`, `azUrl`, `azUrlWithQuery`, `azFeature`, `azSetting`, `azSelector`, `azEndpoint`, `azNonce`, `azRestUrl`) — every accessor takes a fallback and survives a missing config (editor preview).
- Parent JS must not hardcode user-facing text, URLs, or design-dependent selectors. Header/footer are found via `azSelector()`, backed by the `.az-header` / `.az-footer` classes on the template parts. REST calls go through `azRestUrl('wc/store/v1/cart')` — a literal `/wp-json/…` path 404s on a site with plain permalinks.
- A parent block never overrides WooCommerce's internal `.wc-block-*` class names; it ships its own markup instead. `ai-zippy/mini-cart` replaces `woocommerce/mini-cart` for exactly that reason — see `.claude/rules/core-contract.md`.
- Every core behaviour is gated in `theme.js` by `azFeature('{key}')` so a client can switch it off with one filter instead of forking the parent.
- Filters: `ai_zippy_features`, `ai_zippy_feature_{key}`, `ai_zippy_i18n_strings`, `ai_zippy_runtime_config`, `ai_zippy_selectors`, `ai_zippy_runtime_settings`, `ai_zippy_mini_cart_*`. Full reference: `.claude/rules/core-contract.md`.

### PHP — PSR-4 Classes (Parent)
- All parent PHP logic lives in `inc/` under the `AiZippy\` namespace
- `functions.php` only defines constants and requires `loader.php`
- `loader.php` handles autoloading and bootstraps all modules via `::register()`
- Never put logic directly in `functions.php`

### Child Theme `functions.php`
- Not PSR-4 — single-file, procedural (small enough)
- Contains: Vite manifest reader (`ai_zippy_child_vite_manifest()`), enqueue helper (`ai_zippy_child_enqueue_vite()`), module `type="module"` filter, and child block auto-registration
- Mirrors the parent's `AiZippy\Core\ViteAssets` pattern but scoped to the child's own `assets/dist/`
- Skips empty SCSS stub JS files (< 100 bytes with `.scss` src) — avoids enqueueing useless 40-byte stubs when the team's `style.scss` is empty
- Child CSS enqueued with dependency on `ai-zippy-theme-css-0` so it loads after parent

### Dual Build System
- **Vite**: Theme JS/SCSS + React apps → `assets/dist/` (separate configs for parent and child)
- **@wordpress/scripts**: Gutenberg blocks → `assets/blocks/` (separate invocations with different `--webpack-src-dir` + `--output-path`)
- Parent Vite entries: `theme`, `style`, `shop-filter`, `cart`, `checkout`, `product`, `wc-checkout`, `mini-cart-legacy`
- Child Vite entries: `child-theme`, `child-style` (only included if source files exist)
- Manifest keys use full paths from repo root: `src/wp-content/themes/ai-zippy/src/js/theme.js`

### Vite Path Aliases
- **Parent config** (`vite.config.parent.js`): `@` and `@scss` → parent `src/`
- **Child config** (`vite.config.child.js`): `@` / `@scss` → child `src/`, **plus** `@parent` / `@parent-scss` → parent `src/`
- Child SCSS pattern: `@use "@parent-scss/variables" as *;`

### CSS-Only Vite Entries
- `ViteAssets::enqueue()` handles both JS entries and CSS-only entries
- CSS-only entries (like `wc-checkout-entry.scss`) output `.css` files directly in the manifest
- Entry file and partial must have different names to avoid Sass ambiguity (e.g., `wc-checkout-entry.scss` imports `_wc-checkout.scss`)

### SCSS Variables & Mixins
- Colors: `$c-primary`, `$c-accent`, `$c-border`, etc. mapped from `theme.json` CSS custom properties
- Breakpoints: `$bp-sm` (480), `$bp-md` (768), `$bp-lg` (1024), `$bp-xl` (1200), `$bp-xxl` (1400)
- Mixins: `@include from(md)`, `@include until(lg)`, `@include transition(...)`, `@include truncate(2)`, `@include card-hover`

### WC Store API Nonce
- Provided globally via `Runtime::inject()` as `wcBlocksMiddlewareConfig` — WooCommerce Blocks reads that exact global name, so it keeps its legacy shape alongside `window.aiZippy.storeApi`
- Used by `cart-api.js`, `add-to-cart.js`, checkout API, cart API
- Do NOT add duplicate nonces in page-specific asset classes

### Checkout — Dual Template
- Admin selects template in **WooCommerce > Settings > Advanced > Checkout template**
- `react` → React checkout app (step-by-step: Contact → Billing → Payment)
- `woocommerce` → Classic WC checkout with custom `form-checkout.php` override
- `[ai_zippy_checkout]` shortcode in `page-checkout.html` renders the selected template
- `CheckoutAssets` conditionally loads React JS or WC checkout CSS (never both)
- Phone input uses `react-international-phone` library (preferred countries: SG, MY, VN)

### My Account
- Full custom UI in `woocommerce/myaccount/` — sidebar nav + content layout (`ma__` CSS prefix)
- Guests redirected from `/my-account/` to `/login/` via `AccountAssets::redirectGuests()` (allows `lost-password` + `reset-password` endpoints)
- Downloads endpoint removed from nav via `woocommerce_account_menu_items` filter — site has no downloadable products
- Tabbed login/register form (pill tabs with sliding indicator); password visibility toggles; `@parent`-style brand icon
- Order list shows status pills color-coded by WC status (processing/completed/cancelled/etc.)
- `view-order.php` renders our custom detail card **plus** fires `do_action('woocommerce_view_order')` which outputs WC's `order-details.php` + `order-details-customer.php` — both are styled in `_account.scss`

### Transactional Emails
- **Reset password** (`emails/customer-reset-password.php`): self-contained standalone layout (bypasses the shared `email-header.php` / `email-footer.php` so other emails aren't affected)
- All inline styles (Gmail strips `<style>`), table-based layout (Outlook requires it), SVG icons embedded as `data:image/svg+xml` for broad client compatibility
- Honors `$additional_content` from the WC email settings

### React Apps (Frontend)
- **Shop Filter** (`/shop`, `/product-category/*/`): Product filtering, pagination, grid/list toggle. URL sentinel `?sf=1` marks app-set params to distinguish from stale URL state. Config injected via `data-config` on the mount element (taxonomy pages pre-seed `initial_category`).
- **Cart** (`/cart`): Full cart management via WC Store API
- **Checkout** (`/checkout`): Step-by-step with numbered sections, phone with country code
- All use WC Store API for client-side operations (no page reloads)
- Cart steps component (`CartSteps.jsx`) shared between cart and checkout
- Live in `src/js/frontend/` — bundled by `vite.config.parent.js`, ships its own React runtime

### React Apps (Admin)
Admin apps are React panels mounted inside `wp-admin` pages. They consume WordPress's bundled React + `@wordpress/components` via `window.wp.*` globals rather than bundling their own copy, keeping bundles tiny (~12 KB vs ~500 KB).

**Existing panels:**
- **Typography** (Zippy AI → Typography): Hybrid font picker with Google / Upload / URL sources; live preview; drag-drop uploads. Backed by `AiZippy\Api\TypographyApi` REST routes.

**Build pipeline** (`vite.config.admin.js`):
- Entries live in `src/js/admin/{feature}/index.jsx`
- Aliases `@wordpress/element`, `@wordpress/components`, `@wordpress/i18n`, `@wordpress/api-fetch`, `react`, `react-dom` → shim files in `src/js/admin/shared/wp-shims/` that re-export from `window.wp.*`
- Uses **classic JSX runtime** (`jsxRuntime: "classic"`) — automatic runtime isn't compatible with the shim approach since `wp.element` doesn't expose `jsx`/`jsxs` helpers
- Output: `assets/dist-admin/js/{name}.js` + `assets/dist-admin/css/{name}.css` (one file per entry, ES module format)

**Shared admin layer** (`src/js/admin/shared/`):
- `api.js` — `apiFetch` wrapper with `/ai-zippy/v1/` base (`apiGet`, `apiPost`, `apiDelete`, `apiUpload`)
- `hooks/useSettings.js` — Generic `{ value, setValue, save, saving, loading, error, savedAt }` hook for any REST-backed settings resource
- `wp-shims/` — window.wp.* adapters (add a new shim + alias in `vite.config.admin.js` when consuming a new `@wordpress/*` package)

**How to add a new admin panel:**
1. Create `src/js/admin/{feature}/index.jsx` + `App.jsx` + `{feature}.scss`
2. Add the entry to `vite.config.admin.js` `candidates` object
3. Create `inc/Api/{Feature}Api.php` with REST routes guarded by `current_user_can('manage_options')`
4. Register routes in `inc/loader.php` under the `// API` group
5. Create `inc/Admin/{Feature}.php` with `addSubMenu()` (renders `<div id="..."></div>` mount point) and `enqueueAdminApp()` (calls `ViteAssets::enqueueAdmin()`)
6. Register in `inc/loader.php` under the `// Admin` group

**PHP enqueue helper:** `AiZippy\Core\ViteAssets::enqueueAdmin($handle, $manifest_key)` auto-adds `wp-element`, `wp-components`, `wp-i18n`, `wp-api-fetch` as script deps and enqueues `wp-components` CSS.

### Gutenberg Blocks
- Server-side rendered (`save.js` returns null, `render.php` for output)
- Block category: "AI Zippy" (slug: `ai-zippy`) — both parent and child register under this same category
- **Parent blocks** (core, ship with every client): `hero-section`, `product-showcase`, `brand-intro`, `search-bar`, `mini-cart`
- **Child blocks** (client-specific): live in `ai-zippy-child/src/blocks/` and auto-register via the child's `functions.php`
- `product-showcase` uses Swiper.js (CDN, dynamically loaded)

## Coding Rules

- All parent PHP in PSR-4 classes — no procedural functions outside `functions.php`
- Child `functions.php` is procedural (small enough) — if it grows, promote to a PSR-4 namespace
- No jQuery — use vanilla JS or React
- Use theme.json design tokens — never hardcode colors
- SCSS uses `_variables.scss` mixins for responsive breakpoints
- Child SCSS: import parent tokens via `@use "@parent-scss/variables" as *;` — never duplicate token values
- WC template overrides go in `woocommerce/` directory (parent or child)
- Per-client WC override? Copy the file into `ai-zippy-child/woocommerce/` — child version takes priority, parent stays untouched
- CSS class prefixes: `az-` (theme), `zc-` (cart app), `zk-` (checkout app), `sf__` (shop filter), `ma__` (my account), `ps__` (product-showcase block), `bi__` (brand-intro block)
- Frontend strings belong in `Core\L10n`, not in JS — see Core Contract above
- Textdomain is `ai-zippy` everywhere; loaded by `ThemeSetup::setup()` from `languages/`

**Detailed rule files** (read before touching the relevant layer):

| Layer | Rule file |
|-------|-----------|
| Gutenberg blocks | `.claude/rules/blocks.md` |
| PHP classes | `.claude/rules/php-classes.md` |
| SCSS/CSS | `.claude/rules/scss-css.md` |
| FSE templates + WC overrides | `.claude/rules/fse-templates.md` |
| Git workflow | `.claude/rules/git-workflow.md` |
| Core contract (parent ↔ child) | `.claude/rules/core-contract.md` |

---

## Verifying a Frontend Feature

Inspecting the server-rendered HTML is not verification. A drawer can report the
right class, `aria-expanded`, focus and body lock while sitting off-screen, and a
feature can be entirely unstyled while every behavioural assertion passes.

Before calling frontend work done:

1. Build the layer you changed — `build:blocks` for block CSS/JS, `vite build
   --config vite.config.parent.js` for `theme.js`. Use `npx vite`; `vite` is not
   on `PATH`.
2. Drive the real page in a browser and **look at a screenshot**. Read it, do not
   just capture it. Compare against what the feature replaced, if anything.
3. Check the empty state as well as the populated one. They use different code
   paths and different CSS.
4. Confirm zero console errors and that each action fires exactly one request.
5. Report what the screenshot actually shows. "Behaviour passes" is not "feature
   works" — say so plainly when the two differ.

On keyboard activation: Playwright's `click` does not dispatch on this site (the
call times out having sent no events, then fires later and navigates the page).
Use `element.focus()` followed by a key press instead. It is also the better test,
since it exercises the keyboard path.

---

## Git Workflow

```
main              ← Production (protected, never push directly)
  └── develop     ← Integration branch (PR only)
        ├── feature/AZ-{id}-{slug}
        ├── fix/AZ-{id}-{slug}
        ├── hotfix/AZ-{id}-{slug}   ← branches from main
        └── chore/AZ-{id}-{slug}
```

### Branch naming
```bash
git checkout -b feature/AZ-42-cart-upsell-block
git checkout -b fix/AZ-55-checkout-nonce
git checkout -b chore/AZ-61-upgrade-woocommerce
```

### Commit format — Conventional Commits
```
<type>(<scope>): <subject>
```

Types: `feat` `fix` `style` `refactor` `perf` `docs` `chore` `test` `ci`
Scopes: `blocks` `php` `scss` `checkout` `cart` `shop` `api` `templates` `build` `wc`

```bash
feat(blocks): add cart-upsell block with lazy-loaded images
fix(checkout): resolve nonce duplication on checkout page
style(scss): add mobile breakpoints to product-filter
chore(build): upgrade @wordpress/scripts to 28.x
```

### PR flow
```bash
git push -u origin HEAD
```

Full details: `.claude/workflows/commit-standard.md` · `.claude/workflows/pull-request.md`

---

## AI Agent Protocol

All development follows this sequential workflow. **Never skip steps.**

```
A4 (Plan) → A2 (Backend) → A1 (Frontend) → A3 (QA)
```

### A4 — Plan (always first)
Read relevant rule files. Output a numbered checklist of files to create/modify. Wait for developer approval before writing any code.

### A2 — Backend
PHP only. PSR-4 classes in `inc/`. Register via `loader.php`. Never touch `functions.php` beyond constants.

### A1 — Frontend
Implement blocks, React apps, SCSS, templates. Follow prefixes and token rules strictly.

### A3 — QA
Flag real errors only — no verbose reports. Do NOT run build commands (`npm run dev` is already running).

### AI commit generation
After each unit of work, AI analyzes the diff and proposes a conventional commit message. Developer reviews and confirms before `git commit` runs.

### AI PR generation

Prompt templates: `.claude/prompts/generate-commit.md` · `.claude/prompts/generate-pr.md`
