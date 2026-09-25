<?php
/**
 * Front-end cleanup that does not affect SEO output.
 *
 * Nothing here removes tags Rank Math relies on (title, canonical, robots, JSON-LD).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

// Emoji detection script and styles (~15 KB) – modern systems render emoji natively.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

// WordPress version generator tag (no SEO value, minor information disclosure).
remove_action( 'wp_head', 'wp_generator' );

// Classic theme "classic-theme-styles" (button/file block defaults we restyle anyway).
add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_dequeue_style( 'classic-theme-styles' );
	},
	20
);
