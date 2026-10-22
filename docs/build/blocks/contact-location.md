# contact-location

- Source: `.mockup/slim.html:351-376` (left card of the contact grid; the grid itself is `351`, form card is separate block)
- Registration: `ai-zippy/contact-location`; root class `sm-contact-location`.

## Copy (exact)

- Address card: eyebrow `Address` (700 12px uppercase ls .08em `#6B7280`); value (600 15px/1.5): `S.Mart by Profitrade` / `10 Example Road, #01-01` / `Singapore 000000` (3 lines). Placeholder address — confirm real store details.
- Hours card: eyebrow `Hours`; value: `Daily` / `8:00 AM – 12:00 AM`; note (13px `#6B7280`): `Walk-in pickup during opening hours`.

## Map

- `<iframe title="Store map">` src `https://www.openstreetmap.org/export/embed.html?bbox=103.835%2C1.276%2C103.865%2C1.296&layer=mapnik&marker=1.2865%2C103.8500`, `display:block;width:100%;height:280px;border:0`. Coordinates are mockup placeholders — confirm real location.

## Style

- Card: white, border `#EEF0F3`, radius 28px, overflow hidden; below map: `padding:22px`, grid `repeat(auto-fit,minmax(180px,1fr))` gap 18px; each cell column gap 4px.

## Layout context

- Contact page grid (owned by the template/page composition): `repeat(auto-fit,minmax(min(100%,380px),1fr))` gap 24px — this block is column 1, contact-form column 2.

## Attributes

| attr | default |
|---|---|
| mapEmbedUrl | the OpenStreetMap URL above |
| addressLines | `S.Mart by Profitrade`, `10 Example Road, #01-01`, `Singapore 000000` |
| hoursLines | `Daily`, `8:00 AM – 12:00 AM` |
| pickupNote | `Walk-in pickup during opening hours` |

## Behavior

- None (static).
