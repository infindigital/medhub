<?php
/**
 * Product grid: live products from categories and/or a brand.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$slugs = array_values( array_filter( array_map( 'trim', explode( ',', $attributes['categories'] ) ) ) );
$terms = array_values( array_filter( array_map( 'medhub_get_category', $slugs ) ) );
$brand = $attributes['brand'] && taxonomy_exists( 'product_brand' ) ? get_term_by( 'slug', $attributes['brand'], 'product_brand' ) : null;

if ( ! $terms && ! $brand ) {
	return;
}

$query = array(
	'limit'   => max( 2, min( 12, (int) $attributes['limit'] ) ),
	'orderby' => 'price',
	'order'   => 'DESC',
);
if ( $terms ) {
	$query['category'] = wp_list_pluck( $terms, 'slug' );
}
if ( $brand ) {
	$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
		array(
			'taxonomy' => 'product_brand',
			'field'    => 'term_id',
			'terms'    => array( $brand->term_id ),
		),
	);
}

$products = medhub_get_products( $query );
usort( $products, static fn( $a, $b ) => (int) $b->is_in_stock() <=> (int) $a->is_in_stock() );

if ( ! $products ) {
	return;
}

$link = $terms ? get_term_link( $terms[0] ) : get_term_link( $brand );
$uid  = wp_unique_id( 'products-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section products-block' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
	<div class="container">
		<?php
		medhub_section_header(
			array(
				'eyebrow'    => $attributes['eyebrow'],
				'heading'    => $attributes['heading'] ? $attributes['heading'] : ( $terms ? $terms[0]->name : $brand->name ),
				'lede'       => $attributes['lead'],
				'id'         => $uid,
				'link_label' => $attributes['linkLabel'],
				'link_url'   => is_wp_error( $link ) ? '' : $link,
			)
		);
		?>
		<ul class="product-grid" data-reveal-group>
			<?php foreach ( $products as $product ) : ?>
				<li><?php medhub_product_card( $product, array( 'context' => 'grid', 'cta' => 'view' ) ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
