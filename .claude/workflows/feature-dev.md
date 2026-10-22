# Workflow: Feature Development

## Overview

Every feature follows this exact sequence. No shortcuts.

```
1. Ticket exists in tracker
2. Branch from develop
3. A4 Plan → A2 Backend → A1 Frontend → A3 QA
4. AI generates commit messages
5. PR opened with AI-generated description
6. Peer review → Merge → Delete branch
```

---

## Step 1 — Ticket

Before touching any code, a ticket must exist with:
- **AZ-{id}** ticket number
- Clear acceptance criteria
- Design mockup or wireframe (if UI change)

---

## Step 2 — Branch

```bash
git checkout develop
git pull origin develop
git checkout -b {type}/AZ-{id}-{short-slug}
```

Branch type guide:
```
feature/   New capability
fix/       Bug fix
refactor/  Code cleanup (no behavior change)
chore/     Config, deps, tooling
docs/      Documentation only
```

Example:
```bash
git checkout -b feature/AZ-42-cart-upsell-block
```

---

## Step 3 — Agent Protocol

### A4 — Plan First

AI agent reads the relevant rule files and outputs a plan:

```markdown
## Plan — AZ-42 Cart Upsell Block

### Files to create
- [ ] src/blocks/cart-upsell/block.json
- [ ] src/blocks/cart-upsell/index.js
- [ ] src/blocks/cart-upsell/edit.js
- [ ] src/blocks/cart-upsell/save.js
- [ ] src/blocks/cart-upsell/render.php
- [ ] src/blocks/cart-upsell/style.scss
- [ ] src/blocks/cart-upsell/editor.scss

### Files to modify
- [ ] (none — block auto-registers)

### Backend needed?
- [ ] Yes: REST endpoint to fetch upsell products

### Estimated commits: 3
```

Developer approves plan before AI writes code.

### A2 — Backend (if needed)
- Create/modify PHP classes in `inc/`
- Add to `loader.php`
- Commit: `feat(php): add UpsellApi REST endpoint`

### A1 — Frontend
- Implement block/component/template
- Commit per logical unit (block, SCSS, JS)
- Commits: `feat(blocks): add cart-upsell block structure`

### A3 — QA (lightweight)
- Check block markup validity
- Check no hardcoded values
- Check `function_exists` guards in render.php
- Check `save.js` returns null
- NO build commands, NO verbose reports

---

## Step 4 — Commit Loop

After each logical unit of work:

1. `git diff --staged` — AI analyzes changes
2. AI proposes commit message (conventional format)
3. Developer reviews and runs commit
4. Repeat

See `workflows/commit-standard.md` for message format.

---

## Step 5 — Push & PR

```bash
git push -u origin HEAD
```

Then AI generates PR description. See `workflows/pull-request.md`.

---

## Step 6 — Review & Merge

- At least 1 peer review required
- Reviewer checks:
  - [ ] No hardcoded colors
  - [ ] PHP in correct namespace
  - [ ] Commits are atomic and conventional
  - [ ] No build artifacts committed (`assets/dist/`, `assets/blocks/` not in PR unless intentional)
- Squash merge into `develop`
- Delete branch after merge

---

## Common Mistakes to Avoid

```
❌ Branching from main instead of develop
❌ Committing build output (assets/dist/, node_modules/)
❌ One giant commit for entire feature
❌ Mixing refactor + feature in one commit
❌ Pushing directly to develop or main
❌ Forgetting to pull develop before branching
```
