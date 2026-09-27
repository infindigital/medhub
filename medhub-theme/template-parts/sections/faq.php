<?php
/**
 * FAQ wrapper. Questions/answers are core Details blocks (editable in WordPress).
 * Schema: see inc/seo/faq-schema.php.
 *
 * Template part: get_template_part( 'template-parts/sections/faq', null, $args ).
 *
 * @package MedHub
 *
 * @var array $args Section settings (defaults below).
 *
 * @var array  $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$attributes = wp_parse_args(
	$args ?? array(),
	array(
		'eyebrow' => '',
		'heading' => '',
		'lead' => '',
		'schema' => true,
		'content' => '',
	)
);
$content    = (string) $attributes['content'];

if ( '' === trim( $content ) ) {
	return;
}

$uid = wp_unique_id( 'faq-' );
?>
<section <?php echo 'class="' . esc_attr( 'section faq' ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
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
