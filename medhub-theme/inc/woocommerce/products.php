<?php
/**
 * Product presentation helpers (read-only).
 *
 * Rental items on MedHub are simple products in the "medical-equipment-rental"
 * category, priced per month. Some rental items have no price (enquiry only).
 * Nothing here changes product data – it only decides how to display it.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

const MEDHUB_RENTAL_CATEGORY = 'medical-equipment-rental';

/**
 * Whether a product is a rental item.
 *
 * @param WC_Product $product Product.
 */
function medhub_is_rental( WC_Product $product ): bool {
	return has_term( MEDHUB_RENTAL_CATEGORY, 'product_cat', $product->get_id() );
}

/**
 * Whether a product has no usable price (shown as "Ask for a quote").
 *
 * @param WC_Product $product Product.
 */
function medhub_is_quote_only( WC_Product $product ): bool {
	$price = $product->get_price();
	return '' === $price || (float) $price <= 0;
}

/**
 * Price markup for cards and hero chips.
 *
 * @param WC_Product $product Product.
 */
function medhub_price_html( WC_Product $product ): string {
	if ( medhub_is_quote_only( $product ) ) {
		return '<span class="price price--quote">' . esc_html__( 'Ask for a quote', 'medhub' ) . '</span>';
	}

	$current = wc_price( wc_get_price_to_display( $product ) );
	$html    = '<span class="price__now">' . $current . '</span>';

	if ( $product->is_on_sale() && $product->get_regular_price() ) {
		$html .= sprintf(
			'<del class="price__was"><span class="screen-reader-text">%s</span>%s</del>',
			esc_html__( 'Original price:', 'medhub' ),
			wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) )
		);
	}

	if ( medhub_is_rental( $product ) ) {
		$html .= '<span class="price__per">' . esc_html__( '/ month', 'medhub' ) . '</span>';
	}

	return '<span class="price">' . $html . '</span>';
}

/**
 * Availability label, or '' when WooCommerce has no stock information.
 *
 * @param WC_Product $product Product.
 * @return array{label:string,state:string}|null
 */
function medhub_stock_state( WC_Product $product ): ?array {
	if ( $product->is_on_backorder() ) {
		return array( 'label' => __( 'On backorder', 'medhub' ), 'state' => 'warn' );
	}
	if ( ! $product->is_in_stock() ) {
		return array( 'label' => __( 'Out of stock', 'medhub' ), 'state' => 'out' );
	}
	if ( medhub_is_rental( $product ) ) {
		return array( 'label' => __( 'Available to rent', 'medhub' ), 'state' => 'ok' );
	}
	return array( 'label' => __( 'In stock', 'medhub' ), 'state' => 'ok' );
}

/**
 * Most specific display category (skips the rental and uncategorized buckets when possible).
 *
 * @param WC_Product $product Product.
 */
function medhub_primary_category( WC_Product $product ): ?WP_Term {
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}

	usort( $terms, static fn( $a, $b ) => $a->count <=> $b->count ); // Smallest (most specific) first.

	foreach ( $terms as $term ) {
		if ( ! in_array( $term->slug, array( 'uncategorized' ), true ) ) {
			return $term;
		}
	}
	return $terms[0];
}

/**
 * First brand of a product, if any.
 *
 * @param WC_Product $product Product.
 */
function medhub_product_brand( WC_Product $product ): ?WP_Term {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return null;
	}
	$terms = get_the_terms( $product->get_id(), 'product_brand' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/**
 * Products for a section. Thin wrapper over wc_get_products() with safe defaults.
 *
 * @param array $args wc_get_products() args.
 * @return WC_Product[]
 */
function medhub_get_products( array $args ): array {
	$args = wp_parse_args(
		$args,
		array(
			'status'     => 'publish',
			'limit'      => 8,
			'visibility' => 'catalog',
		)
	);

	$cache_key = 'products_' . md5( wp_json_encode( $args ) );
	$ids       = wp_cache_get( $cache_key, 'medhub' );

	if ( false === $ids ) {
		$ids = wc_get_products( array_merge( $args, array( 'return' => 'ids' ) ) );
		wp_cache_set( $cache_key, $ids, 'medhub' );
	}

	return array_values( array_filter( array_map( 'wc_get_product', $ids ) ) );
}

/**
 * Product by slug.
 *
 * @param string $slug Product slug.
 */
function medhub_get_product_by_slug( string $slug ): ?WC_Product {
	if ( '' === $slug ) {
		return null;
	}
	$post    = get_page_by_path( $slug, OBJECT, 'product' );
	$product = $post ? wc_get_product( $post ) : null;
	return $product instanceof WC_Product ? $product : null;
}

/**
 * Render a product card.
 *
 * @param WC_Product $product Product.
 * @param array      $args    context (rail|grid|compact), cta (view|cart), heading_level.
 */
function medhub_product_card( WC_Product $product, array $args = array() ): void {
	get_template_part( 'template-parts/cards/product', null, array_merge( array( 'product' => $product ), $args ) );
}
