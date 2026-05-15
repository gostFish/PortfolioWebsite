# Agent Guidance

This repository is a WordPress site. Keep edits small, targeted, and limited to the site-specific code paths unless the user explicitly asks for core changes.

## Prefer These Paths

- `wp-content/themes/hostinger-ai-theme/` for theme changes
- `wp-content/plugins/` for plugin behavior
- `wp-content/mu-plugins/` for site-wide custom logic
- `wp-content/uploads/` only for user assets, not code

## Avoid Unless Required

- `wp-admin/`
- `wp-includes/`
- Other WordPress core files in the repo root

## Workflow

- Inspect only the files needed for the task.
- Use `rg` for search and prefer the narrowest possible file reads.
- Make the smallest patch that solves the request.
- Do not overwrite unrelated user changes.
- If a change spans multiple site areas, note the exact files touched in the final response.

## Token-Saving Notes

- Reuse existing theme/plugin structure instead of introducing new abstractions.
- Favor direct edits over broad refactors.
- When possible, update one file at a time and verify the impact before expanding scope.
- Keep Codex responses brief by default.
- Expand with details only when the user asks for them or when they are necessary to avoid mistakes.
- Reduce input token usage by reading the smallest useful set of files and avoiding redundant context loading.
