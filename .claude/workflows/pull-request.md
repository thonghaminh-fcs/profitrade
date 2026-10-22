# Workflow: Pull Request

## When to Open a PR

Open a PR when:
- The feature/fix is complete and passes A3 QA
- All commits follow the conventional commit standard
- Branch is pushed to remote

---

## PR Target Branch

```
feature/* → develop    (99% of PRs)
hotfix/*  → main       (then also → develop)
```

Never open a PR directly to `main` except hotfixes.

---

## AI-Generated PR Description

The AI agent generates the PR description automatically from:
1. The branch name and ticket number
2. `git log --oneline origin/develop..HEAD` (all commits in this branch)
3. The diff summary

### How to trigger AI PR generation

Tell the AI:
> "Generate a PR description for this branch"

The AI will analyze the commits and produce a complete PR description following the template below.

---

## PR Description Template

```markdown
## Summary
<!-- One paragraph: what this PR does and why -->

## Changes
<!-- Bullet list of what changed, grouped by layer -->

### Backend (PHP)
- 

### Blocks / Frontend
- 

### Styles (SCSS)
- 

### Templates
- 

## How to Test
<!-- Step-by-step QA instructions -->
1. 
2. 
3. 

## Screenshots
<!-- Before/After if UI changed. Delete if not applicable -->

## Checklist
- [ ] No hardcoded colors (uses `var(--wp--preset--color--*)`)
- [ ] PHP classes in correct `AiZippy\` namespace
- [ ] `render.php` has `function_exists` guard
- [ ] `save.js` returns `null`
- [ ] No logic in `functions.php`
- [ ] Commits are atomic and follow conventional format
- [ ] No build artifacts in PR (`assets/dist/`, `assets/blocks/`)
- [ ] Tested on mobile (375px+)

## Related Ticket
Closes AZ-{id}
```

---

## AI Instructions — Generating PR Descriptions

When generating a PR description, the AI must:

### 1. Parse the commit log

From commits like:
```
feat(php): add UpsellApi REST endpoint
feat(blocks): add cart-upsell block structure
style(scss): add cart-upsell responsive styles
fix(blocks): add function_exists guard to render.php
```

### 2. Group by layer
- PHP commits → Backend section
- Block/JS commits → Blocks/Frontend section
- SCSS commits → Styles section
- Template commits → Templates section

### 3. Write the summary
One clear paragraph describing the user-visible outcome, not the technical implementation.

```markdown
## Summary
Adds a product upsell carousel to the cart page. When customers view their cart, they see a curated list of related products fetched via a new REST endpoint. The carousel is rendered server-side via a custom Gutenberg block and lazy-loads product images for performance.
```

### 4. Write test steps
Concrete, reproducible steps a reviewer can follow:

```markdown
## How to Test
1. Add a product to cart and navigate to `/cart`
2. Scroll to the "You might also like" section
3. Verify 4 products appear with correct images and prices
4. Test on mobile (375px) — carousel should be horizontally scrollable
5. Verify no console errors
6. Remove the block from the cart page in the editor — verify no PHP errors
```

### 5. Auto-fill the checklist
Based on the diff, check items the AI can verify:
- If no hardcoded colors found → ✅
- If `save.js` contains `return null` → ✅
- If `function_exists` guard present → ✅
- Unknown items → leave unchecked for developer

---

## Merge Strategy

- **Squash merge** for feature branches → keeps `develop` history clean
- **Merge commit** for hotfix → preserves emergency fix history
- Delete branch after merge (GitHub: auto-delete enabled)

---

## Review Checklist for Reviewers

```
Code Quality
- [ ] Follows naming conventions (PSR-4, CSS prefixes)
- [ ] No business logic in wrong layer
- [ ] No commented-out code

WordPress / WC
- [ ] WC hooks preserved in template overrides
- [ ] No direct DB queries (use WP/WC APIs)
- [ ] No deprecated WC functions

Performance
- [ ] No N+1 queries in render.php
- [ ] Images lazy-loaded where applicable
- [ ] CSS conditionally loaded (not global for page-specific)

Security
- [ ] User input sanitized
- [ ] Nonce verified for form submissions
- [ ] Capabilities checked before privileged actions
```
