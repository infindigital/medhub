<?php
/**
 * Closing call to action.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( '' === $attributes['heading'] ) {
	return;
}

$uid = wp_unique_id( 'cta-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section cta' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
	<div class="container">
		<div class="cta__panel">
			<div class="cta__copy">
				<?php if ( $attributes['eyebrow'] ) : ?>
					<p class="eyebrow"><?php echo esc_html( $attributes['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h2 class="cta__title" id="<?php echo esc_attr( $uid ); ?>"><?php echo medhub_accent_text( $attributes['heading'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php if ( $attributes['text'] ) : ?>
					<p class="cta__text"><?php echo esc_html( $attributes['text'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="cta__actions">
				<?php
				echo medhub_button( array( 'label' => $attributes['primaryLabel'], 'url' => $attributes['primaryUrl'], 'variant' => 'accent', 'size' => 'lg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo medhub_button( array( 'label' => $attributes['secondaryLabel'], 'url' => $attributes['secondaryUrl'], 'variant' => 'light', 'size' => 'lg', 'icon' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				get_template_part( 'template-parts/components/contact-actions', null, array( 'variant' => 'inline' ) );
				?>
			</div>
		</div>
	</div>
</section>
