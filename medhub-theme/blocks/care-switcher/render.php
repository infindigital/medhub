<?php
/**
 * Care switcher: category tabs with the category's own intro and three live products.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$panels = array();
foreach ( array_filter( array_map( 'trim', explode( ',', $attributes['categories'] ) ) ) as $slug ) {
	$term = medhub_get_category( $slug );
	if ( ! $term ) {
		continue;
	}

	$products = medhub_get_products(
		array(
			'category' => array( $term->slug ),
			'limit'    => 3,
			'orderby'  => 'price',
			'order'    => 'DESC',
		)
	);
	usort( $products, static fn( $a, $b ) => (int) $b->is_in_stock() <=> (int) $a->is_in_stock() );

	if ( $products ) {
		$panels[] = array(
			'term'     => $term,
			'intro'    => wp_trim_words( wp_strip_all_tags( $term->description ), 28 ),
			'products' => $products,
		);
	}
}

if ( ! $panels ) {
	return;
}

$uid = wp_unique_id( 'care-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section care' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>-title">
	<div class="container">
		<?php
		medhub_section_header(
			array(
				'eyebrow' => $attributes['eyebrow'],
				'heading' => $attributes['heading'] ? $attributes['heading'] : __( 'Explore by equipment type', 'medhub' ),
				'lede'    => $attributes['lead'],
				'id'      => $uid . '-title',
			)
		);
		?>
		<div class="care__layout">
			<div class="care__tabs" role="tablist" aria-orientation="vertical" aria-label="<?php esc_attr_e( 'Equipment type', 'medhub' ); ?>" data-tablist>
				<?php foreach ( $panels as $i => $panel ) : ?>
					<button class="care__tab" role="tab" type="button" id="<?php echo esc_attr( "$uid-tab-$i" ); ?>" aria-controls="<?php echo esc_attr( "$uid-panel-$i" ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"<?php echo 0 === $i ? '' : ' tabindex="-1"'; ?>>
						<span class="care__tab-name"><?php echo esc_html( $panel['term']->name ); ?></span>
						<span class="care__tab-count"><?php echo esc_html( number_format_i18n( $panel['term']->count ) ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="care__panels">
				<?php foreach ( $panels as $i => $panel ) : ?>
					<div class="care__panel" role="tabpanel" id="<?php echo esc_attr( "$uid-panel-$i" ); ?>" aria-labelledby="<?php echo esc_attr( "$uid-tab-$i" ); ?>" tabindex="0"<?php echo 0 === $i ? '' : ' hidden'; ?>>
						<div class="care__panel-head">
							<h3 class="care__panel-title"><?php echo esc_html( $panel['term']->name ); ?></h3>
							<?php if ( $panel['intro'] ) : ?>
								<p class="care__panel-intro"><?php echo esc_html( $panel['intro'] ); ?></p>
							<?php endif; ?>
							<?php
							echo medhub_button( // phpcs:ignore WordPress.Security.EscapeOutput
								array(
									/* translators: %s: category name */
									'label'   => sprintf( __( 'See all %s', 'medhub' ), $panel['term']->name ),
									'url'     => get_term_link( $panel['term'] ),
									'variant' => 'ghost',
								)
							);
							?>
						</div>
						<ul class="care__products">
							<?php foreach ( $panel['products'] as $product ) : ?>
								<li><?php medhub_product_card( $product, array( 'context' => 'compact', 'heading_level' => 4, 'sizes' => '(min-width: 1024px) 220px, 45vw' ) ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
