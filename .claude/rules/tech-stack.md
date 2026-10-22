# Rules: Tech Stack & Core Principles

## Core Principles
1. **No jQuery** — Use React (`@wordpress/element`) or vanilla JS
2. **All code in English** — Comments, variables, function names
3. **PHP in PSR-4 classes** — Never put logic in `functions.php`
4. **Design tokens from theme.json** — Never hardcode colors
5. **WC Store API** for client-side cart/checkout — No legacy PHP hooks

## Build Systems

### Vite — Theme JS/SCSS + React apps
- Entry files in `src/js/` and `src/scss/`
- Output → `assets/dist/`
- Dev server runs during development (`npm run dev` — DO NOT restart)
- Enqueue via `ViteAssets::enqueue('handle', 'manifest-key')`

### @wordpress/scripts — Gutenberg blocks
- Source in `src/blocks/{name}/`
- Output → `assets/blocks/`
- Build: `npm run build:blocks`

## PHP Requirements
- PHP 8.1+
- PSR-4 autoloading via `inc/loader.php`
- Namespace: `AiZippy\`
- WordPress coding standards for hooks/filters
- WooCommerce 8.x+ Store API

## JavaScript Requirements
- ES Modules (`"type": "module"` in package.json)
- React 18 via `@wordpress/element`
- No CommonJS `require()`
- Import extensions required: `import X from './file.js'` not `'./file'`

## File Path Conventions

```
src/blocks/{name}/        ← Block source (wp-scripts)
src/js/{app}/             ← React apps (Vite)
src/scss/                 ← SCSS partials (Vite)
assets/blocks/{name}/     ← Block build output
assets/dist/              ← JS/CSS build output
inc/{Domain}/             ← PHP classes
templates/                ← FSE HTML templates
parts/                    ← FSE template parts
woocommerce/              ← WC template overrides
```

## Environment
- WordPress 6.4+
- WooCommerce 8.x
- Node 20+
- Composer (PSR-4 autoload)
- GitHub for version control
- `npm run dev` is always running — never run build commands
