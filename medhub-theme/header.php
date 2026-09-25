<?php
/**
 * Document head and site header.
 *
 * No <title>, meta description, canonical, Open Graph or JSON-LD here:
 * wp_head() lets WordPress and Rank Math print them exactly once.
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
	<script>document.documentElement.classList.add('js');</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'medhub' ); ?></a>
<?php get_template_part( 'template-parts/header/site-header' ); ?>
