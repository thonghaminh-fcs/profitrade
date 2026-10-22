# about-story

- Source: `.mockup/slim.html:296-314`
- Registration: `ai-zippy/about-story`; root class `sm-about-story`.

## Copy (exact)

- Eyebrow pill (bg `#FF9900`, color `#111827`, 700 12px uppercase ls .08em, `padding:7px 14px`, radius 999px): `Our story`
- H1 (800 `clamp(34px,4.6vw,54px)/1.06`, ls -.025em, balance): `Your neighbourhood mini-mart, now on your phone.`
- Paragraph 1 (17px/1.7 `#4B5563`): `S.Mart is a convenience store brand operated by Profitrade. We stock the everyday items people run out for: snacks, chilled and alcoholic drinks, frozen food, bakery and household essentials.`
- Paragraph 2: `Our online shop is connected to the same POS system as our counter, so what you see in stock is what's on the shelf. Order for a delivery slot, go express, or walk in and collect.`

## Image composition

- Wrapper `aspect-ratio:1/0.85`, max-width 580px, centered; red blob `#FF1A1A` inset `0 10% 14% 0` radius B; green blob `#39FF14` inset `18% 0 0 26%` radius A.
- `<img>` `store-aisle.jpg` alt `S.Mart store aisle`, absolute inset 7%, 86%×86%, cover, radius C, shadow `0 30px 60px rgba(17,24,39,.18)`.

## Layout

- Section: container 1280px `padding:48px 20px 24px`, grid `repeat(auto-fit,minmax(min(100%,420px),1fr))`, gap 48px, align center; text column gap 18px.

## Attributes

| attr | default |
|---|---|
| eyebrow | `Our story` |
| heading | `Your neighbourhood mini-mart, now on your phone.` |
| paragraphs | the 2 paragraphs above (rich text array) |
| image | media → `store-aisle.jpg` |

## Behavior

- None (static).

## Responsive

- Stacks < ~880px via auto-fit; keep blob insets proportional (they are %).
