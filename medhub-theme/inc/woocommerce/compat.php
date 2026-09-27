<?php
/**
 * Store presentation carried over from the previous theme.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Currency label: the Elessi child theme showed "AED" instead of WooCommerce's
 * default "د.إ". Kept so prices look the same after the theme switch.
 */
add_filter(
	'woocommerce_currency_symbol',
	static fn( $symbol, $currency ) => 'AED' === $currency ? 'AED' : $symbol,
	10,
	2
);
