<?php
/**
 * Logo: the WordPress custom logo (Appearance › Customize), else a text wordmark.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$medhub_logo_id = (int) get_theme_mod( 'custom_logo' );
$medhub_name    = get_bloginfo( 'name' );

echo '<a class="logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';

if ( $medhub_logo_id ) {
	echo wp_get_attachment_image(
		$medhub_logo_id,
		'medium',
		false,
		array(
			'class'         => 'logo__img',
			'alt'           => $medhub_name,
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'sizes'         => '160px',
		)
	);
} else {
	echo '<span class="logo__text">' . esc_html( $medhub_name ) . '</span>';
}

echo '<span class="screen-reader-text">' . esc_html__( 'Home', 'medhub' ) . '</span></a>';
