<?php

namespace Hostinger\AiTheme\GutenbergBlocks\ContactForm;

defined( 'ABSPATH' ) || exit;

class ContactForm {
    private const MAX_NAME_LENGTH    = 120;
    private const MAX_EMAIL_LENGTH   = 254;
    private const MAX_MESSAGE_LENGTH = 5000;
    private const RATE_LIMIT_MAX     = 5;
    private const RATE_LIMIT_WINDOW  = 900;

    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_assets_for_editor' ) );
        add_action( 'wp_ajax_submit_contactform', array( $this, 'handle_contact_submit' ) );
        add_action( 'wp_ajax_nopriv_submit_contactform', array( $this, 'handle_contact_submit' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
    }

    public function register_block(): void {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type(
            __DIR__ . '/block.json',
            array(
                'render_callback' => array( $this, 'render_block' ),
            )
        );
    }

    public function enqueue_assets_for_editor(): void {
        if ( ! is_admin() ) {
            return;
        }

        wp_register_script(
            'hostinger-contact-form-block-editor-script',
            get_template_directory_uri() . '/gutenberg-blocks/ContactForm/build/index.js',
            array(
                'wp-blocks',
                'wp-element',
                'wp-editor',
                'wp-i18n',
                'wp-components',
                'wp-server-side-render',
            ),
            wp_get_theme()->get( 'Version' ),
            true
        );

        wp_enqueue_script( 'hostinger-contact-form-block-editor-script' );

        wp_add_inline_script(
            'hostinger-contact-form-block-editor-script',
            'window.hst_contact_form_block_data = ' . wp_json_encode(
                array(
                    'user_id'            => get_current_user_id(),
                    'privacy_policy_url' => get_privacy_policy_url(),
                )
            ) . ';',
            'before'
        );
    }

    public function register_scripts(): void {
        $script_path = __DIR__ . '/build/view.js';

        wp_register_script(
            'hostinger-contact-form-block',
            get_template_directory_uri() . '/gutenberg-blocks/ContactForm/build/view.js',
            array( 'jquery' ),
            file_exists( $script_path ) ? (string) filemtime( $script_path ) : wp_get_theme()->get( 'Version' ),
            true
        );

        wp_localize_script(
            'hostinger-contact-form-block',
            'hostinger_contact_form',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'submit_contactform' ),
                'error'    => __( 'An error occurred. Please try again later.', 'hostinger-ai-theme' ),
            )
        );
    }

    public function render_block( array $attributes, string $content, \WP_Block $block ): string {
        wp_enqueue_script( 'hostinger-contact-form-block' );

        ob_start();
        require __DIR__ . '/render.php';

        return ob_get_clean();
    }

    public function handle_contact_submit(): void {
        check_ajax_referer( 'submit_contactform', 'nonce' );

        if ( ! $this->is_post_request() ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'hostinger-ai-theme' ) ), 405 );
        }

        if ( $this->get_post_field( 'website_url' ) !== '' ) {
            wp_send_json_success( array( 'message' => __( 'Successfully submitted!', 'hostinger-ai-theme' ) ) );
        }

        $name           = $this->sanitize_limited_text( $this->get_post_field( 'name' ), self::MAX_NAME_LENGTH );
        $email          = sanitize_email( $this->get_post_field( 'email' ) );
        $privacy_policy = sanitize_text_field( $this->get_post_field( 'privacy_policy' ) );
        $form_message   = $this->sanitize_limited_textarea( $this->get_post_field( 'message' ), self::MAX_MESSAGE_LENGTH );

        if ( $privacy_policy !== 'on' ) {
            wp_send_json_error( array( 'message' => __( 'Please agree with privacy policy.', 'hostinger-ai-theme' ) ) );
        }

        if ( empty( $name ) || empty( $form_message ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'hostinger-ai-theme' ) ) );
        }

        if ( strlen( $email ) > self::MAX_EMAIL_LENGTH || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'hostinger-ai-theme' ) ) );
        }

        if ( $this->is_rate_limited( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please wait a few minutes before trying again.', 'hostinger-ai-theme' ) ), 429 );
        }

        $subject = __( 'New Contact Form Submission', 'hostinger-ai-theme' );

        $email_data = array(
            'name'         => $name,
            'email'        => $email,
            'form_message' => $form_message,
        );

        $message = $this->get_email_content( $email_data );

        $headers     = $this->get_mail_headers( $email );
        $admin_email = sanitize_email( get_option( 'admin_email' ) );
        $send_to     = $admin_email;

	    do_action(
		    'hostinger_reach_submit',
		    array(
			    'group'    => 'WordPress',
				'name'     => $name,
			    'email'    => $email,
			    'metadata' => array(
				    'plugin'  => 'ai-theme',
				    'form_id' => 'ai-theme',
			    ),
		    )
	    );

        if ( is_email( $send_to ) && wp_mail( $send_to, $subject, $message, $headers ) ) {
            wp_send_json_success( array( 'message' => __( 'Successfully submitted!', 'hostinger-ai-theme' ) ) );
        } else {
            $error_message = __( 'Failed to send email. Please try again later.', 'hostinger-ai-theme' );
            wp_send_json_error( array( 'message' => $error_message ) );
        }
    }

    private function get_email_content( array $email_data ): string {
        ob_start();

        get_template_part( 'gutenberg-blocks/ContactForm/templates/email', 'content', $email_data );

        return ob_get_clean();
    }

    private function get_post_field( string $key ): string {
        if ( ! isset( $_POST[ $key ] ) || is_array( $_POST[ $key ] ) ) {
            return '';
        }

        return trim( (string) wp_unslash( $_POST[ $key ] ) );
    }

    private function sanitize_limited_text( string $value, int $max_length ): string {
        return $this->limit_string( sanitize_text_field( $value ), $max_length );
    }

    private function sanitize_limited_textarea( string $value, int $max_length ): string {
        return $this->truncate_string( trim( sanitize_textarea_field( $value ) ), $max_length );
    }

    private function limit_string( string $value, int $max_length ): string {
        $value = str_replace( array( "\r", "\n" ), ' ', trim( $value ) );

        return $this->truncate_string( $value, $max_length );
    }

    private function truncate_string( string $value, int $max_length ): string {
        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $value, 0, $max_length );
        }

        return substr( $value, 0, $max_length );
    }

    private function get_mail_headers( string $reply_to_email ): array {
        $site_name  = $this->sanitize_header_value( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
        $from_email = $this->get_from_email();

        return array(
            sprintf( 'From: %s <%s>', $site_name, $from_email ),
            sprintf( 'Reply-To: %s', $reply_to_email ),
            'Content-Type: text/plain; charset=UTF-8',
        );
    }

    private function get_from_email(): string {
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        $host = is_string( $host ) ? preg_replace( '/[^A-Za-z0-9.-]/', '', $host ) : '';

        if ( ! empty( $host ) ) {
            $from_email = sanitize_email( 'info@' . $host );

            if ( is_email( $from_email ) ) {
                return $from_email;
            }
        }

        return sanitize_email( get_option( 'admin_email' ) );
    }

    private function sanitize_header_value( string $value ): string {
        return trim( str_replace( array( "\r", "\n", '<', '>' ), '', sanitize_text_field( $value ) ) );
    }

    private function is_post_request(): bool {
        return isset( $_SERVER['REQUEST_METHOD'] ) && strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) === 'POST';
    }

    private function is_rate_limited( string $email ): bool {
        $key   = 'hst_contact_' . wp_hash( $this->get_request_ip() . '|' . strtolower( $email ) );
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
