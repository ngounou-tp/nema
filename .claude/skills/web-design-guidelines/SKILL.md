---
name: web-design-guidelines
description: Review UI code for Web Interface Guidelines compliance (accessibility, focus states, forms, animation, typography, performance, dark mode, i18n). Use when asked to "review my UI", "check accessibility", "audit design", "review UX", or "check my site against best practices", and before shipping any new NEMA page.
metadata:
  author: vercel (adapted for NEMA — Vue 3 / Inertia)
  version: "1.0.0"
  license: MIT
  source: https://github.com/vercel-labs/agent-skills/tree/063bee94c3f4df8453406c830b0a7df0f2860278/skills/web-design-guidelines
  argument-hint: <file-or-pattern>
---

# Web Interface Guidelines

Review files for compliance with the Web Interface Guidelines.

## How It Works

1. Read the rules in `references/guidelines.md` (a pinned snapshot of
   https://raw.githubusercontent.com/vercel-labs/web-interface-guidelines/main/command.md).
   Only re-fetch the upstream file if the user explicitly asks for the latest rules.
2. Read the specified files (or ask the user which files / pattern to review).
3. Check them against every rule.
4. Output findings in the terse `file:line` format described in the guidelines.

## Translating the rules to this stack

The upstream rules use React vocabulary. In this project (Vue 3 + Inertia + Tailwind 4 + shadcn-vue/reka-ui) read them as:

- `onClick` / `onKeyDown` → `@click` / `@keydown`
- `<Link>` → Inertia's `<Link>` from `@inertiajs/vue3`
- `className` → `class`
- `dangerouslySetInnerHTML` → `v-html` (forbidden on AI output in NEMA; sanitize with DOMPurify if Markdown must be rendered)
- Prefer the existing `resources/js/components/ui/*` primitives (reka-ui based, already accessible) over hand-rolled widgets.
- The UI language is French: check `lang="fr"`, French number/currency formatting (`Intl.NumberFormat('fr-CM', …)` for FCFA, kg, ha) and French copy.

## Usage

When a user provides a file or pattern argument:
1. Read `references/guidelines.md`
2. Read the specified files
3. Apply all rules
4. Output findings using the format specified in the guidelines
