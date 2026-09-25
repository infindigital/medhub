<?php
/**
 * Resolves config/departments.php and config/navigation.php against live data.
 *
 * Everything here READS WordPress/WooCommerce data. Results are cached per request
 * only (non-persistent object cache), so nothing is written to the database.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Raw department config.
 *
 * @return array<string, array>
 */
function medhub_departments_config(): array {
	static $config = null;
	if ( null === $config ) {
		$config = (array) apply_filters( 'medhub_departments', require MEDHUB_DIR . '/config/departments.php' );
	}
	return $config;
}

/**
 * A product category by slug, only if it exists and has products.
 *
 * @param string $slug Category slug.
 */
function medhub_get_category( string $slug ): ?WP_Term {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	return ( $term instanceof WP_Term && $term->count > 0 ) ? $term : null;
}

/**
 * Published page by path, or null.
 *
 * @param string $path Page path (slug).
 */
function medhub_get_page( string $path ): ?WP_Post {
	$page = get_page_by_path( $path );
	return ( $page instanceof WP_Post && 'publish' === $page->post_status ) ? $page : null;
}

/**
 * Resolve a link spec (['page' => path] | ['term' => slug]) to [label, url], or null.
 *
 * @param array $spec Link spec. Optional 'label' overrides the live name.
 */
function medhub_resolve_link( array $spec ): ?array {
	if ( ! empty( $spec['page'] ) ) {
		$page = medhub_get_page( $spec['page'] );
		return $page ? array( 'label' => $spec['label'] ?? get_the_title( $page ), 'url' => get_permalink( $page ) ) : null;
	}

	if ( ! empty( $spec['term'] ) ) {
		$term = medhub_get_category( $spec['term'] );
		return $term ? array( 'label' => $spec['label'] ?? $term->name, 'url' => get_term_link( $term ), 'count' => $term->count ) : null;
	}

	return null;
}

/**
 * A department resolved against live data, or null if it has no live categories.
 *
 * @param string $key Department key.
 * @return array{key:string,label:string,tagline:string,url:string,terms:WP_Term[],guides:array,count:int,visual:?WC_Product}|null
 */
function medhub_get_department( string $key ): ?array {
	$cache_key = 'dept_' . $key;
	$cached    = wp_cache_get( $cache_key, 'medhub' );
	if ( false !== $cached ) {
		return $cached ? $cached : null;
	}

	$config = medhub_departments_config()[ $key ] ?? null;
	$result = null;

	if ( $config ) {
		$terms = array_values( array_filter( array_map( 'medhub_get_category', $config['terms'] ) ) );

		if ( $terms ) {
			$hub = medhub_resolve_link( $config['hub'] ) ?? array( 'url' => get_term_link( $terms[0] ) );

			$guides = array_values( array_filter( array_map( static fn( $path ) => medhub_resolve_link( array( 'page' => $path ) ), $config['guides'] ) ) );

			$product_ids = wc_get_products(
				array(
					'status'   => 'publish',
					'limit'    => -1,
					'return'   => 'ids',
					'category' => wp_list_pluck( $terms, 'slug' ),
				)
			);

			$result = array(
				'key'     => $key,
				'label'   => $config['label'],
				'tagline' => $config['tagline'] ?? '',
				'url'     => $hub['url'],
				'terms'   => $terms,
				'guides'  => $guides,
				'count'   => count( $product_ids ),
				'ids'     => $product_ids,
				'visual'  => medhub_department_visual( $config['visual'] ?? '', $product_ids ),
			);
		}
	}

	wp_cache_set( $cache_key, $result ? $result : 0, 'medhub' );
	return $result;
}

/**
 * Department image product: the configured slug if it exists and has an image,
 * otherwise the most expensive in-stock product with an image.
 *
 * @param string $slug        Preferred product slug.
 * @param int[]  $product_ids Product IDs in the department.
 */
function medhub_department_visual( string $slug, array $product_ids ): ?WC_Product {
	if ( $slug ) {
		$post    = get_page_by_path( $slug, OBJECT, 'product' );
		$product = $post ? wc_get_product( $post ) : null;
		if ( $product && $product->get_image_id() ) {
			return $product;
		}
	}

	if ( ! $product_ids ) {
		return null;
	}

	$candidates = wc_get_products(
		array(
			'include'      => $product_ids,
			'limit'        => 1,
			'stock_status' => 'instock',
			'orderby'      => 'price',
			'order'        => 'DESC',
			'meta_key'     => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery -- small, cached per request.
		)
	);

	return $candidates[0] ?? null;
}

/**
 * Brands that have products in a set of product IDs, most products first.
 *
 * @param int[] $product_ids Product IDs.
 * @param int   $limit       Max brands.
 * @return WP_Term[]
 */
function medhub_brands_for_products( array $product_ids, int $limit = 6 ): array {
	if ( ! $product_ids || ! taxonomy_exists( 'product_brand' ) ) {
		return array();
	}

	$terms = wp_get_object_terms( $product_ids, 'product_brand', array( 'fields' => 'all_with_object_id' ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$tally = array();
	$by_id = array();
	foreach ( $terms as $term ) {
		$tally[ $term->term_id ] = ( $tally[ $term->term_id ] ?? 0 ) + 1;
		$by_id[ $term->term_id ] = $term;
	}
	arsort( $tally );

	return array_map( static fn( $id ) => $by_id[ $id ], array_slice( array_keys( $tally ), 0, $limit ) );
}

/**
 * Header navigation resolved against live data.
 *
 * @return array[]
 */
function medhub_navigation(): array {
	static $nav = null;
	if ( null !== $nav ) {
		return $nav;
	}

	$nav = array();
	foreach ( (array) require MEDHUB_DIR . '/config/navigation.php' as $index => $item ) {
		$item['id'] = 'nav-' . $index;

		switch ( $item['type'] ) {
			case 'mega':
				$item['departments'] = array_values( array_filter( array_map( 'medhub_get_department', $item['departments'] ) ) );
				if ( ! $item['departments'] ) {
					continue 2;
				}
				$item['url'] = $item['departments'][0]['url'];
				break;

			case 'dropdown':
				$item['items'] = array_values( array_filter( array_map( 'medhub_resolve_link', $item['items'] ) ) );
				if ( ! $item['items'] ) {
					continue 2;
				}
				break;

			case 'term':
				$link = medhub_resolve_link( array( 'term' => $item['slug'] ) );
				if ( ! $link ) {
					continue 2;
				}
				$item['url'] = $link['url'];
				break;

			case 'page':
				$link = medhub_resolve_link( array( 'page' => $item['path'] ) );
				if ( ! $link ) {
					continue 2;
				}
				$item['url'] = $link['url'];
				break;
		}

		$nav[] = $item;
	}

	return $nav;
}
