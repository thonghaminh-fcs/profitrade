# AI Prompt: Generate Pull Request Description

Use this prompt to auto-generate a complete PR description from your branch commits.

---

## Prompt to paste into AI

```
You are a senior developer on the ai-zippy WooCommerce theme.
Generate a GitHub pull request description for this branch.

Project context:
- WooCommerce FSE block theme
- PHP in AiZippy\ PSR-4 namespace
- Blocks in src/blocks/, React apps in src/js/, SCSS in src/scss/
- Vite builds to assets/dist/, wp-scripts builds to assets/blocks/

Commit log (from: git log --oneline origin/develop..HEAD):
---
{PASTE COMMIT LOG HERE}
---

Ticket number: AZ-{id}
Short description of what this feature/fix does: {ONE SENTENCE}

Output the PR description using this exact template:

## Summary
[One paragraph: user-visible outcome, not technical details]

## Changes

### Backend (PHP)
[List PHP changes, or remove section if none]

### Blocks / Frontend  
[List block/JS changes, or remove section if none]

### Styles (SCSS)
[List SCSS changes, or remove section if none]

### Templates
[List template changes, or remove section if none]

## How to Test
[Numbered steps a reviewer can follow to verify the feature works]

## Screenshots
[Note: add before/after screenshots if UI changed]

## Checklist
- [ ] No hardcoded colors (uses `var(--wp--preset--color--*)`)
- [ ] PHP classes in correct `AiZippy\` namespace
- [ ] `render.php` has `function_exists` guard (if applicable)
- [ ] `save.js` returns `null` (if applicable)
- [ ] No logic in `functions.php`
- [ ] Commits are atomic and follow conventional format
- [ ] No build artifacts in PR
- [ ] Tested on mobile (375px+)

## Related Ticket
Closes AZ-{id}
```

---

## Quick usage with GitHub CLI

```bash
# Get commit log
git log --oneline origin/develop..HEAD
```

Or paste the AI output directly into GitHub's PR creation UI.

---

## Shortcut — Claude Users

Just tell Claude:
> "Generate a PR description for this branch. Ticket AZ-42. 
> Here are my commits: [paste git log output]"

Claude will output the complete PR description ready to paste.
