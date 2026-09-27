<?php
/**
 * FAQPage schema, added INTO Rank Math's JSON-LD graph (Rank Math stays the only schema output).
 *
 * Questions come only from FAQs that are visibly rendered:
 *  - pages/posts: Details blocks inside a Group styled "MedHub: FAQ";
 *  - category/brand pages: <details><summary> items in the description shown below the products.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Q&A pairs from Details blocks inside "MedHub: FAQ" groups.
 *
 * @param array $blocks Parsed blocks.
 * @param bool  $in_faq Inside a FAQ group.
 * @return array<int, array{q:string,a:string}>
 */
function medhub_collect_faq_items( array $blocks, bool $in_faq = false ): array {
	$items = array();
	foreach ( $blocks as $block ) {
		$here = $in_faq || 'medhub-faq' === medhub_group_style( $block );
		if ( $here && 'core/details' === $block['blockName'] ) {
			$items = array_merge( $items, medhub_faq_items_from_html( render_block( $block ) ) );
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$items = array_merge( $items, medhub_collect_faq_items( $block['innerBlocks'], $here ) );
		}
	}
	return $items;
}

/**
 * Q&A pairs from <details><summary>…</summary>…</details> HTML.
 *
 * @param string $html HTML.
 * @return array<int, array{q:string,a:string}>
 */
function medhub_faq_items_from_html( string $html ): array {
	$items = array();
	if ( preg_match_all( '#<details\b[^>]*>\s*<summary\b[^>]*>(.*?)</summary>(.*?)</details>#is', $html, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $m ) {
			$q = trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES ) );
			$a = trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $m[2] ), ENT_QUOTES ) ) );
			if ( $q && $a ) {
				$items[] = array( 'q' => $q, 'a' => $a );
			}
		}
	}
	return $items;
}

add_filter(
	'rank_math/json_ld',
	static function ( $data ) {
		$object = get_queried_object();
		$items  = array();
		$url    = '';

		if ( is_singular() && $object instanceof WP_Post ) {
			$items = medhub_collect_faq_items( parse_blocks( $object->post_content ) );
			$url   = get_permalink( $object );
		} elseif ( $object instanceof WP_Term && ( is_tax( 'product_cat' ) || is_tax( 'product_brand' ) ) && function_exists( 'medhub_archive_description' ) ) {
			// Only the part of the description rendered below the product grid.
			$items = medhub_faq_items_from_html( medhub_archive_description()['more'] );
			$url   = get_term_link( $object );
		}

		if ( ! $items || is_wp_error( $url ) ) {
			return $data;
		}

		$questions = array_map(
			static fn( $item ) => array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['a'],
				),
			),
			$items
		);

		// Add FAQPage to Rank Math's own page entity, keeping its type (AboutPage, ContactPage,
		// CollectionPage…). A separate FAQPage node would be merged by Rank Math, which replaces
		// the page type with "FAQPage" when the page has no other schema.
		if ( isset( $data['WebPage'] ) && is_array( $data['WebPage'] ) ) {
			$data['WebPage']['@type']      = array_values( array_unique( array_merge( (array) ( $data['WebPage']['@type'] ?? 'WebPage' ), array( 'FAQPage' ) ) ) );
			$data['WebPage']['mainEntity'] = $questions;
			return $data;
		}

		$data['medhub-faq'] = array(
			'@type'      => 'FAQPage',
			'@id'        => $url . '#faq',
			'mainEntity' => $questions,
		);

		return $data;
	},
	PHP_INT_MAX - 10 // After Rank Math has built and adjusted its graph.
);
