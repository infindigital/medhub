<?php
/**
 * Designed page layout (front page and landing pages).
 *
 * Layout: config/pages.php (hero settings + ordered sections).
 * Copy:   the page content, written with built-in blocks (see inc/content/sections.php).
 * Data:   WooCommerce / WordPress, read live by the section templates.
 *
 * @package MedHub
 *
 * @var array $args post (WP_Post).
 */

defined( 'ABSPATH' ) || exit;

$medhub_post   = $args['post'];
$medhub_config = medhub_page_config( $medhub_post );
$medhub_parts  = medhub_page_parts( $medhub_post );
$medhub_chunks = $medhub_parts['chunks'];

// Pages without a layout entry: header, their content, and a closing call to action.
if ( null === $medhub_config ) {
	$medhub_config = array(
		'hero'     => array(),
		'sections' => array(
			array( 'type' => 'content', 'all' => true ),
			array(
				'type'           => 'cta',
				'eyebrow'        => __( 'Need advice?', 'medhub' ),
				'heading'        => __( 'Talk to MedHub *before* you buy.', 'medhub' ),
				'text'           => __( 'Tell us what you need and we will help you compare options, whether you are buying or renting.', 'medhub' ),
				'primaryLabel'   => __( 'Shop Medical Equipment', 'medhub' ),
				'primaryUrl'     => '/shop/',
				'secondaryLabel' => __( 'Contact MedHub', 'medhub' ),
				'secondaryUrl'   => '/contact-us-medhub/',
			),
		),
	);
}

// Section type → template part.
$medhub_templates = array(
	'trust'    => 'trust-bar',
	'marquee'  => 'marquee',
	'explorer' => 'category-explorer',
	'showcase' => 'product-showcase',
	'products' => 'products',
	'brands'   => 'brands',
	'rental'   => 'rental',
	'switcher' => 'care-switcher',
	'service'  => 'service-facts',
	'posts'    => 'latest-posts',
	'contact'  => 'contact',
	'cta'      => 'cta',
);
?>
<main id="main" class="site-main site-main--landing<?php echo is_front_page() ? ' site-main--home' : ''; ?>">
	<?php if ( ! is_front_page() ) : ?>
		<div class="container"><?php get_template_part( 'template-parts/components/breadcrumb' ); ?></div>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/sections/hero',
		null,
		array_merge(
			(array) ( $medhub_config['hero'] ?? array() ),
			array(
				'heading' => '' !== $medhub_parts['heading'] ? $medhub_parts['heading'] : get_the_title( $medhub_post ),
				'lead'    => $medhub_parts['lead'],
			)
		)
	);

	foreach ( (array) $medhub_config['sections'] as $medhub_section ) {
		$medhub_type = (string) ( $medhub_section['type'] ?? '' );

		if ( 'content' === $medhub_type ) {
			// The next part of the page content (or all of it).
			medhub_the_chunks( ! empty( $medhub_section['all'] ) ? $medhub_chunks : array_splice( $medhub_chunks, 0, 1 ) );
			if ( ! empty( $medhub_section['all'] ) ) {
				$medhub_chunks = array();
			}
			continue;
		}

		// Content added in the editor beyond the layout's slots still shows, before the closing CTA.
		if ( 'cta' === $medhub_type && $medhub_chunks ) {
			medhub_the_chunks( $medhub_chunks );
			$medhub_chunks = array();
		}

		if ( isset( $medhub_templates[ $medhub_type ] ) ) {
			get_template_part( 'template-parts/sections/' . $medhub_templates[ $medhub_type ], null, $medhub_section );
		}
	}

	medhub_the_chunks( $medhub_chunks );
	?>
</main>
