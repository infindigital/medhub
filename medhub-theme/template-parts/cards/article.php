<?php
/**
 * Article card.
 *
 * $args: post (WP_Post), lead (bool – large variant), heading_level (int, default 3)
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$post = $args['post'] ?? null;
if ( ! $post instanceof WP_Post ) {
	return;
}

$lead     = ! empty( $args['lead'] );
$level    = (int) ( $args['heading_level'] ?? 3 );
$category = get_the_category( $post->ID );
$minutes  = max( 1, (int) round( str_word_count( wp_strip_all_tags( $post->post_content ) ) / 220 ) );
?>
<article class="acard<?php echo $lead ? ' acard--lead' : ''; ?>">
	<?php if ( has_post_thumbnail( $post ) ) : ?>
		<div class="acard__media">
			<?php
			echo get_the_post_thumbnail( // phpcs:ignore WordPress.Security.EscapeOutput
				$post,
				$lead ? 'large' : 'medium_large',
				array(
					'class'   => 'acard__img',
					'loading' => 'lazy',
					'alt'     => '',
					'sizes'   => $lead ? '(min-width: 1024px) 60vw, 100vw' : '(min-width: 1024px) 30vw, 100vw',
				)
			);
			?>
		</div>
	<?php endif; ?>
	<div class="acard__body">
		<p class="acard__meta">
			<?php if ( $category ) : ?>
				<span><?php echo esc_html( $category[0]->name ); ?></span>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time>
		</p>
		<h<?php echo (int) $level; ?> class="acard__title"><a class="acard__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h<?php echo (int) $level; ?>>
		<?php if ( $lead && has_excerpt( $post ) ) : ?>
			<p class="acard__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 26 ) ); ?></p>
		<?php endif; ?>
		<span class="acard__cta" aria-hidden="true"><?php esc_html_e( 'Read guide', 'medhub' ); ?><?php echo medhub_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	</div>
</article>
