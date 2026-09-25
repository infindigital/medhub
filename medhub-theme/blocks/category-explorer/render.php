<?php
/**
 * Category explorer: department tiles (bento on desktop, swipe row on mobile).
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$keys  = array_filter( array_map( 'trim', explode( ',', $attributes['departments'] ) ) );
$depts = array_values( array_filter( array_map( 'medhub_get_department', $keys ) ) );

if ( ! $depts ) {
	return;
}

$heading_id = wp_unique_id( 'explorer-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section explorer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="container">
		<?php
		medhub_section_header(
			array(
				'eyebrow' => $attributes['eyebrow'],
				'heading' => $attributes['heading'] ? $attributes['heading'] : __( 'Shop by department', 'medhub' ),
				'lede'    => $attributes['lead'],
				'id'      => $heading_id,
			)
		);
		?>
		<ul class="explorer__grid" data-reveal-group>
			<?php foreach ( $depts as $i => $dept ) : ?>
				<li class="explorer__cell explorer__cell--<?php echo (int) $i; ?>">
					<a class="tile<?php echo 'rental' === $dept['key'] ? ' tile--dark' : ''; ?>" href="<?php echo esc_url( $dept['url'] ); ?>">
						<div class="tile__text">
							<h3 class="tile__title"><?php echo esc_html( $dept['label'] ); ?></h3>
							<?php if ( $dept['tagline'] ) : ?>
								<span class="tile__tagline"><?php echo esc_html( $dept['tagline'] ); ?></span>
							<?php endif; ?>
						</div>
						<div class="tile__foot">
							<span class="tile__count"><span data-count-to="<?php echo (int) $dept['count']; ?>"><?php echo esc_html( number_format_i18n( $dept['count'] ) ); ?></span> <?php echo esc_html( _n( 'product', 'products', $dept['count'], 'medhub' ) ); ?></span>
							<span class="tile__arrow"><?php echo medhub_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						</div>
						<?php
						if ( $dept['visual'] ) {
							echo $dept['visual']->get_image( // phpcs:ignore WordPress.Security.EscapeOutput
								'woocommerce_thumbnail',
								array(
									'class'   => 'tile__img',
									'alt'     => '',
									'loading' => 'lazy',
									'sizes'   => '(min-width: 1024px) 22vw, 50vw',
								)
							);
						}
						?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
