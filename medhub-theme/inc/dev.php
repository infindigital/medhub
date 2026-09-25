<?php
/**
 * Development-environment markers.
 *
 * In the local sandbox (MEDHUB_SANDBOX), a fixed label reminds everyone that the
 * catalogue shown is development data, not the live medhub.ae database.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MEDHUB_SANDBOX' ) || true !== MEDHUB_SANDBOX ) {
	return;
}

add_action(
	'wp_footer',
	static function () {
		echo '<div class="dev-ribbon" role="note">' . esc_html__( 'DEV SANDBOX · development data', 'medhub' ) . '</div>';
	}
);
