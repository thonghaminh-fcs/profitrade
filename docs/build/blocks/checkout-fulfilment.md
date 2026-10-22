# checkout-fulfilment

- Source: `.mockup/slim.html:653-686` (fulfilment step + pickup note), `583-598` (same choice in cart drawer), `748-789` (order summary fee row); logic `.mockup/logic.js:103-104,151-156,162-163`
- Registration: `ai-zippy/checkout-fulfilment`; root class `sm-fulfilment`. Placed by child `templates/page-checkout.html` above the Woo checkout form. Data: `woo-cart`.

## Copy (exact)

- Step eyebrow (700 12px uppercase ls .08em `#6B7280`): `1 · Fulfilment`
- Option cards (radius 16px, `2px` border, selected border `#FF1A1A`, unselected `#E5E7EB`; title 700 15px; sub 12.5px `#6B7280`; `padding:14px`; grid `repeat(auto-fit,minmax(170px,1fr))` gap 10px):
  1. id `delivery` — label `Delivery` (or `1-Hour Express` when express is on in delivery state), sub `Today, <slot> · Free` (or `Within 60 min · $20.00`)
  2. id `pickup` — label `Walk-in Pickup`, sub `Collect at store · Free`
- Pickup note panel (shown instead of shipping address fields; bg `#F9FAFB` radius 14px padding 14px, 14px/1.6): `Collect at ` + bold `S.Mart, 10 Example Road #01-01` + `. We'll message you when your order is packed, usually within 30 minutes.`
- Fee naming used by summary (logic.js:162): `Slot delivery` (free) / `Express delivery` ($20.00) / `Pickup` (free).

## Behavior

- Reads shared delivery state `{slot, express}` (delivery-bar / mini-cart, child.js) and preselects the matching option.
- Renders hidden inputs `sm_fulfilment` (`delivery|pickup`), `sm_express` (`0|1`), `sm_slot` (slot label) that post with the Woo checkout form; a PHP hook (`woocommerce_checkout_create_order`) saves them as order meta `_sm_fulfilment`, `_sm_express`, `_sm_slot`.
- Express fee: applied as a Woo shipping/fee adjustment — simplest faithful mapping is a fee via `woocommerce_cart_calculate_fees` ($20.00, label `Express delivery`) when express is chosen; slot delivery and pickup are free. Builder must keep the cart drawer totals consistent (drawer shows the same fee).
- When `pickup` is selected, child.js hides the Woo shipping address fields (billing address still collected; detailsTitle becomes `Pickup details`, logic.js:156) and shows the pickup note panel.

## Attributes

| attr | default |
|---|---|
| expressFee | 20 |
| pickupNote | the exact note above |

## Responsive

- Option cards wrap via auto-fit minmax(170px). Verify at 375px inside the Woo checkout column layout.
