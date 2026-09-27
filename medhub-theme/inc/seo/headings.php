<?php
/**
 * One H1 per page.
 *
 * Posts, products and text pages print their H1 in the template.
 * Some existing descriptions and articles start with their own <h1>, which would give the
 * page two H1s. On output only (the stored content is untouched), such content H1s become
 * H2s. (Designed pages take their H1 from the first Heading block and are rendered
 * without this filter, see inc/content/sections.php.)
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'the_content',
	static function ( $content ) {
		if ( ! is_singular( array( 'post', 'product', 'page' ) ) || ! in_the_loop() || ! is_main_query() || false === stripos( $content, '<h1' ) ) {
			return $content;
		}
		return preg_replace( array( '#<h1(\s|>)#i', '#</h1>#i' ), array( '<h2$1', '</h2>' ), $content );
	},
	// After blocks, shortcodes and page builders (Elementor filters the_content at 9).
	100
);
