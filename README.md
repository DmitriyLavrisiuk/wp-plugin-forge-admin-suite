# Forge Admin Suite

WordPress plugin with a React admin SPA (Vite + React + TypeScript + Tailwind + shadcn/ui).

## Requirements

- WordPress 6.2+
- PHP 8.0+
- Node.js (LTS)
- pnpm

## Setup

Clone the repo into `wp-content/plugins/forge-admin-suite`.

## MAMP notes

- WordPress site can run at `http://localhost:8888`.
- Vite dev server runs at `http://localhost:5173`.

## Development

```
pnpm -C ui install
pnpm -C ui dev
```

Open WordPress admin and navigate to **Forge Suite**. The plugin loads assets from the Vite dev server with HMR.

## Production build

```
pnpm -C ui build
```

WordPress loads production assets from `ui/dist/.vite/manifest.json`.

## Release notes (v0.1.3)

- Version is synced across plugin, REST, and UI, and WP admin now shows the correct version.
- Production assets use file-based cache busting when available.

## Versioning workflow

1. Create a release branch (e.g. `release/v0.1.3`).
2. Open a PR, review, and merge.
3. Tag the merge commit (e.g. `v0.1.3`).
4. Delete the release branch after the tag is pushed.

## Versioning checklist

- Update `FORGE_ADMIN_SUITE_VERSION` and the plugin header in `forge-admin-suite.php`.
- Update `ui/package.json` version.
- Update `readme.txt` stable tag.
- Add a `CHANGELOG.md` entry.

## Troubleshooting

- If Vite is not running, the plugin falls back to the production build in `ui/dist`.
- REST calls require a valid nonce (`X-WP-Nonce`) and `manage_options` capability.

## UI architecture

- `ui/src/lib/appConfig.ts` exposes the WordPress-provided config and throws if missing.
- `ui/src/lib/apiClient.ts` wraps REST calls with nonce handling and normalized errors.
- `ui/src/components/common/` contains shared UI (loading, error, error boundary).
- New pages should live in `ui/src/pages/`, wired through `ui/src/App.tsx`.
- Toasts use `sonner` and are triggered via `toast.*` with the `Toaster` mounted in `App`.
