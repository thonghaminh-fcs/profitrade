# shop-catalog

- Source: `.mockup/slim.html:213-293` (layout), `470-531` (quickview modal), `795-800` (toast); logic `.mockup/logic.js:80-94,106-113,133-136`
- Registration: `ai-zippy/shop-catalog`; root class `sm-shop`. Rendered by child `templates/archive-product.html` + `taxonomy-product_cat.html`. Data: `woo-products`.

## Layout

- Section: container 1280px, `padding:32px 20px 64px`, flex wrap gap 28px, align start.
- Sidebar `aside`: `flex:1 1 220px`, `position:sticky;top:160px` (unstick below ~900px), column gap 18px.
- Main: `flex:4 1 520px; min-width:0`, column gap 18px.

## Sidebar

- Categories card (white, border `#EEF0F3`, radius 24px, padding 18px): eyebrow `Categories` (700 12px uppercase ls .08em `#6B7280`); buttons `All products` + 7 category names (products.json order: Snacks, Chilled Drinks, Alcoholic Beverages, Frozen Foods, Household Essentials, Bread & Bakery, Instant & Pantry) each with right-aligned count (12px, opacity .7). Active: bg `#111827` white; inactive transparent `#111827`; radius 12px, `padding:10px 12px`, 600 14px. Counts = published products per category (14 total).
- Sort card: label `Sort by`; select options: `Most popular` (popular), `Price: low to high` (low), `Price: high to low` (high); style border `#E5E7EB` radius 12px padding 10px 14px/600 bg `#F9FAFB`.

## Header row + grid

- H1 = `All products` or the active category name (800 32px/1.1); right label `N items` / `N items for "q"` (14px `#6B7280`, logic.js:135).
- Grid `repeat(auto-fill,minmax(190px,1fr))` gap 16px. Card: white, radius 22px, border `#EEF0F3`, overflow hidden, hover `translateY(-3px)` + `0 14px 30px rgba(17,24,39,.10)`.
  - Image square (aspect 1/1, bg `#F3F4F6`, cover); badge pill top-left 10px (colours per ui-spec §Palette; 800 11px `padding:5px 10px`).
  - Body `padding:14px 14px 16px`: category eyebrow (600 11px uppercase ls .06em `#6B7280`), name (600 15px/1.3), unit line (400 13px `#6B7280`): `N options` if variants > 1 else first variant label (logic.js:84).
  - Price row: `from $X.XX` when multi-variant (700 18px), was-price strikethrough `#9CA3AF` 13px when on sale; quick-add round button 40px bg `#FF1A1A` white `+` (600 24px), hover `#111827`, `aria-label="Add to cart"` → adds first variant, qty 1.
- No results (logic.js:136): dashed card (border `1px dashed #D1D5DB`, radius 24px, padding 48px, centered): `No products match "<q>"` (700 18px) + dark pill button `Clear filters`.

## Quickview modal (rendered by this block, opened by card click)

- Overlay z-60 `rgba(17,24,39,.55)` click closes; panel white radius 32px `min(920px,100%)`, `max-height:calc(100vh - 32px)`, grid `repeat(auto-fit,minmax(min(100%,340px),1fr))`; `×` button top-right 40px circle white shadow.
- Left pane bg `#F9FAFB` padding 32px: category-tint blob (radius A) + image disc (radius C, 86% width, shadow `0 20px 40px rgba(17,24,39,.18)`).
- Right pane padding 32px gap 16px: category eyebrow (700 12px uppercase, color `#FF1A1A`); name (800 28px/1.15); description (15px/1.6 `#4B5563`); variant label = `variant_title` when >1 variant else `Size` (logic.js:109); variant buttons (2px border, selected `#FF1A1A`/`#FFF1F1`, else `#E5E7EB`/white, radius 14px, label 600 14px + price 500 12px `#6B7280`); specs grid on `#F9FAFB` radius 16px (`k` `#6B7280`, `v` 600) from products.json + row `Type` = `Variable product`/`Simple product`; qty stepper (2px `#E5E7EB` pill h52, −/+ 48px) + `Add to Cart` red pill h52 showing `Add to Cart` / live total right; stock line: `● <stock> in stock at store · synced with POS` (12px, 600, `#16A34A`).

## Behavior (child.js + Woo Store API)

- URL args `?q=`, `?cat=` seed search/category; filtering, sorting (popular = `popularity` meta from products.json `popularity`, low/high = first-variant price) client-side over rendered product JSON (block passes catalog data as JSON script tag or uses Store API `/wc/store/v1/products`).
- Card click → modal; quick-add and modal add → Store API add-to-cart (variation mapping: multi-variant products are seeded as simple at base price — see report questions), then toast `Added <qty> × <name>` (dark pill bottom-center, green dot, 1800ms) and mini-cart refresh.

## Attributes

| attr | default |
|---|---|
| perPage | 14 (all) |
| columns | intrinsic (minmax 190px) |
