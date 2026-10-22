# Rules: Git Workflow

## Branch Strategy

```
main                              ← Production. Protected. Never push directly.
  └── develop                     ← Integration. All features merge here first.
        ├── feature/AZ-42-cart-upsell     ← New features
        ├── fix/AZ-55-checkout-nonce      ← Bug fixes
        ├── hotfix/AZ-60-payment-crash    ← Critical prod bugs (branches from main)
        └── chore/AZ-61-upgrade-wc        ← Dependencies, config, non-code changes
```

### Branch naming convention

```
{type}/{ticket-id}-{short-slug}
```

| Type | Use for |
|------|---------|
| `feature/` | New functionality |
| `fix/` | Bug fixes on develop |
| `hotfix/` | Critical fix on production (branches from `main`) |
| `chore/` | Deps, config, CI, non-code |
| `docs/` | Documentation only |
| `refactor/` | Code restructure, no behavior change |

Examples:
```bash
git checkout -b feature/AZ-42-cart-upsell-block
git checkout -b fix/AZ-55-checkout-nonce-missing
git checkout -b chore/AZ-61-upgrade-woocommerce-8
```

---

## Commit Standard

See `workflows/commit-standard.md` for full details.

**Format:**
```
<type>(<scope>): <subject>
```

**Types:** `feat` `fix` `style` `refactor` `docs` `chore` `test` `perf` `ci`

**Scopes** (match project domains):
```
blocks    php    scss    checkout    cart    shop    api    templates    build
```

**Examples:**
```
feat(blocks): add product-showcase server-side block
fix(checkout): resolve nonce duplication in CartAssets
style(scss): extract _product-filter partial, add mobile breakpoints
refactor(php): move ShopAssets enqueue logic to dedicated method
chore(build): update @wordpress/scripts to 28.x
docs(readme): add block development quickstart
```

### Atomic commits — one change per commit

```bash
# WRONG — mixing concerns
git commit -m "add block, fix checkout bug, update styles"

# CORRECT — separate commits
git commit -m "feat(blocks): add brand-intro block skeleton"
git commit -m "fix(checkout): prevent duplicate nonce injection"
git commit -m "style(scss): add brand-intro responsive styles"
```

---

## Pull Request Flow

```
feature/AZ-42-cart-upsell  →  develop  →  main
                           PR #1        PR #2 (release)
```

1. Branch from `develop`
2. Commit with conventional commits
3. Push branch
4. Open PR → `develop`
5. AI generates PR description (see `workflows/pull-request.md`)
6. At least 1 peer review required
7. Squash merge into `develop`
8. Delete feature branch after merge

---

## Protected Branch Rules

| Branch | Push | Force push | Delete |
|--------|------|------------|--------|
| `main` | ❌ | ❌ | ❌ |
| `develop` | ❌ (PR only) | ❌ | ❌ |
| `feature/*` | ✅ | ✅ | ✅ after merge |

---

## Prerequisites — Install GitHub CLI

GitHub CLI (`gh`) is required for creating PRs from the terminal.

```bash
# macOS
brew install gh

# Windows
winget install --id GitHub.cli

# Ubuntu / Debian
sudo apt install gh

# Authenticate (once per machine)
gh auth login
# → Choose: GitHub.com → HTTPS → Login with browser
```

Verify: `gh --version`

---

## Git Commands Reference

```bash
# Start a new feature
git checkout develop && git pull
git checkout -b feature/AZ-{id}-{slug}

# Stage and commit
git add -p                              # Stage by hunk (preferred over git add .)
git commit                              # Opens editor with commit template

# Push
git push -u origin HEAD
```

---

## Commit Template Setup

Run once per machine to activate commit message template:

```bash
git config commit.template .claude/templates/commit-template.txt
```

---

## AI-Assisted Commits

AI agents automatically generate commit messages. The developer reviews and approves.

**Workflow:**
1. AI makes code changes
2. AI runs: `git diff --staged` to analyze changes
3. AI outputs a commit message following conventional commits
4. Developer approves or edits
5. Developer runs: `git commit -m "..."` or `git commit` with template

The AI MUST NOT run `git commit` autonomously without developer confirmation.
