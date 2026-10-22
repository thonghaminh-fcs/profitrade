# Rules: Gutenberg Block Development

## File Structure
Each block in `src/blocks/{name}/` must contain:
```
block.json      — Metadata, attributes, supports
index.js        — Registration entry (imports edit, save, styles)
edit.js         — Editor UI component
save.js         — Always returns null (server-side render)
render.php      — Frontend HTML output
style.scss      — Frontend + editor shared styles
editor.scss     — Editor-only style overrides
```

## Critical Rules

### 1. `render.php` — Always use `function_exists` guard
WordPress calls `render.php` every time the block appears on a page. Without a guard, adding multiple instances causes a fatal "Cannot redeclare" error.

```php
// WRONG — will crash with 2+ blocks on same page
function my_helper() { ... }

// CORRECT
if (!function_exists('my_helper')) :
function my_helper() { ... }
endif;
```

### 2. `save.js` — Always return null
All blocks use server-side rendering via `render.php`. Never return JSX from save — it causes block validation errors when markup changes.

```js
export default function save() {
    return null;
}
```

### 3. `edit.js` — Don't duplicate className in useBlockProps
WordPress auto-adds the block class name. Adding it manually causes duplicate classes.

```js
// WRONG
const blockProps = useBlockProps({ className: "wp-block-ai-zippy-my-block" });

// CORRECT
const blockProps = useBlockProps();

// OK — adding extra classes is fine
const blockProps = useBlockProps({ className: "my-extra-class" });
```

### 4. Loading skeleton — Must exclude editor
If your block has a loading/shimmer state (`:not(.is-loaded)::after`), `view.js` only runs on the frontend so `.is-loaded` never gets added in the editor.

Fix in `editor.scss` with doubled class selector for higher specificity than `style-index.css`:

```scss
// editor.scss
.wp-block-ai-zippy-{name}.wp-block-ai-zippy-{name}:not(.is-loaded)::after {
    content: none !important;
    display: none !important;
}

.wp-block-ai-zippy-{name}.wp-block-ai-zippy-{name}:not(.is-loaded) .my__content {
    visibility: visible !important;
    height: auto !important;
    overflow: visible !important;
}
```

### 5. block.json — Asset paths
Use `file:./` relative paths. Never use absolute or parent directory paths.

```json
{
    "editorScript": "file:./index.js",
    "editorStyle": "file:./index.css",
    "style": "file:./style-index.css",
    "render": "file:./render.php"
}
```

### 6. Block registration
Blocks are auto-registered in `ThemeSetup::registerBlocks()` by scanning `assets/blocks/*/block.json`. No manual registration needed.

### 7. Block category
All custom blocks use category `"ai-zippy"`. This is registered in `ThemeSetup::blockCategories()`.

### 8. Import extensions
Because of `"type": "module"` in package.json, block imports need `.js` extension:

```js
// WRONG
import Edit from "./edit";

// CORRECT
import Edit from "./edit.js";
```
