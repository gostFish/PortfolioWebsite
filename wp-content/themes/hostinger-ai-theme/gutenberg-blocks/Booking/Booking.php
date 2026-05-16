<?php

namespace Hostinger\AiTheme\GutenbergBlocks\Booking;

defined( 'ABSPATH' ) || exit;

class Booking {
    private const MAX_FIELDS        = 30;
    private const MAX_FIELD_LENGTH  = 2000;
    private const RATE_LIMIT_MAX    = 5;
    private const RATE_LIMIT_WINDOW = 900;

    public function __construct() {
        add_action( 'init', [ $this, 'register_block' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        add_action( 'admin_menu', [ $this, 'maybe_add_admin_menu' ] );
    }

    public function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type( __DIR__ . '/block.json', [
            'render_callback' => [ $this, 'render_block' ],
        ] );
    }

    public function register_rest_routes() {
        register_rest_route( 'hostinger/v1', '/booking-submissions', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_booking_submission' ],
            'permission_callback' => '__return_true',
        ] );
    }

    public function handle_booking_submission( $request ) {
        if ( ! $this->is_post_request() ) {
            return new \WP_Error( 'invalid_request', __( 'Invalid request.', 'hostinger-ai-theme' ), [ 'status' => 405 ] );
        }

        $data = $request->get_json_params();

        if ( empty( $data ) || ! is_array( $data ) ) {
            return new \WP_Error( 'invalid_data', __( 'Invalid data provided', 'hostinger-ai-theme' ), [ 'status' => 400 ] );
        }

        $nonce = isset( $data['booking_nonce'] ) && is_scalar( $data['booking_nonce'] ) ? sanitize_text_field( (string) $data['booking_nonce'] ) : '';

        if ( ! wp_verify_nonce( $nonce, 'hostinger_booking_submission' ) ) {
            return new \WP_Error( 'invalid_nonce', __( 'Invalid request.', 'hostinger-ai-theme' ), [ 'status' => 403 ] );
        }

        if ( ! empty( $data['website_url'] ) ) {
            return new \WP_REST_Response( [
                'message' => __( 'Booking submitted successfully', 'hostinger-ai-theme' ),
                'status'  => 'success',
            ], 200 );
        }

        unset( $data['booking_nonce'], $data['website_url'] );

        $data = $this->sanitize_submission_data( $data );

        if ( empty( $data ) ) {
            return new \WP_Error( 'invalid_data', __( 'Invalid data provided', 'hostinger-ai-theme' ), [ 'status' => 400 ] );
        }

        if ( array_key_exists( 'privacy_policy', $data ) && $data['privacy_policy'] !== 'Yes' ) {
            return new \WP_Error( 'privacy_required', __( 'Please agree with privacy policy.', 'hostinger-ai-theme' ), [ 'status' => 400 ] );
        }

        if ( $this->has_invalid_email_field( $data ) ) {
            return new \WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'hostinger-ai-theme' ), [ 'status' => 400 ] );
        }

        $email = $this->get_submission_email( $data );

        if ( $this->is_rate_limited( $email ) ) {
            return new \WP_Error( 'rate_limited', __( 'Please wait a few minutes before trying again.', 'hostinger-ai-theme' ), [ 'status' => 429 ] );
        }

        $submissions   = get_option( 'hostinger_booking_submissions', [] );
        $submissions   = is_array( $submissions ) ? $submissions : [];
        $submissions[] = [
            'data'      => $data,
            'timestamp' => current_time( 'mysql' ),
        ];

        update_option( 'hostinger_booking_submissions', $submissions, false );

        $this->send_admin_notification_email( $data );

        return new \WP_REST_Response( [
            'message' => __( 'Booking submitted successfully', 'hostinger-ai-theme' ),
            'status'  => 'success',
        ], 200 );
    }

    public function send_admin_notification_email( array $data ): void {
        $admin_email = sanitize_email( get_option( 'admin_email' ) );

        if ( ! is_email( $admin_email ) ) {
            return;
        }

        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

        $subject = sprintf(
            __( '[%s] New Booking Received', 'hostinger-ai-theme' ),
            $site_name
        );

        $message = __( "A new booking has been submitted on your website.\n\n", 'hostinger-ai-theme' );
        $message .= __( "Booking Details:\n", 'hostinger-ai-theme' );

        foreach ( $data as $key => $value ) {
            $message .= sprintf(
                            __( '%1$s: %2$s', 'hostinger-ai-theme' ),
                            ucwords( str_replace( '_', ' ', sanitize_key( $key ) ) ),
                            $value
                        ) . "\n";
        }

        $message .= __( "\n\nYou can view all bookings in your WordPress admin dashboard under the 'Bookings' menu.", 'hostinger-ai-theme' );

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

        wp_mail( $admin_email, $subject, $message, $headers );
    }

    public function maybe_add_admin_menu() {
        $submissions = get_option( 'hostinger_booking_submissions', [] );

        if ( ! empty( $submissions ) ) {
            add_menu_page(
                'Bookings',
                'Bookings',
                'manage_options',
                'hostinger-bookings',
                [ $this, 'render_admin_page' ],
                'dashicons-calendar-alt',
                30
            );
        }
    }

    public function render_admin_page() {
        $submissions = get_option( 'hostinger_booking_submissions', [] ); ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Bookings', 'hostinger-ai-theme' ); ?></h1>
            <?php
            if ( empty( $submissions ) ) : ?>
                <p><?php esc_html_e( 'No bookings yet.', 'hostinger-ai-theme' ); ?></p>
            <?php
            else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'hostinger-ai-theme' ); ?></th>
                        <?php
                        // Get all unique field names from submissions
                        $fields = [];
                        foreach ( $submissions as $submission ) {
                            foreach ( $submission['data'] as $field => $value ) {
                                $fields[$field] = true;
                            }
                        }
                        foreach ( $fields as $field => $value ) :
                            ?>
                            <th><?php echo esc_html( ucfirst( $field ) ); ?></th>
                        <?php
                        endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ( $submissions as $submission ) : ?>
                        <tr>
                            <td><?php
                                echo esc_html( $submission['timestamp'] ); ?></td>
                            <?php
                            foreach ( $fields as $field => $value ) : ?>
                                <td><?php
                                    echo esc_html( $submission['data'][$field] ?? '' ); ?></td>
                            <?php
                            endforeach; ?>
                        </tr>
                    <?php
                    endforeach; ?>
                    </tbody>
                </table>
            <?php
            endif; ?>
        </div>
        <?php
    }

    public function render_block( $attributes, $content, $block ) {
        ob_start();
        require __DIR__ . '/render.php';

        return ob_get_clean();
    }

    private function sanitize_submission_data( array $data ): array {
        $clean = [];
        $count = 0;

        foreach ( $data as $key => $value ) {
            if ( $count >= self::MAX_FIELDS || ! is_scalar( $value ) ) {
                continue;
            }

            $field_key = sanitize_key( (string) $key );

            if ( $field_key === '' ) {
                continue;
            }

            if ( is_bool( $value ) ) {
                $clean[ $field_key ] = $value ? 'Yes' : 'No';
            } else {
                $clean[ $field_key ] = $this->sanitize_limited_textarea( (string) $value, self::MAX_FIELD_LENGTH );
            }

            $count++;
        }

        return $clean;
    }

    private function sanitize_limited_textarea( string $value, int $max_length ): string {
        $value = trim( sanitize_textarea_field( $value ) );

        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $value, 0, $max_length );
        }

        return substr( $value, 0, $max_length );
    }

    private function get_submission_email( array $data ): string {
        foreach ( $data as $key => $value ) {
            if ( str_contains( (string) $key, 'email' ) && is_email( $value ) ) {
                return sanitize_email( $value );
            }
        }

        return '';
    }

    private function has_invalid_email_field( array $data ): bool {
        foreach ( $data as $key => $value ) {
            if ( str_contains( (string) $key, 'email' ) && $value !== '' && ! is_email( $value ) ) {
                return true;
            }
        }

        return false;
    }

    private function is_post_request(): bool {
        return isset( $_SERVER['REQUEST_METHOD'] ) && strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) === 'POST';
    }

    private function is_rate_limited( string $email ): bool {
        $key   = 'hst_booking_' . wp_hash( $this->get_request_ip() . '|' . strtolower( $email ) );
        $count = (int) get_transient( $key );

        if ( $count >= self::RATE_LIMIT_MAX ) {
            return true;
        }

        set_transient( $key, $count + 1, self::RATE_LIMIT_WINDOW );

        return false;
    }

    private function get_request_ip(): string {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : 'unknown';
    }
}
