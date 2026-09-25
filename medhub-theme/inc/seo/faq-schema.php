<?php
/**
 * FAQPage schema for medhub/faq blocks – added INTO Rank Math's JSON-LD graph.
 *
 * - Runs only through Rank Math's `rank_math/json_ld` filter, so without Rank Math
 *   the theme prints no schema at all (Rank Math stays the single source).
 * - Uses only questions/answers that are visibly rendered on the current page.
 * - Skipped if the block's "schema" toggle is off.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Collect visible FAQ items from medhub/faq blocks in post content.
 *
 * @param array $blocks Parsed blocks.
 * @return array<int, array{q:string,a:string}>
 */
function medhub_collect_faq_items( array $blocks ): array {
	$items = array();

	foreach ( $blocks as $block ) {
		if ( 'medhub/faq' === $block['blockName'] ) {
			if ( isset( $block['attrs']['schema'] ) && false === $block['attrs']['schema'] ) {
				continue;
			}
			foreach ( $block['innerBlocks'] as $inner ) {
				if ( 'core/details' !== $inner['blockName'] ) {
					continue;
				}
				if ( ! preg_match( '#<summary[^>]*>(.*?)</summary>#s', $inner['innerHTML'], $m ) ) {
					continue;
				}
				$question = trim( wp_strip_all_tags( $m[1] ) );
				$answer   = trim( wp_strip_all_tags( implode( ' ', array_map( 'render_block', $inner['innerBlocks'] ) ) ) );
				if ( $question && $answer ) {
					$items[] = array( 'q' => $question, 'a' => $answer );
				}
			}
		} elseif ( ! empty( $block['innerBlocks'] ) ) {
			$items = array_merge( $items, medhub_collect_faq_items( $block['innerBlocks'] ) );
		}
	}

	return $items;
}

add_filter(
	'rank_math/json_ld',
	static function ( $data ) {
		$object = get_queried_object();
		$post   = null;
		$url    = '';

		if ( is_singular() && $object instanceof WP_Post ) {
			$post = $object;
			$url  = get_permalink( $post );
		} elseif ( $object instanceof WP_Term && function_exists( 'medhub_get_term_content' ) && ( is_tax( 'product_cat' ) || is_tax( 'product_brand' ) ) ) {
			// Category/brand pages: FAQs from their Category content entry (rendered on the page).
			$post = medhub_get_term_content( $object );
			$url  = get_term_link( $object );
		}

		if ( ! $post instanceof WP_Post || ! has_block( 'medhub/faq', $post ) ) {
			return $data;
		}

		$items = medhub_collect_faq_items( parse_blocks( $post->post_content ) );
		if ( ! $items ) {
			return $data;
		}

		$data['medhub-faq'] = array(
			'@type'      => 'FAQPage',
			'@id'        => $url . '#faq',
			'mainEntity' => array_map(
				static fn( $item ) => array(
					'@type'          => 'Question',
					'name'           => $item['q'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $item['a'],
					),
				),
				$items
			),
		);

		return $data;
	},
	20
);
