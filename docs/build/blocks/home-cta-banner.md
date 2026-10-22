# home-cta-banner

- Source: `.mockup/slim.html:194-211`
- Registration: `ai-zippy/home-cta-banner`; root class `sm-cta-banner`.

## Copy (exact)

- Heading: `Out of milk at 11 PM? We're still open.` (800 `clamp(28px,3.4vw,40px)/1.1`, ls -.02em, `text-wrap:balance`)
- Paragraph (`#D1D5DB`, 16px/1.6): `Order now and choose a delivery slot, or pick up at the counter.`
- Button → `/shop/`: `Start shopping` (bg `#39FF14`, color `#111827`, 800 16px, `padding:16px 30px`, radius 999px)

## Style

- Section: container 1280px `padding:16px 20px 64px`.
- Panel: bg `#111827`, white text, radius 36px, `padding:48px clamp(24px,5vw,64px)`, flex wrap, align center, space-between, gap 24px, `overflow:hidden`, `position:relative`.
- Decorative blobs (absolute): red `#FF1A1A` 280px circle-blob radius A at `right:-60px;top:-80px`; orange `#FF9900` 200px radius B at `right:120px;bottom:-110px`; green `#39FF14` 26px circle at `right:60px;top:40px`.
- Text column: `position:relative`, max-width 560px, gap 10px.

## Attributes

| attr | default |
|---|---|
| heading | `Out of milk at 11 PM? We're still open.` |
| paragraph | `Order now and choose a delivery slot, or pick up at the counter.` |
| ctaLabel | `Start shopping` |
| ctaUrl | `/shop/` |

## Behavior

- None (pure static banner).

## Responsive

- Fluid padding via clamp; text and button stack on narrow widths (flex-wrap) — verify at 375px (button full-width not required by mockup, keep intrinsic).
