<?php
/**
 * WooCommerce image sizes (decision D9).
 *
 * The live site crops woocommerce_thumbnail to 350x220, which cuts off
 * square product photos. The theme proposes square sizes, but changing sizes
 * makes WooCommerce regenerate thumbnails in the background, which writes files
 * and attachment metadata.
 *
 * Safety: on a production environment the new sizes are NOT declared and
 * background regeneration is switched off, until MEDHUB_IMAGE_SIZES_APPROVED
 * is defined as true in wp-config.php (after local testing and a planned go-live).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the theme's image sizes may be applied in this environment.
 */
function medhub_image_sizes_enabled(): bool {
	if ( 'production' !== wp_get_environment_type() ) {
		return true;
	}

	return defined( 'MEDHUB_IMAGE_SIZES_APPROVED' ) && true === MEDHUB_IMAGE_SIZES_APPROVED;
}

add_action(
	'after_setup_theme',
	static function () {
		$support = array(
			'product_grid' => array(
				'default_columns' => 3,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		);

		if ( medhub_image_sizes_enabled() ) {
			// Values are a starting point and are tuned during local testing (D9).
			$support['thumbnail_image_width']         = 600;  // Cards, 1:1 crop.
			$support['single_image_width']            = 1000; // Product gallery main image.
			$support['gallery_thumbnail_image_width'] = 160;  // Gallery thumbnails.
		}

		add_theme_support( 'woocommerce', $support );
	}
);

// Keep product thumbnails square once the sizes are enabled.
add_filter(
	'woocommerce_get_image_size_thumbnail',
	static function ( $size ) {
		if ( medhub_image_sizes_enabled() ) {
			$size['height'] = $size['width'];
			$size['crop']   = 1;
		}
		return $size;
	}
);

// Never start background regeneration on production until approved.
add_filter(
	'woocommerce_background_image_regeneration',
	static function ( $enabled ) {
		return medhub_image_sizes_enabled() ? $enabled : false;
	}
);
