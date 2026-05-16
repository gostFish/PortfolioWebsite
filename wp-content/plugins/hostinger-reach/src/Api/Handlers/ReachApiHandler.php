<?php

namespace Hostinger\Reach\Api\Handlers;

use Hostinger\Reach\Api\ApiKeyManager;
use Hostinger\Reach\Api\ResourceIdManager;
use Hostinger\Reach\Functions;
use Hostinger\Reach\Integrations\Reach\ReachFormIntegration;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class ReachApiHandler extends ApiHandler {
    private const MAX_EMAIL_LENGTH          = 254;
    private const MAX_NAME_LENGTH           = 120;
    private const MAX_GROUP_LENGTH          = 120;
    private const MAX_TAGS_LENGTH           = 500;
    private const MAX_METADATA_ITEMS        = 20;
    private const MAX_METADATA_VALUE_LENGTH = 200;
    private const RATE_LIMIT_WINDOW         = 15 * MINUTE_IN_SECONDS;
    private const RATE_LIMIT_EMAIL_MAX      = 5;
    private const RATE_LIMIT_IP_MAX         = 20;

    protected string $hostinger_auth_url;
    protected string $reach_domain;
    public ApiKeyManager $api_key_manager;
    public ResourceIdManager $resource_id_manager;

    public function __construct( Functions $functions, ApiKeyManager $api_key_manager, ResourceIdManager $resource_id_manager ) {
        parent::__construct( $functions );
        $this->api_key_manager     = $api_key_manager;
        $this->resource_id_manager = $resource_id_manager;
        $this->set_api_base_name();
        $this->init_hooks();
    }


    public function get_not_connected_error_message(): string {
        return __( 'Your site is not connected to Reach. Connect to Reach and try again.', 'hostinger-reach' );
    }

    public function init_hooks(): void {

        add_filter(
            'allowed_http_origins',
            function ( $origins ) {
                $origins[] = HOSTINGER_REACH_REST_URI;

                return $origins;
            }
        );

        add_filter(
            'rest_exposed_cors_headers',
            function ( array $exposed_headers ): array {
                $exposed_headers[] = 'X-WP-Total';
                $exposed_headers[] = 'X-WP-TotalPages';
                $exposed_headers[] = 'Link';

                return array_values( array_unique( $exposed_headers ) );
            }
        );

        add_filter(
            'rest_allowed_cors_headers',
            function ( array $allowed_headers, WP_REST_Request $request ): array {
                $allowed_headers[] = 'Authorization';
                $allowed_headers[] = 'Content-Type';
                $allowed_headers[] = 'X-WP-Nonce';

                return array_values( array_unique( $allowed_headers ) );
            },
            10,
            2
        );

        /**
         * Submits a contact to Reach
         *
         * @param array $data The data to be sent
         * email: string - - Contact email
         * name: string (optional) - Contact name
         * surname: string (optional) - Contact surname
         * group: string (optional) - The group to which the contact belongs WordPress by default
         * metadata: array (optional) - Additional metadata to be sent with the contact
         *    - plugin: string - The name of the plugin sending the contact
         *
         * example of usage:
         *
         * do_action( 'hostinger_reach_submit', array(
         *     'email' => 'john.doe@example.com',
         *     'name' => 'John',
         *     'surname' => 'Doe',
         *     'group' => 'your plugin name',
         *     'metadata' => array(
         *         'plugin' => 'your plugin name',
         *     )
         * ))
         *
         * @fires hostinger_reach_contact_submitted when the contact is submitted successfully
         * @fires hostinger_reach_contact_failed when the contact submission fails
         * @since 1.1.0
         *
         */
        if ( ! has_action( 'hostinger_reach_submit' ) ) {
            add_action( 'hostinger_reach_submit', array( $this, 'post_contact' ) );
        }
    }

    public function get_default_headers(): array {
        return array(
            'Authorization' => 'Bearer ' . $this->api_key_manager->get_token(),
        );
    }

    public function is_connected(): bool {
        return ! empty( $this->api_key_manager->get_token() );
    }

    public function get_resource_id(): string {
        if ( ! $this->is_connected() ) {
            return ResourceIdManager::NON_EXISTENT_RESOURCE_ID;
        }

        $resource_id = $this->resource_id_manager->get_resource_id();

        if ( ! empty( $resource_id ) ) {
            return $resource_id;
        }

        return $this->generate_resource_id();
    }

    public function generate_resource_id(): string {
        $overview_data = $this->get_overview_handler()->get_data();
        if ( isset( $overview_data['data']['resourceId'] ) ) {
            $this->resource_id_manager->store_resource_id( $overview_data['data']['resourceId'] );
        } else {
            $this->resource_id_manager->store_resource_id( ResourceIdManager::NON_EXISTENT_RESOURCE_ID );
        }

        return $this->resource_id_manager->get_resource_id();
    }

    public function post_contact_handler( WP_REST_Request $request ): WP_REST_Response {
        if ( ! $this->has_valid_rest_nonce( $request ) ) {
            return $this->handle_wp_error( new WP_Error( 'invalid_request', 'You cannot perform this action', array( 'status' => 403 ) ) );
        }

        if ( $this->is_honeypot_submission( $request ) ) {
            return new WP_REST_Response( array( 'success' => true ), 200 );
        }

        $email = sanitize_email( $this->get_scalar_request_param( $request, 'email' ) );
        if ( strlen( $email ) > self::MAX_EMAIL_LENGTH || ! is_email( $email ) ) {
            return $this->handle_wp_error(
                new WP_Error(
                    'invalid_email',
                    __( 'Please enter a valid email address.', 'hostinger-reach' ),
                    array( 'status' => 400 )
                )
            );
        }

        $name     = $this->sanitize_limited_text( $this->get_scalar_request_param( $request, 'name' ), self::MAX_NAME_LENGTH );
        $surname  = $this->sanitize_limited_text( $this->get_scalar_request_param( $request, 'surname' ), self::MAX_NAME_LENGTH );
        $form_id  = $this->sanitize_limited_text( $this->get_scalar_request_param( $request, 'id' ), self::MAX_GROUP_LENGTH );
        $metadata = $this->sanitize_metadata( $request->get_param( 'metadata' ) );
        $tags     = $this->sanitize_limited_text( $this->get_scalar_request_param( $request, 'tags' ), self::MAX_TAGS_LENGTH );
        $group    = apply_filters( 'hostinger_reach_get_group', $this->get_scalar_request_param( $request, 'group' ), $form_id );
        $group    = is_scalar( $group ) ? $this->sanitize_limited_text( (string) $group, self::MAX_GROUP_LENGTH ) : '';

        return $this->post_contact(
            array(
                'form_id'  => $form_id,
                'group'    => $group,
                'tags'     => $tags,
                'email'    => $email,
                'name'     => $name,
                'surname'  => $surname,
                'metadata' => $metadata,
            )
        );
    }

    public function is_authorized( WP_REST_Request $request ): bool {
        return $this->has_valid_rest_nonce( $request ) && $this->get_connection_status_handler();
    }

    private function has_valid_rest_nonce( WP_REST_Request $request ): bool {
        $nonce = $request->get_header( 'X-WP-Nonce' );

        return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
    }

    public function post_generate_auth_url(): WP_REST_Response {
        $this->api_key_manager->generate_csrf();

        $query_params = array(
            'fromPlugin' => true,
            'type'       => 'wordpress',
            'userType'   => $this->get_functions()->is_hostinger_user() ? 'internal' : 'external',
            'token'      => urlencode( $this->api_key_manager->get_csrf() ),
            'domain'     => $this->get_functions()->get_host_info(),
        );

        $reach_url = add_query_arg( $query_params, $this->reach_domain . 'settings/connect-site' );
        $auth_url  = add_query_arg(
            array(
                'redirectUrl' => urlencode( $reach_url ),
            ),
            $this->hostinger_auth_url
        );

        return new WP_REST_Response(
            array(
                'auth_url' => $auth_url,
                'success'  => true,
            ),
            200
        );
    }

    public function get_connection_status_handler(): bool {
        if ( ! $this->is_connected() ) {
            return false;
        }

        $status = $this->get( 'connection/status' );
        if ( is_wp_error( $status ) || wp_remote_retrieve_response_code( $status ) >= 400 ) {
            return false;
        }

        return true;
    }

    public function post_token_handler( WP_REST_Request $request ): WP_REST_Response {
        $csrf_field = $request->get_param( 'csrf_field' );
        $token      = $request->get_param( 'token' );
        if ( ! $this->api_key_manager->validate_csrf( $csrf_field ) ) {
            return $this->handle_wp_error( new WP_Error( $this->get_not_connected_error_message(), 'You cannot perform this action' ) );
        }

        $this->api_key_manager->store_token( $token );
        $this->api_key_manager->clear_csrf();

        return new WP_REST_Response( array( 'success' => true ) );
    }

    public function get_overview_handler(): WP_REST_Response {
        if ( ! $this->get_connection_status_handler() ) {
            $this->api_key_manager->clear_token();
            $this->resource_id_manager->clear_resource_id();

            return $this->handle_wp_error( new WP_Error( $this->get_not_connected_error_message(), 'You cannot perform this action', array( 'status' => 403 ) ) );
        }

        $response = $this->get( 'overview' );

        if ( is_wp_error( $response ) ) {
            return $this->handle_wp_error( $response );
        }

        return $this->handle_response( $response );
    }

    public function post_contact( array $data ): WP_REST_Response {
        $email = sanitize_email( (string) ( $data['email'] ?? '' ) );
        if ( strlen( $email ) > self::MAX_EMAIL_LENGTH || ! is_email( $email ) ) {
            return $this->handle_wp_error(
                new WP_Error(
                    'invalid_email',
                    __( 'Please enter a valid email address.', 'hostinger-reach' ),
                    array( 'status' => 400 )
                )
            );
        }

        if ( $this->is_rate_limited( $email ) ) {
            return $this->handle_wp_error(
                new WP_Error(
                    'rate_limited',
                    __( 'Please wait a moment before trying again.', 'hostinger-reach' ),
                    array( 'status' => 429 )
                )
            );
        }

        $data['email'] = $email;

        if ( ! $this->get_connection_status_handler() ) {
            $this->api_key_manager->clear_token();

            return $this->handle_wp_error( new WP_Error( $this->get_not_connected_error_message(), 'You cannot perform this action' ) );
        }

        $raw_group     = $data['group'] ?? '';
        $raw_tags      = $data['tags'] ?? '';
        $data['group'] = is_scalar( $raw_group ) ? $this->sanitize_limited_text( (string) $raw_group, self::MAX_GROUP_LENGTH ) : '';
        $data['tags']  = is_scalar( $raw_tags ) ? $this->sanitize_limited_text( (string) $raw_tags, self::MAX_TAGS_LENGTH ) : '';

        $contact    = $this->parse_contact( $data );
        $group_name = ! empty( $data['group'] ) ? $data['group'] : HOSTINGER_REACH_DEFAULT_CONTACT_LIST;
        $tag_ids    = $this->get_tag_ids( $data['tags'] ?? '', $group_name );

        if ( empty( $tag_ids ) ) {
            $group_tag = $this->create_tag_from_group( $group_name );
            if ( ! empty( $group_tag ) ) {
                $tag_ids[] = $group_tag;
            }
        }

        $args = array(
            'tagUuids'  => $tag_ids,
            'groupName' => $group_name,
            'contacts'  => array( $contact ),
        );

        $response = $this->post(
            'contacts',
            $args
        );

        if ( is_wp_error( $response ) ) {
            do_action( 'hostinger_reach_contact_failed', $data );

            return $this->handle_wp_error( $response );
        }

        do_action( 'hostinger_reach_contact_submitted', $data );

        return $this->handle_response( $response );
    }

    public function post_import_contacts( array $contacts_data ): WP_REST_Response {
        if ( ! $this->get_connection_status_handler() ) {
            $this->api_key_manager->clear_token();

            return $this->handle_wp_error( new WP_Error( $this->get_not_connected_error_message(), 'You cannot perform this action' ) );
        }

        $group = $contacts_data[0]['metadata']['group'] ?? HOSTINGER_REACH_DEFAULT_CONTACT_LIST;

        $contacts = array();
        foreach ( $contacts_data as $contact_data ) {
            $contacts[] = $this->parse_contact( $contact_data );
        }

        $args = array(
            'groupName' => $group,
            'contacts'  => $contacts,
        );

        $response = $this->post(
            'contacts',
            $args
        );

        if ( is_wp_error( $response ) ) {
            do_action( 'hostinger_reach_imports_contact_failed' );

            return $this->handle_wp_error( $response );
        }

        do_action( 'hostinger_reach_contacts_imported', count( $contacts ), $group );

        return $this->handle_response( $response );
    }

    public function post_webhook_event( array $webhook_payload ): WP_REST_Response {
        if ( ! $this->get_connection_status_handler() ) {
            $this->api_key_manager->clear_token();

            return $this->handle_wp_error( new WP_Error( $this->get_not_connected_error_message(), 'You cannot perform this action' ) );
        }

        if ( ! isset( $webhook_payload['name'] ) ) {
            return $this->handle_wp_error( new WP_Error( 'Bad request', 'Missing parameter [name] in the WebHook data' ) );
        }

        if ( ! isset( $webhook_payload['contact']['email'] ) ) {
            return $this->handle_wp_error( new WP_Error( 'Bad request', 'Missing parameter [contact.email] in the WebHook data' ) );
        }

        $webhook_payload['timestamp'] = gmdate( 'Y-m-d\TH:i:s\Z' );

        $response = $this->post(
            'webhooks',
            $webhook_payload
        );

        if ( is_wp_error( $response ) ) {
            return $this->handle_wp_error( $response );
        }

        return $this->handle_response( $response );
    }

    public function get_tags_handler(): WP_REST_Response {
        if ( ! $this->get_connection_status_handler() ) {
            $this->api_key_manager->clear_token();

            return $this->handle_wp_error( new WP_Error( $this->get_not_connected_error_message(), 'You cannot perform this action', array( 'status' => 403 ) ) );
        }

        $response = $this->get( 'tags' );

        if ( is_wp_error( $response ) ) {
            return $this->handle_wp_error( $response );
        }

        return $this->handle_response( $response );
    }

    public function post_tags_handler( WP_REST_Request $request ): WP_REST_Response {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) || ! $this->get_connection_status_handler() ) {
            $this->api_key_manager->clear_token();

            return $this->handle_wp_error( new WP_Error( $this->get_not_connected_error_message(), 'You cannot perform this action', array( 'status' => 403 ) ) );
        }

        $names = $request->get_param( 'names' );
        if ( empty( $names ) ) {
            return $this->handle_wp_error( new WP_Error( 'names_empty', 'Names parameter cannot be empty.' ) );
        }

        $response = $this->post(
            'tags',
            array(
                'names' => $names,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $this->handle_wp_error( $response );
        }

        return $this->handle_response( $response );
    }

    private function parse_contact( array $data ): array {
        $contact = array(
            'email' => sanitize_email( (string) ( $data['email'] ?? '' ) ),
        );

        if ( ! empty( $data['name'] ) ) {
            $contact['name'] = $this->sanitize_limited_text( (string) $data['name'], self::MAX_NAME_LENGTH );
        }

        if ( ! empty( $data['surname'] ) ) {
            $contact['surname'] = $this->sanitize_limited_text( (string) $data['surname'], self::MAX_NAME_LENGTH );
        }

        $metadata = $this->sanitize_metadata( $data['metadata'] ?? array() );

        // phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText -- Internal metadata key.
        $metadata['platform'] = 'wordpress';

        if ( ! isset( $metadata['plugin'] ) ) {
            $metadata['plugin'] = ReachFormIntegration::INTEGRATION_NAME;
        }

        $contact['metadata'] = $metadata;

        return $contact;
    }

    private function get_scalar_request_param( WP_REST_Request $request, string $param ): string {
        $value = $request->get_param( $param );

        return is_scalar( $value ) ? (string) $value : '';
    }

    private function is_honeypot_submission( WP_REST_Request $request ): bool {
        return trim( $this->get_scalar_request_param( $request, 'website_url' ) ) !== '';
    }

    private function is_rate_limited( string $email ): bool {
        $ip = $this->get_request_ip();

        if ( $ip !== 'unknown' && $this->exceeds_rate_limit( 'hostinger_reach_ip_' . hash( 'sha256', $ip ), self::RATE_LIMIT_IP_MAX ) ) {
            return true;
        }

        return $this->exceeds_rate_limit(
            'hostinger_reach_email_' . hash( 'sha256', strtolower( $email ) . '|' . $ip ),
            self::RATE_LIMIT_EMAIL_MAX
        );
    }

    private function exceeds_rate_limit( string $key, int $limit ): bool {
        $attempts = (int) get_transient( $key );
        if ( $attempts >= $limit ) {
            return true;
        }

        set_transient( $key, $attempts + 1, self::RATE_LIMIT_WINDOW );

        return false;
    }

    private function get_request_ip(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        return is_string( $ip ) && filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : 'unknown';
    }

    private function sanitize_metadata( $metadata ): array {
        if ( ! is_array( $metadata ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $metadata as $key => $value ) {
            if ( count( $sanitized ) >= self::MAX_METADATA_ITEMS ) {
                break;
            }

            $sanitized_key = sanitize_key( (string) $key );
            if ( $sanitized_key === '' || ! is_scalar( $value ) ) {
                continue;
            }

            $sanitized[ $sanitized_key ] = $this->sanitize_limited_text( (string) $value, self::MAX_METADATA_VALUE_LENGTH );
        }

        return $sanitized;
    }

    private function sanitize_limited_text( string $value, int $max_length ): string {
        $value = sanitize_text_field( $this->ensure_utf8( $value ) );

        return $this->truncate_string( $value, $max_length );
    }

    private function truncate_string( string $value, int $max_length ): string {
        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $value, 0, $max_length, 'UTF-8' );
        }

        return substr( $value, 0, $max_length );
    }

    private function ensure_utf8( string $value ): string {
        if ( $value === '' || ! function_exists( 'mb_check_encoding' ) || mb_check_encoding( $value, 'UTF-8' ) ) {
            return $value;
        }

        $detected  = mb_detect_encoding( $value, array( 'UTF-8', 'Windows-1252', 'ISO-8859-1' ), true );
        $converted = mb_convert_encoding( $value, 'UTF-8', $detected !== false ? $detected : 'ISO-8859-1' );

        return is_string( $converted ) ? $converted : $value;
    }

    private function set_api_base_name(): void {
        if ( $this->get_functions()->is_staging() ) {
            $this->hostinger_auth_url = 'https://auth.hostinger.dev/login';
            $this->reach_domain       = 'https://reach.hostinger.dev/';
        } else {
            $this->hostinger_auth_url = 'https://auth.hostinger.com/login';
            $this->reach_domain       = 'https://reach.hostinger.com/';
        }

        $this->api_base_name = $this->reach_domain . 'api/public/v1/';
    }

    private function get_tag_ids( string $tag_names, string $group_name ): array {
        if ( empty( $tag_names ) && empty( $group_name ) ) {
            return array();
        }

        $tag_names = explode( ',', $tag_names );
        $tag_ids   = array();
        $handler   = $this->get_tags_handler();
        $tags_data = $handler->get_data();
        $tags      = $tags_data['data'] ?? array();

        foreach ( $tags as $tag ) {
            if ( in_array( $tag['value'], $tag_names, true ) || $tag['value'] === $group_name ) {
                $tag_ids[] = $tag['uuid'];
            }
        }

        return $tag_ids;
    }

    private function create_tag_from_group( string $group ): string {
        if ( empty( $group ) ) {
            return '';
        }

        $response = $this->post(
            'tags',
            array(
                'names' => array( $group ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return '';
        }

        $body = wp_remote_retrieve_body( $response );
        $body = json_decode( $body, true );
        return $body['data'][0]['uuid'] ?? '';
    }
}
