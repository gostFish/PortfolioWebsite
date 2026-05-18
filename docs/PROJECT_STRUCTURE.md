# Project Structure

## Runtime Entry

- `index.php` loads WordPress through `wp-blog-header.php`.
- `.htaccess` sets `DirectoryIndex index.php index.html` and rewrites non-file requests to WordPress.
- `wp-config.php` and `.private/config.json` provide environment/configuration values.

## Primary Modification Areas

- `wp-content/themes/hostinger-ai-theme/` is the active site theme.
- `wp-content/themes/hostinger-ai-theme/functions.php` defines theme constants, reads Hostinger config, loads Composer, boots the theme, and adds section redirect handling.
- `wp-content/themes/hostinger-ai-theme/includes/Boot.php` wires theme services, REST routes, builders, Elementor integrations, WooCommerce support, surveys, menus, analytics, redirects, SEO, and admin dependencies.
- `wp-content/plugins/hostinger/`, `wp-content/plugins/hostinger-reach/`, and `wp-content/plugins/hostinger-easy-onboarding/` are Hostinger plugin packages.
- `wp-content/mu-plugins/` contains site-wide must-use plugins.

## Expected Noise

- `vendor/`, `dist/`, `build/`, `*.min.js`, `*.asset.php`, translation files, and WordPress bundled themes are expected in this deployed WordPress repo.
- `wp-content/litespeed/` contains LiteSpeed cache/plugin artifacts.
- `wp-content/uploads/` is for user assets. Do not treat uploaded images as dead code without checking the live site or WordPress database references.

## Naming Observations

The project mixes WordPress conventions, Composer/PHP conventions, and frontend build conventions. Examples include `includes/Builder`, `gutenberg-blocks`, `blocks/woo`, `vue-frontend`, and `frontend/dist`. This is normal for the current stack, but future changes should prefer the naming style already used in the folder being edited.
