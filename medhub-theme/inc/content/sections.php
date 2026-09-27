<?php
/**
 * Page content with BUILT-IN blocks only (Option B).
 *
 * Editors write copy in the normal block editor. Three styles are added to the core
 * Group block (Block → Styles) so a group of built-in blocks is shown as a designed section:
 *
 *   MedHub: bento       Paragraph (class "eyebrow", optional) + Heading + Groups (one per card;
 *                       class "is-feature" / "is-dark" on a card for the large / dark card)
 *   MedHub: cards       same, equal cards
 *   MedHub: FAQ         eyebrow + Heading (+ Paragraph) + Details blocks (question / answer)
 *   MedHub: link cards  eyebrow + Heading + List of links (categories, brands, products, pages)
 *
 * The theme renders those groups with the same markup as the rest of the design
 * (template-parts/sections/). Everything else in the content is output as normal text.
 *
 * A page's first Heading (level 1) and the Paragraph after it become the page header (H1 + intro).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Group styles (name => label).
 */
function medhub_group_styles(): array {
	return array(
		'medhub-bento' => __( 'MedHub: bento', 'medhub' ),
		'medhub-cards' => __( 'MedHub: cards', 'medhub' ),
		'medhub-faq'   => __( 'MedHub: FAQ', 'medhub' ),
		'medhub-links' => __( 'MedHub: link cards', 'medhub' ),
	);
}

add_action(
	'init',
	static function () {
		foreach ( medhub_group_styles() as $name => $label ) {
			register_block_style( 'core/group', array( 'name' => $name, 'label' => $label ) );
		}
	}
);

// Only load CSS for the core blocks actually used on a page.
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * MedHub style of a core/group block ('' if none).
 *
 * @param array $block Parsed block.
 */
function medhub_group_style( array $block ): string {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return '';
	}
	return preg_match( '/\bis-style-(medhub-[a-z]+)\b/', (string) ( $block['attrs']['className'] ?? '' ), $m ) && isset( medhub_group_styles()[ $m[1] ] ) ? $m[1] : '';
}

/**
 * Inner HTML of a heading/paragraph block without its wrapper tag.
 *
 * @param array $block Parsed block.
 */
function medhub_block_text_html( array $block ): string {
	return trim( (string) preg_replace( '#^\s*<(h[1-6]|p)\b[^>]*>|</(h[1-6]|p)>\s*$#i', '', (string) $block['innerHTML'] ) );
}

/**
 * Inline HTML → plain text using the theme's "*accent*" convention for <em>.
 *
 * @param string $html Inline HTML.
 */
function medhub_accent_from_html( string $html ): string {
	$html = (string) preg_replace( '#<em\b[^>]*>(.*?)</em>#is', '*$1*', $html );
	return trim( html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Parsed blocks without the empty whitespace blocks the parser returns between blocks.
 *
 * @param array $blocks Parsed blocks.
 */
function medhub_real_blocks( array $blocks ): array {
	return array_values( array_filter( $blocks, static fn( $b ) => null !== $b['blockName'] || '' !== trim( (string) $b['innerHTML'] ) ) );
}

/**
 * Take the section header (eyebrow paragraph, heading, intro paragraph) off the front of a
 * list of blocks.
 *
 * @param array $blocks Parsed blocks (modified).
 * @return array{eyebrow:string,heading:string,lead:string}
 */
function medhub_shift_section_head( array &$blocks ): array {
	$head   = array( 'eyebrow' => '', 'heading' => '', 'lead' => '' );
	$blocks = medhub_real_blocks( $blocks );

	if ( $blocks && 'core/paragraph' === $blocks[0]['blockName'] && str_contains( (string) ( $blocks[0]['attrs']['className'] ?? '' ), 'eyebrow' ) ) {
		$head['eyebrow'] = wp_strip_all_tags( medhub_block_text_html( array_shift( $blocks ) ) );
	}
	if ( $blocks && 'core/heading' === $blocks[0]['blockName'] ) {
		$head['heading'] = medhub_accent_from_html( medhub_block_text_html( array_shift( $blocks ) ) );
		if ( $blocks && 'core/paragraph' === $blocks[0]['blockName'] ) {
			$head['lead'] = wp_strip_all_tags( medhub_block_text_html( array_shift( $blocks ) ) );
		}
	}
	return $head;
}

/**
 * Render blocks to HTML (the parts of the_content that apply to block content).
 *
 * @param array $blocks Parsed blocks.
 */
function medhub_render_blocks( array $blocks ): string {
	$html = '';
	foreach ( $blocks as $block ) {
		$html .= render_block( $block );
	}
	return wp_filter_content_tags( do_shortcode( $html ) );
}

/**
 * "type:slug | Label" line for the link-cards section, from a link in the content.
 *
 * @param string $url   Link URL.
 * @param string $label Link text.
 */
function medhub_link_spec_from_url( string $url, string $label ): string {
	$label = trim( str_replace( '|', '/', $label ) );
	$host  = (string) wp_parse_url( $url, PHP_URL_HOST );
	$path  = (string) wp_parse_url( $url, PHP_URL_PATH );

	if ( $host && strtolower( $host ) !== strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ) {
		return 'url:' . $url . ' | ' . $label;
	}
	foreach ( array( 'category' => '#/product-category/(?:[^/]+/)*([^/]+)/?$#', 'brand' => '#/brand/([^/]+)/?$#', 'product' => '#/product/([^/]+)/?$#' ) as $type => $pattern ) {
		if ( preg_match( $pattern, $path, $m ) ) {
			return $type . ':' . rawurldecode( $m[1] ) . ' | ' . $label;
		}
	}
	$id = url_to_postid( home_url( $path ) );
	return $id ? 'postid:' . $id . ' | ' . $label : 'url:' . $url . ' | ' . $label;
}

/**
 * Links (href + text) from List blocks.
 *
 * @param array $blocks Parsed blocks.
 * @return string[] Link-card lines.
 */
function medhub_link_specs_from_blocks( array $blocks ): array {
	$specs = array();
	foreach ( $blocks as $block ) {
		if ( ! empty( $block['innerBlocks'] ) ) {
			$specs = array_merge( $specs, medhub_link_specs_from_blocks( $block['innerBlocks'] ) );
		}
		if ( 'core/list-item' === $block['blockName'] && preg_match( '#<a\b[^>]*href="([^"]+)"[^>]*>(.*?)</a>#is', (string) $block['innerHTML'], $m ) ) {
			$specs[] = medhub_link_spec_from_url( html_entity_decode( $m[1] ), wp_strip_all_tags( html_entity_decode( $m[2], ENT_QUOTES ) ) );
		}
	}
	return $specs;
}

/**
 * Render a MedHub-styled Group with the design's section markup.
 *
 * @param array  $block Parsed core/group block.
 * @param string $style Style name.
 */
function medhub_render_styled_group( array $block, string $style ): string {
	$inner = $block['innerBlocks'];
	$head  = medhub_shift_section_head( $inner );

	ob_start();
	switch ( $style ) {
		case 'medhub-bento':
		case 'medhub-cards':
			get_template_part( 'template-parts/sections/bento', null, $head + array( 'layout' => 'medhub-cards' === $style ? 'cards' : '', 'content' => medhub_render_blocks( $inner ) ) );
			break;
		case 'medhub-faq':
			get_template_part( 'template-parts/sections/faq', null, $head + array( 'content' => medhub_render_blocks( $inner ) ) );
			break;
		case 'medhub-links':
			get_template_part( 'template-parts/sections/link-cards', null, $head + array( 'items' => implode( "\n", medhub_link_specs_from_blocks( $inner ) ) ) );
			break;
	}
	return (string) ob_get_clean();
}

// Runs after core's wp_restore_group_inner_container (priority 10), whose multi-line regex
// would otherwise inject an unclosed inner-container into the section markup we return.
add_filter(
	'render_block_core/group',
	static function ( $html, $block ) {
		$style = medhub_group_style( $block );
		return $style ? medhub_render_styled_group( $block, $style ) : $html;
	},
	20,
	2
);

/**
 * Page parts: H1 + intro from the first Heading (level 1) / Paragraph, and the rest of the
 * content as chunks. A chunk is one MedHub-styled Group, or a run of other blocks (shown as text).
 *
 * @param WP_Post $post Page.
 * @return array{heading:string,lead:string,chunks:array<int,array>}
 */
function medhub_page_parts( WP_Post $post ): array {
	$blocks  = medhub_real_blocks( parse_blocks( $post->post_content ) );
	$heading = '';
	$lead    = '';

	if ( $blocks && 'core/heading' === $blocks[0]['blockName'] && 1 === (int) ( $blocks[0]['attrs']['level'] ?? 2 ) ) {
		$heading = medhub_accent_from_html( medhub_block_text_html( array_shift( $blocks ) ) );
		if ( $blocks && 'core/paragraph' === $blocks[0]['blockName'] ) {
			$lead = wp_strip_all_tags( medhub_block_text_html( array_shift( $blocks ) ) );
		}
	}

	$chunks = array();
	$run    = array();
	foreach ( $blocks as $block ) {
		if ( medhub_group_style( $block ) ) {
			if ( $run ) {
				$chunks[] = array( 'type' => 'text', 'blocks' => $run );
				$run      = array();
			}
			$chunks[] = array( 'type' => 'section', 'blocks' => array( $block ) );
		} else {
			$run[] = $block;
		}
	}
	if ( $run ) {
		$chunks[] = array( 'type' => 'text', 'blocks' => $run );
	}

	return array( 'heading' => $heading, 'lead' => $lead, 'chunks' => $chunks );
}

/**
 * Output content chunks (sections as designed, other blocks as readable text).
 *
 * @param array $chunks Chunks from medhub_page_parts().
 */
function medhub_the_chunks( array $chunks ): void {
	foreach ( $chunks as $chunk ) {
		$html = medhub_render_blocks( $chunk['blocks'] );
		if ( 'text' === $chunk['type'] ) {
			$html = '<section class="section section--text"><div class="container"><div class="prose">' . $html . '</div></div></section>';
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- rendered blocks.
	}
}

/**
 * Layout config (config/pages.php) for a page, or null.
 *
 * @param WP_Post $post Page.
 */
function medhub_page_config( WP_Post $post ): ?array {
	static $all = null;
	if ( null === $all ) {
		$all = (array) require MEDHUB_DIR . '/config/pages.php';
	}
	$key = (int) get_option( 'page_on_front' ) === $post->ID ? 'home' : (string) preg_replace( '/[^a-z0-9-]/', '', strtolower( rawurldecode( $post->post_name ) ) );
	return isset( $all[ $key ] ) && is_array( $all[ $key ] ) ? $all[ $key ] : null;
}

/**
 * Whether a page uses the designed (landing) layout: it has a layout entry, or its content
 * starts with a level-1 Heading.
 *
 * @param WP_Post $post Page.
 */
function medhub_is_designed_page( WP_Post $post ): bool {
	return null !== medhub_page_config( $post ) || '' !== medhub_page_parts( $post )['heading'];
}
