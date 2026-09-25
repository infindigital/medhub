<?php
/**
 * Product summary / buy box (sticky on desktop).
 *
 * Purchasable products use WooCommerce's own add-to-cart template (quantity, nonce,
 * hooks – untouched). Items without a price (quote-only rentals) get enquiry actions.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$product   = $args['product'];
$category  = medhub_primary_category( $product );
$brand     = medhub_product_brand( $product );
$stock     = medhub_stock_state( $product );
$is_rental = medhub_is_rental( $product );
$quote     = medhub_is_quote_only( $product );
$delivery  = medhub_business( 'delivery' );
$payment   = medhub_business( 'payment' );
$address   = medhub_business( 'address' );
$contact   = medhub_get_page( 'contact-us-medhub' );
$whatsapp  = medhub_business( 'whatsapp' );
?>
<div class="buy-box" data-buy-box>
	<p class="buy-box__meta">
		<?php if ( $category ) : ?>
			<a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
		<?php endif; ?>
		<?php if ( $brand ) : ?>
			<span aria-hidden="true">·</span>
			<a href="<?php echo esc_url( get_term_link( $brand ) ); ?>"><?php echo esc_html( $brand->name ); ?></a>
		<?php endif; ?>
	</p>

	<h1 class="buy-box__title product_title entry-title"><?php echo esc_html( $product->get_name() ); ?></h1>

	<div class="buy-box__price">
		<?php echo medhub_price_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $stock ) : ?>
			<span class="stock stock--<?php echo esc_attr( $stock['state'] ); ?>"><?php echo esc_html( $stock['label'] ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( $is_rental && ! $quote ) : ?>
		<p class="buy-box__note"><?php esc_html_e( 'Rental price per month.', 'medhub' ); ?> <?php echo esc_html( (string) medhub_business( 'rental_terms', '' ) ); ?></p>
	<?php endif; ?>

	<?php if ( $product->get_short_description() ) : ?>
		<div class="buy-box__excerpt prose"><?php echo wp_kses_post( apply_filters( 'woocommerce_short_description', $product->get_short_description() ) ); ?></div>
	<?php endif; ?>

	<div class="buy-box__action" data-buy-action>
		<?php
		if ( ! $quote && $product->is_purchasable() ) {
			woocommerce_template_single_add_to_cart();
		} else {
			echo '<p class="buy-box__quote">' . esc_html__( 'Pricing for this item is given on request.', 'medhub' ) . '</p>';
			if ( $whatsapp ) {
				printf(
					'<a class="btn btn--whatsapp btn--lg" href="%s" target="_blank" rel="noopener">%s<span>%s</span></a>',
					esc_url( 'https://wa.me/' . rawurlencode( (string) $whatsapp ) . '?text=' . rawurlencode( sprintf( 'Hello, I would like a quote for: %s', $product->get_name() ) ) ),
					medhub_icon( 'chat' ), // phpcs:ignore WordPress.Security.EscapeOutput
					esc_html__( 'Ask on WhatsApp', 'medhub' )
				);
			}
			if ( $contact ) {
				echo medhub_button( array( 'label' => __( 'Request a quote', 'medhub' ), 'url' => add_query_arg( 'product', rawurlencode( $product->get_name() ), get_permalink( $contact ) ), 'variant' => $whatsapp ? 'secondary' : 'primary', 'size' => 'lg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
		?>
	</div>

	<?php if ( ! $quote && $contact ) : ?>
		<p class="buy-box__help">
			<?php esc_html_e( 'Not sure it is the right model?', 'medhub' ); ?>
			<a href="<?php echo esc_url( get_permalink( $contact ) ); ?>"><?php esc_html_e( 'Ask MedHub', 'medhub' ); ?></a>
		</p>
	<?php endif; ?>

	<?php if ( $delivery || $payment || $address ) : ?>
		<ul class="buy-box__trust">
			<?php if ( $delivery ) : ?>
				<?php /* translators: 1: fee, 2: timeline */ ?>
				<li><?php echo medhub_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( sprintf( __( 'UAE delivery %1$s · %2$s', 'medhub' ), $delivery['fee'], $delivery['timeline'] ) ); ?></span><?php echo medhub_business_marker( 'delivery' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
			<?php endif; ?>
			<?php if ( $payment ) : ?>
				<li><?php echo medhub_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $payment ); ?></span></li>
			<?php endif; ?>
			<?php if ( $address ) : ?>
				<?php /* translators: %s: locality */ ?>
				<li><?php echo medhub_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( sprintf( __( 'Showroom in %s', 'medhub' ), $address['locality'] ) ); ?></span></li>
			<?php endif; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $product->get_sku() ) : ?>
		<p class="buy-box__sku"><?php esc_html_e( 'SKU:', 'medhub' ); ?> <span class="sku"><?php echo esc_html( $product->get_sku() ); ?></span></p>
	<?php endif; ?>

	<?php do_action( 'woocommerce_single_product_summary' ); ?>
</div>
