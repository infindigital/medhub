<?php
/**
 * Blog post (article).
 *
 * Article schema comes from Rank Math; the theme prints none.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$medhub_post    = get_post();
	$medhub_cats    = get_the_category();
	$medhub_content = medhub_prose_toc( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals -- core filter.
	$medhub_depts   = array_values( array_filter( array_map( 'medhub_get_department', medhub_post_departments( $medhub_post ) ) ) );
	$medhub_related = medhub_related_posts( $medhub_post );

	// Equipment related to this guide: products from its departments.
	$medhub_products = array();
	if ( $medhub_depts ) {
		$medhub_products = medhub_get_products(
			array(
				'include'      => array_merge( array( 0 ), ...array_map( static fn( $d ) => $d['ids'], $medhub_depts ) ),
				'limit'        => 4,
				'stock_status' => 'instock',
				'orderby'      => 'price',
				'order'        => 'DESC',
			)
		);
	}
	?>
	<main id="main" class="site-main site-main--article">
		<article <?php post_class( 'article' ); ?>>
			<header class="article-head container">
				<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>
				<div class="article-head__text">
					<?php if ( $medhub_cats ) : ?>
						<p class="article-head__cat"><a href="<?php echo esc_url( get_category_link( $medhub_cats[0] ) ); ?>"><?php echo esc_html( $medhub_cats[0]->name ); ?></a></p>
					<?php endif; ?>
					<h1 class="article-head__title"><?php the_title(); ?></h1>
					<p class="article-head__meta">
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<span aria-hidden="true">·</span>
						<?php
						/* translators: %d: minutes */
						echo esc_html( sprintf( _n( '%d min read', '%d min read', medhub_reading_minutes( $medhub_post ), 'medhub' ), medhub_reading_minutes( $medhub_post ) ) );
						?>
					</p>
				</div>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="article-head__media">
						<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(min-width: 1320px) 1240px, 100vw' ) ); ?>
					</figure>
				<?php endif; ?>
			</header>

			<div class="container reading reading--article<?php echo count( $medhub_content['toc'] ) >= 3 ? ' reading--toc' : ''; ?>">
				<?php if ( count( $medhub_content['toc'] ) >= 3 ) : ?>
					<nav class="toc" aria-labelledby="toc-title">
						<p class="toc__title" id="toc-title"><?php esc_html_e( 'In this guide', 'medhub' ); ?></p>
						<ol class="toc__list">
							<?php foreach ( $medhub_content['toc'] as $medhub_item ) : ?>
								<li><a href="#<?php echo esc_attr( $medhub_item['id'] ); ?>"><?php echo esc_html( $medhub_item['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ol>
					</nav>
				<?php endif; ?>

				<div class="reading__body prose">
					<?php echo $medhub_content['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output. ?>
				</div>
			</div>
		</article>

		<?php if ( $medhub_depts ) : ?>
			<section class="section article-shop" aria-labelledby="article-shop-title">
				<div class="container">
					<?php
					medhub_section_header(
						array(
							'eyebrow' => __( 'Related equipment', 'medhub' ),
							/* translators: %s: department name */
							'heading' => sprintf( __( 'Shop %s', 'medhub' ), $medhub_depts[0]['label'] ),
							'id'      => 'article-shop-title',
						)
					);
					?>
					<ul class="chips article-shop__cats">
						<?php foreach ( $medhub_depts as $medhub_dept ) : ?>
							<?php foreach ( $medhub_dept['terms'] as $medhub_t ) : ?>
								<li><a class="chip" href="<?php echo esc_url( get_term_link( $medhub_t ) ); ?>"><?php echo esc_html( $medhub_t->name ); ?></a></li>
							<?php endforeach; ?>
							<?php foreach ( $medhub_dept['guides'] as $medhub_g ) : ?>
								<li><a class="chip" href="<?php echo esc_url( $medhub_g['url'] ); ?>"><?php echo esc_html( $medhub_g['label'] ); ?></a></li>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</ul>
					<?php if ( $medhub_products ) : ?>
						<ul class="product-grid">
							<?php foreach ( $medhub_products as $medhub_p ) : ?>
								<li><?php medhub_product_card( $medhub_p, array( 'context' => 'grid', 'cta' => 'view' ) ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/sections/contact-band' ); ?>

		<?php if ( $medhub_related ) : ?>
			<section class="section article-related" aria-labelledby="related-guides-title">
				<div class="container">
					<?php
					medhub_section_header(
						array(
							'eyebrow' => __( 'Keep reading', 'medhub' ),
							'heading' => __( 'Related *guides*', 'medhub' ),
							'id'      => 'related-guides-title',
						)
					);
					?>
					<ul class="blog-grid blog-grid--compact">
						<?php foreach ( $medhub_related as $medhub_r ) : ?>
							<li class="blog-grid__item"><?php get_template_part( 'template-parts/cards/article', null, array( 'post' => $medhub_r ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
