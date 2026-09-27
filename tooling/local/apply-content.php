<?php
/**
 * LOCAL COPY ONLY: page and category copy with BUILT-IN blocks (Option B).
 *
 *   node tooling/content/build.mjs
 *   tooling/local/wp.sh eval-file tooling/local/apply-content.php apply
 *   tooling/local/wp.sh eval-file tooling/local/apply-content.php status
 *   tooling/local/wp.sh eval-file tooling/local/apply-content.php rollback
 *
 * Pages: same ID, slug, title, status and Rank Math meta. The state before the first
 * migration (Elementor content, excerpt, page template, Elementor mode) is kept in
 * _medhub_legacy_* meta, never overwritten, so "rollback" restores the original page exactly.
 * Elementor's own data is not touched.
 *
 * Categories: the generated copy is appended to the WooCommerce category Description
 * (the original description is kept in _medhub_legacy_description and restored by rollback).
 * Leftovers of the earlier "Category content" approach (medhub_term_content posts,
 * medhub_heading / medhub_below_content term meta) are removed.
 *
 * Links in the generated copy ({{type:slug}}) are resolved against this database into
 * root-relative URLs; links to things that do not exist are dropped (and reported).
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
$medhub_dist = dirname( __DIR__ ) . '/content/dist';

/*
 * Page ID → [expected slug, content key]. IDs come from docs/local-wordpress-audit.md;
 * the slug check stops the tool if the database differs from what was audited.
 */
$medhub_pages = array(
	3806 => array( 'elm-medical-v1-2024-01-30-13-28-52', 'home' ),
	3171 => array( 'medical-equipment', 'medical-equipment' ),
	5994 => array( 'cpap-in-dubai', 'cpap-in-dubai' ),
	6004 => array( 'bipap-in-dubai', 'bipap-in-dubai' ),
	6024 => array( 'oxygen-concentrator-in-dubai', 'oxygen-concentrator-in-dubai' ),
	5958 => array( 'portable-oxygen-machine-in-dubai', 'portable-oxygen-machine-in-dubai' ),
	5952 => array( 'oxygen-machine-in-dubai', 'oxygen-machine-in-dubai' ),
	4325 => array( '%e2%81%a0sleep-apnea-machine-in-dubai', 'sleep-apnea-machine-in-dubai' ),
	4317 => array( 'devilbiss-service-centre-in-dubai', 'devilbiss-service-centre-in-dubai' ),
	3170 => array( 'contact-us-medhub', 'contact-us-medhub' ),
);

/*
 * Pages without a custom Rank Math description use "%excerpt%", which Rank Math fills from
 * the first text of the content. The previous description is kept as the page excerpt
 * (editable in WordPress). Values are what Rank Math printed before (docs/seo-baseline-elessi.json).
 */
$medhub_excerpts = array(
	3170 => 'Shop No. 1 B, Makateb Building, Port Saeed,Opposite of Nissan Showroom, Airport road, Deira Dubai.',
);

/**
 * Resolve a link target to a root-relative URL and a title (null if it does not exist).
 *
 * @param string $type Target type.
 * @param string $slug Target slug.
 */
function medhub_local_target( string $type, string $slug ): ?array {
	$rel = static fn( $url ) => is_string( $url ) ? wp_make_link_relative( $url ) : null;
	switch ( $type ) {
		case 'category':
		case 'brand':
			$term = get_term_by( 'slug', $slug, 'category' === $type ? 'product_cat' : 'product_brand' );
			return $term && ( 'category' === $type || $term->count ) ? array( 'url' => $rel( get_term_link( $term ) ), 'title' => $term->name ) : null;
		case 'product':
		case 'post':
			$post = get_page_by_path( $slug, OBJECT, $type );
			return $post && 'publish' === $post->post_status ? array( 'url' => $rel( get_permalink( $post ) ), 'title' => get_the_title( $post ) ) : null;
		case 'page':
			foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1 ) ) as $page ) {
				// Compared without invisible characters (e.g. the U+2060 in the sleep-apnea URL).
				if ( preg_replace( '/[^a-z0-9-]/', '', strtolower( rawurldecode( $page->post_name ) ) ) === $slug ) {
					return array( 'url' => $rel( get_permalink( $page ) ), 'title' => get_the_title( $page ) );
				}
			}
			return null;
	}
	return null;
}

/**
 * Replace {{type:slug}} links; drop list items whose target does not exist.
 *
 * @param string $html     Markup.
 * @param array  $warnings Unresolved targets (appended).
 */
function medhub_local_resolve_links( string $html, array &$warnings ): string {
	return (string) preg_replace_callback(
		'#(<!-- wp:list-item -->\s*)?<li><a href="\{\{([a-z]+):([^}]+)\}\}">(.*?)</a></li>(\s*<!-- /wp:list-item -->)?#s',
		static function ( $m ) use ( &$warnings ) {
			$target = medhub_local_target( $m[2], $m[3] );
			if ( ! $target ) {
				$warnings[] = $m[2] . ':' . $m[3];
				return '';
			}
			$label = (string) preg_replace_callback( '#\{\{label:[^}]+\}\}#', static fn() => esc_html( $target['title'] ), $m[4] );
			return ( $m[1] ?? '' ) . '<li><a href="' . esc_url( $target['url'] ) . '">' . $label . '</a></li>' . ( $m[5] ?? '' );
		},
		$html
	);
}

/**
 * Read a generated file.
 *
 * @param string $dist Folder.
 * @param string $file File name.
 */
function medhub_local_dist( string $dist, string $file ): string {
	$path = $dist . '/' . $file;
	if ( ! is_readable( $path ) ) {
		WP_CLI::error( "Missing $path (run: node tooling/content/build.mjs)." );
	}
	return trim( (string) file_get_contents( $path ) );
}

if ( 'status' === $medhub_task ) {
	foreach ( $medhub_pages as $id => [ $slug ] ) {
		$post = get_post( $id );
		$core = $post && ! str_contains( $post->post_content, '<!-- wp:medhub/' ) && has_blocks( $post->post_content );
		WP_CLI::log( sprintf( '#%d %-45s built-in blocks=%s elementor=%s legacy kept=%s', $id, $post ? $post->post_name : 'MISSING', $core ? 'yes' : 'no', get_post_meta( $id, '_elementor_edit_mode', true ) ?: '-', metadata_exists( 'post', $id, '_medhub_legacy_post_content' ) ? 'yes' : 'no' ) );
	}
	foreach ( glob( $medhub_dist . '/category-*.html' ) ?: array() as $file ) {
		$slug = substr( basename( $file, '.html' ), 9 );
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		WP_CLI::log( sprintf( 'category %-38s description=%db legacy kept=%s', $slug, $term ? strlen( $term->description ) : -1, $term && metadata_exists( 'term', $term->term_id, '_medhub_legacy_description' ) ? 'yes' : 'no' ) );
	}
	return;
}

if ( 'apply' === $medhub_task ) {
	$warnings = array();

	foreach ( $medhub_pages as $id => [ $slug, $key ] ) {
		$post = get_post( $id );
		if ( ! $post || 'page' !== $post->post_type || $slug !== $post->post_name ) {
			WP_CLI::warning( "#$id skipped: expected page '$slug', found " . ( $post ? "'{$post->post_name}'" : 'nothing' ) );
			continue;
		}

		// The pre-migration state is recorded once and never overwritten.
		if ( ! metadata_exists( 'post', $id, '_medhub_legacy_post_content' ) ) {
			add_post_meta( $id, '_medhub_legacy_post_content', wp_slash( $post->post_content ), true );
			add_post_meta( $id, '_medhub_legacy_excerpt', wp_slash( $post->post_excerpt ), true );
			add_post_meta( $id, '_medhub_legacy_template', (string) get_post_meta( $id, '_wp_page_template', true ), true );
			add_post_meta( $id, '_medhub_legacy_elementor_edit_mode', (string) get_post_meta( $id, '_elementor_edit_mode', true ), true );
		}

		// Legacy theme templates (Elessi page-blank.php etc.) don't exist in MedHub; WordPress
		// rejects saving a page with an unknown template, so reset it first (old value kept above).
		update_post_meta( $id, '_wp_page_template', 'default' );

		$data = array(
			'ID'           => $id,
			'post_content' => medhub_local_resolve_links( medhub_local_dist( $medhub_dist, "page-$key.html" ), $warnings ),
		);
		if ( isset( $medhub_excerpts[ $id ] ) && '' === $post->post_excerpt && ! get_post_meta( $id, 'rank_math_description', true ) ) {
			$data['post_excerpt'] = $medhub_excerpts[ $id ];
		}
		$result = wp_update_post( wp_slash( $data ), true );
		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( "#$id " . $result->get_error_message() );
			continue;
		}

		// Elementor stops rendering the page; its data stays for rollback.
		delete_post_meta( $id, '_elementor_edit_mode' );
		update_post_meta( $id, '_medhub_migrated', gmdate( 'c' ) . ' built-in blocks: ' . $key );
		clean_post_cache( $id );
		WP_CLI::log( "#$id {$post->post_name} ← page-$key.html" );
	}

	// Category copy → WooCommerce category Description (appended to the original text).
	foreach ( glob( $medhub_dist . '/category-*.html' ) ?: array() as $file ) {
		$slug = substr( basename( $file, '.html' ), 9 );
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term ) {
			WP_CLI::warning( "category $slug not found" );
			continue;
		}
		if ( ! metadata_exists( 'term', $term->term_id, '_medhub_legacy_description' ) ) {
			add_term_meta( $term->term_id, '_medhub_legacy_description', wp_slash( $term->description ), true );
		}
		$original = (string) get_term_meta( $term->term_id, '_medhub_legacy_description', true );
		$extra    = medhub_local_resolve_links( (string) file_get_contents( $file ), $warnings );
		wp_update_term( $term->term_id, 'product_cat', array( 'description' => trim( $original ) . "\n\n" . trim( $extra ) ) );

		// Remove the earlier approach (display heading / below-content meta).
		$old_heading = (string) get_term_meta( $term->term_id, '_medhub_legacy_heading', true );
		'' !== $old_heading ? update_term_meta( $term->term_id, 'medhub_heading', $old_heading ) : delete_term_meta( $term->term_id, 'medhub_heading' );
		delete_term_meta( $term->term_id, '_medhub_legacy_heading' );
		delete_term_meta( $term->term_id, 'medhub_below_content' );
		WP_CLI::log( "category $slug: description " . strlen( $original ) . 'b + ' . strlen( $extra ) . 'b' );
	}


	// "Category content" entries from the earlier approach.
	global $wpdb;
	foreach ( $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'medhub_term_content'" ) as $cid ) {
		wp_delete_post( (int) $cid, true );
		WP_CLI::log( "removed category-content entry #$cid" );
	}

	foreach ( array_unique( $warnings ) as $w ) {
		WP_CLI::warning( "link target not found, link left out: $w" );
	}
	WP_CLI::success( 'Applied (local copy).' );
	return;
}

if ( 'rollback' === $medhub_task ) {
	foreach ( array_keys( $medhub_pages ) as $id ) {
		if ( ! metadata_exists( 'post', $id, '_medhub_legacy_post_content' ) ) {
			continue;
		}
		wp_update_post(
			wp_slash(
				array(
					'ID'           => $id,
					'post_content' => get_post_meta( $id, '_medhub_legacy_post_content', true ),
					'post_excerpt' => (string) get_post_meta( $id, '_medhub_legacy_excerpt', true ),
				)
			)
		);
		$tpl  = (string) get_post_meta( $id, '_medhub_legacy_template', true );
		$mode = (string) get_post_meta( $id, '_medhub_legacy_elementor_edit_mode', true );
		$tpl ? update_post_meta( $id, '_wp_page_template', $tpl ) : delete_post_meta( $id, '_wp_page_template' );
		if ( $mode ) {
			update_post_meta( $id, '_elementor_edit_mode', $mode );
		}
		foreach ( array( '_medhub_legacy_post_content', '_medhub_legacy_excerpt', '_medhub_legacy_template', '_medhub_legacy_elementor_edit_mode', '_medhub_migrated' ) as $k ) {
			delete_post_meta( $id, $k );
		}
		WP_CLI::log( "#$id restored" );
	}
	foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'meta_key' => '_medhub_legacy_description' ) ) as $term ) {
		wp_update_term( $term->term_id, 'product_cat', array( 'description' => (string) get_term_meta( $term->term_id, '_medhub_legacy_description', true ) ) );
		delete_term_meta( $term->term_id, '_medhub_legacy_description' );
		WP_CLI::log( "category {$term->slug} restored" );
	}

	WP_CLI::success( 'Rolled back (local copy).' );
}

/*
 * Blog posts built with Elementor → built-in blocks.
 *
 * The four Elementor articles are one "HTML" widget each: a hand-built article with its own
 * <style>, markup and a small <script>. They are moved, byte for byte, into WordPress's
 * built-in Custom HTML block, so nothing about the article changes. Two more posts only carry
 * a stale Elementor flag over content that is already blocks; the flag is removed.
 * Title, slug, dates (published AND modified), featured image, categories and Rank Math meta
 * are untouched. The previous content and flag are kept in _medhub_legacy_* meta.
 */
if ( 'posts' === $medhub_task || 'posts-rollback' === $medhub_task ) {
	global $wpdb;
	$ids = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_elementor_edit_mode' AND m.meta_value = 'builder' WHERE p.post_type = 'post' AND p.post_status = 'publish'" );
	if ( 'posts-rollback' === $medhub_task ) {
		$ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_medhub_legacy_post_elementor'" );
	}

	foreach ( $ids as $id ) {
		$id   = (int) $id;
		$post = get_post( $id );
		$keep = array( 'post_modified' => $post->post_modified, 'post_modified_gmt' => $post->post_modified_gmt );

		if ( 'posts-rollback' === $medhub_task ) {
			wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => get_post_meta( $id, '_medhub_legacy_post_content', true ) ) ) );
			update_post_meta( $id, '_elementor_edit_mode', get_post_meta( $id, '_medhub_legacy_post_elementor', true ) );
			delete_post_meta( $id, '_medhub_legacy_post_content' );
			delete_post_meta( $id, '_medhub_legacy_post_elementor' );
			$wpdb->update( $wpdb->posts, $keep, array( 'ID' => $id ) );
			clean_post_cache( $id );
			WP_CLI::log( "post #$id restored" );
			continue;
		}

		$data = json_decode( (string) get_post_meta( $id, '_elementor_data', true ), true );
		$html = array();
		$walk = static function ( $elements ) use ( &$walk, &$html ) {
			foreach ( (array) $elements as $el ) {
				if ( 'widget' === ( $el['elType'] ?? '' ) ) {
					if ( 'html' !== ( $el['widgetType'] ?? '' ) ) {
						WP_CLI::error( 'Unexpected Elementor widget: ' . ( $el['widgetType'] ?? '?' ) ); // Only HTML widgets are expected (audited).
					}
					$html[] = (string) ( $el['settings']['html'] ?? '' );
				}
				$walk( $el['elements'] ?? array() );
			}
		};
		$walk( $data );

		if ( ! metadata_exists( 'post', $id, '_medhub_legacy_post_elementor' ) ) {
			add_post_meta( $id, '_medhub_legacy_post_content', wp_slash( $post->post_content ), true );
			add_post_meta( $id, '_medhub_legacy_post_elementor', 'builder', true );
		}

		if ( $html ) {
			$content = implode( "\n\n", array_map( static fn( $h ) => "<!-- wp:html -->\n" . trim( $h ) . "\n<!-- /wp:html -->", $html ) );
			kses_remove_filters(); // The article's own <style>/<script> are kept, as when an admin saves a Custom HTML block.
			wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ) );
			kses_init_filters();
			WP_CLI::log( "post #$id {$post->post_name}: " . count( $html ) . ' HTML widget(s) → Custom HTML block (' . strlen( $content ) . 'b)' );
		} else {
			WP_CLI::log( "post #$id {$post->post_name}: no Elementor content, content already blocks, flag removed" );
		}

		delete_post_meta( $id, '_elementor_edit_mode' );
		$wpdb->update( $wpdb->posts, $keep, array( 'ID' => $id ) ); // Keep the original modified date.
		clean_post_cache( $id );
	}
	WP_CLI::success( 'posts' === $medhub_task ? 'Posts converted (local copy).' : 'Posts rolled back (local copy).' );
}
