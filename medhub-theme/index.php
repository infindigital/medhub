<?php
/**
 * Fallback template. Specific templates (front-page, page, single, archive,
 * WooCommerce overrides) replace it in later steps.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			?>
			<article <?php post_class(); ?>>
				<?php
				if ( is_singular() ) {
					the_title( '<h1>', '</h1>' );
				} else {
					the_title( sprintf( '<h2><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' );
				}

				is_singular() ? the_content() : the_excerpt();
				?>
			</article>
			<?php
		}

		the_posts_pagination();
	} else {
		echo '<p>' . esc_html__( 'Nothing found.', 'medhub' ) . '</p>';
	}
	?>
</main>
<?php
get_footer();
