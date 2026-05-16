<?php
/**
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.  */

namespace Hostinger\AiTheme\GutenbergBlocks\BookingBlock;

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <div class="wp-block-group">
        <form class="booking-form" data-endpoint="<?php echo esc_url( rest_url( 'hostinger/v1/booking-submissions' ) ); ?>">
            <?php wp_nonce_field( 'hostinger_booking_submission', 'booking_nonce', false ); ?>
            <?php
            $formFields = $attributes['formFields'] ?? [];
            if ( ! empty( $formFields ) && is_array( $formFields ) ):
                foreach ( $formFields as $field ):
                    $field_type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';
                    $allowed_field_types = [ 'text', 'email', 'tel', 'date', 'time', 'number', 'url', 'textarea', 'privacy' ];
                    if ( ! in_array( $field_type, $allowed_field_types, true ) ) {
                        $field_type = 'text';
                    }
                    $field_label = isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '';
                    if ( $field_type !== 'privacy' && $field_label === '' ) {
                        continue;
                    }
                    ?>
                    <div class="form-field <?php echo esc_attr( $field_type === 'privacy' ? 'privacy-consent-field' : '' ); ?>">
                        <?php if ( $field_type === 'privacy' ):
                            $policy_parts = explode('Privacy Policy', $field['consentText'] ?? '');
                            $consent_text = '';

                            if (count($policy_parts) >= 2) {
                                $policy_url = esc_url($field['policyUrl'] ?? '/privacy-policy');
                                $policy_text = esc_html($field['policyLinkText'] ?? 'Privacy Policy');

                                $consent_text = esc_html($policy_parts[0]) .
                                    '<a href="' . $policy_url . '" target="_blank" rel="noopener noreferrer">' . $policy_text . '</a>' .
                                    esc_html($policy_parts[1]);
                            } else {
                                $consent_text = esc_html($field['consentText'] ?? '');
                            }
                        ?>
                            <label class="privacy-consent-label">
                                <input type="checkbox"
                                       name="privacy_policy"
                                       <?php echo ( ! empty( $field['required'] ) ) ? 'required' : ''; ?>>
                                <span><?php echo $consent_text; ?></span>
                            </label>
                        <?php else: ?>
                            <label>
                                <?php echo esc_html( $field_label ); ?>
                                <?php
                                if ( ! empty( $field['required'] ) ): ?>
                                    <span class="required">*</span>
                                <?php
                                endif; ?>
                            </label>
                            <?php
                            if ( $field_type === 'textarea' ): ?>
                                <textarea name="<?php echo esc_attr( sanitize_title( $field_label ) ); ?>"
                                <?php
                                echo ( ! empty( $field['required'] ) ) ? 'required' : ''; ?>
                                placeholder="<?php
                                echo esc_attr( $field['placeholder'] ?? '' ); ?>"
                                maxlength="2000"
                                ></textarea>
                            <?php
                            else: ?>
                                <input type="<?php echo esc_attr( $field_type ); ?>"
                                       name="<?php echo esc_attr( sanitize_title( $field_label ) ); ?>"
                                    <?php
                                    echo ( ! empty( $field['required'] ) ) ? 'required' : ''; ?>
                                       placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
                                       maxlength="2000"
                                />
                            <?php
                            endif; ?>
                        <?php endif; ?>
                    </div>
                <?php
                endforeach;
            endif;
            ?>

            <div class="form-field honeypot-field" style="display:none;" aria-hidden="true">
                <input type="text" name="website_url" autocomplete="off" tabindex="-1">
            </div>

            <button type="submit" class="wp-block-button__link wp-element-button">
                <?php
                echo esc_html( $attributes['buttonText'] ?? 'Submit' ); ?>
            </button>
        </form>
        <div class="form-message"></div>
    </div>
</div>
