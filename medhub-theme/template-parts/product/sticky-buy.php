<?php
/**
 * Mobile sticky buy bar. Shown by JS only while the main buy button is off-screen.
 * Its button triggers the real WooCommerce form (no separate cart logic).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'];
$quote   = medhub_is_quote_only( $product );

if ( ! $quote && ! ( $product->is_purchasable() && $product->is_in_stock() ) ) {
	return;
}
?>
<div class="sticky-buy" data-sticky-buy hidden>
	<div class="sticky-buy__info">
		<span class="sticky-buy__name"><?php echo esc_html( $product->get_name() ); ?></span>
		<?php echo medhub_price_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<button class="btn btn--primary btn--md" type="button" data-sticky-buy-go>
		<span><?php echo $quote ? esc_html__( 'Request a quote', 'medhub' ) : esc_html( $product->single_add_to_cart_text() ); ?></span>
	</button>
</div>
