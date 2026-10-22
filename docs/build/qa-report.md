# QA report — qa_visual

Compared `.mockup/index.html` (reference) against the live site (`http://localhost:16921`)
at 1440 / 768 / 375 for every page in `docs/build/block-map.json`. Captures in `/tmp/ref-*.png`
and `/tmp/site-*.png`. Cart/checkout/my-account have no mockup view; they were checked against
the brand system and their own empty/filled states.

## Per page / width

| Page | 1440 | 768 | 375 |
| --- | --- | --- | --- |
| home (`/`) | PASS | PASS | PASS |
| shop (`/shop/`) | PASS | PASS | PASS |
| about (`/about/`) | PASS | PASS | PASS |
| contact (`/contact/`) | PASS | PASS | PASS |
| cart (`/cart/`) | PASS (empty + filled) | — | — |
| checkout (`/checkout/`) | PASS | — | — |
| my-account (`/my-account/`) | PASS | — | — |

Editor parity (front page, post 44): PASS — canvas renders the same hero, category and
promotion styling as the front end. The "Welcome to the editor" overlay is core WP's
first-run guide, not a theme defect.

Cross-cutting checks: no horizontal overflow on any page/width; no white gaps between
sections; every `<img>` served from `/wp-content/uploads/`; hamburger `aria-expanded`
toggles false→true→false with nav visibility at 375 on home/shop/about/contact.

## Fixes applied

1. `src/scss/_base.scss` — top-level reset used `margin: 0 !important`, which cancelled
   `margin: 0 auto` and pushed every section against the left edge. Now `margin-block: 0 !important`.
2. `parts/header.html` — header pointed at the parent `ai-zippy/mini-cart`; switched to the
   child `ai-zippy/sm-mini-cart` so the red pill shows the live total and count badge.
3. `src/scss/sections/_shop-catalog.scss` — product images letterboxed because WooCommerce's
   `.woocommerce img { height:auto }` out-specified the child rule; scoped override added.
   Portrait photos then stretched the `aspect-ratio: 1/1` media box (its automatic minimum
   size), so the img (grid cards and quick-view disc) is now absolutely positioned: all media
   boxes measure square (230×230 @768, 210×210 @1440).
4. `templates/page-contact.html` + `src/scss/_base.scss` — contact page had no H1 and no
   composition; added the H1 and a page-scoped grid (full-bleed banner span, 2 columns ≥820px,
   stacked below).
5. `src/scss/_base.scss` — cart promo form: parent flex quirk pushed "Apply" outside the promo
   card; child grid override keeps input+button inside (form right 1246 ⊇ button right 1246).
6. `src/scss/sections/_my-account.scss` (new) + `style.scss` — the parent's customer-account
   block emits a bare unsized `<svg class="icon">`; WooCommerce only sizes
   `.wc-block-customer-account__account-icon`, so the silhouette filled the row. Child rule
   sizes it to 1.5em.

## Accepted remaining diffs (not defects)

- Mobile header uses a hamburger drawer; the mockup shows inline pill nav at ≤768. Deliberate
  adaptation, toggle behaviour verified.
- `home-categories` renders 2 columns at 375 per `docs/build/blocks/home-categories.md`.
- Shop sidebar category order follows term_id; the mockup lists a hand-picked order.
- Checkout shows two fulfilment cards (delivery / pickup): mockup `logic.js` `fulfilOpts` has
  exactly two entries and switches the delivery label to "1-Hour Express" when the bar toggle
  is on — a third express card would diverge from the mockup.

## Open issues

- Intermittent uncaught `SyntaxError: Unexpected token '<'` on `/my-account/` only. Not
  reproducible on demand; `/wp-json/wc/store/v1/cart` returns 200 JSON and the child mini-cart
  fetch paths guard `res.ok`. Suspected third-party script; non-blocking, recorded here.
