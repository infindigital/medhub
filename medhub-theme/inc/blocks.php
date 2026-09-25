<?php
/**
 * Theme blocks and patterns.
 *
 * Design lives in the theme; copy lives in WordPress. Every MedHub block is
 * server-rendered (block.json + render.php): editors change text in the block
 * editor, while product/category/brand/post data is always read live.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		// One small, dependency-free editor script shared by all MedHub blocks.
		wp_register_script(
			'medhub-blocks-editor',
			MEDHUB_URI . '/assets/dist/js/blocks-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
			medhub_asset_version( 'js/blocks-editor.js' ),
			true
		);

		$names = array();
		foreach ( glob( MEDHUB_DIR . '/blocks/*/block.json' ) as $block_json ) {
			$type = register_block_type( dirname( $block_json ) );
			if ( $type ) {
				$names[] = $type->name;
			}
		}
		wp_add_inline_script( 'medhub-blocks-editor', 'window.medhubBlocks = ' . wp_json_encode( $names ) . ';', 'before' );

		register_block_pattern_category( 'medhub', array( 'label' => __( 'MedHub', 'medhub' ) ) );

		foreach ( glob( MEDHUB_DIR . '/patterns/*.html' ) as $pattern_file ) {
			$slug = basename( $pattern_file, '.html' );
			register_block_pattern(
				'medhub/' . $slug,
				array(
					'title'      => ucwords( str_replace( '-', ' ', $slug ) ) . ' (MedHub)',
					'categories' => array( 'medhub' ),
					'content'    => (string) file_get_contents( $pattern_file ),
					'inserter'   => true,
				)
			);
		}
	}
);

add_filter(
	'block_categories_all',
	static function ( $categories ) {
		array_unshift( $categories, array( 'slug' => 'medhub', 'title' => __( 'MedHub', 'medhub' ) ) );
		return $categories;
	}
);

// Only load CSS for the core blocks actually used on a page.
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
