# about-advantages

- Source: `.mockup/slim.html:315-330`; content `.mockup/logic.js:38-43` (ADV)
- Registration: `ai-zippy/about-advantages`; root class `sm-advantages`.

## Copy (exact, 4 cards, numbered 01-04)

| # | Blob bg / fg | Title (700 19px/1.25) | Body (15px/1.6 `#4B5563`) |
|---|---|---|---|
| 01 | `#FF1A1A` / `#fff` | `Open 8:00 AM to 12:00 AM` | `Every day, including public holidays, for late-night and early-morning top-ups.` |
| 02 | `#39FF14` / `#111827` | `Fast delivery slots` | `Choose a free 3-hour slot or 1-hour express delivery to your door.` |
| 03 | `#FF9900` / `#111827` | `POS-integrated real-time stock` | `Online stock comes straight from our store POS, so items you order are on the shelf.` |
| 04 | `#111827` / `#fff` | `Walk-in pickup` | `Order ahead and collect at the counter with no delivery fee.` |

## Style

- Section: container 1280px `padding:40px 20px 72px`; heading `Why S.Mart by Profitrade` (800 30px/1.1, ls -.02em, `margin:0 0 24px`).
- Grid `repeat(auto-fit,minmax(240px,1fr))` gap 16px.
- Card: white, border `#EEF0F3`, radius 28px, padding 26px, column gap 14px.
- Number blob: 54×54, radius A (organic), bg per table, centered number 800 18px in fg colour.

## Attributes

- `heading` (default `Why S.Mart by Profitrade`)
- `items`: array of `{ number, title, body, color, foreground }` with the 4 rows above as defaults.

## Behavior

- None (static).

## Responsive

- 4 across at 1280px → 2 at ~768px → 1 at 375px via auto-fit minmax(240px).
