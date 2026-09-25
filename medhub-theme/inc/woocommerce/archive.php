<?php
/**
 * Shop / category / brand archives: presentation hooks and read-only filtering.
 *
 * - WooCommerce's default loop markup callbacks are unhooked (the theme renders its
 *   own card), but every loop HOOK still fires so plugins keep working
 *   (Pixel Manager impressions, YITH, etc.).
 * - Filters are plain GET parameters applied to the main product query only:
 *     filter_brand=slug,slug   stock=instock   min_price / max_price (WooCommerce native)
 *   They never write anything; filtered URLs are noindex (inc/seo/robots.php).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/*
 * ---------------------------------------------------------------------------
 * Hook clean-up (default markup only – the hooks themselves remain).
 * ---------------------------------------------------------------------------
 */
add_action(
	'init',
	static function () {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header' );
		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
		remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	}
);

/*
 * ---------------------------------------------------------------------------
 * Filtering (main query, GET parameters only).
 * ---------------------------------------------------------------------------
 */

/**
 * Current filter state from the request.
 *
 * @return array{brands:string[],stock:bool,min:?float,max:?float}
 */
function medhub_filter_state(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only GET filters.
	// Accepts filter_brand=a,b (links) or filter_brand[]=a&filter_brand[]=b (the no-JS form).
	$raw    = isset( $_GET['filter_brand'] ) ? wp_unslash( $_GET['filter_brand'] ) : array();
	$raw    = is_array( $raw ) ? $raw : explode( ',', (string) $raw );
	$brands = array_unique( array_filter( array_map( 'sanitize_title', $raw ) ) );
	$min    = isset( $_GET['min_price'] ) && '' !== $_GET['min_price'] ? max( 0, (float) wp_unslash( $_GET['min_price'] ) ) : null;
	$max    = isset( $_GET['max_price'] ) && '' !== $_GET['max_price'] ? max( 0, (float) wp_unslash( $_GET['max_price'] ) ) : null;
	$stock  = isset( $_GET['stock'] ) && 'instock' === $_GET['stock'];
	// phpcs:enable

	return array(
		'brands' => array_values( $brands ),
		'stock'  => $stock,
		'min'    => $min,
		'max'    => $max,
	);
}

/**
 * Whether any theme/WooCommerce filter or sort parameter is present.
 */
function medhub_is_filtered_request(): bool {
	foreach ( array( 'filter_brand', 'stock', 'min_price', 'max_price', 'orderby' ) as $key ) {
		if ( isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
	}
	return false;
}

// Empty price fields (e.g. a form submitted without JS) would make WooCommerce filter
// everything out ("max_price=" = 0), so they are dropped before the query runs.
add_action(
	'parse_request',
	static function () {
		foreach ( array( 'min_price', 'max_price' ) as $key ) {
			if ( isset( $_GET[ $key ] ) && '' === trim( (string) $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				unset( $_GET[ $key ] );
			}
		}
	}
);

add_action(
	'woocommerce_product_query',
	static function ( WP_Query $query ) {
		$state = medhub_filter_state();

		if ( $state['brands'] && taxonomy_exists( 'product_brand' ) ) {
			$tax_query   = (array) $query->get( 'tax_query' );
			$tax_query[] = array(
				'taxonomy' => 'product_brand',
				'field'    => 'slug',
				'terms'    => $state['brands'],
			);
			$query->set( 'tax_query', $tax_query );
		}

		if ( $state['stock'] ) {
			$meta_query   = (array) $query->get( 'meta_query' );
			$meta_query[] = array(
				'key'   => '_stock_status',
				'value' => 'instock',
			);
			$query->set( 'meta_query', $meta_query );
		}
	}
);

/*
 * ---------------------------------------------------------------------------
 * Archive context helpers.
 * ---------------------------------------------------------------------------
 */

/**
 * Product IDs in the current archive before theme filters (for filter options).
 *
 * @return int[]
 */
function medhub_archive_base_ids(): array {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}

	$args = array(
		'status'     => 'publish',
		'limit'      => -1,
		'return'     => 'ids',
		'visibility' => 'catalog',
	);

	$term = get_queried_object();
	if ( $term instanceof WP_Term && is_tax( 'product_cat' ) ) {
		$args['category'] = array( $term->slug );
	} elseif ( $term instanceof WP_Term && is_tax( 'product_brand' ) ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => 'product_brand',
				'field'    => 'term_id',
				'terms'    => array( $term->term_id ),
			),
		);
	} elseif ( is_search() ) {
		$args['s'] = get_search_query();
	}

	$ids = wc_get_products( $args );
	return $ids;
}

/**
 * Brand filter options with counts for the current archive.
 *
 * @return array<int, array{term:WP_Term,count:int}>
 */
function medhub_archive_brand_options(): array {
	if ( is_tax( 'product_brand' ) || ! taxonomy_exists( 'product_brand' ) ) {
		return array();
	}

	$ids = medhub_archive_base_ids();
	if ( ! $ids ) {
		return array();
	}

	$terms = wp_get_object_terms( $ids, 'product_brand', array( 'fields' => 'all_with_object_id' ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$options = array();
	foreach ( $terms as $term ) {
		if ( ! isset( $options[ $term->term_id ] ) ) {
			$options[ $term->term_id ] = array( 'term' => $term, 'count' => 0 );
		}
		++$options[ $term->term_id ]['count'];
	}

	uasort( $options, static fn( $a, $b ) => $b['count'] <=> $a['count'] ?: strcmp( $a['term']->name, $b['term']->name ) );
	return array_values( $options );
}

/**
 * Lowest and highest price in the current archive (rounded outward), or null.
 *
 * @return array{min:int,max:int}|null
 */
function medhub_archive_price_range(): ?array {
	global $wpdb;

	$ids = array_map( 'intval', medhub_archive_base_ids() );
	if ( ! $ids ) {
		return null;
	}

	$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
	$row          = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT MIN(min_price) AS lo, MAX(max_price) AS hi FROM {$wpdb->wc_product_meta_lookup} WHERE product_id IN ($placeholders) AND max_price > 0", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
			$ids
		)
	);

	if ( ! $row || null === $row->hi ) {
		return null;
	}

	return array(
		'min' => (int) floor( (float) $row->lo ),
		'max' => (int) ceil( (float) $row->hi ),
	);
}

/**
 * Sub-navigation for the current archive (internal links):
 *  - category page → the other categories in the same department + its guides
 *  - brand page    → categories the brand has products in
 *  - shop / search → department hubs
 *
 * @return array{items:array<int,array{label:string,url:string,count:?int,current:bool}>,guides:array}
 */
function medhub_archive_subnav(): array {
	$term   = get_queried_object();
	$items  = array();
	$guides = array();

	if ( $term instanceof WP_Term && is_tax( 'product_cat' ) ) {
		foreach ( medhub_departments_config() as $key => $config ) {
			if ( in_array( $term->slug, $config['terms'], true ) ) {
				$dept = medhub_get_department( $key );
				if ( $dept ) {
					foreach ( $dept['terms'] as $t ) {
						$items[] = array( 'label' => $t->name, 'url' => get_term_link( $t ), 'count' => (int) $t->count, 'current' => $t->term_id === $term->term_id );
					}
					$guides = $dept['guides'];
				}
				break;
			}
		}
	} elseif ( $term instanceof WP_Term && is_tax( 'product_brand' ) ) {
		$cats = wp_get_object_terms( medhub_archive_base_ids(), 'product_cat' );
		if ( ! is_wp_error( $cats ) ) {
			$seen = array();
			foreach ( $cats as $cat ) {
				if ( isset( $seen[ $cat->term_id ] ) || 'uncategorized' === $cat->slug ) {
					continue;
				}
				$seen[ $cat->term_id ] = true;
				$items[]               = array( 'label' => $cat->name, 'url' => get_term_link( $cat ), 'count' => null, 'current' => false );
			}
		}
	} else {
		foreach ( array_keys( medhub_departments_config() ) as $key ) {
			$dept = medhub_get_department( $key );
			if ( $dept ) {
				$items[] = array( 'label' => $dept['label'], 'url' => $dept['url'], 'count' => $dept['count'], 'current' => false );
			}
		}
	}

	return array( 'items' => $items, 'guides' => $guides );
}

/**
 * Archive heading: optional "Display heading" term field, else the term/shop title.
 */
function medhub_archive_heading(): string {
	$term = get_queried_object();
	if ( $term instanceof WP_Term ) {
		$custom = (string) get_term_meta( $term->term_id, 'medhub_heading', true );
		return '' !== $custom ? $custom : $term->name;
	}
	return (string) woocommerce_page_title( false );
}

/**
 * Split the term description into a short intro (above the grid) and the rest
 * (below the grid), so long SEO copy never pushes products down the page.
 *
 * @return array{intro:string,more:string}
 */
function medhub_archive_description(): array {
	$term = get_queried_object();
	if ( ! $term instanceof WP_Term || '' === trim( $term->description ) ) {
		return array( 'intro' => '', 'more' => '' );
	}

	$html  = wpautop( $term->description );
	$words = str_word_count( wp_strip_all_tags( $html ) );

	if ( $words <= 60 ) {
		return array( 'intro' => $html, 'more' => '' );
	}

	// First paragraph above the grid, the rest below.
	$parts = preg_split( '#(?<=</p>)#', $html, 2 );
	$intro = $parts[0] ?? '';
	if ( str_word_count( wp_strip_all_tags( $intro ) ) > 60 ) {
		return array( 'intro' => '<p>' . esc_html( wp_trim_words( wp_strip_all_tags( $intro ), 40 ) ) . '</p>', 'more' => $html );
	}

	return array( 'intro' => $intro, 'more' => $parts[1] ?? '' );
}

/**
 * Active filter chips with removal links.
 *
 * @return array<int, array{label:string,url:string}>
 */
function medhub_active_filters(): array {
	$state = medhub_filter_state();
	$chips = array();
	$base  = get_pagenum_link( 1, false ); // Current archive, page 1, keeping its query string.

	foreach ( $state['brands'] as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_brand' );
		if ( ! $term ) {
			continue;
		}
		$rest    = array_diff( $state['brands'], array( $slug ) );
		$chips[] = array(
			'label' => $term->name,
			'url'   => $rest ? add_query_arg( 'filter_brand', implode( ',', $rest ), $base ) : remove_query_arg( 'filter_brand', $base ),
		);
	}

	if ( $state['stock'] ) {
		$chips[] = array( 'label' => __( 'In stock', 'medhub' ), 'url' => remove_query_arg( 'stock', $base ) );
	}

	if ( null !== $state['min'] || null !== $state['max'] ) {
		if ( null === $state['min'] ) {
			/* translators: %s: maximum price */
			$label = sprintf( __( 'Up to AED %s', 'medhub' ), number_format_i18n( (float) $state['max'] ) );
		} elseif ( null === $state['max'] ) {
			/* translators: %s: minimum price */
			$label = sprintf( __( 'From AED %s', 'medhub' ), number_format_i18n( $state['min'] ) );
		} else {
			/* translators: 1: minimum price, 2: maximum price */
			$label = sprintf( __( 'AED %1$s – %2$s', 'medhub' ), number_format_i18n( $state['min'] ), number_format_i18n( $state['max'] ) );
		}

		$chips[] = array(
			'label' => $label,
			'url'   => remove_query_arg( array( 'min_price', 'max_price' ), $base ),
		);
	}

	return $chips;
}

/**
 * URL of the current archive, page 1, without any filters (for "Clear all").
 */
function medhub_archive_clean_url(): string {
	return remove_query_arg( array( 'filter_brand', 'stock', 'min_price', 'max_price' ), get_pagenum_link( 1, false ) );
}
