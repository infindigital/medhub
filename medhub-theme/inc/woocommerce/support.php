<?php
/**
 * WooCommerce integration: presentation only.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce's own stylesheets are kept on shop, cart, checkout and account pages
 * (restyled in steps 2E–2G) and skipped everywhere else, where the theme renders
 * its own product markup.
 */
add_filter(
	'woocommerce_enqueue_styles',
	static function ( $styles ) {
		if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
			return $styles;
		}
		return array();
	}
);

/**
 * Header cart count, refreshed through WooCommerce's standard cart fragments
 * whenever WooCommerce's cart scripts are present on a page.
 */
function medhub_cart_count_html(): string {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

	return sprintf(
		'<span class="cart-count" data-count="%1$d"><span class="screen-reader-text">%2$s</span><span aria-hidden="true">%1$d</span></span>',
		$count,
		/* translators: %d: number of items in cart */
		esc_html( sprintf( _n( '%d item in cart', '%d items in cart', $count, 'medhub' ), $count ) )
	);
}

add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( $fragments ) {
		$fragments['span.cart-count'] = medhub_cart_count_html();
		return $fragments;
	}
);
