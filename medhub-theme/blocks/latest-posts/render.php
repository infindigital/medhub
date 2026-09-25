<?php
/**
 * Latest guides (blog posts).
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$posts = get_posts(
	array(
		'numberposts'         => max( 1, min( 6, (int) $attributes['count'] ) ),
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'suppress_filters'    => false,
	)
);

if ( ! $posts ) {
	return;
}

$uid      = wp_unique_id( 'posts-' );
$blog_id  = (int) get_option( 'page_for_posts' );
$blog_url = $blog_id ? get_permalink( $blog_id ) : '';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section journal' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
	<div class="container">
		<?php
		medhub_section_header(
			array(
				'eyebrow'    => $attributes['eyebrow'],
				'heading'    => $attributes['heading'] ? $attributes['heading'] : __( 'Guides', 'medhub' ),
				'lede'       => $attributes['lead'],
				'id'         => $uid,
				'link_label' => $blog_url ? __( 'All guides', 'medhub' ) : '',
				'link_url'   => $blog_url,
			)
		);
		?>
		<ul class="journal__grid" data-reveal-group>
			<?php foreach ( $posts as $i => $post ) : ?>
				<li class="journal__item<?php echo 0 === $i ? ' journal__item--lead' : ''; ?>">
					<?php get_template_part( 'template-parts/cards/article', null, array( 'post' => $post, 'lead' => 0 === $i ) ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
