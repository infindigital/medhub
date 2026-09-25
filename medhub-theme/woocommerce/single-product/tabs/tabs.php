<?php
/**
 * Product tabs as an accessible accordion (native <details>, first one open).
 *
 * Override of WooCommerce templates/single-product/tabs/tabs.php (@version 9.8.0).
 * Uses the same `woocommerce_product_tabs` filter and tab callbacks, so plugin tabs
 * still appear; only the markup changes.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( empty( $product_tabs ) ) {
	return;
}
?>
<div class="product-tabs woocommerce-tabs">
	<?php $medhub_first = true; ?>
	<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
		<details class="product-tabs__item woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?>" id="tab-<?php echo esc_attr( $key ); ?>"<?php echo $medhub_first ? ' open' : ''; ?>>
			<summary class="product-tabs__title">
				<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
			</summary>
			<div class="product-tabs__panel prose">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					call_user_func( $product_tab['callback'], $key, $product_tab );
				}
				?>
			</div>
		</details>
		<?php $medhub_first = false; ?>
	<?php endforeach; ?>

	<?php do_action( 'woocommerce_product_after_tabs' ); ?>
</div>
