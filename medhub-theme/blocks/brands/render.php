<?php
/**
 * Brands wall. Uses typographic wordmarks for a consistent look, because the brand
 * logo images currently on the site vary widely in size and quality.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'product_brand' ) ) {
	return;
}

$brands = get_terms(
	array(
		'taxonomy'   => 'product_brand',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => max( 6, min( 40, (int) $attributes['limit'] ) ),
	)
);

if ( ! $brands || is_wp_error( $brands ) ) {
	return;
}

$uid = wp_unique_id( 'brands-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section brands' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
	<div class="container">
		<?php
		medhub_section_header(
			array(
				'eyebrow' => $attributes['eyebrow'],
				'heading' => $attributes['heading'] ? $attributes['heading'] : __( 'Brands', 'medhub' ),
				'lede'    => $attributes['lead'],
				'id'      => $uid,
			)
		);
		?>
		<ul class="brands__grid" data-reveal-group>
			<?php foreach ( $brands as $brand ) : ?>
				<li>
					<a class="brand-tile" href="<?php echo esc_url( get_term_link( $brand ) ); ?>">
						<span class="brand-tile__name"><?php echo esc_html( $brand->name ); ?></span>
						<span class="brand-tile__count"><?php echo esc_html( medhub_count_label( (int) $brand->count ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
