# AGENTS.md — Universal AI Agent Instructions
# Compatible: OpenAI Codex, GitHub Copilot, Cursor, Windsurf, Aider, Claude

> This file is the universal entry point for any AI coding agent.
> It mirrors `.claude/CLAUDE.md` but uses agent-agnostic conventions.

---

## Mandatory Reading Order

Before writing a single line of code, the AI agent MUST read these files:

1. `.claude/rules/tech-stack.md`
2. `.claude/rules/php-classes.md` — if touching PHP
3. `.claude/rules/blocks.md` — if touching Gutenberg blocks
4. `.claude/rules/scss-css.md` — if touching SCSS/CSS
5. `.claude/rules/fse-templates.md` — if touching templates
6. `.claude/rules/git-workflow.md` — before any commit

---

## Project Identity

- **Theme**: `ai-zippy` — WooCommerce FSE block theme
- **Namespace**: `AiZippy\`
- **Block prefix**: `ai-zippy/`
- **CSS prefixes**: `az-` (theme), `zc-` (cart), `zk-` (checkout), `sf__` (shop filter)
- **Build**: Vite → `assets/dist/` | @wordpress/scripts → `assets/blocks/`

---

## Hard Constraints for All Agents

```yaml
no_jquery: true
no_logic_in_functions_php: true
no_hardcoded_colors: true
no_direct_wp_enqueue: true          # Use ViteAssets::enqueue()
no_jsx_in_save_js: true             # Always return null
no_duplicate_nonces: true
no_build_commands: true             # Dev server is running
php_namespace: "AiZippy\\"
php_standard: PSR-4
css_token_prefix: "var(--wp--preset--color--"
block_category: "ai-zippy"
language: english                   # All code/comments in English
```

---

## Agent Workflow

When given a task, agents must follow this process:

### Step 1 — Understand
- Read relevant rule files
- Identify all files to create/modify
- Do NOT start coding yet

### Step 2 — Plan
Output a plan with checkboxes. For example:
```
## Plan
- [ ] inc/Shop/RelatedProducts.php — new class
- [ ] src/blocks/related-products/ — new block
- [ ] src/scss/_related-products.scss — new partial
```

### Step 3 — Implement
Follow the plan. One task at a time.

### Step 4 — Commit
Generate a conventional commit message. See `workflows/commit-standard.md`.

---

## PHP Pattern — Every Class

```php
<?php
namespace AiZippy\{Domain};

defined('ABSPATH') || exit;

class {ClassName}
{
    public static function register(): void
    {
        add_action('hook_name', [self::class, 'method']);
    }

    public static function method(): void
    {
        // logic here
    }
}
```

---

## Block Pattern — File Checklist

Every block in `src/blocks/{name}/` needs ALL of these:
- `block.json` — metadata, attributes, supports
- `index.js` — registration (`import Edit from './edit.js'` — include `.js`!)
- `edit.js` — editor component
- `save.js` — always `return null`
- `render.php` — server-side HTML with `function_exists` guard
- `style.scss` — frontend + editor shared
- `editor.scss` — editor-only overrides

---

## SCSS Token Map

```scss
// Colors — NEVER hardcode
var(--wp--preset--color--primary)    // #1a1a1a
var(--wp--preset--color--accent)     // #c8a97e
var(--wp--preset--color--border)     // #e5e5e5

// Breakpoints — use mixins
@include from(md)   // min-width: 768px
@include until(lg)  // max-width: 1023px
```

---

## Git Commit Format

```
<type>(<scope>): <subject>

[optional body]

[optional footer]
```

Types: `feat` `fix` `style` `refactor` `docs` `chore` `test`

Example:
```
feat(blocks): add product-showcase block with lazy load

- Implement render.php with WC product query
- Add loading skeleton with editor override
- Register in ThemeSetup::registerBlocks()
```
