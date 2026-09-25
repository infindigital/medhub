<?php
/**
 * Blog archives (categories, tags, dates, authors) and the posts page (home.php).
 * Product archives use woocommerce/archive-product.php instead.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header();

$medhub_term     = get_queried_object();
$medhub_is_blog  = is_home();
$medhub_featured = ! is_paged() && have_posts();
$medhub_blog_id  = (int) get_option( 'page_for_posts' );
?>
<main id="main" class="site-main site-main--blog">
	<header class="blog-head">
		<div class="container">
			<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>
			<p class="eyebrow"><?php esc_html_e( 'Guides & insights', 'medhub' ); ?></p>
			<h1 class="blog-head__title">
				<?php
				if ( $medhub_is_blog ) {
					echo esc_html( $medhub_blog_id ? get_the_title( $medhub_blog_id ) : __( 'Guides', 'medhub' ) );
				} elseif ( is_category() || is_tag() ) {
					single_term_title();
				} else {
					echo wp_kses_post( get_the_archive_title() );
				}
				?>
			</h1>
			<?php
			$medhub_desc = $medhub_term instanceof WP_Term ? term_description( $medhub_term ) : '';
			if ( $medhub_desc ) {
				echo '<div class="blog-head__intro">' . wp_kses_post( $medhub_desc ) . '</div>';
			}
			?>

			<?php $medhub_cats = medhub_blog_categories(); ?>
			<?php if ( $medhub_cats ) : ?>
				<nav class="subnav" aria-label="<?php esc_attr_e( 'Guide topics', 'medhub' ); ?>">
					<ul class="subnav__list">
						<?php if ( $medhub_blog_id ) : ?>
							<li><a class="chip<?php echo $medhub_is_blog ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $medhub_blog_id ) ); ?>"<?php echo $medhub_is_blog ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'All guides', 'medhub' ); ?></a></li>
						<?php endif; ?>
						<?php foreach ( $medhub_cats as $medhub_cat ) : ?>
							<?php $medhub_current = $medhub_term instanceof WP_Term && $medhub_term->term_id === $medhub_cat->term_id; ?>
							<li><a class="chip<?php echo $medhub_current ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_category_link( $medhub_cat ) ); ?>"<?php echo $medhub_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $medhub_cat->name ); ?> <small><?php echo esc_html( number_format_i18n( $medhub_cat->count ) ); ?></small></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</header>

	<div class="container blog-body">
		<?php if ( have_posts() ) : ?>
			<ul class="blog-grid">
				<?php
				$medhub_i = 0;
				while ( have_posts() ) :
					the_post();
					$medhub_lead = $medhub_featured && 0 === $medhub_i;
					?>
					<li class="blog-grid__item<?php echo $medhub_lead ? ' blog-grid__item--lead' : ''; ?>">
						<?php get_template_part( 'template-parts/cards/article', null, array( 'post' => get_post(), 'lead' => $medhub_lead, 'heading_level' => 2 ) ); ?>
					</li>
					<?php
					++$medhub_i;
				endwhile;
				?>
			</ul>

			<?php
			the_posts_pagination(
				array(
					'mid_size'           => 1,
					'prev_text'          => __( 'Previous', 'medhub' ),
					'next_text'          => __( 'Next', 'medhub' ),
					'screen_reader_text' => __( 'Guides navigation', 'medhub' ),
				)
			);
			?>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'No guides here yet.', 'medhub' ); ?></p>
		<?php endif; ?>

		<aside class="blog-search" aria-label="<?php esc_attr_e( 'Search guides', 'medhub' ); ?>">
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-search">
				<label class="screen-reader-text" for="guide-search"><?php esc_html_e( 'Search guides', 'medhub' ); ?></label>
				<input class="field-input" id="guide-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search guides…', 'medhub' ); ?>">
				<input type="hidden" name="post_type" value="post">
				<button class="btn btn--primary btn--md" type="submit"><span><?php esc_html_e( 'Search', 'medhub' ); ?></span></button>
			</form>
		</aside>
	</div>
</main>
<?php
get_footer();
