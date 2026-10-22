# home-categories

- Source: `.mockup/slim.html:133-154`; data `.mockup/logic.js:11-19` (CATS, `home:true` = first 6; `pantry` is excluded from home)
- Registration: `ai-zippy/home-categories`; root class `sm-home-categories`. Data: `woo-products` (product_cat terms).

## Copy (exact)

- Heading: `Shop by category` (800 30px/1.1, ls -.02em)
- Right link-button: `View all products →` (700 14px, color `#FF1A1A`, transparent bg) → `/shop/`
- Tiles show category name only; the 6 home categories in order:
  1. `Snacks` — image `product-chips.jpg`, tint `#FF9900`
  2. `Chilled Drinks` — `product-cola.jpg`, tint `#39FF14`
  3. `Alcoholic Beverages` — `product-beer.jpg`, tint `#FF1A1A`
  4. `Frozen Foods` — `product-icecream.jpg`, tint `#39FF14`
  5. `Household Essentials` — `product-cleaner.jpg`, tint `#FF9900`
  6. `Bread & Bakery` — `product-bread.jpg`, tint `#FF1A1A`

## Tile style

- Grid `repeat(auto-fill,minmax(170px,1fr))`, gap 16px; header row flex space-between, `margin-bottom:22px`.
- Tile: white, border `1px solid #EEF0F3`, radius 24px, `padding:18px 16px 20px`, column, centered, gap 14px; hover `translateY(-4px)` + shadow `0 14px 30px rgba(17,24,39,.10)`, transition .15s; cursor pointer; click → `/shop/?cat=<slug>`.
- Image disc 118×118: tinted blob (radius A, `rotate(-8deg) scale(1.06)`, absolute inset 0) + image disc inset 6px 106×106 radius C, `background: #F3F4F6 url(...) center/cover`, `role="img" aria-label="<name>"`.
- Name: 700 15px/1.25, centered.

## Render / attributes

- `render.php`: query `product_cat` terms that have products, ordered to match the list above (seed categories per products.json; term image = mapped product image, term meta `sm_tint` = tint hex). Fall back to the 6 names if terms missing.
- Attributes: `heading` (default `Shop by category`), `viewAllLabel` (default `View all products →`), `viewAllUrl` (default `/shop/`), `columns` unused (intrinsic grid).

## Responsive

- 6 across at 1280px, wraps intrinsically; 2 columns at 375px (minmax 170px). Verify gap/padding at 375/768.
