# S.Mart UI Spec

Source: `.mockup/styles.css` (globals) + inline styles in `.mockup/slim.html` (line refs). All values quoted from the mockup — no estimates.

## Palette

| Token | Hex | Used for |
|---|---|---|
| brand-red | `#FF1A1A` | Primary CTA, links, active variant border, cart button, accents (slim.html:109, 166 styles.css) |
| brand-red-hover | `#E00F0F` | Hover on red CTAs (slim.html:109 `style-hover`) |
| brand-green | `#39FF14` | Badge pill, cart count badge, status dot, express toggle track, success blobs |
| brand-orange | `#FF9900` | Flash-sale badge, express fee label, blobs |
| ink | `#111827` | Body text (styles.css:164), dark bar/footer/modal overlays, "Chilled" badge bg |
| page-bg | `#F9FAFB` | Body background (styles.css:164), input bg, spec panels |
| card-bg | `#FFFFFF` | Cards, header, drawer |
| card-border | `#EEF0F3` | Card/header borders (slim.html:25, 142) |
| input-border | `#E5E7EB` | Inputs, variant buttons, dividers |
| surface-gray | `#F3F4F6` | Search field bg, image placeholders, qty steppers |
| text-muted | `#6B7280` | Secondary labels, category eyebrow |
| text-body | `#4B5563` | Paragraph text |
| text-faint | `#9CA3AF` | Strikethrough price, footer legal |
| text-on-dark | `#D1D5DB` | Footer text, delivery note |
| dark-panel | `#1F2937` | Slot `<select>` bg (slim.html:75) |
| dark-border | `#374151` | Slot `<select>` border; footer divider uses `#1F2937` (slim.html:459) |
| variant-selected-bg | `#FFF1F1` | Selected variant button bg (logic.js:110) |
| stock-green | `#16A34A` | "in stock at store · synced with POS" line (slim.html:526) |
| paynow-purple | `#7C1A8E` | PayNow text/QR frame (slim.html:446, 692, 707) |
| visa-navy | `#1A1F71` | VISA wordmark (slim.html:448, 694) |
| mc-red / mc-amber | `#EB001B` / `#F79E1B` | Mastercard circles (slim.html:451-453) |
| scrollbar | `#D1D5DB` thumb, 8px, radius 8px (styles.css:168) | |

Badge colours (logic.js:36): `1-for-1` → bg `#FF1A1A` fg `#fff`; `Flash Sale` → bg `#FF9900` fg `#111827`; `Daily Discount` → bg `#39FF14` fg `#111827`; `Chilled` → bg `#111827` fg `#fff`.
Category tints (logic.js:11-19): Snacks `#FF9900`, Chilled Drinks `#39FF14`, Alcoholic Beverages `#FF1A1A`, Frozen Foods `#39FF14`, Household Essentials `#FF9900`, Bread & Bakery `#FF1A1A`, Instant & Pantry `#FF9900`.
Overlays: quickview `rgba(17,24,39,.55)` (slim.html:471), cart `rgba(17,24,39,.45)` (533), checkout `rgba(17,24,39,.6)` (626).

## Fonts

Family: **Poppins** (`font-family:Poppins,system-ui,sans-serif`, styles.css:164). Weights actually used: 400, 500, 600, 700, 800, 900 (styles.css @font-face set; 900 only in the S.MART wordmark).

Copy latin subset woff2 from `.mockup/assets/` to `$CHILD/assets/fonts/` (devanagari/latin-ext optional, not needed for English copy):

| Weight | File |
|---|---|
| 400 | `dca22b5e.woff2` |
| 500 | `2d0aa977.woff2` |
| 600 | `2e0400ee.woff2` |
| 700 | `6eea89d0.woff2` |
| 800 | `a299cbbd.woff2` |
| 900 | `613c6d47.woff2` |

Declare `@font-face { font-display: swap; }` in child SCSS.

## Type scale (quoted)

- Home h1: `800 clamp(38px,5.4vw,66px)/1.04`, `letter-spacing:-.025em`, `text-wrap:balance` (slim.html:102); red span `#FF1A1A` on "Your Convenience".
- About h1: `800 clamp(34px,4.6vw,54px)/1.06` (300). Contact h1: `800 clamp(34px,4.6vw,52px)/1.06` (334).
- Shop h1 / checkout "Checkout" title: `800 32px/1.1` (245) / `800 28px` (651).
- Section h2: `800 30px/1.1`, `letter-spacing:-.02em` (135, 156, 316). Late-CTA h2: `800 clamp(28px,3.4vw,40px)/1.1` (203).
- Promo card title: `800 28px/1.1` (164). Modal h2: `800 28px/1.15` (484).
- Lead paragraph: `400 17px/1.6` `#4B5563` (106); about paragraphs `17px/1.7` (302).
- Card/product title: `600 15px/1.3` (272). Price: `700 18px` (278). Totals: `800 18px` (612) / `800 20px` (782).
- Eyebrow/label: `700 12px` uppercase `letter-spacing:.08em` `#6B7280` (126, 217, 489).
- Nav/button: `600 14px` (47), CTA `700 16px` (109). Wordmark: `900 28px/1` with `-webkit-text-stroke:3px #FF1A1A; paint-order:stroke fill` (30); footer wordmark `900 24px/1` (418).

## Spacing & container

- Container: `max-width:1280px; margin:0 auto; padding:…20px` sides (every section, e.g. 26, 98).
- Section vertical padding: hero `40px 20px 24px` (98); categories `40px 20px` (133); promos `24px 20px 40px` (155); late-CTA `16px 20px 64px` (194); shop `32px 20px 64px` (214); about `48px 20px 24px` + `40px 20px 72px` (296, 315); contact `48px 20px 72px` (333); footer `44px 20px 28px` (414).
- Card padding: 18px (category/sidebar), 14px 14px 16px (product), 26px (promo), 28px (advantage/contact form), 32px (modal panes), 48px `clamp(24px,5vw,64px)` (late-CTA).
- Grid gaps: hero 40px, category grid 16px, promo scroller 18px, shop layout 28px, product grid 16px, advantages 16px, contact grid 24px.

## Radii

- Pill `999px`: buttons, search field, badges, cart button, toasts, qty steppers.
- Cards: product `22px`, category/sidebar/dashed-empty `24px`, promo/advantage `28px`, banners/checkout `32px`, late-CTA `36px`, inputs `14px`, inner panels `16px`/`20px`, cart item image `16px`.
- Organic blob radii (decorative shapes & image masks): `58% 42% 63% 37% / 45% 55% 45% 55%` (A), `42% 58% 35% 65% / 60% 40% 60% 40%` (B), `50% 50% 38% 62% / 55% 62% 38% 45%` (C).

## Shadows

- Red CTA: `0 10px 24px rgba(255,26,26,.28)`. Card hover: `0 14px 30px rgba(17,24,39,.10)` with `translateY(-3px)` (products) / `-4px` (categories), transition `.15s`.
- Hero/next-slot card: `0 12px 30px rgba(17,24,39,.15)`; big image: `0 30px 60px rgba(17,24,39,.18)`; modal image: `0 20px 40px rgba(17,24,39,.18)`.
- Cart drawer: `-20px 0 50px rgba(17,24,39,.18)`. Toast: `0 10px 30px rgba(17,24,39,.3)`. Modal close btn: `0 2px 10px rgba(0,0,0,.12)`.

## Breakpoints / responsiveness

The mockup has **no media queries** — it is fluid: `repeat(auto-fit, minmax(min(100%,420px),1fr))` (hero 98, about 296, modal 472), `auto-fill minmax(170px,1fr)` (home categories 140), `minmax(190px,1fr)` (product grid 258), `minmax(min(100%,380px),1fr)` (contact 351), flex `1 1 220px` sidebar / `4 1 520px` grid on shop (215, 243), `min(86vw,400px)` promo cards with `scroll-snap-type:x mandatory` (158-159), `clamp()` type. Keep the same intrinsic approach; parent mixins `from(md)/until(lg)` only where sticky sidebar must unstick (shop aside `position:sticky;top:160px`, 215 — disable < 900px approx, verify at 375/768).

## Global behaviours

1. **Top colour strip**: 3×4px rows green/red/orange above header (slim.html:17-24) and at footer bottom (461-468).
2. **Sticky header** `position:sticky;top:0;z-index:30`, white, border-bottom `#EEF0F3`; wordmark logo click → home; nav pills Home/Shop/About Us/Contact, active = bg `#111827` fg `#fff`, inactive transparent/`#111827` (logic.js:76-78).
3. **Header search**: pill input `#F3F4F6`, focus-within border `2px solid #111827`, placeholder `Search chips, drinks, ice cream…`; typing from any view switches to shop view filtered by query (logic.js:124) → on WP: submit/redirect to `/shop/?q=…`.
4. **Header cart button**: red pill, cart icon, live `{{ cartTotalLabel }}` + green count badge (slim.html:51-64) → opens cart drawer.
5. **Delivery bar** (dark `#111827` under header): status dot + `Open today 8:00 AM – 12:00 AM`; "3-hour slot" `<select>` with 5 slots (logic.js:37): `8:00 – 11:00 AM`, `11:00 AM – 2:00 PM`, `2:00 – 5:00 PM`, `5:00 – 8:00 PM`, `8:00 – 11:00 PM` (default index 2); "1-Hour Express +$20.00" toggle (track `#4B5563`→`#FF1A1A`, knob 3px→19px, `.2s`); right-side note `Delivering today, 2:00 – 5:00 pm` or `Express: arrives within 60 minutes` (logic.js:128). Choosing a slot turns express off and dims selector to `opacity .45` when express on.
6. **Cart drawer**: fixed right, `width:min(430px,100vw)`, z-51, overlay z-50; header `Your cart (n)`; empty state = logo image + `Your cart is empty` + red `Browse products` button; item rows 64px image, variant·unit price, qty stepper pill, `Remove` underlined, line total; footer: Delivery picker (2 buttons `3-Hour Slot` / `1-Hour Express`, selected border `#FF1A1A`), Subtotal / Delivery (`Free` or `$20.00`) / Total rows, red full-width `Checkout` button (54px).
7. **Product quickview modal** (slim.html:470-531): overlay z-60, panel `min(920px,100%)` radius 32px, two panes; tinted blob behind product image; category eyebrow in red; name; description; variant buttons (label + price, selected `2px #FF1A1A` / `#FFF1F1`); spec table incl. `Type: Variable product|Simple product` (logic.js:111); qty stepper + `Add to Cart` button showing live total; `● {stock} in stock at store · synced with POS` in `#16A34A`; `×` close top-right.
8. **Checkout modal** (slim.html:625-794): overlay z-70, panel `min(980px,100%)`; steps `1 · Fulfilment` (Delivery/1-Hour Express vs Walk-in Pickup cards), `2 · Shipping details|Pickup details` (name, mobile, address/postal/notes or pickup note), `3 · Payment` (PayNow QR panel with `Corporate PayNow`, `Profitrade Pte. Ltd.`, `UEN 2020XXXXXK`, amount; or Visa/MC card fields); `Place order · {total}` red button; right column `Order summary` with line items + Subtotal / fee (`Pickup|Express delivery|Slot delivery`, logic.js:162) / Total. Success pane: green blob ✓, `Order confirmed`, `Order #SM{6 digits}`, note per fulfilment (logic.js:165), `Continue shopping`. → On WP this becomes the `/checkout/` page (Woo) styled the same; see pages.json.
9. **Toast** (slim.html:795-800): fixed bottom-center dark pill with green dot, `Added {qty} × {name}`, auto-hides after 1800ms (logic.js:67). Owner: child.js.
10. **Countdown**: `Ends in HH:MM:SS` to midnight, ticked every 1s (logic.js:57, 115-117, 132).
11. **Image error fallback**: broken images swap to `product-fallback.jpg` (logic.js:2, 57).
12. **Money format**: `$` + 2 decimals (logic.js:44). WhatsApp link `https://wa.me/6590000000?text=Hi%20S.Mart…` (logic.js:138) — number is a placeholder prop, confirm real one.
