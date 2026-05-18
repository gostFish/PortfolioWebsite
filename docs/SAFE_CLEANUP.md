# Safe Cleanup Notes

This document tracks cleanup candidates without removing anything that could affect the current live site or Hostinger integration.

## Keep

- `.private/config.json` because the active theme reads it.
- `.htaccess` because it controls LiteSpeed and WordPress routing.
- `wp-config.php` because it controls the WordPress environment.
- `wp-content/themes/hostinger-ai-theme/` because it is the active theme.
- `wp-content/plugins/hostinger/`, `wp-content/plugins/hostinger-reach/`, and `wp-content/plugins/hostinger-easy-onboarding/` because they are Hostinger integrations.
- `wp-content/plugins/litespeed-cache/` because it owns the LiteSpeed cache behavior.
- `vendor/`, `dist/`, and `build/` directories inside active theme/plugin packages because this repo appears to be a deployed WordPress copy.

## Cleanup Candidates

- `default.php` appears to be a Hostinger placeholder page and is not referenced by the WordPress routing path.
- `wp-content/themes/hostinger-ai-theme/gutenberg-blocks/*/build/` is generated block output.
- `wp-content/themes/hostinger-ai-theme/assets/js/*.min.js` is compiled theme script output.
- `wp-content/plugins/hostinger/vue-frontend/dist/` is compiled Vue frontend output.
- `wp-content/plugins/hostinger-reach/frontend/dist/` is compiled plugin frontend output.
- `wp-content/plugins/hostinger-easy-onboarding/assets/js/*.min.js` and `wp-content/plugins/hostinger-easy-onboarding/assets/css/*.min.css` are compiled plugin assets.

## Applied Cleanup

- `.htaccess.bk`
- `readme.html`
- `license.txt`
- `wp-content/litespeed/qc.curr.vercheck`
- `wp-content/litespeed/qc.last.vercheck`
- `wp-content/litespeed/debug/index.php`

## Keep Until Verified

- `index.html` is custom portfolio content, but the current `.htaccess` entry order sends normal traffic to `index.php`. Keep it until the intended static-vs-WordPress deployment path is confirmed.
- `wp-content/uploads/portfolio/malcolm-grech-cv-picture.jpg` should only be removed after checking WordPress page content, templates, and live media references.

## Future Refactor Targets

- Move redirect-specific logic out of `wp-content/themes/hostinger-ai-theme/functions.php` into a focused include/class if more redirects are added.
- Split `wp-content/themes/hostinger-ai-theme/includes/Boot.php` only when a future feature changes the same area; avoid broad refactors while the site is stable.
- Add short notes near any future custom code explaining whether it is WordPress, Hostinger, Elementor, WooCommerce, or portfolio-specific.
