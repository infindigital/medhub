<?php
/**
 * Bento grid wrapper. Cells are the inner blocks (editable copy in WordPress).
 *
 * Template part: get_template_part( 'template-parts/sections/bento', null, $args ).
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
		'layout' => 'editorial',
		'content' => '',
	)
);
$content    = (string) $attributes['content'];

if ( '' === trim( $content ) ) {
	return;
}

$uid = wp_unique_id( 'bento-' );
?>
<section <?php echo 'class="' . esc_attr( 'section bento-section' ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $attributes['heading'] ? ' aria-labelledby="' . esc_attr( $uid ) . '"' : ''; ?>>
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
		<div class="bento<?php echo 'cards' === $attributes['layout'] ? ' bento--cards' : ''; ?>" data-reveal-group>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- rendered blocks. ?>
		</div>
	</div>
</section>
