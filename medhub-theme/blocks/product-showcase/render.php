<?php
/**
 * Product showcase: tabbed live product grids.
 *
 * Tabs: featured (WooCommerce "featured" flag), sale (on sale now), new (latest).
 * Empty tabs are dropped. In-stock products are listed first.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$limit  = max( 4, min( 12, (int) $attributes['limit'] ) );
$labels = array(
	'featured' => __( 'Featured', 'medhub' ),
	'sale'     => __( 'On sale', 'medhub' ),
	'new'      => __( 'New in', 'medhub' ),
);

$tabs = array();
foreach ( array_filter( array_map( 'trim', explode( ',', $attributes['tabs'] ) ) ) as $key ) {
	$query = array(
		'limit'        => $limit,
		'stock_status' => 'instock',
	);

	switch ( $key ) {
		case 'featured':
			$query['featured'] = true;
			break;
		case 'sale':
			$query['include'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			break;
		case 'new':
			$query['orderby'] = 'date';
			$query['order']   = 'DESC';
			break;
		default:
			continue 2;
	}

	$products = medhub_get_products( $query );
	if ( $products ) {
		$tabs[ $key ] = $products;
	}
}

if ( ! $tabs ) {
	return;
}

$uid        = wp_unique_id( 'showcase-' );
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
$first      = array_key_first( $tabs );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section showcase' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>-title">
	<div class="container">
		<div class="showcase__head">
			<?php
			medhub_section_header(
				array(
					'eyebrow'    => $attributes['eyebrow'],
					'heading'    => $attributes['heading'] ? $attributes['heading'] : __( 'Featured equipment', 'medhub' ),
					'lede'       => $attributes['lead'],
					'id'         => $uid . '-title',
					'link_label' => $attributes['linkLabel'],
					'link_url'   => $shop_url,
				)
			);
			?>
			<?php if ( count( $tabs ) > 1 ) : ?>
				<div class="tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Product selection', 'medhub' ); ?>" data-tablist>
					<?php foreach ( $tabs as $key => $products ) : ?>
						<button class="tabs__tab" role="tab" type="button" id="<?php echo esc_attr( "$uid-tab-$key" ); ?>" aria-controls="<?php echo esc_attr( "$uid-panel-$key" ); ?>" aria-selected="<?php echo $key === $first ? 'true' : 'false'; ?>"<?php echo $key === $first ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( $labels[ $key ] ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div data-tabs>
			<?php foreach ( $tabs as $key => $products ) : ?>
				<div class="tabs__panel" role="tabpanel" id="<?php echo esc_attr( "$uid-panel-$key" ); ?>" aria-labelledby="<?php echo esc_attr( "$uid-tab-$key" ); ?>" tabindex="0"<?php echo $key === $first ? '' : ' hidden'; ?>>
					<ul class="product-grid">
						<?php foreach ( $products as $product ) : ?>
							<li><?php medhub_product_card( $product, array( 'context' => 'grid', 'cta' => 'view' ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
