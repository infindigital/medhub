<?php
/**
 * Accessors for config/business.php.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Full business config, loaded once per request.
 *
 * @return array<string, array{value:mixed, status:string}>
 */
function medhub_business_config(): array {
	static $config = null;

	if ( null === $config ) {
		$config = (array) require MEDHUB_DIR . '/config/business.php';

		/**
		 * Allows a later settings screen (or a local override) to replace entries
		 * without editing the config file.
		 */
		$config = (array) apply_filters( 'medhub_business_config', $config );
	}

	return $config;
}

/**
 * Whether a business value may be rendered in the current environment.
 *
 * @param string $key Config key.
 */
function medhub_business_can_show( string $key ): bool {
	$entry = medhub_business_config()[ $key ] ?? null;

	if ( ! $entry || null === $entry['value'] ) {
		return false;
	}

	if ( 'confirmed' === $entry['status'] ) {
		return true;
	}

	// Site-sourced values are visible outside production only, so they can be reviewed.
	return 'site-sourced' === $entry['status'] && 'production' !== wp_get_environment_type();
}

/**
 * A business value, or $fallback when it may not be shown here.
 *
 * @param string $key      Config key.
 * @param mixed  $fallback Returned when the value is unconfirmed or missing.
 * @return mixed
 */
function medhub_business( string $key, $fallback = null ) {
	return medhub_business_can_show( $key ) ? medhub_business_config()[ $key ]['value'] : $fallback;
}

/**
 * Admin-only marker next to values that still need confirming (never shown to visitors).
 *
 * @param string $key Config key.
 */
function medhub_business_marker( string $key ): string {
	$entry = medhub_business_config()[ $key ] ?? null;

	if ( ! $entry || 'confirmed' === $entry['status'] || ! current_user_can( 'manage_options' ) ) {
		return '';
	}

	return sprintf(
		'<span class="medhub-tbc" title="%s">%s</span>',
		esc_attr( $entry['note'] ?? '' ),
		esc_html__( 'To confirm', 'medhub' )
	);
}
