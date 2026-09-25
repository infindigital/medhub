<?php
/**
 * Link cards: descriptive internal links to categories, brands, pages, posts or products.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$cards = array_values( array_filter( array_map( 'medhub_resolve_link_card', preg_split( '/\r\n|\n/', (string) $attributes['items'] ) ) ) );

if ( ! $cards ) {
	return;
}

$uid = wp_unique_id( 'links-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section link-cards' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $attributes['heading'] ? ' aria-labelledby="' . esc_attr( $uid ) . '"' : ''; ?>>
	<div class="container">
		<?php
		medhub_section_header(
			array(
				'eyebrow' => $attributes['eyebrow'],
				'heading' => $attributes['heading'],
				'lede'    => $attributes['lead'],
				'id'      => $uid,
			)
		);
		?>
		<ul class="link-cards__grid" data-reveal-group>
			<?php foreach ( $cards as $card ) : ?>
				<li>
					<a class="link-card link-card--<?php echo esc_attr( $card['type'] ); ?>" href="<?php echo esc_url( $card['url'] ); ?>">
						<?php if ( $card['image'] ) : ?>
							<span class="link-card__media"><?php echo $card['image']; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<?php endif; ?>
						<span class="link-card__body">
							<span class="link-card__kicker"><?php echo esc_html( $card['kicker'] ); ?></span>
							<span class="link-card__title"><?php echo esc_html( $card['label'] ); ?></span>
						</span>
						<span class="link-card__arrow"><?php echo medhub_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
