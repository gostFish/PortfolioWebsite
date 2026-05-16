<?php
/**
 * Site-level security hardening that avoids changing the public user experience.
 */

defined( 'ABSPATH' ) || exit;

// Blocks WordPress admin's built-in file editor only; filesystem, Git, SFTP, and Codex edits still work.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}

add_filter( 'automatic_updater_disabled', '__return_false', 100 );
add_filter( 'allow_minor_auto_core_updates', '__return_true', 100 );
add_filter( 'allow_major_auto_core_updates', '__return_false', 100 );
add_filter( 'allow_dev_auto_core_updates', '__return_false', 100 );

add_action(
    'send_headers',
    static function (): void {
        if ( headers_sent() ) {
            return;
        }

        header( 'X-Content-Type-Options: nosniff' );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'Referrer-Policy: strict-origin-when-cross-origin' );
        header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );

        if ( is_ssl() ) {
            header( 'Strict-Transport-Security: max-age=31536000' );
        }
    }
);

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter(
    'wp_headers',
    static function ( array $headers ): array {
        unset( $headers['X-Pingback'] );

        return $headers;
    }
);

add_filter(
    'login_errors',
    static function (): string {
        return __( 'Invalid login details.', 'hostinger-ai-theme' );
    }
);

add_filter( 'the_generator', '__return_empty_string' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

add_action(
    'template_redirect',
    static function (): void {
        if ( is_admin() || ! isset( $_GET['author'] ) ) {
            return;
        }

        $author = wp_unslash( $_GET['author'] );

        if ( is_scalar( $author ) && preg_match( '/^\d+$/', (string) $author ) ) {
            wp_safe_redirect( home_url( '/' ), 301 );
            exit;
        }
    },
    0
);

add_filter(
    'rest_endpoints',
    static function ( array $endpoints ): array {
        if ( is_user_logged_in() ) {
            return $endpoints;
        }

        unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)' ] );

        return $endpoints;
    }
);
