<?php
/**
 * Asset loading.
 *
 * Only built files from assets/dist are enqueued. Versions use the file's
 * modification time so caches bust on every build. Per-template bundles
 * (shop, product, checkout, blog) are added in later steps.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version string for a dist file (falls back to the theme version).
 *
 * @param string $relative Path relative to assets/dist.
 */
function medhub_asset_version( string $relative ): string {
	$file = MEDHUB_DIR . '/assets/dist/' . $relative;
	return file_exists( $file ) ? (string) filemtime( $file ) : MEDHUB_VERSION;
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style(
			'medhub-base',
			MEDHUB_URI . '/assets/dist/css/base.css',
			array(),
			medhub_asset_version( 'css/base.css' )
		);

		wp_enqueue_script_module(
			'medhub-core',
			MEDHUB_URI . '/assets/dist/js/core.js',
			array(),
			medhub_asset_version( 'js/core.js' )
		);
	}
);
