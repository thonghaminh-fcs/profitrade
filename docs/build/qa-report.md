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

## Open issues (qa_visual)

- Intermittent uncaught `SyntaxError: Unexpected token '<'` on `/my-account/` only. Root cause
  confirmed in qa_func below (wpautop-mangled parent login script). Non-blocking; login works.

---

# QA report — qa_func

Drove the live site (`http://localhost:16921`) with Playwright (throwaway scripts in `/tmp`) plus
HTTP status checks and `wpcli`. Evidence: HTTP codes, cart/order totals, `wc shop_order list`,
DOM state assertions. No production files changed (one attempted fix reverted — see Defects).

## Links & HTTP status

| URL | Code | Note |
| --- | --- | --- |
| `/`, `/shop/`, `/about/`, `/contact/`, `/cart/`, `/my-account/` | 200 | — |
| `/checkout/` (empty cart) | 302 → `/cart/` | Standard Woo empty-cart redirect, not a defect |
| `/checkout/` (cart filled) | 200 | React `[ai_zippy_checkout]` renders |
| `/product/<slug>/` (×3 sampled) | 200 | single product renders |
| `/shop/?q=cola` | 200 | filters to 2 ("Cola…", "Ch**ocola**te Chip Cookies" substring) |
| `/shop/?cat=<slug>` | 200 | tiles use slugs (`?cat=snacks`), filters correctly |

## Console

Clean on `/`, `/shop/`, `/about/`, `/contact/`, `/cart/`, single products, and shop query args.
`/my-account/` throws one `SyntaxError: Unexpected token '<'` — see Defect 3 (parent-theme
wpautop, non-blocking).

## Behaviors (docs/build/block-map.json) — all PASS

- header search submit → `/shop/?q=<term>`, grid filtered (native `<form action=/shop/ name=q>`)
- wordmark click → `/`
- slot `<select>` change → delivery note updates, express reset, select stays enabled
- express toggle → note "Express: arrives within 60 minutes", slot wrap dims (`opacity .45`),
  `+$20.00` fee applied to cart total
- promo countdown `[data-sm-countdown]` ticks every second (HH:MM:SS to midnight)
- home category tile → `/shop/?cat=<slug>` filtered
- promo card / CTA buttons → `/shop/` or `/about/`
- product card click → quickview modal (`is-open`); qty `+` updates live total ($2.50→$5.00); close works
- quick-add `+` → Store API add + toast "Added 1 × Classic Salted Potato Chips" + cart count 0→1
- drawer qty stepper → item qty updates, fragments/totals refresh
- drawer Checkout → `/checkout/`
- checkout fulfilment card click → hidden `sm_fulfilment` toggles delivery↔pickup, `is-selected`
  + pickup note (order-meta persistence is Defect 2)
- img load error → swaps to `/wp-content/uploads/2026/10/product-fallback.jpg`
- contact form submit → success pane + "Send another" reset (but no email is sent — Defect 1)

## Shop / cart / checkout / account

- Shop archive renders 14 product cards; sort + category + search filters work.
- Mini-cart drawer: correct subtotal/total; express fee ($20) reflected in total ($2.50 → $22.50).
- **COD checkout created order id 53** — `wc shop_order list`: `status=processing, total=2.50,
  payment_method=cod`. Contact + billing captured (`_billing_address_index`). (HPOS stores orders
  in `wp_wc_orders`, so `post list --post_type=shop_order` is empty by design.)
- My Account: classic Woo login form (`#username`/`#password` + `woocommerce-login-nonce`);
  admin login succeeds → logged-in account (logout present).

## Defects (fixed in the 2026-10-22 rework pass below)

1. **Contact form never sends (medium).** Spec (block-map) = "AJAX → admin-ajax → wp_mail → success
   pane". `contact-form/view.js` only `preventDefault()`s and swaps to the success pane;
   `render.php` ships `<form method="post">` with **no action, no nonce, no admin-ajax handler, no
   wp_mail**. Verified: no network request fires on submit — the success message is cosmetic and
   the inquiry is discarded. Fix: add a nonce'd admin-ajax/REST handler in the child theme that
   calls `wp_mail`, and a `fetch` in `view.js` (keep the success pane gated on a real response).
2. **Checkout fulfilment choice not saved to order meta (medium).** Order 53 has empty
   `_sm_fulfilment`/`_sm_slot`/`_sm_express`. Root cause: the order-meta + express-fee hooks are
   registered inside `checkout-fulfilment/render.php`, which runs **only when the checkout page
   renders the block**. The custom `[ai_zippy_checkout]` is a **Store-API React checkout** — its
   order-creation request never renders the block, and `woocommerce_checkout_create_order` (classic
   hook) does not fire for Store-API orders. `render.php`'s own comment flags this. Fix: register
   the hooks from an always-loaded file (`functions.php`) and hook the Store-API order-processed
   action (`woocommerce_store_api_checkout_order_processed`), reading the `sm_*` cookies the block
   already sets. The express **fee** is applied to the cart total (cookie-driven `cart_calculate_fees`),
   but the **order-meta** persistence needs the always-on registration.
3. **`/my-account/` console `SyntaxError: Unexpected token '<'` (low, non-blocking).** Root cause:
   WP `wpautop` wraps the blank-line-separated statements of the inline login-card `<script>` in
   the parent's `woocommerce/myaccount/form-login.php` with `<p>`/`<br>` (confirmed:
   `wpautop("<script>\nvar a=1;\n\nvar b=2;\n</script>")` → `<p><script>…</p>`). The parent theme
   is read-only/out of scope. Impact is limited: login itself works (classic form POST) and the
   rendered card has no login/register tabs to break. Attempted a child `functions.php`
   `the_content` cleanup filter — ineffective (this FSE shortcode path doesn't run `the_content`),
   so it was **reverted** to avoid shipping a no-op global filter. A proper fix would need a child
   WooCommerce template override of `myaccount/form-login.php` (move the JS to an enqueued file).

## Verdict

Core commerce (shop → cart → COD order → login) works end to end. `functional: fail` because two
specified behaviors do not work (contact-form email, checkout fulfilment order-meta). Both are
documented above with root cause and the exact fix path.

---

# QA report — rework pass (2026-10-22)

Rework brief: implement the two missing behaviours (contact-form email, checkout-fulfilment
order persistence), fix the `/my-account/` SyntaxError if cheap, then a short regression pass of
the 7 block-map pages at 1440 and 375. All three defects above are now FIXED.

## 1. Contact form — real submission

- New `inc/contact-form.php` (required from `functions.php`): admin-ajax handler
  `wp_ajax[_nopriv]_sm_contact_submit` verifying a nonce, rejecting silently when the honeypot
  `company` field is filled, validating name/email/message server-side, `wp_mail()` to
  `get_option('admin_email')` with `Reply-To`, JSON responses (`success`, or per-field errors).
- `contact-form/render.php` prints `data-ajax-url` + `data-nonce` on the wrapper; `view.js` posts
  the form with `fetch`, shows the success pane only on a real success response, renders
  per-field errors (`role=alert`, `aria-invalid`) otherwise; submit disabled while in flight.
- Evidence (PHP filter test via `pre_wp_mail` capture, run in the container):
  `valid` → `{"success":true}` + mail to `dev@zippy.sg`, subject `[S.Mart] Contact inquiry from QA Bot`,
  Reply-To header, message body present; `honeypot` → `{"success":true}` with **no mail**;
  `bademail` (empty name + bad email) → per-field errors, no mail; `badnonce` → 403-style error;
  `mailfail` (wp_mail false) → friendly form error.
- Evidence (real HTTP): POST to `/wp-admin/admin-ajax.php` with the nonce scraped from `/contact/`
  → `{"success":true}` HTTP 200; site mail log (`/tmp/mail.log`, dev-only mu-plugin logger, see
  Notes) records `to=dev@zippy.sg | subject=[S.Mart] Contact inquiry from QA End2End`.
- Evidence (browser): Playwright fill + submit on `/contact/` → ajax 200/success, success pane
  visible, form hidden, "Send another" resets to the form; console clean.

## 2. Checkout fulfilment — persisted on Store-API orders

- New `inc/checkout-fulfilment.php` (required from `functions.php`), hooks removed from the
  block's `render.php` (they never ran for the React checkout):
  - `woocommerce_store_api_register_endpoint_data` declares `extensions["sm-fulfilment"]`
    (`fulfilment` / `express` / `slot`) on the checkout endpoint;
  - `woocommerce_store_api_checkout_update_order_from_request` saves `_sm_fulfilment`,
    `_sm_express`, `_sm_slot` — request extensions first, then posted fields, then the `sm_*`
    cookies `view.js` keeps in sync (the parent React app does not forward the hidden inputs);
  - `woocommerce_checkout_create_order` kept for the classic checkout;
  - `woocommerce_cart_calculate_fees` now registered on **every** request, so the +$20 express
    fee also lands in the Store-API checkout totals (previously only in page-rendered fragments);
  - choice shown in wp-admin (`woocommerce_admin_order_data_after_shipping_address`) and in
    order emails (`woocommerce_email_order_meta_fields`).
- Evidence (real COD orders through `/wp-json/wc/store/v1/checkout`, cookies as the browser sets
  them): **order #55** pickup → `status=processing total=5.50 pay=cod`, meta
  `_sm_fulfilment=pickup, _sm_express=0, _sm_slot=8:00 – 11:00 PM`, no fee; **order #56** express →
  `total=25.50` (5.50 + 20.00 fee line `Express delivery=20`), meta
  `_sm_fulfilment=delivery, _sm_express=1, _sm_slot=2:00 – 5:00 PM`.
- Evidence (emails): both orders' admin + customer emails contain `Fulfilment: Walk-in Pickup` /
  `Fulfilment: 1-Hour Express` (mail log).

## 3. /my-account/ SyntaxError

- New child WooCommerce template override `woocommerce/myaccount/form-login.php`: identical
  markup to the parent, inline `<script>` condensed to contain **no blank lines** so `wpautop`
  cannot inject `<p>`/`<br>` inside it. Playwright on `/my-account/`: `consoleErrors: []`,
  login card present.

## Regression pass — 7 pages × 1440 / 375

| Page | 1440 | 375 |
| --- | --- | --- |
| home (`/`) | PASS | PASS |
| shop (`/shop/`) | PASS | PASS |
| about (`/about/`) | PASS | PASS |
| contact (`/contact/`) | PASS | PASS |
| cart (`/cart/`) | PASS | PASS |
| checkout (`/checkout/`, filled cart) | PASS | PASS |
| my-account (`/my-account/`) | PASS | PASS |

All captures: `overflowX: false`, `consoleErrors: []` (wp-shot JSON + checkout Playwright run).
Fulfilment picker re-checked in the browser: 2 cards, pickup↔delivery clicks flip `aria-checked`
and the hidden `sm_fulfilment` input. No white gaps, images unchanged.

## Notes

- The container has no MTA (`mail()` returns false), so a dev-only mu-plugin
  (`/var/www/html/wp-content/mu-plugins/qa-mail-log.php`, outside the repo) logs every `wp_mail`
  to `/tmp/mail.log` via `pre_wp_mail` and reports success. Without it the handler correctly
  surfaces a "could not be sent" error — the transport, not the code, is what fails.
- Accepted diffs from the earlier passes remain accepted (mobile drawer nav, 2-col categories at
  375, sidebar term order, two fulfilment cards).
