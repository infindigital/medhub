<?php
/**
 * Theme supports.
 *
 * 'title-tag' lets WordPress (and therefore Rank Math) own the <title>.
 * The theme must never print its own <title>, meta description, canonical,
 * Open Graph/Twitter tags or JSON-LD.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 120,
				'width'       => 600,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		// Page content (landing copy, FAQs, supporting sections) is authored in the
		// block editor, so the editor should preview with the theme's styles (added in 2B).
		add_theme_support( 'editor-styles' );

		load_theme_textdomain( 'medhub', MEDHUB_DIR . '/languages' );
	}
);
