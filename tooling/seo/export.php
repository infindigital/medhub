<?php
/**
 * Read-only export of everything SEO-relevant, for tooling/seo/*.mjs.
 *
 *   tooling/local/wp.sh eval-file tooling/seo/export.php <out.json>
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$medhub_out = $args[0] ?? '';
if ( ! $medhub_out ) {
	WP_CLI::error( 'Usage: export.php <out.json>' );
}

global $wpdb;
$medhub_rows = array();
$medhub_ids  = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('page','post','product') ORDER BY post_type, ID" );

foreach ( $medhub_ids as $medhub_id ) {
	$medhub_post  = get_post( (int) $medhub_id );
	$medhub_thumb = (int) get_post_thumbnail_id( $medhub_post );
	$medhub_row   = array(
		'kind'        => $medhub_post->post_type,
		'id'          => (int) $medhub_post->ID,
		'slug'        => urldecode( $medhub_post->post_name ),
		'url'         => str_replace( home_url(), '', get_permalink( $medhub_post ) ),
		'name'        => html_entity_decode( get_the_title( $medhub_post ), ENT_QUOTES ),
		'rm_title'    => (string) get_post_meta( $medhub_post->ID, 'rank_math_title', true ),
		'rm_desc'     => (string) get_post_meta( $medhub_post->ID, 'rank_math_description', true ),
		'kw'          => (string) get_post_meta( $medhub_post->ID, 'rank_math_focus_keyword', true ),
		'robots'      => get_post_meta( $medhub_post->ID, 'rank_math_robots', true ),
		'content'     => $medhub_post->post_content,
		'excerpt'     => $medhub_post->post_excerpt,
		'thumb'       => $medhub_thumb,
		'thumb_alt'   => $medhub_thumb ? (string) get_post_meta( $medhub_thumb, '_wp_attachment_image_alt', true ) : '',
	);
	if ( 'product' === $medhub_post->post_type ) {
		$medhub_row['cats']   = wp_get_post_terms( $medhub_post->ID, 'product_cat', array( 'fields' => 'names' ) );
		$medhub_row['brands'] = taxonomy_exists( 'product_brand' ) ? wp_get_post_terms( $medhub_post->ID, 'product_brand', array( 'fields' => 'names' ) ) : array();
		$medhub_product       = wc_get_product( $medhub_post->ID );
		$medhub_row['rental'] = (bool) preg_match( '/\brent/i', $medhub_row['name'] . ' ' . implode( ' ', $medhub_row['cats'] ) );
		$medhub_row['type']   = $medhub_product ? $medhub_product->get_type() : '';
	}
	$medhub_rows[] = $medhub_row;
}

foreach ( array( 'product_cat', 'product_brand' ) as $medhub_tax ) {
	if ( ! taxonomy_exists( $medhub_tax ) ) {
		continue;
	}
	foreach ( get_terms( array( 'taxonomy' => $medhub_tax, 'hide_empty' => false ) ) as $medhub_term ) {
		$medhub_products = get_posts(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => 'ids',
				'tax_query'   => array( array( 'taxonomy' => $medhub_tax, 'terms' => $medhub_term->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		$medhub_rows[] = array(
			'kind'     => $medhub_tax,
			'id'       => (int) $medhub_term->term_id,
			'slug'     => urldecode( $medhub_term->slug ),
			'url'      => str_replace( home_url(), '', (string) get_term_link( $medhub_term ) ),
			'name'     => html_entity_decode( $medhub_term->name, ENT_QUOTES ),
			'parent'   => $medhub_term->parent ? html_entity_decode( get_term( $medhub_term->parent )->name, ENT_QUOTES ) : '',
			'rm_title' => (string) get_term_meta( $medhub_term->term_id, 'rank_math_title', true ),
			'rm_desc'  => (string) get_term_meta( $medhub_term->term_id, 'rank_math_description', true ),
			'kw'       => (string) get_term_meta( $medhub_term->term_id, 'rank_math_focus_keyword', true ),
			'robots'   => get_term_meta( $medhub_term->term_id, 'rank_math_robots', true ),
			'content'  => $medhub_term->description,
			'count'    => count( $medhub_products ),
			'products' => array_map( static fn( $id ) => html_entity_decode( get_the_title( $id ), ENT_QUOTES ), array_slice( $medhub_products, 0, 40 ) ),
			'product_cats' => array_values( array_unique( array_merge( array(), ...array_map( static fn( $id ) => wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'names' ) ), $medhub_products ) ) ) ),
		);
	}
}

file_put_contents( $medhub_out, wp_json_encode( $medhub_rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
WP_CLI::success( count( $medhub_rows ) . ' items → ' . $medhub_out );
