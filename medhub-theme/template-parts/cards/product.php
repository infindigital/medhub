<?php
/**
 * Product card.
 *
 * $args:
 *   product       WC_Product (required)
 *   context       'rail' | 'grid' | 'compact'           (default 'grid')
 *   cta           'view' | 'cart'                        (default 'view'; D7: 'view' outside the shop)
 *   heading_level int                                    (default 3)
 *   sizes         string  <img sizes> hint
 *   eager         bool    load the image eagerly (above the fold only)
 *
 * All data is read live from WooCommerce. Nothing is invented: category/brand,
 * price and stock rows are omitted when WooCommerce has no value.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? null;
if ( ! $product instanceof WC_Product ) {
	return;
}

$context   = $args['context'] ?? 'grid';
$cta       = $args['cta'] ?? 'view';
$level     = (int) ( $args['heading_level'] ?? 3 );
$permalink = $product->get_permalink();
$category  = medhub_primary_category( $product );
$brand     = medhub_product_brand( $product );
$stock     = medhub_stock_state( $product );
$is_rental = medhub_is_rental( $product );
$quote     = medhub_is_quote_only( $product );

$image = $product->get_image(
	'woocommerce_thumbnail',
	array(
		'class'    => 'pcard__img',
		'loading'  => empty( $args['eager'] ) ? 'lazy' : 'eager',
		'decoding' => 'async',
		'sizes'    => $args['sizes'] ?? '(min-width: 1280px) 300px, (min-width: 768px) 30vw, 70vw',
	)
);

$meta = array_filter( array( $category ? $category->name : '', $brand ? $brand->name : '' ) );
?>
<article class="pcard pcard--<?php echo esc_attr( $context ); ?><?php echo $product->is_in_stock() ? '' : ' is-out'; ?>">
	<div class="pcard__media">
		<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput -- WooCommerce image markup. ?>
		<div class="pcard__badges">
			<?php if ( $product->is_on_sale() && ! $quote ) : ?>
				<span class="badge badge--sale"><?php esc_html_e( 'Sale', 'medhub' ); ?></span>
			<?php endif; ?>
			<?php if ( $is_rental ) : ?>
				<span class="badge badge--rent"><?php esc_html_e( 'Rental', 'medhub' ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<div class="pcard__body">
		<?php if ( $meta ) : ?>
			<p class="pcard__meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p>
		<?php endif; ?>

		<h<?php echo (int) $level; ?> class="pcard__title">
			<a class="pcard__link" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h<?php echo (int) $level; ?>>

		<div class="pcard__foot">
			<?php echo medhub_price_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from wc_price(). ?>
			<?php if ( $stock ) : ?>
				<span class="stock stock--<?php echo esc_attr( $stock['state'] ); ?>"><?php echo esc_html( $stock['label'] ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( 'cart' === $cta && ! $quote && $product->is_purchasable() && $product->is_in_stock() && $product->is_type( 'simple' ) ) : ?>
			<div class="pcard__action">
				<?php
				// WooCommerce's loop button reads the global product; restore it afterwards.
				$medhub_prev_product = $GLOBALS['product'] ?? null;
				$GLOBALS['product']  = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				woocommerce_template_loop_add_to_cart( array( 'class' => 'btn btn--primary btn--sm add_to_cart_button ajax_add_to_cart' ) );
				$GLOBALS['product'] = $medhub_prev_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				?>
			</div>
		<?php else : ?>
			<span class="pcard__cta" aria-hidden="true">
				<?php
				if ( $quote ) {
					esc_html_e( 'Ask for a quote', 'medhub' );
				} elseif ( $is_rental ) {
					esc_html_e( 'View rental', 'medhub' );
				} else {
					esc_html_e( 'View product', 'medhub' );
				}
				echo medhub_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</span>
		<?php endif; ?>
	</div>
</article>
