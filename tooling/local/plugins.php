<?php
/**
 * LOCAL COPY ONLY: deactivate the old theme's design/page-builder plugins (and restore them).
 *
 *   tooling/local/wp.sh eval-file tooling/local/plugins.php status|deactivate|restore
 *
 * The stored active_plugins list is edited directly: WordPress would otherwise read the list
 * through the local safety guard (which hides Pixel Manager, Hostinger Reach and WP Mail SMTP
 * at runtime) and save it without them. Deactivation hooks are not run, so no plugin cleans up
 * data. The original list is kept in the option _medhub_legacy_active_plugins for "restore".
 * Nothing is deleted.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

if ( ! ( defined( 'MEDHUB_LOCAL_COPY' ) && MEDHUB_LOCAL_COPY ) || ! preg_match( '#^https?://medhub\.local$#', home_url() ) ) {
	WP_CLI::error( 'Refusing to run: this is not the MedHub local copy.' );
}

// Plugin folder => why it goes (see docs/plugin-cleanup.md).
$medhub_remove = array(
	'elementor'                       => 'Page builder: no page or post depends on it after the content migration.',
	'header-footer-elementor'         => 'Elementor header/footer templates of the old theme.',
	'nasa-core'                       => 'Elessi companion (shortcodes, product hooks).',
	'revslider'                       => 'Slider Revolution: 24 sliders, none used in content.',
	'yith-woocommerce-compare'        => 'Product comparison is not part of the design.',
	'super-fast-wp-plugin'            => 'Page cache is unsafe for WooCommerce; minify/defer break scripts.',
	'medhub-floating-contact-buttons' => 'Replaced by the theme floating contact buttons.',
	'medhub-mobile-banner'            => 'Replaced by the theme floating contact buttons.',
	'wpforms-lite'                    => 'No forms exist; the contact page shows contact details (no form is built yet).',
);

global $wpdb;
$medhub_task = $args[0] ?? 'status';
$medhub_raw  = static fn() => (array) maybe_unserialize( $wpdb->get_var( "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'active_plugins'" ) );
$medhub_save = static function ( array $list ) use ( $wpdb ) {
	$wpdb->update( $wpdb->options, array( 'option_value' => maybe_serialize( array_values( $list ) ) ), array( 'option_name' => 'active_plugins' ) );
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'active_plugins', 'options' );
};
$medhub_dir = static fn( $file ) => strtok( (string) $file, '/' );

if ( 'status' === $medhub_task ) {
	foreach ( $medhub_raw() as $file ) {
		WP_CLI::log( ( isset( $medhub_remove[ $medhub_dir( $file ) ] ) ? '[to remove] ' : '[keep]      ' ) . $file );
	}
	return;
}

if ( 'deactivate' === $medhub_task ) {
	$current = $medhub_raw();
	if ( false === get_option( '_medhub_legacy_active_plugins' ) ) {
		add_option( '_medhub_legacy_active_plugins', $current, '', false );
	}
	$keep = array_filter( $current, static fn( $f ) => ! isset( $medhub_remove[ $medhub_dir( $f ) ] ) );
	foreach ( array_diff( $current, $keep ) as $file ) {
		WP_CLI::log( "deactivated $file" );
	}
	$medhub_save( $keep );
	WP_CLI::success( count( $current ) - count( $keep ) . ' plugins deactivated (local copy). Still active: ' . count( $keep ) );
	return;
}

if ( 'restore' === $medhub_task ) {
	$original = get_option( '_medhub_legacy_active_plugins' );
	if ( ! is_array( $original ) ) {
		WP_CLI::error( 'No saved plugin list.' );
	}
	$medhub_save( $original );
	delete_option( '_medhub_legacy_active_plugins' );
	WP_CLI::success( 'Original plugin list restored (' . count( $original ) . ' active).' );
}
