<?php
/**
 * Site search results (guides and pages). Product searches (post_type=product)
 * use the WooCommerce archive template instead.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main site-main--blog">
	<header class="blog-head">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Search', 'medhub' ); ?></p>
			<?php /* translators: %s: search query */ ?>
			<h1 class="blog-head__title"><?php printf( esc_html__( 'Results for “%s”', 'medhub' ), esc_html( get_search_query() ) ); ?></h1>
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-search">
				<label class="screen-reader-text" for="site-search"><?php esc_html_e( 'Search', 'medhub' ); ?></label>
				<input class="field-input" id="site-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
				<button class="btn btn--primary btn--md" type="submit"><span><?php esc_html_e( 'Search', 'medhub' ); ?></span></button>
			</form>
			<?php /* translators: %s: search query */ ?>
			<p class="blog-head__intro"><a href="<?php echo esc_url( add_query_arg( array( 's' => get_search_query(), 'post_type' => 'product' ), home_url( '/' ) ) ); ?>"><?php printf( esc_html__( 'Search products for “%s” instead', 'medhub' ), esc_html( get_search_query() ) ); ?></a></p>
		</div>
	</header>

	<div class="container blog-body">
		<?php if ( have_posts() ) : ?>
			<ul class="blog-grid blog-grid--compact">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li class="blog-grid__item"><?php get_template_part( 'template-parts/cards/article', null, array( 'post' => get_post(), 'heading_level' => 2 ) ); ?></li>
				<?php endwhile; ?>
			</ul>
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Nothing matched that search. Try a product name or a category such as “CPAP mask”.', 'medhub' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
