<?php

namespace Hostinger\AiTheme\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class ContactFormWidget extends Widget_Base {
    private const MAX_NAME_LENGTH    = 120;
    private const MAX_EMAIL_LENGTH   = 254;
    private const MAX_MESSAGE_LENGTH = 5000;
    private const RATE_LIMIT_MAX     = 5;
    private const RATE_LIMIT_WINDOW  = 900;

    public function get_name(): string {
        return 'hostinger-contact-form';
    }

    public function get_title(): string {
        return __( 'Contact Form', 'hostinger-ai-theme' );
    }

    public function get_icon(): string {
        return 'eicon-form-horizontal';
    }

    public function get_categories(): array {
        return [ 'general' ];
    }

    public function get_keywords(): array {
        return [ 'contact', 'form', 'email', 'message' ];
    }

    public function get_script_depends(): array {
        return [ 'hostinger-elementor-widgets' ];
    }

    public function get_style_depends(): array {
        return [ 'hostinger-elementor-contact-form-styles' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_form_fields_controls();
        $this->register_privacy_policy_controls();
    }

    private function register_content_controls(): void {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'hostinger-ai-theme' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label'        => __( 'Show Title', 'hostinger-ai-theme' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Show', 'hostinger-ai-theme' ),
                'label_off'    => __( 'Hide', 'hostinger-ai-theme' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'title',
            [
                'label'       => __( 'Title', 'hostinger-ai-theme' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Get in Touch', 'hostinger-ai-theme' ),
                'placeholder' => __( 'Enter form title', 'hostinger-ai-theme' ),
                'condition'   => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_description',
            [
                'label'        => __( 'Show Description', 'hostinger-ai-theme' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Show', 'hostinger-ai-theme' ),
                'label_off'    => __( 'Hide', 'hostinger-ai-theme' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label'        => __( 'Show Date', 'hostinger-ai-theme' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Show', 'hostinger-ai-theme' ),
                'label_off'    => __( 'Hide', 'hostinger-ai-theme' ),
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );

        $this->add_control(
            'description',
            [
                'label'       => __( 'Description', 'hostinger-ai-theme' ),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => __( 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.', 'hostinger-ai-theme' ),
                'placeholder' => __( 'Enter form description', 'hostinger-ai-theme' ),
                'condition'   => [
                    'show_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label'       => __( 'Button Text', 'hostinger-ai-theme' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Send Message', 'hostinger-ai-theme' ),
                'placeholder' => __( 'Enter button text', 'hostinger-ai-theme' ),
            ]
        );

        $this->add_control(
            'recipient_email',
            [
                'label'       => __( 'Recipient Email Address', 'hostinger-ai-theme' ),
                'type'        => Controls_Manager::TEXT,
                'input_type'  => 'email',
                'default'     => get_option( 'admin_email' ),
                'placeholder' => __( 'Enter recipient email address', 'hostinger-ai-theme' ),
                'description' => __( 'Email address where form submissions will be sent. Defaults to WordPress admin email.', 'hostinger-ai-theme' ),
            ]
        );

        $this->end_controls_section();
    }

    private function register_form_fields_controls(): void {
        $this->start_controls_section(
            'form_fields_section',
            [
                'label' => __( 'Form Fields', 'hostinger-ai-theme' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'name_label',
            [
                'label'   => __( 'Name Label', 'hostinger-ai-theme' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Name', 'hostinger-ai-theme' ),
            ]
        );

        $this->add_control(
            'name_placeholder',
            [
                'label'   => __( 'Name Placeholder', 'hostinger-ai-theme' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'What\'s your name?', 'hostinger-ai-theme' ),
            ]
        );

        $this->add_control(
            'email_label',
            [
                'label'   => __( 'Email Label', 'hostinger-ai-theme' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Email', 'hostinger-ai-theme' ),
            ]
        );

        $this->add_control(
            'email_placeholder',
            [
                'label'   => __( 'Email Placeholder', 'hostinger-ai-theme' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'What\'s your email?', 'hostinger-ai-theme' ),
            ]
        );

        $this->add_control(
            'message_label',
            [
                'label'   => __( 'Message Label', 'hostinger-ai-theme' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Message', 'hostinger-ai-theme' ),
            ]
        );

        $this->add_control(
            'message_placeholder',
            [
                'label'   => __( 'Message Placeholder', 'hostinger-ai-theme' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Write your message...', 'hostinger-ai-theme' ),
            ]
        );

        $this->add_control(
            'date_label',
            [
                'label'     => __( 'Date Label', 'hostinger-ai-theme' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => __( 'Date', 'hostinger-ai-theme' ),
                'condition' => [
                    'show_date' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'date_placeholder',
            [
                'label'     => __( 'Date Placeholder', 'hostinger-ai-theme' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => __( 'Select a date', 'hostinger-ai-theme' ),
                'condition' => [
                    'show_date' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_privacy_policy_controls(): void {
        $this->start_controls_section(
            'privacy_policy_section',
            [
                'label' => __( 'Privacy Policy', 'hostinger-ai-theme' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'privacy_policy_text',
            [
                'label'       => __( 'Privacy Policy Text', 'hostinger-ai-theme' ),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => '',
                'placeholder' => __( 'Leave empty to use default privacy policy text', 'hostinger-ai-theme' ),
                'description' => __( 'Leave empty to use the default privacy policy text with automatic link generation.', 'hostinger-ai-theme' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $form_id  = 'contact-form-' . uniqid();

        $privacy_policy_text = $settings['privacy_policy_text'];
        if ( empty( $privacy_policy_text ) ) {
            $privacy_policy_text = sprintf(
                '%s %s%s%s %s',
                __( 'I consent to use of provided personal data for the purpose of responding to the request as described in', 'hostinger-ai-theme' ),
                '<a href="' . esc_url( get_privacy_policy_url() ) . '" target="_blank" rel="noopener noreferrer">',
                __( 'Privacy Policy', 'hostinger-ai-theme' ),
                '</a>',
                __( 'which I have read. I may withdraw my consent at any time.', 'hostinger-ai-theme' )
            );
        }

        $recipient_email     = sanitize_email( $settings['recipient_email'] ?? '' );
        $recipient_signature = is_email( $recipient_email ) ? self::get_recipient_signature( $recipient_email ) : '';

        wp_enqueue_script( 'hostinger-contact-form-block' );
        ?>
        <div class="hostinger-elementor-contact-form">
            <section class="hts-section hts-page hts-contact-form">
                <div class="hts-details">
                    <div class="elementor-hts-contact-details elementor-hts-contacts">
                        <?php if ( 'yes' === $settings['show_title'] && ! empty( $settings['title'] ) ) : ?>
                            <h2 class="contact-form-title"><?php echo esc_html( $settings['title'] ); ?></h2>
                        <?php endif; ?>

                        <?php if ( 'yes' === $settings['show_description'] && ! empty( $settings['description'] ) ) : ?>
                            <p class="contact-form-description"><?php echo esc_html( $settings['description'] ); ?></p>
                        <?php endif; ?>

                        <form id="<?php echo esc_attr( $form_id ); ?>"
                              data-recipient="<?php echo esc_attr( is_email( $recipient_email ) ? base64_encode( $recipient_email ) : '' ); ?>"
                              data-recipient-signature="<?php echo esc_attr( $recipient_signature ); ?>">
                            <?php wp_nonce_field( 'hts_submit_contactform', 'contactform_nonce', false ); ?>

                            <label for="<?php echo esc_attr( $form_id ); ?>-name"><?php echo esc_html( $settings['name_label'] ); ?></label>
                            <input type="text"
                                   id="<?php echo esc_attr( $form_id ); ?>-name"
                                   class="contact-name"
                                   name="name"
                                   placeholder="<?php echo esc_attr( $settings['name_placeholder'] ); ?>"
                                   maxlength="120"
                                   required>

                            <label for="<?php echo esc_attr( $form_id ); ?>-email"><?php echo esc_html( $settings['email_label'] ); ?></label>
                            <input type="email"
                                   id="<?php echo esc_attr( $form_id ); ?>-email"
                                   class="contact-email"
                                   name="email"
                                   placeholder="<?php echo esc_attr( $settings['email_placeholder'] ); ?>"
                                   maxlength="254"
                                   required>

                            <?php if ( 'yes' === $settings['show_date'] ) : ?>
                                <label for="<?php echo esc_attr( $form_id ); ?>-date"><?php echo esc_html( $settings['date_label'] ); ?></label>
                                <input type="date"
                                       id="<?php echo esc_attr( $form_id ); ?>-date"
                                       class="contact-date"
                                       name="date"
                                       placeholder="<?php echo esc_attr( $settings['date_placeholder'] ); ?>"
                                       required>
                            <?php endif; ?>

                            <label for="<?php echo esc_attr( $form_id ); ?>-message"><?php echo esc_html( $settings['message_label'] ); ?></label>
                            <textarea id="<?php echo esc_attr( $form_id ); ?>-message"
                                      class="contact-message"
                                      name="message"
                                      placeholder="<?php echo esc_attr( $settings['message_placeholder'] ); ?>"
                                      maxlength="5000"
                                      required></textarea>

                            <div class="form-field honeypot-field" style="display:none;" aria-hidden="true">
                                <label for="<?php echo esc_attr( $form_id ); ?>-website-url"><?php esc_html_e( 'Website', 'hostinger-ai-theme' ); ?></label>
                                <input type="text"
                                       id="<?php echo esc_attr( $form_id ); ?>-website-url"
                                       name="website_url"
                                       autocomplete="off"
                                       tabindex="-1">
                            </div>

                            <div class="hts-privacy-agree">
                                <label class="hts-form-control">
                                    <input type="checkbox"
                                           id="<?php echo esc_attr( $form_id ); ?>-privacy-policy-checkbox"
                                           class="privacy-policy-checkbox"
                                           name="privacy_policy"
                                           required>
                                    <span><?php echo wp_kses_post( $privacy_policy_text ); ?></span>
                                </label>
                            </div>

                            <input type="submit" value="<?php echo esc_attr( $settings['button_text'] ); ?>">
                            <div class="validate-message"></div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
        <?php
    }

    public static function handle_contact_submit(): void {
        check_ajax_referer( 'hts_submit_contactform', 'nonce' );

        if ( ! self::is_post_request() ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'hostinger-ai-theme' ) ), 405 );
        }

        if ( self::get_post_field( 'website_url' ) !== '' ) {
            wp_send_json_success( array( 'message' => __( 'Successfully submitted!', 'hostinger-ai-theme' ) ) );
        }

        $name                = self::sanitize_limited_text( self::get_post_field( 'name' ), self::MAX_NAME_LENGTH );
        $email               = sanitize_email( self::get_post_field( 'email' ) );
        $date                = self::sanitize_date( self::get_post_field( 'date' ) );
        $privacy_policy      = sanitize_text_field( self::get_post_field( 'privacy_policy' ) );
        $form_message        = self::sanitize_limited_textarea( self::get_post_field( 'message' ), self::MAX_MESSAGE_LENGTH );
        $recipient_email     = sanitize_email( self::get_post_field( 'recipient_email' ) );
        $recipient_signature = self::get_post_field( 'recipient_signature' );

        if ( $privacy_policy !== 'on' ) {
            wp_send_json_error( array( 'message' => __( 'Please agree with privacy policy.', 'hostinger-ai-theme' ) ) );
        }

        if ( empty( $name ) || empty( $form_message ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'hostinger-ai-theme' ) ) );
        }

        if ( strlen( $email ) > self::MAX_EMAIL_LENGTH || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'hostinger-ai-theme' ) ) );
        }

        if ( self::is_rate_limited( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please wait a few minutes before trying again.', 'hostinger-ai-theme' ) ), 429 );
        }

        $subject = __( 'New Contact Form Submission', 'hostinger-ai-theme' );

        $email_data = array(
            'name'         => $name,
            'email'        => $email,
            'date'         => $date,
            'form_message' => $form_message,
        );

        $message = self::get_email_content( $email_data );

        $headers = self::get_mail_headers( $email );

        $admin_email = sanitize_email( get_option( 'admin_email' ) );
        $send_to     = $admin_email;

        if ( is_email( $recipient_email ) && self::is_valid_recipient_signature( $recipient_email, $recipient_signature ) ) {
            $send_to = $recipient_email;
        }

        if ( is_email( $send_to ) && wp_mail( $send_to, $subject, $message, $headers ) ) {
            wp_send_json_success( array( 'message' => __( 'Successfully submitted!', 'hostinger-ai-theme' ) ) );
        } else {
            $error_message = __( 'Failed to send email. Please try again later.', 'hostinger-ai-theme' );
            wp_send_json_error( array( 'message' => $error_message ) );
        }
    }

    private static function get_email_content( array $email_data ): string {
        ob_start();

        get_template_part( 'gutenberg-blocks/ContactForm/templates/email', 'content', $email_data );

        return ob_get_clean();
    }

    private static function get_post_field( string $key ): string {
        if ( ! isset( $_POST[ $key ] ) || is_array( $_POST[ $key ] ) ) {
            return '';
        }

        return trim( (string) wp_unslash( $_POST[ $key ] ) );
    }

    private static function sanitize_limited_text( string $value, int $max_length ): string {
        return self::limit_string( sanitize_text_field( $value ), $max_length );
    }

    private static function sanitize_limited_textarea( string $value, int $max_length ): string {
        return self::truncate_string( trim( sanitize_textarea_field( $value ) ), $max_length );
    }

    private static function limit_string( string $value, int $max_length ): string {
        $value = str_replace( array( "\r", "\n" ), ' ', trim( $value ) );

        return self::truncate_string( $value, $max_length );
    }

    private static function truncate_string( string $value, int $max_length ): string {
        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $value, 0, $max_length );
        }

        return substr( $value, 0, $max_length );
    }

    private static function sanitize_date( string $value ): string {
        $value = sanitize_text_field( $value );

        return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
    }

    private static function get_mail_headers( string $reply_to_email ): array {
        $site_name  = self::sanitize_header_value( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
        $from_email = self::get_from_email();

        return array(
            sprintf( 'From: %s <%s>', $site_name, $from_email ),
            sprintf( 'Reply-To: %s', $reply_to_email ),
            'Content-Type: text/plain; charset=UTF-8',
        );
    }

    private static function get_from_email(): string {
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

    private static function sanitize_header_value( string $value ): string {
        return trim( str_replace( array( "\r", "\n", '<', '>' ), '', sanitize_text_field( $value ) ) );
    }

    private static function get_recipient_signature( string $recipient_email ): string {
        return wp_hash( 'hostinger_elementor_contact_recipient|' . strtolower( $recipient_email ) );
    }

    private static function is_valid_recipient_signature( string $recipient_email, string $signature ): bool {
        if ( empty( $signature ) ) {
            return false;
        }

        return hash_equals( self::get_recipient_signature( $recipient_email ), $signature );
    }

    private static function is_post_request(): bool {
        return isset( $_SERVER['REQUEST_METHOD'] ) && strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) === 'POST';
    }

    private static function is_rate_limited( string $email ): bool {
        $key   = 'hst_elementor_contact_' . wp_hash( self::get_request_ip() . '|' . strtolower( $email ) );
        $count = (int) get_transient( $key );

        if ( $count >= self::RATE_LIMIT_MAX ) {
            return true;
        }

        set_transient( $key, $count + 1, self::RATE_LIMIT_WINDOW );

        return false;
    }

    private static function get_request_ip(): string {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : 'unknown';
    }
}
