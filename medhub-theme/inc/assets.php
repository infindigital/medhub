<?php
/**
 * Asset loading.
 *
 * - base.css + core.js on every page (header, footer, components).
 * - home.css only on the front page; more per-template bundles follow (shop, product…).
 * - One font file (Geist variable) is preloaded; the serif accent loads on demand.
 * Versions use file modification times so caches bust on every build.
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

/**
 * Enqueue a dist stylesheet if it exists.
 *
 * @param string $handle Handle suffix.
 * @param string $file   File name in assets/dist/css.
 */
function medhub_enqueue_css( string $handle, string $file ): void {
	if ( file_exists( MEDHUB_DIR . '/assets/dist/css/' . $file ) ) {
		wp_enqueue_style( 'medhub-' . $handle, MEDHUB_URI . '/assets/dist/css/' . $file, array( 'medhub-base' ), medhub_asset_version( 'css/' . $file ) );
	}
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'medhub-base', MEDHUB_URI . '/assets/dist/css/base.css', array(), medhub_asset_version( 'css/base.css' ) );

		if ( is_front_page() ) {
			medhub_enqueue_css( 'home', 'home.css' );
		}

		wp_enqueue_script_module( 'medhub-core', MEDHUB_URI . '/assets/dist/js/core.js', array(), medhub_asset_version( 'js/core.js' ) );
	}
);

// Preload the main text font so headings render without a swap.
add_action(
	'wp_head',
	static function () {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( MEDHUB_URI . '/assets/fonts/geist-latin-var.woff2' )
		);
	},
	1
);

// Editor canvas: load the real theme stylesheets (fonts resolve correctly, unlike
// add_editor_style() rewriting) so block previews match the front end.
add_action(
	'enqueue_block_assets',
	static function () {
		if ( ! is_admin() ) {
			return;
		}
		wp_enqueue_style( 'medhub-editor-base', MEDHUB_URI . '/assets/dist/css/base.css', array(), medhub_asset_version( 'css/base.css' ) );
		wp_enqueue_style( 'medhub-editor-home', MEDHUB_URI . '/assets/dist/css/home.css', array( 'medhub-editor-base' ), medhub_asset_version( 'css/home.css' ) );
		wp_add_inline_style( 'medhub-editor-home', '.medhub-editor-block .hero__actions a,.medhub-editor-block .btn{pointer-events:none}.medhub-editor-label{font:600 12px/1.4 var(--font-sans);letter-spacing:.08em;text-transform:uppercase;color:var(--c-muted);margin:0 0 8px}' );
	}
);
