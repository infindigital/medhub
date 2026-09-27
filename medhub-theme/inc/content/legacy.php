<?php
/**
 * Leftover shortcodes from the previous theme's page builders.
 *
 * Some existing pages still contain WPBakery / Nasa Core / YITH Compare shortcodes
 * (the cart, order tracking and compare pages). With those plugins no longer active the
 * shortcodes would be printed as raw text, so, only when nobody else registers them:
 *  - layout wrappers (vc_row, vc_column, vc_column_text…) are unwrapped, keeping their content,
 *    so e.g. [woocommerce_cart] inside them keeps working;
 *  - widgets of removed plugins print nothing.
 * The stored content is not changed.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		$unwrap = array( 'vc_row', 'vc_row_inner', 'vc_column', 'vc_column_inner', 'vc_column_text', 'vc_section' );
		$remove = array( 'vc_empty_space', 'vc_separator', 'nasa_title', 'nasa_products', 'yith_woocompare_table' );

		foreach ( $unwrap as $tag ) {
			if ( ! shortcode_exists( $tag ) ) {
				add_shortcode( $tag, static fn( $atts, $content = '' ) => do_shortcode( (string) $content ) );
			}
		}
		foreach ( $remove as $tag ) {
			if ( ! shortcode_exists( $tag ) ) {
				add_shortcode( $tag, '__return_empty_string' );
			}
		}
	},
	99
);
