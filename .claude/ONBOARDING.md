# Team Onboarding — AI-Assisted Development

Welcome to the ai-zippy team. This guide gets you up and running in 15 minutes.

---

## 1. Setup Your AI Agent

### Claude (recommended)
1. Open a new conversation
2. Upload all files in `.claude/` as context, OR paste the contents of `CLAUDE.md`
3. Tell Claude: _"You are working on the ai-zippy WooCommerce theme. Read CLAUDE.md and all rule files."_
4. Claude will confirm it has read the rules — then you're ready

### Cursor / Windsurf
- The `.claude/AGENTS.md` file is auto-loaded as project context
- Rules in `.claude/rules/` are available via `@rules/` mentions

### GitHub Copilot / Codex
- Paste the contents of `.claude/AGENTS.md` into the system prompt
- Reference specific rule files as needed

---

## 2. Git Setup

```bash
# Clone the repo
git clone git@github.com:your-org/ai-zippy.git
cd ai-zippy

# Install dependencies
npm install
composer install

# Set commit message template
git config commit.template .claude/templates/commit-template.txt

# Start dev server (keep this running)
npm run dev
```

---

## 3. Your First Feature

```bash
# 1. Create branch from develop
git checkout develop && git pull
git checkout -b feature/AZ-{your-ticket}-{slug}

# 2. Tell AI to plan it
# "Plan the implementation for AZ-{id}: {description}"

# 3. AI presents plan → you approve → AI codes

# 4. After each unit of work:
# "Generate a commit message for my staged changes. Ticket AZ-{id}."
# → Review message → git commit

# 5. When done:
# "Generate a PR description. Here are my commits: [git log --oneline origin/develop..HEAD]"
# → Copy to GitHub PR

# 6. Push and open PR
git push -u origin HEAD
```

---

## 4. Key Rules to Know

| Rule | Where defined |
|------|--------------|
| No jQuery | `rules/tech-stack.md` |
| PHP in PSR-4 classes only | `rules/php-classes.md` |
| No hardcoded colors | `rules/scss-css.md` |
| Block file structure | `rules/blocks.md` |
| Branch naming | `rules/git-workflow.md` |
| Commit format | `workflows/commit-standard.md` |

---

## 5. Ask the AI

The AI knows all project rules. Use it freely:

```
"How do I add a new Gutenberg block?"
"Show me the pattern for a new PHP class in AiZippy\Shop\"
"What CSS prefix should I use for the new wishlist feature?"
"Generate a commit message for [paste diff]"
"Generate a PR description for this branch [paste git log]"
```

---

## 6. Checklist — Before Every PR

- [ ] Branch from `develop` (not `main`)
- [ ] Conventional commits throughout
- [ ] No hardcoded colors
- [ ] PHP in correct namespace
- [ ] Tested on mobile
- [ ] AI-generated PR description filled out
- [ ] At least 1 reviewer assigned
