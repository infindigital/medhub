<?php
/**
 * Single product page wrapper.
 *
 * Override of WooCommerce templates/single-product.php (@version 1.6.4):
 * only the page shell differs (theme <main>, no sidebar); the WooCommerce loop,
 * content-single-product template and hooks are unchanged.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>
<main id="main" class="site-main site-main--product">
	<?php
	do_action( 'woocommerce_before_main_content' );

	while ( have_posts() ) {
		the_post();
		wc_get_template_part( 'content', 'single-product' );
	}

	do_action( 'woocommerce_after_main_content' );
	do_action( 'woocommerce_sidebar' ); // Default sidebar output is unhooked; the hook remains for plugins.
	?>
</main>
<?php
get_footer( 'shop' );
