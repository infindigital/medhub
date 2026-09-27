<?php
/**
 * LOCAL COPY ONLY: Rank Math SEO for pages and posts, from tooling/seo/meta.json.
 *
 *   tooling/local/wp.sh eval-file tooling/local/apply-seo.php status
 *   tooling/local/wp.sh eval-file tooling/local/apply-seo.php apply
 *   tooling/local/wp.sh eval-file tooling/local/apply-seo.php rollback
 *
 * Sets rank_math_title / rank_math_description / rank_math_focus_keyword / rank_math_robots
 * per item, optionally the WordPress title (post_title; the slug/URL never changes), and the
 * Rank Math title templates under "templates", and empty featured-image alt text for the posts in
 * "thumbnail_alt_from_title" (set to the post title). Before the first change the previous values are
 * saved (_medhub_seo_prev post meta, medhub_seo_prev_templates option) and never overwritten,
 * so "rollback" restores exactly what was there.
 *
 * Refuses to run anywhere but the local copy.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

if ( ! ( defined( 'MEDHUB_LOCAL_COPY' ) && MEDHUB_LOCAL_COPY ) || 'local' !== wp_get_environment_type() || ! preg_match( '#^https?://medhub\.local$#', home_url() ) ) {
	WP_CLI::error( 'Refusing to run: this is not the MedHub local copy.' );
}

$medhub_task = $args[0] ?? 'status';
$medhub_plan = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/seo/meta.json' ), true );
if ( ! is_array( $medhub_plan ) ) {
	WP_CLI::error( 'tooling/seo/meta.json is missing or not valid JSON.' );
}

$medhub_keys = array( 'rank_math_title', 'rank_math_description', 'rank_math_focus_keyword', 'rank_math_robots' );

/* ---------- Title templates ---------- */
$medhub_titles = (array) get_option( 'rank-math-options-titles', array() );
if ( 'apply' === $medhub_task ) {
	if ( false === get_option( 'medhub_seo_prev_templates' ) ) {
		update_option( 'medhub_seo_prev_templates', array_intersect_key( $medhub_titles, $medhub_plan['templates'] ), false );
	}
	update_option( 'rank-math-options-titles', array_merge( $medhub_titles, $medhub_plan['templates'] ) );
} elseif ( 'rollback' === $medhub_task ) {
	$medhub_prev = get_option( 'medhub_seo_prev_templates' );
	if ( is_array( $medhub_prev ) ) {
		update_option( 'rank-math-options-titles', array_merge( $medhub_titles, $medhub_prev ) );
		delete_option( 'medhub_seo_prev_templates' );
	}
}
$medhub_titles = (array) get_option( 'rank-math-options-titles', array() );
foreach ( $medhub_plan['templates'] as $medhub_key => $medhub_value ) {
	WP_CLI::log( sprintf( 'template %-24s %s', $medhub_key, $medhub_titles[ $medhub_key ] ?? '' ) );
}

/* ---------- Pages and posts ---------- */
$medhub_done = 0;
foreach ( $medhub_plan['items'] as $medhub_id => $medhub_item ) {
	$medhub_post = get_post( (int) $medhub_id );
	if ( ! $medhub_post || urldecode( $medhub_post->post_name ) !== $medhub_item['slug'] ) {
		WP_CLI::warning( "#$medhub_id: not found or slug differs from {$medhub_item['slug']} – skipped." );
		continue;
	}

	if ( 'apply' === $medhub_task ) {
		if ( ! metadata_exists( 'post', $medhub_post->ID, '_medhub_seo_prev' ) ) {
			$medhub_prev = array( 'post_title' => $medhub_post->post_title );
			foreach ( $medhub_keys as $medhub_key ) {
				$medhub_prev[ $medhub_key ] = metadata_exists( 'post', $medhub_post->ID, $medhub_key ) ? get_post_meta( $medhub_post->ID, $medhub_key, true ) : null;
			}
			add_post_meta( $medhub_post->ID, '_medhub_seo_prev', $medhub_prev, true );
		}
		$medhub_map = array(
			'title'       => 'rank_math_title',
			'description' => 'rank_math_description',
			'keyword'     => 'rank_math_focus_keyword',
		);
		foreach ( $medhub_map as $medhub_field => $medhub_key ) {
			if ( isset( $medhub_item[ $medhub_field ] ) ) {
				update_post_meta( $medhub_post->ID, $medhub_key, $medhub_item[ $medhub_field ] );
			}
		}
		if ( ! empty( $medhub_item['noindex'] ) ) {
			update_post_meta( $medhub_post->ID, 'rank_math_robots', array( 'noindex', 'follow' ) );
		}
		if ( isset( $medhub_item['post_title'] ) && $medhub_item['post_title'] !== $medhub_post->post_title ) {
			wp_update_post( array( 'ID' => $medhub_post->ID, 'post_title' => $medhub_item['post_title'] ) );
		}
		++$medhub_done;
	} elseif ( 'rollback' === $medhub_task ) {
		$medhub_prev = get_post_meta( $medhub_post->ID, '_medhub_seo_prev', true );
		if ( is_array( $medhub_prev ) ) {
			foreach ( $medhub_keys as $medhub_key ) {
				null === $medhub_prev[ $medhub_key ] ? delete_post_meta( $medhub_post->ID, $medhub_key ) : update_post_meta( $medhub_post->ID, $medhub_key, $medhub_prev[ $medhub_key ] );
			}
			if ( $medhub_prev['post_title'] !== $medhub_post->post_title ) {
				wp_update_post( array( 'ID' => $medhub_post->ID, 'post_title' => $medhub_prev['post_title'] ) );
			}
			delete_post_meta( $medhub_post->ID, '_medhub_seo_prev' );
			++$medhub_done;
		}
	}

	$medhub_robots = get_post_meta( $medhub_post->ID, 'rank_math_robots', true );
	WP_CLI::log(
		sprintf(
			'#%-5d %-40s title:%-3d desc:%-3d kw:%s%s',
			$medhub_post->ID,
			mb_substr( $medhub_item['slug'], 0, 40 ),
			mb_strlen( (string) get_post_meta( $medhub_post->ID, 'rank_math_title', true ) ),
			mb_strlen( (string) get_post_meta( $medhub_post->ID, 'rank_math_description', true ) ),
			get_post_meta( $medhub_post->ID, 'rank_math_focus_keyword', true ) ? 'yes' : '-',
			is_array( $medhub_robots ) && in_array( 'noindex', $medhub_robots, true ) ? ' NOINDEX' : ''
		)
	);
}

/* ---------- Featured-image alt text ---------- */
$medhub_alts = (array) get_option( 'medhub_seo_prev_alts', array() );
if ( 'rollback' === $medhub_task ) {
	foreach ( $medhub_alts as $medhub_att => $medhub_alt ) {
		update_post_meta( (int) $medhub_att, '_wp_attachment_image_alt', $medhub_alt );
	}
	delete_option( 'medhub_seo_prev_alts' );
} else {
	foreach ( (array) ( $medhub_plan['thumbnail_alt_from_title'] ?? array() ) as $medhub_id ) {
		$medhub_att = (int) get_post_thumbnail_id( (int) $medhub_id );
		if ( ! $medhub_att ) {
			continue;
		}
		$medhub_alt = (string) get_post_meta( $medhub_att, '_wp_attachment_image_alt', true );
		if ( 'apply' === $medhub_task && '' === $medhub_alt ) {
			$medhub_alts[ $medhub_att ] = '';
			$medhub_alt                 = wp_strip_all_tags( html_entity_decode( get_the_title( (int) $medhub_id ), ENT_QUOTES ) );
			update_post_meta( $medhub_att, '_wp_attachment_image_alt', $medhub_alt );
		}
		WP_CLI::log( sprintf( 'alt  #%-5d image %-5d %s', $medhub_id, $medhub_att, $medhub_alt ? mb_substr( $medhub_alt, 0, 60 ) : '(empty)' ) );
	}
	if ( 'apply' === $medhub_task ) {
		update_option( 'medhub_seo_prev_alts', $medhub_alts, false );
	}
}

if ( 'status' !== $medhub_task ) {
	WP_CLI::success( "$medhub_task: $medhub_done item(s)." );
}
