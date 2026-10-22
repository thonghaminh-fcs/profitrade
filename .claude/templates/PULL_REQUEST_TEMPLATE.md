## Summary
<!-- One paragraph: what this PR does and why. Focus on user/product impact, not implementation. -->

## Changes

### Backend (PHP)
<!-- List PHP class changes. Remove section if none. -->
- 

### Blocks / Frontend
<!-- List block, React, or JS changes. Remove section if none. -->
- 

### Styles (SCSS)
<!-- List SCSS/CSS changes. Remove section if none. -->
- 

### Templates
<!-- List FSE template or WC override changes. Remove section if none. -->
- 

## How to Test
<!-- Step-by-step instructions. Be specific — include URLs, test data, expected results. -->
1. 
2. 
3. 

## Screenshots
<!-- Before / After for any UI change. Delete this section if no visual changes. -->

| Before | After |
|--------|-------|
|        |       |

## Checklist
- [ ] No hardcoded colors — uses `var(--wp--preset--color--*)`
- [ ] PHP classes under `AiZippy\` namespace in `inc/`
- [ ] No logic in `functions.php`
- [ ] `render.php` has `function_exists` guard (blocks only)
- [ ] `save.js` returns `null` (blocks only)
- [ ] Assets enqueued via `ViteAssets::enqueue()` not `wp_enqueue_script`
- [ ] No duplicate WC Store API nonce
- [ ] Commits are atomic and use conventional format
- [ ] No build artifacts committed (`assets/dist/`, `assets/blocks/`)
- [ ] Tested on mobile (375px minimum)
- [ ] No console errors

## Related Ticket
Closes AZ-
