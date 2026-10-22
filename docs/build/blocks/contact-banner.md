# contact-banner

- Source: `.mockup/slim.html:336-350`; link `.mockup/logic.js:138`
- Registration: `ai-zippy/contact-banner`; root class `sm-contact-banner`.

## Copy (exact)

- Title (800 24px/1.2): `Need Carton/Bulk Pricing? Contact us on WhatsApp`
- Body (15px/1.5): `Wholesale rates for offices, events and resellers. Replies within store hours.`
- CTA link (bg `#111827`, white, 700 16px, `padding:16px 26px`, radius 999px, hover bg `#FF1A1A`): green dot 10px `#39FF14` + `Chat on WhatsApp`
- `href` = `https://wa.me/<number>?text=Hi%20S.Mart%2C%20I%20would%20like%20carton%2Fbulk%20pricing%20for%3A`, `target="_blank" rel="noopener"`. Default number `6590000000` (placeholder in mockup — confirm real number).

## Style

- Panel: bg `#39FF14`, color `#111827`, radius 32px, `padding:28px clamp(22px,4vw,44px)`, flex wrap center space-between gap 18px, `overflow:hidden`, relative.
- Decorative blob: orange `#FF9900` 200px radius B at `right:-40px;top:-60px`, `opacity:.9`.
- Text column: relative, gap 6px, max-width 560px.

## Attributes

| attr | default |
|---|---|
| title | `Need Carton/Bulk Pricing? Contact us on WhatsApp` |
| body | `Wholesale rates for offices, events and resellers. Replies within store hours.` |
| ctaLabel | `Chat on WhatsApp` |
| whatsappNumber | `6590000000` |

## Behavior

- None beyond the wa.me link.

## Responsive

- Fluid padding; CTA wraps under text on narrow screens (flex-wrap).
