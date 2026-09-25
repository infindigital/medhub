<?php
/**
 * Content helpers: layout detection, table of contents, reading time, related content.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a post's content is built with MedHub blocks (→ landing layout).
 *
 * @param WP_Post|null $post Post.
 */
function medhub_has_medhub_blocks( ?WP_Post $post ): bool {
	return $post instanceof WP_Post && str_contains( $post->post_content, '<!-- wp:medhub/' );
}

/**
 * Add ids to H2 headings and build a table of contents from them.
 * Only used for long prose (policies, articles); the text is not changed.
 *
 * @param string $html Rendered content.
 * @return array{html:string,toc:array<int,array{id:string,label:string}>}
 */
function medhub_prose_toc( string $html ): array {
	$toc  = array();
	$used = array();

	$html = (string) preg_replace_callback(
		'#<h2([^>]*)>(.*?)</h2>#is',
		static function ( $m ) use ( &$toc, &$used ) {
			$label = trim( wp_strip_all_tags( $m[2] ) );
			if ( '' === $label ) {
				return $m[0];
			}

			if ( preg_match( '/\sid=["\']([^"\']+)["\']/', $m[1], $existing ) ) {
				$id = $existing[1];
			} else {
				$base = sanitize_title( $label );
				$id   = $base ? $base : 'section';
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$m[1] .= ' id="' . esc_attr( $id ) . '"';
			}

			$used[ $id ] = true;
			$toc[]       = array( 'id' => $id, 'label' => $label );
			return '<h2' . $m[1] . '>' . $m[2] . '</h2>';
		},
		$html
	);

	return array( 'html' => $html, 'toc' => $toc );
}

/**
 * Estimated reading time in minutes.
 *
 * @param WP_Post $post Post.
 */
function medhub_reading_minutes( WP_Post $post ): int {
	return max( 1, (int) round( str_word_count( wp_strip_all_tags( $post->post_content ) ) / 220 ) );
}

/**
 * Department keys related to a blog post (via config/departments.php → post_categories).
 *
 * @param WP_Post $post Post.
 * @return string[]
 */
function medhub_post_departments( WP_Post $post ): array {
	$slugs = wp_list_pluck( get_the_category( $post->ID ), 'slug' );
	$keys  = array();

	foreach ( medhub_departments_config() as $key => $config ) {
		if ( array_intersect( $slugs, $config['post_categories'] ?? array() ) ) {
			$keys[] = $key;
		}
	}

	return $keys;
}

/**
 * Other posts in the same categories (most recent first).
 *
 * @param WP_Post $post  Post.
 * @param int     $limit Max posts.
 * @return WP_Post[]
 */
function medhub_related_posts( WP_Post $post, int $limit = 3 ): array {
	$related = get_posts(
		array(
			'numberposts'      => $limit,
			'post__not_in'     => array( $post->ID ),
			'category__in'     => wp_get_post_categories( $post->ID ),
			'suppress_filters' => false,
		)
	);

	if ( count( $related ) < $limit ) {
		$related = array_merge(
			$related,
			get_posts(
				array(
					'numberposts'      => $limit - count( $related ),
					'post__not_in'     => array_merge( array( $post->ID ), wp_list_pluck( $related, 'ID' ) ),
					'suppress_filters' => false,
				)
			)
		);
	}

	return $related;
}

/**
 * Blog categories that have posts, for the category chips.
 *
 * @return WP_Term[]
 */
function medhub_blog_categories(): array {
	$terms = get_categories(
		array(
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_category' ) ),
		)
	);
	return is_array( $terms ) ? $terms : array();
}
