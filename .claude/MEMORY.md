# MEMORY.md — AI Agent Persistent Notes

This file stores team-specific preferences that override default AI behavior.
AI agents must read this file at the start of every session.

---

## Active Preferences

- **[Skip A3 verbose reports]** — Don't run QA agent with full audit output. Only flag actual errors. Save tokens.
- **[Don't run build]** — User has `npm run dev` running. Skip all `npm run build` and `npm run build:blocks` commands.
- **[Atomic plan approval]** — Always present the A4 plan and wait for developer approval before writing code.
- **[Commit message: output only]** — Never run `git commit` automatically. Output the message, let developer confirm.

---

## Project-Specific Shortcuts

- Blocks auto-register — no need to add to any config file
- Nonce is global — never add a second nonce in page-specific enqueue classes
- ViteAssets::enqueue() only — never use wp_enqueue_script() for Vite assets
- Import .js extensions — always include in block imports

---

## How to Add a Memory

When the developer says "remember that..." or "always do...", add it here:

```
- **[Short label]** — Description of preference
```

Example:
```
- **[No semicolons in SCSS]** — Team uses Prettier with no-semicolons rule
```
