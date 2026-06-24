# Portfolio Website

This repository is the source of truth for Malcolm Grech's portfolio site. The public site is now static-first, with `index.html` as the primary entry point; many files are still WordPress-era leftovers, generated assets, or host-specific configuration.

## Start Here

- Main site edits: `index.html` and supporting assets
- Legacy WordPress-era files: `wp-content/themes/hostinger-ai-theme/`
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

The root `index.html` is the public homepage. WordPress files remain in the repo for backward compatibility and reference, but they should no longer control normal traffic.
