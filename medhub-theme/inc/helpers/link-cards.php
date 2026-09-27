<?php
/**
 * Link-card resolver for the Link cards block (internal linking).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve one "type:slug | Optional label" line.
 * Returns null for anything that does not exist, so no broken internal links are printed.
 *
 * @param string $line Line from the block attribute.
 * @return array{type:string,label:string,url:string,kicker:string,image:string}|null
 */
function medhub_resolve_link_card( string $line ): ?array {
	$line = trim( $line );
	if ( '' === $line || ! str_contains( $line, ':' ) ) {
		return null;
	}

	[ $spec, $custom ] = array_pad( array_map( 'trim', explode( '|', $line, 2 ) ), 2, '' );
	[ $type, $slug ]   = array_map( 'trim', explode( ':', $spec, 2 ) );
	$img_args          = array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '96px' );

	switch ( $type ) {
		case 'category':
			$term = medhub_get_category( $slug );
			if ( ! $term ) {
				return null;
			}
			$visual = medhub_get_products(
				array(
					'category'     => array( $term->slug ),
					'limit'        => 1,
					'stock_status' => 'instock',
					'orderby'      => 'price',
					'order'        => 'DESC',
				)
			);
			return array(
				'type'   => 'category',
				'label'  => $custom ? $custom : $term->name,
				'url'    => get_term_link( $term ),
				'kicker' => medhub_count_label( (int) $term->count ),
				'image'  => $visual ? $visual[0]->get_image( 'woocommerce_thumbnail', $img_args ) : '',
			);

		case 'brand':
			$term = taxonomy_exists( 'product_brand' ) ? get_term_by( 'slug', $slug, 'product_brand' ) : null;
			if ( ! $term || ! $term->count ) {
				return null;
			}
			$logo = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
			return array(
				'type'   => 'brand',
				'label'  => $custom ? $custom : $term->name,
				'url'    => get_term_link( $term ),
				/* translators: %s: number of products */
				'kicker' => sprintf( __( 'Brand · %s', 'medhub' ), medhub_count_label( (int) $term->count ) ),
				'image'  => $logo ? wp_get_attachment_image( $logo, 'thumbnail', false, $img_args ) : '',
			);

		case 'page':
			$page = medhub_get_page( $slug );
			if ( ! $page ) {
				return null;
			}
			return array(
				'type'   => 'page',
				'label'  => $custom ? $custom : get_the_title( $page ),
				'url'    => get_permalink( $page ),
				'kicker' => __( 'Guide', 'medhub' ),
				'image'  => '',
			);

		case 'post':
			$post = get_page_by_path( $slug, OBJECT, 'post' );
			if ( ! $post || 'publish' !== $post->post_status ) {
				return null;
			}
			return array(
				'type'   => 'post',
				'label'  => $custom ? $custom : get_the_title( $post ),
				'url'    => get_permalink( $post ),
				'kicker' => __( 'Article', 'medhub' ),
				'image'  => get_the_post_thumbnail( $post, 'thumbnail', $img_args ),
			);

		case 'postid':
			$post = get_post( (int) $slug );
			if ( ! $post || 'publish' !== $post->post_status || ! in_array( $post->post_type, array( 'page', 'post' ), true ) ) {
				return null;
			}
			return array(
				'type'   => 'post' === $post->post_type ? 'post' : 'page',
				'label'  => $custom ? $custom : get_the_title( $post ),
				'url'    => get_permalink( $post ),
				'kicker' => 'post' === $post->post_type ? __( 'Article', 'medhub' ) : __( 'Guide', 'medhub' ),
				'image'  => 'post' === $post->post_type ? get_the_post_thumbnail( $post, 'thumbnail', $img_args ) : '',
			);

		case 'url':
			// Any other link typed in the editor (e.g. an external page).
			$url = esc_url_raw( $slug );
			if ( ! $url ) {
				return null;
			}
			return array(
				'type'   => 'page',
				'label'  => $custom ? $custom : $url,
				'url'    => $url,
				'kicker' => __( 'Link', 'medhub' ),
				'image'  => '',
			);

		case 'product':
			$product = medhub_get_product_by_slug( $slug );
			if ( ! $product || ! $product->is_visible() ) {
				return null;
			}
			$cat = medhub_primary_category( $product );
			return array(
				'type'   => 'product',
				'label'  => $custom ? $custom : $product->get_name(),
				'url'    => $product->get_permalink(),
				'kicker' => $cat ? $cat->name : __( 'Product', 'medhub' ),
				'image'  => $product->get_image( 'woocommerce_thumbnail', $img_args ),
			);
	}

	return null;
}
