# home-hero

- Source: `.mockup/slim.html:98-132`
- Registration: `ai-zippy/home-hero`; root class `sm-hero`.

## Copy (exact)

- Eyebrow pill (bg `#39FF14`, 700 12px, uppercase, ls .08em, `padding:7px 14px`): `Delivery in as fast as 1 hour`
- H1: `Daily Quick-Stop Groceries at ` + red span `Your Convenience` — 800 `clamp(38px,5.4vw,66px)/1.04`, ls -.025em
- Paragraph (400 17px/1.6 `#4B5563`, max-width 480px): `Snacks, chilled drinks, frozen favourites and household basics from your neighbourhood S.Mart. Pick a 3-hour slot, go express, or walk in and collect.`
- Primary CTA → `/shop/`: `Shop Now` (red pill, 700 16px, `padding:16px 30px`, shadow `0 10px 24px rgba(255,26,26,.28)`, hover `#E00F0F`)
- Secondary CTA → `/about/`: `How it works` (border `2px solid #111827`, transparent bg, 700 16px, `padding:14px 26px`, hover bg `#111827` white text)

## Image composition (right column)

- Wrapper: `aspect-ratio:1/0.86`, max-width 600px, centered.
- Green blob `#39FF14` inset `2% 18% 12% 2%` radius A; orange blob `#FF9900` inset `14% 0 0 22%` radius B; photo inset `6% 8%` radius C, overflow hidden, bg `#FF1A1A`, shadow `0 30px 60px rgba(17,24,39,.18)`. (Blob radii in ui-spec.md §Radii.)
- Image: `hero-shelves.jpg`, alt `Mini-mart shelves`, object-fit cover.
- Small green circle: top 4% left 0, 9% square, radius 50%, `#39FF14`.
- Floating card (bottom 4% left 2%, white, radius 18px, `padding:12px 16px`, shadow `0 12px 30px rgba(17,24,39,.15)`): eyebrow `Next slot` (600 11px uppercase ls .08em `#6B7280`) + value `Today, 2:00 – 5:00 PM` (700 15px) — text updated from delivery state by child.js.

## Layout & attributes

- Section: container 1280px, `padding:40px 20px 24px`, grid `repeat(auto-fit,minmax(min(100%,420px),1fr))`, gap 40px, align center; text column flex gap 22px.
- Attributes (all with the exact defaults above): `eyebrow`, `heading`, `highlightText`, `paragraph`, `primaryLabel`, `primaryUrl`, `secondaryLabel`, `secondaryUrl`, `image` (media id → `hero-shelves.jpg`), `nextSlotLabel` (static text `Next slot`).

## Responsive

- Stacks to one column < ~860px automatically via auto-fit; floating card stays bottom-left over image; reduce gap to 24px on mobile if needed (verify at 375px).
