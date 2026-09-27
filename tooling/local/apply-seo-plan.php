<?php
/**
 * LOCAL COPY ONLY: applies tooling/seo/plan.json (built by tooling/seo/plan.mjs).
 *
 *   tooling/local/wp.sh eval-file tooling/local/apply-seo-plan.php status
 *   tooling/local/wp.sh eval-file tooling/local/apply-seo-plan.php apply
 *   tooling/local/wp.sh eval-file tooling/local/apply-seo-plan.php rollback
 *
 * Per item: Rank Math title / description / focus keyword / robots, content additions
 * (content_lead is added to the start of the first paragraph, content_prepend / content_append
 * around the content), featured-image alt text (only when empty) and, for terms, a description
 * (only when empty). The state before the first apply is saved once (_medhub_seo_plan_prev post
 * and term meta, medhub_seo_plan_prev_alts option) and content is always rebuilt from that
 * saved original, so re-running apply is safe and rollback restores everything exactly.
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
$medhub_plan = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/seo/plan.json' ), true );
if ( ! is_array( $medhub_plan ) ) {
	WP_CLI::error( 'tooling/seo/plan.json is missing or not valid JSON. Run node tooling/seo/plan.mjs first.' );
}

$medhub_meta = array(
	'rm_title' => 'rank_math_title',
	'rm_desc'  => 'rank_math_description',
	'kw'       => 'rank_math_focus_keyword',
	'robots'   => 'rank_math_robots',
);
$medhub_alts = (array) get_option( 'medhub_seo_plan_prev_alts', array() );
$medhub_n    = array( 'posts' => 0, 'terms' => 0, 'content' => 0, 'alts' => 0 );

kses_remove_filters(); // Post content keeps its own markup, as when an administrator saves it.

foreach ( $medhub_plan as $medhub_key => $medhub_item ) {
	list( $medhub_kind, $medhub_id ) = explode( ':', $medhub_key );
	$medhub_id                       = (int) $medhub_id;
	$medhub_is_term                  = in_array( $medhub_kind, array( 'product_cat', 'product_brand' ), true );

	$get    = $medhub_is_term ? 'get_term_meta' : 'get_post_meta';
	$update = $medhub_is_term ? 'update_term_meta' : 'update_post_meta';
	$delete = $medhub_is_term ? 'delete_term_meta' : 'delete_post_meta';
	$exists = static fn( $k ) => metadata_exists( $medhub_is_term ? 'term' : 'post', $medhub_id, $k );

	if ( $medhub_is_term ) {
		$medhub_term = get_term( $medhub_id, $medhub_kind );
		if ( ! $medhub_term instanceof WP_Term ) {
			WP_CLI::warning( "$medhub_key not found – skipped." );
			continue;
		}
	} else {
		$medhub_post = get_post( $medhub_id );
		if ( ! $medhub_post || $medhub_post->post_type !== $medhub_kind ) {
			WP_CLI::warning( "$medhub_key not found – skipped." );
			continue;
		}
	}

	if ( 'apply' === $medhub_task ) {
		if ( ! $exists( '_medhub_seo_plan_prev' ) ) {
			$medhub_prev = array();
			foreach ( $medhub_meta as $medhub_meta_key ) {
				$medhub_prev[ $medhub_meta_key ] = $exists( $medhub_meta_key ) ? $get( $medhub_id, $medhub_meta_key, true ) : null;
			}
			$medhub_prev['content'] = $medhub_is_term ? $medhub_term->description : $medhub_post->post_content;
			$update( $medhub_id, '_medhub_seo_plan_prev', wp_slash( $medhub_prev ) );
		}
		$medhub_prev = $get( $medhub_id, '_medhub_seo_plan_prev', true );

		foreach ( $medhub_meta as $medhub_field => $medhub_meta_key ) {
			if ( isset( $medhub_item[ $medhub_field ] ) ) {
				$update( $medhub_id, $medhub_meta_key, $medhub_item[ $medhub_field ] );
			}
		}

		if ( $medhub_is_term ) {
			if ( isset( $medhub_item['term_description'] ) && '' === trim( (string) $medhub_prev['content'] ) ) {
				wp_update_term( $medhub_id, $medhub_kind, array( 'description' => $medhub_item['term_description'] ) );
				++$medhub_n['content'];
			}
			++$medhub_n['terms'];
		} else {
			if ( isset( $medhub_item['content_lead'] ) || isset( $medhub_item['content_prepend'] ) || isset( $medhub_item['content_append'] ) ) {
				$medhub_content = (string) $medhub_prev['content'];
				if ( isset( $medhub_item['content_lead'] ) ) {
					$medhub_content = preg_replace( '/<p\b[^>]*>/i', '$0' . addcslashes( $medhub_item['content_lead'], '\\$' ) . ' ', $medhub_content, 1 );
				}
				$medhub_content = ( $medhub_item['content_prepend'] ?? '' ) . $medhub_content . ( $medhub_item['content_append'] ?? '' );
				if ( $medhub_content !== $medhub_post->post_content ) {
					wp_update_post( wp_slash( array( 'ID' => $medhub_id, 'post_content' => $medhub_content ) ) );
				}
				++$medhub_n['content'];
			}
			$medhub_thumb = (int) get_post_thumbnail_id( $medhub_id );
			if ( isset( $medhub_item['thumb_alt'] ) && $medhub_thumb && '' === (string) get_post_meta( $medhub_thumb, '_wp_attachment_image_alt', true ) ) {
				$medhub_alts[ $medhub_thumb ] = '';
				update_post_meta( $medhub_thumb, '_wp_attachment_image_alt', $medhub_item['thumb_alt'] );
				++$medhub_n['alts'];
			}
			++$medhub_n['posts'];
		}
	} elseif ( 'rollback' === $medhub_task && $exists( '_medhub_seo_plan_prev' ) ) {
		$medhub_prev = $get( $medhub_id, '_medhub_seo_plan_prev', true );
		// Content is only restored for items whose content this plan changes (pages built by
		// apply-content.php keep their current copy).
		if ( ! array_intersect( array( 'content_lead', 'content_prepend', 'content_append', 'term_description' ), array_keys( $medhub_item ) ) ) {
			$medhub_prev['content'] = $medhub_is_term ? $medhub_term->description : $medhub_post->post_content;
		}
		foreach ( $medhub_meta as $medhub_meta_key ) {
			null === $medhub_prev[ $medhub_meta_key ] ? $delete( $medhub_id, $medhub_meta_key ) : $update( $medhub_id, $medhub_meta_key, $medhub_prev[ $medhub_meta_key ] );
		}
		if ( $medhub_is_term ) {
			if ( $medhub_term->description !== $medhub_prev['content'] ) {
				wp_update_term( $medhub_id, $medhub_kind, array( 'description' => $medhub_prev['content'] ) );
			}
			++$medhub_n['terms'];
		} else {
			if ( $medhub_post->post_content !== $medhub_prev['content'] ) {
				wp_update_post( wp_slash( array( 'ID' => $medhub_id, 'post_content' => $medhub_prev['content'] ) ) );
			}
			++$medhub_n['posts'];
		}
		$delete( $medhub_id, '_medhub_seo_plan_prev' );
	} elseif ( 'status' === $medhub_task ) {
		$exists( '_medhub_seo_plan_prev' ) ? ++$medhub_n[ $medhub_is_term ? 'terms' : 'posts' ] : null;
	}
}

if ( 'apply' === $medhub_task ) {
	update_option( 'medhub_seo_plan_prev_alts', $medhub_alts, false );
} elseif ( 'rollback' === $medhub_task ) {
	foreach ( $medhub_alts as $medhub_att => $medhub_alt ) {
		update_post_meta( (int) $medhub_att, '_wp_attachment_image_alt', $medhub_alt );
		++$medhub_n['alts'];
	}
	delete_option( 'medhub_seo_plan_prev_alts' );
}

kses_init_filters();
WP_CLI::success( sprintf( '%s: %d posts/products, %d terms, %d content changes, %d image alt texts%s.', $medhub_task, $medhub_n['posts'], $medhub_n['terms'], $medhub_n['content'], $medhub_n['alts'], 'status' === $medhub_task ? ' (items currently applied)' : '' ) );
