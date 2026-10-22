# mini-cart (adapt parent `ai-zippy/mini-cart`)

- Source: header button `.mockup/slim.html:51-64`; drawer `532-624`; logic `.mockup/logic.js:96-105,144-149`
- Do NOT edit the parent. Child overrides presentation: re-skin the parent drawer markup in child SCSS and, where markup is missing (delivery picker), extend via the parent's `window.aiZippy` runtime contract / child.js injection.

## Header button (`sm-cart-button`)

- Red `#FF1A1A` pill, height 46px, `padding:0 18px 0 14px`, font 700 14px, white cart SVG icon (stroke 2.2, 22px).
- Shows live cart total `{{ cartTotalLabel }}` (money format). Hover bg `#E00F0F`.
- Count badge: absolute top -6 right -6, min-width 22px h22, bg `#39FF14`, color `#111827`, `800 12px/22px`, `border:2px solid #fff`, radius 999px.

## Drawer (`sm-cart-drawer`)

- Fixed right, `width:min(430px,100vw)`, z-51, bg #fff, shadow `-20px 0 50px rgba(17,24,39,.18)`; overlay z-50 `rgba(17,24,39,.45)`, click closes.
- Header row: `Your cart` (800 22px) + `(n)` (`#6B7280`, 600 15px); `×` close 40px circle bg `#F3F4F6`; border-bottom `#EEF0F3`.
- Empty state: logo `logo-smart.jpg` 110px, `Your cart is empty` (700 18px), red pill `Browse products` → `/shop/`.
- Item row: 64px image radius 16px; name 600 14px/1.3; `<variant> · <unit price>` 12px `#6B7280`; qty pill (bg `#F3F4F6`, h32, −/+ 32px buttons, qty 700 13px); `Remove` underlined 500 12px `#6B7280`; line total 700 15px right.
- Footer (bg `#F9FAFB`, border-top `#EEF0F3`, `padding:18px 22px 22px`):
  - Eyebrow `Delivery` (700 12px uppercase, ls .08em, `#6B7280`)
  - 2 picker buttons: `3-Hour Slot` / sub `Today, <slot> · Free`; `1-Hour Express` / sub `Within 60 min · $20.00`. Selected border `2px #FF1A1A`, else `#E5E7EB`; radius 16px; titles 700 14px, subs 12px `#6B7280`.
  - Rows: `Subtotal`, `Delivery` (value `Free` or `$20.00`), `Total` (800 18px).
  - `Checkout` button: full width h54 red pill 700 16px → navigates `/checkout/`.

## Behavior

- Cart data via Woo (fragments/Store API as the parent already does). Delivery picker reads/writes the shared `{slot, express}` state from delivery-bar (child.js); express adds `expressFee` ($20.00 default) to totals display and must be sent to checkout (hidden field / session).
- Opening drawer: from header button; close on overlay/×/Esc.

## Responsive

- <430px drawer is full width. Keep footer sticky at bottom (`flex` column, list `overflow:auto`).
