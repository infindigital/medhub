<?php
/**
 * Trust bar. Every item is derived from config/business.php (status-gated) or
 * from live WooCommerce data; nothing is typed into the block.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$items    = array();
$address  = medhub_business( 'address' );
$delivery = medhub_business( 'delivery' );
$payment  = medhub_business( 'payment' );

if ( $address ) {
	/* translators: %s: locality, e.g. "Deira, Dubai" */
	$items[] = array( 'pin', sprintf( __( 'Showroom in %s', 'medhub' ), $address['locality'] ), medhub_business_marker( 'address' ) );
}
if ( $delivery ) {
	/* translators: %s: delivery fee */
	$items[] = array( 'truck', sprintf( __( 'UAE delivery · %s', 'medhub' ), $delivery['fee'] ), medhub_business_marker( 'delivery' ) );
}
if ( medhub_get_category( MEDHUB_RENTAL_CATEGORY ) ) {
	$items[] = array( 'swap', __( 'Buy or rent monthly', 'medhub' ), '' );
}
if ( $payment ) {
	$items[] = array( 'lock', $payment, medhub_business_marker( 'payment' ) );
}

if ( ! $items ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'trust' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<ul class="trust__list container">
		<?php foreach ( $items as $item ) : ?>
			<li class="trust__item"><?php echo medhub_icon( $item[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $item[1] ); ?></span><?php echo $item[2]; // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
		<?php endforeach; ?>
	</ul>
</div>
