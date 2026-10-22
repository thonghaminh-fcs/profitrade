# Workflow: Commit Standard

## Format — Conventional Commits

```
<type>(<scope>): <subject>

[optional body — explain WHY, not WHAT]

[optional footer — breaking changes, closes tickets]
```

### Rules for subject line
- Lowercase after the colon
- No period at the end
- Max 72 characters
- Imperative mood: "add", "fix", "update" — not "added", "fixed"

---

## Types

| Type | When to use |
|------|-------------|
| `feat` | New feature or capability |
| `fix` | Bug fix |
| `style` | CSS/SCSS changes, no logic change |
| `refactor` | Code restructure, no behavior change |
| `perf` | Performance improvement |
| `docs` | Documentation only |
| `chore` | Build tools, dependencies, config |
| `test` | Adding or fixing tests |
| `ci` | CI/CD pipeline changes |
| `revert` | Reverting a previous commit |

---

## Scopes

Use the domain that changed:

| Scope | Files |
|-------|-------|
| `blocks` | `src/blocks/` |
| `php` | `inc/` |
| `scss` | `src/scss/` |
| `checkout` | Checkout-related (any layer) |
| `cart` | Cart-related (any layer) |
| `shop` | Shop/archive page |
| `api` | REST API endpoints |
| `templates` | `templates/`, `parts/` |
| `build` | `vite.config.js`, `package.json`, `webpack` |
| `wc` | WooCommerce overrides |

---

## Examples

```bash
# New block
feat(blocks): add product-showcase block with WC query

# Bug fix with ticket reference
fix(checkout): resolve nonce duplication on checkout page

Closes AZ-55

# SCSS update
style(scss): add mobile breakpoints to product-filter

# Refactor with breaking change
refactor(php): rename ShopAssets methods to match PSR conventions

BREAKING CHANGE: ShopAssets::loadStyles() renamed to ShopAssets::enqueue()

# WC override
fix(wc): preserve wpautop whitespace collapse in thankyou template

# Chore
chore(build): upgrade @wordpress/scripts to 28.x

# Multiple related files (use body to explain)
feat(cart): implement upsell product carousel

- Add CartUpsell block with WC product query
- Add REST endpoint for upsell product data
- Add zc__upsell SCSS component
- Lazy load images with IntersectionObserver

Closes AZ-42
```

---

## AI Commit Generation — Instructions for AI Agents

When asked to generate a commit message, the AI must:

### 1. Analyze the diff
Run mentally (or literally): `git diff --staged`

Identify:
- Which files changed
- What type of change (new file, edit, delete)
- What domain (block, PHP, SCSS, template)
- What problem was solved

### 2. Output the commit message

Format:
```
<type>(<scope>): <subject>

<body — only if non-obvious>

<footer — Closes AZ-XX if known>
```

### 3. Rules for AI-generated commits

```
✅ One concern per commit — if the diff touches 3 unrelated things, suggest 3 commits
✅ Subject describes what changed, body explains why
✅ Never mention "I" or "we" in commit messages
✅ Never say "Updated X to Y" — say "refactor(scope): rename X to Y"
✅ Include ticket number in footer if working on a ticket
❌ Never auto-run git commit — present message for developer approval
❌ Never write vague subjects: "fix bug", "update file", "changes"
```

### 4. Multi-commit suggestions

If the staged diff covers multiple concerns, the AI outputs:

```markdown
## Suggested Commits (in order)

**Commit 1:**
```
feat(php): add UpsellApi REST endpoint
```

**Commit 2:**
```
feat(blocks): add cart-upsell block structure and edit UI
```

**Commit 3:**
```
style(scss): add cart-upsell responsive layout styles
```

Stage each group separately with `git add -p` before committing.
```

---

## .gitignore — Never Commit These

```
node_modules/
vendor/
assets/dist/          ← built by Vite (add to gitignore unless deploying via git)
assets/blocks/        ← built by wp-scripts (same as above)
*.log
.env
.DS_Store
```

> **Team policy**: Build artifacts (`assets/dist/`, `assets/blocks/`) are committed only on release branches. Feature branches never include build output.
