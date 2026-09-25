<?php
/**
 * Filtered / sorted product listings are not indexed (they duplicate the clean
 * category URL). Canonicals stay with Rank Math, which points to the clean URL.
 *
 * Works with WordPress core robots output and with Rank Math's robots meta.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current request is a filtered/sorted product listing.
 */
function medhub_is_filtered_listing(): bool {
	if ( ! function_exists( 'is_woocommerce' ) || ! function_exists( 'medhub_is_filtered_request' ) ) {
		return false;
	}
	return ( is_shop() || is_product_taxonomy() ) && medhub_is_filtered_request();
}

add_filter(
	'wp_robots',
	static function ( $robots ) {
		if ( medhub_is_filtered_listing() ) {
			unset( $robots['index'] );
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
);

add_filter(
	'rank_math/frontend/robots',
	static function ( $robots ) {
		if ( medhub_is_filtered_listing() ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}
		return $robots;
	}
);
