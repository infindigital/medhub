<?php
/**
 * Contact form adapter.
 *
 * config/business.php → contact_form = ['provider' => 'wpforms'|'cf7'|null, 'id' => …]
 *
 *  - Provider configured AND its plugin active → that plugin's form (styled by the theme).
 *  - Otherwise, in the dev sandbox → a clearly marked development form that sends nothing.
 *  - Otherwise (staging/production without a confirmed form) → no form; the contact block
 *    shows the contact details instead. A raw shortcode is never printed.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contact form markup, or '' when no working form is available.
 */
function medhub_contact_form_html(): string {
	$config   = medhub_business_config()['contact_form']['value'] ?? array();
	$provider = $config['provider'] ?? null;
	$id       = isset( $config['id'] ) ? absint( $config['id'] ) : 0;

	if ( medhub_business_can_show( 'contact_form' ) && $id ) {
		if ( 'wpforms' === $provider && shortcode_exists( 'wpforms' ) ) {
			return '<div class="form-provider form-provider--wpforms">' . do_shortcode( sprintf( '[wpforms id="%d" title="false"]', $id ) ) . '</div>';
		}
		if ( 'cf7' === $provider && shortcode_exists( 'contact-form-7' ) ) {
			return '<div class="form-provider form-provider--cf7">' . do_shortcode( sprintf( '[contact-form-7 id="%d"]', $id ) ) . '</div>';
		}
	}

	if ( defined( 'MEDHUB_SANDBOX' ) && true === MEDHUB_SANDBOX ) {
		ob_start();
		get_template_part( 'template-parts/forms/contact-dev' );
		return (string) ob_get_clean();
	}

	return '';
}
