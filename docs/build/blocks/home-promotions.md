# home-promotions

- Source: `.mockup/slim.html:155-193`; countdown `.mockup/logic.js:57,115-117,132`
- Registration: `ai-zippy/home-promotions`; root class `sm-promotions`.

## Copy (exact, 3 cards in order)

1. **1-for-1** — bg `#FF1A1A`, text #fff; badge pill (bg #fff, color `#FF1A1A`, 800 13px, `padding:6px 12px`): `1-for-1 Deals`; title (800 28px/1.1): `Buy one, get one on chips`; sub (500 14px/1.5): `All 150g packs this week`; image `product-chips.jpg` (alt empty).
2. **Flash Sale** — bg `#FF9900`, text `#111827`; badge (bg `#111827`, #fff): `Flash Sale`; title: `25% off ice cream tubs`; sub (700 14px/1.5, `font-variant-numeric:tabular-nums`): `Ends in HH:MM:SS` (live countdown to midnight); image `product-icecream.jpg`.
3. **Daily Discounts** — bg `#39FF14`, text `#111827`; badge (bg `#FF1A1A`, #fff): `Daily Discounts`; title: `Fresh bakery from $2.80`; sub (500 14px/1.5): `New markdowns every morning`; image `product-croissants.jpg`.

## Style

- Section: container 1280px `padding:24px 20px 40px`; heading `Today's promotions` (800 30px/1.1, ls -.02em, `margin:0 0 22px`).
- Scroller: flex gap 18px, `overflow-x:auto`, `scroll-snap-type:x mandatory`, `padding-bottom:10px`; card `flex:0 0 min(86vw,400px)`, `scroll-snap-align:start`, radius 28px, padding 26px, min-height 240px, column space-between, cursor pointer → all cards link `/shop/`.
- Card image: absolute `right:-30px;bottom:-30px`, 210×210, cover, `border:6px solid` contrasting colour (card 1 `#FF9900`, card 2 `#FF1A1A`, card 3 `#FF9900`), blob radii B / A / C respectively.
- Text block `position:relative; max-width:55%` (card 2: 58%).

## Behavior

- child.js: countdown `HH:MM:SS` to next midnight, tick 1s (`padStart(2,'0')`), update card 2 sub.

## Attributes

- `heading` default `Today's promotions`; `cards` array (bg, badge, badgeBg, badgeFg, title, sub, image, imageUrl) defaulting to the 3 cards above; card 2 flagged `countdown:true` so its `sub` is replaced at runtime.

## Responsive

- Horizontal snap-scroll at all widths (86vw cards on mobile); hide scrollbar styling per mockup (8px `#D1D5DB` thumb acceptable).
