# AI Zippy — WordPress FSE Theme + WooCommerce

A Full Site Editing (FSE) WordPress theme with WooCommerce integration, built with Vite + React + `@wordpress/scripts`. Ships as a **parent + child theme** pair so that core features and per-client customizations stay cleanly separated.

---

## Table of contents

- [Who does what](#who-does-what)
- [Quick start](#quick-start)
- [Project structure](#project-structure)
- [Build commands](#build-commands)
- [Parent theme workflow (core)](#parent-theme-workflow-core)
- [Child theme workflow (client work)](#child-theme-workflow-client-work)
- [Shared SCSS variables & mixins](#shared-scss-variables--mixins)
- [Creating Gutenberg blocks](#creating-gutenberg-blocks)
- [WooCommerce template overrides](#woocommerce-template-overrides)
- [Ports reference](#ports-reference)
- [Troubleshooting](#troubleshooting)

---

## Who does what

| Role | Theme | Command | BrowserSync |
|---|---|---|---|
| **Core maintainer** (Shin) | `ai-zippy` (parent) | `npm run dev` | http://localhost:3000 |
| **Client team** | `ai-zippy-child` (child) | `npm run dev:child` | http://localhost:3001 |

- **Parent theme** holds the core system: cart, checkout, account, shop filter, shared blocks, REST API, design tokens.
- **Child theme** holds per-client customizations: client-specific blocks, style overrides, template tweaks.
- Both processes are **fully independent** — run in parallel without conflict.

---

## Quick start

```bash
# 1. Install dependencies (once)
npm install

# 2. Set WordPress URL in .env
echo 'PROJECT_HOST=http://localhost:24' > .env

# 3. Start dev (pick one)
npm run dev          # parent only — for core work
npm run dev:child    # child only  — for client work
npm run dev:all      # both        — parallel

# 4. For production
npm run build        # builds everything
```

---

## Project structure

```
ai_zippy/
├── package.json                  # All scripts (dev, dev:child, build, ...)
├── vite.config.parent.js         # Parent theme Vite config
├── vite.config.child.js          # Child theme Vite config
├── bs.config.parent.js           # Parent BrowserSync (port 3000)
├── bs.config.child.js            # Child BrowserSync (port 3001)
├── docker-compose.yml            # Local WordPress + MySQL
├── .env                          # PROJECT_HOST (not committed)
│
└── src/wp-content/themes/
    │
    ├── ai-zippy/                 # Parent theme (core — maintained by Shin)
    │   ├── functions.php         # 16-line loader — do not add logic here
    │   ├── theme.json            # Design tokens (single source of truth)
    │   ├── style.css             # Theme header
    │   ├── templates/            # FSE page templates (.html)
    │   ├── parts/                # Reusable parts (header, footer)
    │   ├── patterns/             # Block patterns
    │   ├── woocommerce/          # WC template overrides (checkout, myaccount, emails)
    │   ├── inc/                  # PSR-4 PHP classes, AiZippy\ namespace
    │   │   ├── loader.php        # Autoloader + module bootstrap
    │   │   ├── Core/             # ViteAssets, ThemeSetup, Customizer, Cache
    │   │   ├── Api/              # REST: /ai-zippy/v1/products
    │   │   ├── Hooks/            # Cache invalidation
    │   │   ├── Shop/             # Shop page assets
    │   │   ├── Cart/             # Cart page assets
    │   │   ├── Checkout/         # Checkout assets, validation, shortcode
    │   │   └── Account/          # My Account guest redirect + nav filter
    │   ├── src/
    │   │   ├── js/
    │   │   │   ├── theme.js              # Entry — header, add-to-cart, etc.
    │   │   │   ├── modules/              # Vanilla JS modules
    │   │   │   ├── shop-filter/          # React app — product filtering
    │   │   │   ├── cart/                 # React app — cart page
    │   │   │   └── checkout/             # React app — checkout
    │   │   ├── scss/
    │   │   │   ├── style.scss            # Entry — imports all partials
    │   │   │   ├── _variables.scss       # Colors, breakpoints, mixins
    │   │   │   ├── _account.scss
    │   │   │   ├── _cart.scss
    │   │   │   ├── _checkout.scss
    │   │   │   └── ...
    │   │   └── blocks/                   # Core Gutenberg blocks
    │   │       ├── hero-section/
    │   │       ├── product-showcase/
    │   │       └── brand-intro/
    │   └── assets/
    │       ├── dist/                     # Vite output
    │       └── blocks/                   # wp-scripts output
    │
    └── ai-zippy-child/           # Child theme (per-client — maintained by team)
        ├── functions.php         # Vite asset loader + child block registration
        ├── style.css             # Child theme header (Template: ai-zippy)
        ├── theme.json            # Client overrides (merged with parent)
        ├── templates/            # Per-client template overrides
        ├── parts/                # Per-client part overrides
        ├── patterns/             # Per-client patterns
        ├── src/
        │   ├── js/
        │   │   └── child.js      # Entry — client-specific JS
        │   ├── scss/
        │   │   └── style.scss    # Entry — client overrides
        │   └── blocks/           # Client-specific blocks
        └── assets/
            ├── dist/             # Child Vite output
            └── blocks/           # Child wp-scripts output
```

---

## Build commands

All commands run from the project root.

### Dev (watch + BrowserSync)

| Command | Purpose |
|---|---|
| `npm run dev` | **Parent only** — Vite watch + wp-scripts start + BS `:3000` |
| `npm run dev:child` | **Child only** — Vite watch + wp-scripts start + BS `:3001` |
| `npm run dev:all` | Both in parallel (each keeps its own labelled output) |

### Production build

| Command | Purpose |
|---|---|
| `npm run build` | Build everything (parent + child) |
| `npm run build:parent` | Parent only (Vite + parent blocks) |
| `npm run build:child` | Child only (Vite + child blocks) |
| `npm run build:blocks` | Parent blocks only |
| `npm run build:blocks:child` | Child blocks only |

---

## Parent theme workflow (core)

**You are working on core features** that apply across every client: cart, checkout, account, shop filter, shared blocks, design tokens.

```bash
npm run dev
```

This starts three processes with labelled output:

- `[vite]`   — watches `ai-zippy/src/**` and rebuilds to `ai-zippy/assets/dist/`
- `[blocks]` — wp-scripts watches `ai-zippy/src/blocks/**` → `ai-zippy/assets/blocks/`
- `[bs]`     — BrowserSync proxies WordPress at port 3000

Open **http://localhost:3000**. SCSS changes hot-inject; JS/PHP/HTML trigger a full reload.

### Parent conventions

- All PHP logic lives in `inc/` classes under the `AiZippy\` namespace (PSR-4)
- `functions.php` is 16 lines — constants + `require loader.php` only
- Every class has a static `register()` method called from `inc/loader.php`
- Assets enqueued via `\AiZippy\Core\ViteAssets::enqueue('handle', 'manifest-key')`
- Design tokens (`$c-primary`, `$c-accent`, etc.) come from `theme.json` → `_variables.scss`. **Never hardcode colors.**

---

## Child theme workflow (client work)

**The team is customizing for a specific client.** Keep customizations here so the parent stays clean and upgradeable.

```bash
npm run dev:child
```

Opens **http://localhost:3001**. Parent CSS (from `ai-zippy/assets/dist/`) loads first, then child CSS (from `ai-zippy-child/assets/dist/`) overrides anything.

### Adding a client-specific style override

Edit `ai-zippy-child/src/scss/style.scss`:

```scss
@use "@parent-scss/variables" as *;

// Override the accent color for this client
:root {
  --wp--preset--color--accent: #e65c00;
}

// Add a client-specific component
.client-banner {
  background: $c-primary;
  color: $c-white;
  padding: 2rem;
  @include from(md) { padding: 3rem; }
  @include transition(background);
}
```

Save — BrowserSync hot-injects the new styles. No reload needed.

### Adding client-specific JS

Edit `ai-zippy-child/src/js/child.js`:

```js
import "./modules/client-popup.js";
import "./modules/client-analytics.js";
```

### Adding a client-specific PHP template override

Copy the FSE template or WC template you want to customize into the child theme. WordPress uses it automatically:

```bash
# Example: override the shop template for this client
cp src/wp-content/themes/ai-zippy/templates/archive-product.html \
   src/wp-content/themes/ai-zippy-child/templates/archive-product.html
```

Then edit the child copy.

---

## Shared SCSS variables & mixins

The child theme has direct access to all parent tokens via path aliases:

```scss
@use "@parent-scss/variables" as *;

// Now available:
$c-primary, $c-accent, $c-secondary, $c-border, $c-muted, $c-danger, $c-success, ...
$bp-sm, $bp-md, $bp-lg, $bp-xl, $bp-xxl
$border-radius, $border-radius-lg, $box-shadow, $transition-speed

// Mixins:
@include from(md) { ... }       // min-width: 768px
@include until(lg) { ... }      // max-width: 1023px
@include transition((color, background));
@include truncate(2);
@include card-hover;
```

**Why this matters:** if you change `$c-primary` in the parent, every child re-builds with the new color automatically. No duplication, no drift between clients.

### Path aliases summary

| Alias | Points to | Use in |
|---|---|---|
| `@` | Current theme's `src/` | Both |
| `@scss` | Current theme's `src/scss/` | Both |
| `@parent` | Parent theme's `src/` | Child only |
| `@parent-scss` | Parent theme's `src/scss/` | Child only |

---

## Creating Gutenberg blocks

Both parent and child auto-register blocks under the **AI Zippy** category from their respective `assets/blocks/*/block.json`.

### Parent block (core — used across all clients)

Create under `src/wp-content/themes/ai-zippy/src/blocks/my-block/`:

```
my-block/
├── block.json         # Metadata
├── index.js           # Entry (imports edit, save)
├── edit.js            # Editor component
├── save.js            # return null (server-side render)
├── render.php         # Frontend HTML
├── style.scss         # Frontend + editor styles
└── editor.scss        # Editor-only overrides
```

Run `npm run dev` — wp-scripts picks it up automatically.

### Child block (client-specific)

Mirror the same structure under `src/wp-content/themes/ai-zippy-child/src/blocks/client-block/`. Run `npm run dev:child`.

### Critical rules for blocks

- `save.js` **must** return `null` (all blocks are server-rendered via `render.php`)
- `render.php` **must** wrap any helper functions in `if (!function_exists('my_helper'))` to prevent fatal errors when the block is used more than once on a page
- Use `file:./` relative paths in `block.json`
- Import `.js` extensions explicitly: `import Edit from "./edit.js"`

See [.claude/rules/blocks.md](.claude/rules/blocks.md) for the full list.

---

## WooCommerce template overrides

Override any WooCommerce template by copying it into the theme's `woocommerce/` directory. Our existing overrides live in the **parent theme**:

```
src/wp-content/themes/ai-zippy/woocommerce/
├── checkout/
│   ├── form-checkout.php       # Custom card-based layout
│   └── thankyou.php            # Branded thank-you page
├── myaccount/
│   ├── my-account.php          # Sidebar + content layout
│   ├── navigation.php          # Icon-based sidebar nav
│   ├── dashboard.php           # Welcome + stats + recent orders
│   ├── orders.php              # Order list with status pills
│   ├── view-order.php          # Order detail with timeline
│   ├── form-login.php          # Tabbed sign-in / register
│   ├── form-lost-password.php  # Styled password recovery
│   ├── form-reset-password.php # Styled password reset
│   ├── form-edit-account.php   # Profile + password change cards
│   ├── my-address.php          # Address card grid
│   └── form-edit-address.php   # Styled address form
└── emails/
    └── customer-reset-password.php  # Branded reset email
```

**For client-specific overrides:** copy the file into `ai-zippy-child/woocommerce/` — the child version takes priority. Edit the child copy, leave the parent untouched.

---

## Ports reference

| Service | Port | URL |
|---|---|---|
| WordPress (Docker) | 24 | http://localhost:24 |
| **Parent BrowserSync** | 3000 | http://localhost:3000 |
| Parent BS UI | 3010 | http://localhost:3010 |
| **Child BrowserSync** | 3001 | http://localhost:3001 |
| Child BS UI | 3011 | http://localhost:3011 |

Adjust `PROJECT_HOST` in `.env` if WordPress runs on a different port.

---

## Troubleshooting

### "No entry file discovered" in child blocks

Normal when the team hasn't added any blocks yet. `src/wp-content/themes/ai-zippy-child/src/blocks/` only has `.gitkeep`. wp-scripts prints the message and continues — no action needed.

### Parent styles not loading in child dev

Make sure the **child theme is active** in WordPress Admin → Appearance → Themes. The child loads the parent's CSS as a dependency; if the child isn't active, only the parent loads (but you wouldn't see child overrides).

### Port 3000 already in use

Another process is using the parent BrowserSync port. Either kill it, or edit `bs.config.parent.js` and change `port: 3000` to something else (e.g., `3002`). Do the same in `bs.config.child.js` if 3001 conflicts.

### Child CSS isn't overriding parent

Check the enqueue order in `ai-zippy-child/functions.php` — the child CSS is enqueued with `['ai-zippy-theme-css-0']` as a dependency, so it loads *after* the parent. Hard refresh the browser (Cmd/Ctrl+Shift+R) to clear cached CSS.

### Blocks showing "Cannot redeclare function"

A `render.php` helper function isn't wrapped in `function_exists`. Every helper inside a block's `render.php` must be wrapped:

```php
if (!function_exists('my_helper')) :
function my_helper() { ... }
endif;
```

### Vite deprecation warnings about `lighten()`

Pre-existing warnings in `src/blocks/product-showcase/style.scss`. Not from the build system — a minor cleanup for later. Builds still succeed.

### Parent and child dev can't run simultaneously

Each uses different ports (3000 vs 3001, 3010 vs 3011 for BS UI). If you get conflicts, check that no leftover `browser-sync` or `vite` processes are hanging:

```bash
pkill -f browser-sync
pkill -f "vite build"
```

---

## License

Proprietary — Zippy <dev@zippy.sg>
