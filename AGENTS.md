# AGENTS.md — Forge Admin Suite (WordPress plugin)

## Goal
Multipurpose WordPress plugin with a large admin SPA:
Vite + React + TypeScript + Tailwind + shadcn/ui.

## Structure
- `forge-admin-suite.php` — plugin entry
- `includes/` — PHP (admin menu, assets, REST)
- `ui/` — Vite React app
- `ui/dist/` — build output (manifest)

## Dev (MAMP)
- Run `pnpm -C ui dev` (Vite: http://localhost:5173)
- WP admin page loads scripts from Vite (HMR).

## Prod
- Run `pnpm -C ui build`
- WP enqueues assets from `ui/dist/.vite/manifest.json`

## Security
- Admin access: `manage_options`
- REST:
  - namespace `forge-admin-suite/v1`
  - `permission_callback`: capability + REST nonce
- Sanitize inputs, escape outputs.

## Versioning
- Tag each build: v0.1.0, v0.1.1, ...
- Update CHANGELOG.md for each tag.

## Commands
- UI: `pnpm -C ui lint`, `pnpm -C ui build`
