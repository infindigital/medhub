<?php
/**
 * Bento grid wrapper. Cells are the inner blocks (editable copy in WordPress).
 *
 * @package MedHub
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Rendered inner blocks.
 */

defined( 'ABSPATH' ) || exit;

if ( '' === trim( $content ) ) {
	return;
}

$uid = wp_unique_id( 'bento-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section bento-section' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $attributes['heading'] ? ' aria-labelledby="' . esc_attr( $uid ) . '"' : ''; ?>>
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
		<div class="bento" data-reveal-group>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- rendered blocks. ?>
		</div>
	</div>
</section>
