<?php
/**
 * Mega menu panel for one navigation item.
 *
 * $args['item'] = resolved nav item (see medhub_navigation()).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$item    = $args['item'];
$depts   = $item['departments'];
$compact = ! empty( $item['compact'] );
$guides  = array_merge( ...array_map( static fn( $d ) => $d['guides'], $depts ) );
$single  = 1 === count( $depts );
$feature = ! empty( $item['feature'] ) ? $depts[0]['visual'] : null;
?>
<div class="mega" id="<?php echo esc_attr( $item['id'] ); ?>" data-mega hidden>
	<div class="mega__inner container">
		<div class="mega__cols<?php echo $single ? ' mega__cols--single' : ''; ?>">
			<?php foreach ( $depts as $dept ) : ?>
				<?php
				$terms = $compact ? array_slice( $dept['terms'], 0, 4 ) : $dept['terms'];
				// A single-department panel splits its categories into two columns.
				$chunks = ( $single && count( $terms ) > 4 ) ? array_chunk( $terms, (int) ceil( count( $terms ) / 2 ) ) : array( $terms );
				?>
				<?php foreach ( $chunks as $i => $chunk ) : ?>
					<div class="mega__col">
						<?php if ( 0 === $i ) : ?>
							<p class="mega__heading"><a href="<?php echo esc_url( $dept['url'] ); ?>"><?php echo esc_html( $dept['label'] ); ?></a></p>
						<?php else : ?>
							<p class="mega__heading" aria-hidden="true">&nbsp;</p>
						<?php endif; ?>
						<ul class="mega__list">
							<?php foreach ( $chunk as $term ) : ?>
								<li>
									<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
										<span><?php echo esc_html( $term->name ); ?></span>
										<small><?php echo esc_html( number_format_i18n( $term->count ) ); ?></small>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>

			<?php if ( $guides ) : ?>
				<div class="mega__col">
					<p class="mega__heading"><?php esc_html_e( 'Guides', 'medhub' ); ?></p>
					<ul class="mega__list mega__list--plain">
						<?php foreach ( $guides as $guide ) : ?>
							<li><a href="<?php echo esc_url( $guide['url'] ); ?>"><?php echo esc_html( $guide['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

		<aside class="mega__aside">
			<?php if ( $feature ) : ?>
				<a class="mega-feature" href="<?php echo esc_url( $feature->get_permalink() ); ?>">
					<span class="mega-feature__media">
						<?php echo $feature->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'sizes' => '140px', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</span>
					<span class="mega-feature__body">
						<span class="eyebrow"><?php echo esc_html( medhub_primary_category( $feature )->name ?? '' ); ?></span>
						<span class="mega-feature__name"><?php echo esc_html( $feature->get_name() ); ?></span>
						<?php echo medhub_price_html( $feature ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</span>
				</a>
			<?php endif; ?>

			<?php
			$hub_label = $single
				/* translators: %s: department name */
				? sprintf( __( 'Explore %s', 'medhub' ), $depts[0]['label'] )
				: '';
			if ( $hub_label ) {
				echo medhub_button( // phpcs:ignore WordPress.Security.EscapeOutput
					array(
						'label'   => $hub_label,
						'url'     => $depts[0]['url'],
						'variant' => 'secondary',
						'size'    => 'sm',
					)
				);
			}
			if ( ! empty( $item['all_link'] ) && function_exists( 'wc_get_page_permalink' ) ) {
				echo medhub_button( // phpcs:ignore WordPress.Security.EscapeOutput
					array(
						'label'   => __( 'Shop all equipment', 'medhub' ),
						'url'     => wc_get_page_permalink( 'shop' ),
						'variant' => 'primary',
						'size'    => 'sm',
					)
				);
			}
			?>
		</aside>
	</div>
</div>
