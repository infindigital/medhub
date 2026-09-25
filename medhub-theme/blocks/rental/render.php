<?php
/**
 * Rental spotlight: live products from the rental category in a swipeable rail.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$term = medhub_get_category( $attributes['category'] );
if ( ! $term ) {
	return;
}

$products = medhub_get_products(
	array(
		'category' => array( $term->slug ),
		'limit'    => max( 3, min( 12, (int) $attributes['limit'] ) ),
		'orderby'  => 'menu_order',
		'order'    => 'ASC',
	)
);

// In-stock rentals first, keeping the store's order otherwise.
usort( $products, static fn( $a, $b ) => (int) $b->is_in_stock() <=> (int) $a->is_in_stock() );

if ( ! $products ) {
	return;
}

$uid = wp_unique_id( 'rental-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section rental' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
	<div class="rental__inner container">
		<div class="rental__intro">
			<?php
			medhub_section_header(
				array(
					'eyebrow' => $attributes['eyebrow'],
					'heading' => $attributes['heading'] ? $attributes['heading'] : $term->name,
					'lede'    => $attributes['lead'],
					'id'      => $uid,
				)
			);
			?>
			<p class="rental__count"><span data-count-to="<?php echo (int) $term->count; ?>"><?php echo esc_html( number_format_i18n( $term->count ) ); ?></span> <?php esc_html_e( 'items available to rent by the month', 'medhub' ); ?></p>
			<?php
			echo medhub_button( // phpcs:ignore WordPress.Security.EscapeOutput
				array(
					'label'   => $attributes['ctaLabel'] ? $attributes['ctaLabel'] : __( 'See all rental equipment', 'medhub' ),
					'url'     => get_term_link( $term ),
					'variant' => 'accent',
					'size'    => 'lg',
				)
			);
			?>
		</div>

		<div class="rail" data-rail>
			<ul class="rail__track" data-rail-track>
				<?php foreach ( $products as $product ) : ?>
					<li class="rail__item"><?php medhub_product_card( $product, array( 'context' => 'rail', 'cta' => 'view', 'sizes' => '(min-width: 1024px) 280px, 70vw' ) ); ?></li>
				<?php endforeach; ?>
			</ul>
			<div class="rail__controls">
				<button class="icon-btn icon-btn--dark" type="button" data-rail-prev><?php echo medhub_icon( 'arrow', '', 'icon--flip' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php esc_html_e( 'Previous items', 'medhub' ); ?></span></button>
				<button class="icon-btn icon-btn--dark" type="button" data-rail-next><?php echo medhub_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php esc_html_e( 'Next items', 'medhub' ); ?></span></button>
			</div>
		</div>
	</div>
</section>
