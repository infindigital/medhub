<?php
/**
 * "Category content": landing-page sections for product categories and brands.
 *
 * Many target keywords are owned by existing category URLs (Stage 1 keyword map),
 * so they get their landing content without new pages: editors build sections
 * (how to choose, related links, FAQ, CTA…) with MedHub blocks in a non-public
 * entry, linked to one category or brand. The archive template renders it below
 * the product grid.
 *
 * The post type is not public: no URL, not searchable, not in sitemaps, not indexed.
 * Content lives in WordPress; the theme only renders it.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

const MEDHUB_TERM_CONTENT_TYPE = 'medhub_term_content';
const MEDHUB_TERM_CONTENT_META = '_medhub_term';

add_action(
	'init',
	static function () {
		register_post_type(
			MEDHUB_TERM_CONTENT_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Category content', 'medhub' ),
					'singular_name' => __( 'Category content', 'medhub' ),
					'add_new_item'  => __( 'Add category content', 'medhub' ),
					'edit_item'     => __( 'Edit category content', 'medhub' ),
					'menu_name'     => __( 'Category content', 'medhub' ),
				),
				'description'         => __( 'Landing sections shown below the products of a category or brand page.', 'medhub' ),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=product',
				'show_in_rest'        => true, // Required for the block editor.
				'show_in_nav_menus'   => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'editor', 'revisions', 'custom-fields' ),
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
			)
		);

		register_post_meta(
			MEDHUB_TERM_CONTENT_TYPE,
			MEDHUB_TERM_CONTENT_META,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static fn() => current_user_can( 'edit_pages' ),
			)
		);
	}
);

/**
 * The published content entry for a term, if any.
 *
 * @param WP_Term $term Category or brand.
 */
function medhub_get_term_content( WP_Term $term ): ?WP_Post {
	$posts = get_posts(
		array(
			'post_type'   => MEDHUB_TERM_CONTENT_TYPE,
			'post_status' => 'publish',
			'numberposts' => 1,
			'meta_key'    => MEDHUB_TERM_CONTENT_META, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $term->taxonomy . ':' . $term->slug, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);

	return $posts[0] ?? null;
}

/**
 * Rendered blocks of a term's content entry ('' if none).
 *
 * @param WP_Term $term Category or brand.
 */
function medhub_render_term_content( WP_Term $term ): string {
	$post = medhub_get_term_content( $term );
	return $post ? do_blocks( $post->post_content ) : '';
}

/*
 * Admin: pick the category/brand in a sidebar meta box.
 */
add_action(
	'add_meta_boxes_' . MEDHUB_TERM_CONTENT_TYPE,
	static function () {
		add_meta_box( 'medhub-term', __( 'Shown on', 'medhub' ), 'medhub_term_content_metabox', MEDHUB_TERM_CONTENT_TYPE, 'side', 'high' );
	}
);

/**
 * Meta box: category/brand selector.
 *
 * @param WP_Post $post Current entry.
 */
function medhub_term_content_metabox( WP_Post $post ): void {
	$current = (string) get_post_meta( $post->ID, MEDHUB_TERM_CONTENT_META, true );
	wp_nonce_field( 'medhub_term_content', 'medhub_term_content_nonce' );

	echo '<label class="screen-reader-text" for="medhub-term-select">' . esc_html__( 'Category or brand', 'medhub' ) . '</label>';
	echo '<select id="medhub-term-select" name="medhub_term" style="width:100%"><option value="">' . esc_html__( '— Choose —', 'medhub' ) . '</option>';

	foreach ( array( 'product_cat' => __( 'Categories', 'medhub' ), 'product_brand' => __( 'Brands', 'medhub' ) ) as $taxonomy => $label ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		echo '<optgroup label="' . esc_attr( $label ) . '">';
		foreach ( is_array( $terms ) ? $terms : array() as $term ) {
			$value = $taxonomy . ':' . $term->slug;
			printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $term->name ) );
		}
		echo '</optgroup>';
	}

	echo '</select><p class="description">' . esc_html__( 'These sections appear below the products on that page.', 'medhub' ) . '</p>';
}

add_action(
	'save_post_' . MEDHUB_TERM_CONTENT_TYPE,
	static function ( int $post_id ) {
		if (
			! isset( $_POST['medhub_term_content_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['medhub_term_content_nonce'] ), 'medhub_term_content' )
			|| ! current_user_can( 'edit_post', $post_id )
			|| ! isset( $_POST['medhub_term'] )
		) {
			return;
		}
		update_post_meta( $post_id, MEDHUB_TERM_CONTENT_META, sanitize_text_field( wp_unslash( $_POST['medhub_term'] ) ) );
	}
);
