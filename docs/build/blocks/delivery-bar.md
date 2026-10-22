# delivery-bar

- Source: `.mockup/slim.html:66-94`; state/labels `.mockup/logic.js:37,125-129`
- Registration: `ai-zippy/delivery-bar` in `src/wp-content/themes/ai-zippy-child/src/blocks/delivery-bar/`
- Placed in: child `parts/header.html`, under the brand row. Root class `sm-delivery-bar`.

## Copy (exact)

- Status: green dot (8px, `#39FF14`) + `Open today 8:00 AM – 12:00 AM` (weight 600)
- Slot label: `3-hour slot` (color `#D1D5DB`); `<select>` options (logic.js:37):
  `8:00 – 11:00 AM`, `11:00 AM – 2:00 PM`, `2:00 – 5:00 PM`, `5:00 – 8:00 PM`, `8:00 – 11:00 PM`
- Express toggle: `1-Hour Express` + orange `+$20.00` (money(fee), fee default 20)
- Right note (margin-left:auto, `#D1D5DB`): express off → `Delivering today, <slot lowercase>`; express on → `Express: arrives within 60 minutes`

## Style

- Bar bg `#111827`, white text, font-size 13px; inner container 1280px, `padding:8px 20px`, flex wrap gap `10px 20px`.
- Select: bg `#1F2937`, border `1px solid #374151`, radius 8px, `padding:5px 8px`, 13px/600. Wrapper opacity .45 when express on.
- Toggle: 38×22px track radius 999px, bg `#4B5563` → `#FF1A1A` when on; 16px white knob, left 3px → 19px, `transition .2s`.

## Attributes

| attr | type | default |
|---|---|---|
| expressFee | number | 20 |
| openHoursText | string | `Open today 8:00 AM – 12:00 AM` |
| defaultSlot | number | 2 |

## Behavior

- child.js owns delivery state `{slot, express}` (shared with mini-cart + checkout-fulfilment; persist in `sessionStorage`).
- Slot change → `express=false`. Express click → toggle. Note text re-renders on change.

## Responsive

- Single row ≥1024px; wraps naturally (flex-wrap). Note goes full-width last on small screens; keep toggle text on one line (`white-space:nowrap` on toggle).
