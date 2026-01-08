=== Forge Admin Suite ===
Contributors: (placeholder)
Requires at least: 6.2
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 0.1.6

Forge Admin Suite provides a modern admin SPA experience inside WordPress.

== Description ==
Forge Admin Suite is a WordPress admin plugin that loads a React-based single-page app with a secure REST API.

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/forge-admin-suite/`.
2. Activate the plugin through the Plugins menu in WordPress.
3. Open the **Forge Suite** menu in the WordPress admin.

== Development ==
1. Run `pnpm -C ui install`.
2. Run `pnpm -C ui dev` to start Vite at `http://localhost:5173`.
3. Open **Forge Suite** in the WordPress admin to load the dev assets.

== Changelog ==
= 0.1.6 =
* Add frontend asset setting with REST persistence and Vite frontend entry.

= 0.1.4 =
* Add settings MVP with REST endpoints and UI form, plus version bump.

= 0.1.3 =
* Sync version across plugin, REST, and UI with improved cache busting.

= 0.1.1 =
* Add repo hygiene files, CI workflow, and plugin metadata updates.

= 0.1.0 =
* Initial plugin scaffold with admin SPA and secure REST ping.
