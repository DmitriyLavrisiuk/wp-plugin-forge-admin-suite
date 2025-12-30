# Forge Admin Suite

WordPress plugin with a React admin SPA (Vite + React + TypeScript + Tailwind + shadcn/ui).

## Requirements

- WordPress 6.2 +
- PHP 8.0+
- Node.js (LTS)
- pnpm

## Local development (MAMP + Vite)

1. Start the Vite dev server:

```
pnpm -C ui install
pnpm -C ui dev
```

2. In WordPress admin, open **Forge Suite**. The plugin will load assets from `http://localhost:5173` with HMR.

## Production build

```
pnpm -C ui install
pnpm -C ui build
```

The build outputs to `ui/dist` and the plugin reads `ui/dist/.vite/manifest.json` for production assets.

## Versioning & tags

Tag builds as `v0.1.0`, `v0.1.1`, etc., and update `CHANGELOG.md` for each release.

## REST API

- Namespace: `forge-admin-suite/v1`
- Route: `GET /ping`
- Requires `manage_options` capability and `X-WP-Nonce` (`wp_rest`) header.
