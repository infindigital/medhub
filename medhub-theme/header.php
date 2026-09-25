<?php
/**
 * Document head and site header.
 *
 * No <title>, meta description, canonical, Open Graph or JSON-LD here:
 * wp_head() lets WordPress and Rank Math print them exactly once.
 * The designed header (logo, mega menu, search, cart) arrives in step 2D.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'medhub' ); ?></a>

<header class="site-header">
	<?php
	if ( has_custom_logo() ) {
		the_custom_logo();
	} else {
		printf(
			'<a class="site-header__brand" href="%s" rel="home">%s</a>',
			esc_url( home_url( '/' ) ),
			esc_html( get_bloginfo( 'name' ) )
		);
	}
	?>
</header>
