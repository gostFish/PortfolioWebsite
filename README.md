# Portfolio Website

This repository is a deployed WordPress site for Malcolm Grech's portfolio. Treat it as a live site export first and a source repository second: many files are WordPress core, third-party plugin code, generated assets, or host-specific configuration.

## Start Here

- Main site/theme edits: `wp-content/themes/hostinger-ai-theme/`
- Site-wide custom logic: `wp-content/mu-plugins/`
- Plugin behavior: `wp-content/plugins/`
- User assets: `wp-content/uploads/`
- Project notes: `docs/`

## Do Not Remove Casually

- `.private/config.json` is read by the Hostinger AI theme.
- `.htaccess` controls WordPress routing and LiteSpeed cache behavior.
- `wp-config.php` contains the WordPress environment configuration.
- `wp-content/plugins/hostinger*` contains Hostinger integration/plugin code.
- `wp-content/themes/hostinger-ai-theme/vendor/` is required by the active theme's Composer autoloader.
- WordPress core files in the repo root, `wp-admin/`, and `wp-includes/` are part of the deployed install.

## Notes

The root `index.html` is a custom static landing page, but `.htaccess` currently prefers `index.php`, so normal traffic enters WordPress first. Keep it documented rather than deleting it until the intended deployment path is clear.
