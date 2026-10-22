# AI Prompt: Generate Commit Message

Use this prompt with any AI agent (Claude, Codex, Cursor) to generate a commit message.

---

## Prompt to paste into AI

```
You are a senior developer on the ai-zippy WooCommerce theme.
Analyze the following git diff and generate a conventional commit message.

Rules:
- Format: <type>(<scope>): <subject>
- Types: feat, fix, style, refactor, perf, docs, chore, test, ci
- Scopes: blocks, php, scss, checkout, cart, shop, api, templates, build, wc
- Subject: lowercase, imperative, no period, max 72 chars
- Body: only if non-obvious — explain WHY, not WHAT
- Footer: include "Closes AZ-{id}" if working on a ticket
- If diff touches multiple concerns: suggest multiple commits with staging instructions
- Never run git commit automatically — output the message only

Git diff:
---
{PASTE GIT DIFF HERE}
---

Ticket (optional): AZ-{id}
```

---

## Quick usage

1. Run `git diff --staged` in terminal
2. Copy the output
3. Paste into the prompt above (replace `{PASTE GIT DIFF HERE}`)
4. AI returns the commit message
5. You run: `git commit -m "..."` or copy into your git client

---

## Example output from AI

**Input diff:** New file `inc/Api/UpsellApi.php` added with REST endpoint registration

**AI output:**
```
feat(api): add UpsellApi REST endpoint for cart upsell products

Registers GET /wp-json/ai-zippy/v1/upsell-products with WC product
query filtered by current cart contents. Returns title, price, and
thumbnail URL for display in the cart upsell block.

Closes AZ-42
```

---

## Shortcut — Claude Users

Just tell Claude:
> "Generate a commit message for my staged changes. Ticket is AZ-42."

Claude will ask you to paste the diff, then return the message.
