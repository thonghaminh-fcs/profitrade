# Update new changes 💬

## Summary
<!-- One paragraph: what this PR does and why. Focus on outcome, not implementation. -->

## Changes
<!-- Bullet list of what changed -->
- 

## How to Test
<!-- Step-by-step. Include URLs, test data, expected results. -->
1. 
2. 
3. 

## Screenshots
<!-- Before / After for any UI change. Delete if no visual changes. -->

---

### Checklist

**Code quality**
- [ ] All variables, files and function names are in English
- [ ] No `console.log` or debug variables left
- [ ] No commented-out dead code
- [ ] No hardcoded colors — uses `var(--wp--preset--color--*)`
- [ ] No logic in `functions.php` — all PHP in `inc/` PSR-4 classes
- [ ] No jQuery used

**WordPress / WooCommerce**
- [ ] `render.php` has `function_exists` guard (blocks only)
- [ ] `save.js` returns `null` (blocks only)
- [ ] Assets enqueued via `ViteAssets::enqueue()` — not `wp_enqueue_script` directly
- [ ] No duplicate WC Store API nonce

**QA**
- [ ] Page has an `h1` tag
- [ ] No conflicts with existing styles or scripts
- [ ] Tested on desktop: 1920×1080
- [ ] Tested on desktop: 1440×760
- [ ] Tested on mobile: 375×667

**Git**
- [ ] Commits follow conventional format (`feat(scope): subject`)
- [ ] No build artifacts committed (`assets/dist/`, `assets/blocks/`)
- [ ] Branch created from `develop` (not `main`)

## Related Ticket
Closes AZ-
