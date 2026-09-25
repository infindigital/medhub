<?php
/**
 * FAQ wrapper. Questions/answers are core Details blocks (editable in WordPress).
 * Schema: see inc/seo/faq-schema.php.
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

$uid = wp_unique_id( 'faq-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section faq' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
	<div class="faq__layout container">
		<div class="faq__head">
			<?php
			medhub_section_header(
				array(
					'eyebrow' => $attributes['eyebrow'],
					'heading' => $attributes['heading'] ? $attributes['heading'] : __( 'Frequently asked questions', 'medhub' ),
					'lede'    => $attributes['lead'],
					'id'      => $uid,
				)
			);
			?>
		</div>
		<div class="faq__list">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- rendered blocks. ?>
		</div>
	</div>
</section>
