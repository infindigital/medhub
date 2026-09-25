<?php
/**
 * Product in a shop loop.
 *
 * Override of WooCommerce templates/content-product.php (@version 9.4.0).
 * The before/after loop-item hooks still fire (plugins attach there); WooCommerce's
 * default link/title/price/button callbacks are unhooked and the theme card is used.
 * In the shop experience the card offers "Add to cart" where purchasable (D7).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'product-grid__item', $product ); ?>>
	<?php
	do_action( 'woocommerce_before_shop_loop_item' );
	medhub_product_card(
		$product,
		array(
			'context' => 'grid',
			'cta'     => 'cart',
			'sizes'   => '(min-width: 1280px) 280px, (min-width: 768px) 30vw, 46vw',
			'eager'   => wc_get_loop_prop( 'loop' ) < 4,
		)
	);
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
