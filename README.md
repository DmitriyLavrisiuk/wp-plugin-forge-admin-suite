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

## Versioning workflow

1. Create a release branch (e.g. `release/v0.1.1`).
2. Open a PR, review, and merge.
3. Tag the merge commit (e.g. `v0.1.1`).
4. Delete the release branch after the tag is pushed.

## Troubleshooting

- If Vite is not running, the plugin falls back to the production build in `ui/dist`.
- REST calls require a valid nonce (`X-WP-Nonce`) and `manage_options` capability.
