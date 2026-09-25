<?php
/**
 * Single product: presentation hooks (read-only).
 *
 * WooCommerce's default output callbacks are unhooked and re-rendered by the theme's
 * layout (content-single-product.php), but every hook still fires:
 *   woocommerce_before_single_product, …_before_single_product_summary,
 *   …_single_product_summary, …_after_single_product_summary, …_after_single_product
 * The add-to-cart form itself is WooCommerce's own template (quantity, nonce, hooks).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	}
);

/**
 * Tabs: keep WooCommerce's (description, additional information, reviews) and any
 * plugin tabs, and add "Delivery & returns" built from confirmed business details.
 */
add_filter(
	'woocommerce_product_tabs',
	static function ( $tabs ) {
		if ( isset( $tabs['description'] ) ) {
			$tabs['description']['title'] = __( 'Product details', 'medhub' );
		}

		if ( medhub_business( 'delivery' ) || medhub_get_page( 'delivery-policy' ) || medhub_get_page( 'refund-policy' ) ) {
			$tabs['medhub_delivery'] = array(
				'title'    => __( 'Delivery & returns', 'medhub' ),
				'priority' => 40,
				'callback' => 'medhub_product_delivery_tab',
			);
		}

		return $tabs;
	},
	20
);

/**
 * "Delivery & returns" tab content.
 */
function medhub_product_delivery_tab(): void {
	$delivery = medhub_business( 'delivery' );
	$links    = array_filter(
		array(
			medhub_resolve_link( array( 'page' => 'delivery-policy', 'label' => __( 'Delivery policy', 'medhub' ) ) ),
			medhub_resolve_link( array( 'page' => 'refund-policy', 'label' => __( 'Refund and return policy', 'medhub' ) ) ),
			medhub_resolve_link( array( 'page' => 'cancellation-policy', 'label' => __( 'Cancellation policy', 'medhub' ) ) ),
		)
	);

	if ( $delivery ) {
		echo '<ul class="fact-list">';
		/* translators: %s: region */
		printf( '<li>%s</li>', esc_html( sprintf( __( 'Delivery within the %s.', 'medhub' ), $delivery['region'] ) ) );
		/* translators: %s: delivery fee */
		printf( '<li>%s</li>', esc_html( sprintf( __( 'Standard delivery fee: %s.', 'medhub' ), $delivery['fee'] ) ) );
		/* translators: 1: processing time, 2: delivery timeline */
		printf( '<li>%s</li>', esc_html( sprintf( __( 'Orders are processed %1$s and delivered within %2$s.', 'medhub' ), $delivery['processing'], $delivery['timeline'] ) ) );
		echo '</ul>';
		echo medhub_business_marker( 'delivery' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	if ( $links ) {
		echo '<p class="fact-links">';
		foreach ( $links as $link ) {
			printf( '<a href="%s">%s</a> ', esc_url( $link['url'] ), esc_html( $link['label'] ) );
		}
		echo '</p>';
	}
}

/**
 * Related + upsell products (live), de-duplicated.
 *
 * @param WC_Product $product Product.
 * @param int        $limit   Max products.
 * @return WC_Product[]
 */
function medhub_related_products( WC_Product $product, int $limit = 4 ): array {
	$ids = array_unique( array_merge( $product->get_upsell_ids(), wc_get_related_products( $product->get_id(), $limit * 2 ) ) );
	$ids = array_diff( $ids, array( $product->get_id() ) );

	$products = array_values( array_filter( array_map( 'wc_get_product', $ids ), static fn( $p ) => $p && $p->is_visible() ) );
	usort( $products, static fn( $a, $b ) => (int) $b->is_in_stock() <=> (int) $a->is_in_stock() );

	return array_slice( $products, 0, $limit );
}

/**
 * Categories + department guides for the "Explore more" links under a product.
 *
 * @param WC_Product $product Product.
 * @return array<int, array{label:string,url:string}>
 */
function medhub_product_explore_links( WC_Product $product ): array {
	$links = array();
	$terms = get_the_terms( $product->get_id(), 'product_cat' );

	foreach ( is_array( $terms ) ? $terms : array() as $term ) {
		if ( 'uncategorized' === $term->slug ) {
			continue;
		}
		$links[ 'cat-' . $term->term_id ] = array( 'label' => $term->name, 'url' => get_term_link( $term ) );

		foreach ( medhub_departments_config() as $key => $config ) {
			if ( in_array( $term->slug, $config['terms'], true ) ) {
				$dept = medhub_get_department( $key );
				foreach ( $dept ? $dept['guides'] : array() as $guide ) {
					$links[ 'guide-' . $guide['url'] ] = array( 'label' => $guide['label'], 'url' => $guide['url'] );
				}
			}
		}
	}

	$brand = medhub_product_brand( $product );
	if ( $brand ) {
		/* translators: %s: brand name */
		$links[ 'brand-' . $brand->term_id ] = array( 'label' => sprintf( __( 'More from %s', 'medhub' ), $brand->name ), 'url' => get_term_link( $brand ) );
	}

	return array_values( $links );
}
