<?php
/**
 * Pages.
 *
 * The layout is chosen from the content itself, so no page template has to be
 * assigned in the database:
 *   - WooCommerce cart / checkout / account → plain wrapper (styled in step 2G)
 *   - Content built with MedHub blocks        → landing layout (the Hero block prints the H1)
 *   - Everything else (policies, legal, text) → reading layout with table of contents;
 *     the page text itself is output unchanged.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$medhub_post = get_post();

	if ( function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() ) ) :
		?>
		<main id="main" class="site-main site-main--woo">
			<div class="container">
				<h1 class="page-title"><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</div>
		</main>
		<?php

	elseif ( medhub_has_medhub_blocks( $medhub_post ) ) :
		?>
		<main id="main" class="site-main site-main--landing">
			<div class="container">
				<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>
			</div>
			<?php if ( ! has_block( 'medhub/hero', $medhub_post ) ) : ?>
				<header class="page-head container">
					<h1 class="page-head__title"><?php the_title(); ?></h1>
				</header>
			<?php endif; ?>
			<?php the_content(); ?>
		</main>
		<?php

	else :
		$medhub_content = medhub_prose_toc( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals -- core filter.
		$medhub_has_toc = count( $medhub_content['toc'] ) >= 3;
		?>
		<main id="main" class="site-main site-main--prose">
			<div class="container">
				<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>

				<header class="page-head">
					<h1 class="page-head__title"><?php the_title(); ?></h1>
					<p class="page-head__meta">
						<?php
						/* translators: %s: date */
						printf( esc_html__( 'Last updated %s', 'medhub' ), '<time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time>' );
						?>
					</p>
				</header>

				<div class="reading<?php echo $medhub_has_toc ? ' reading--toc' : ''; ?>">
					<?php if ( $medhub_has_toc ) : ?>
						<nav class="toc" aria-labelledby="toc-title">
							<p class="toc__title" id="toc-title"><?php esc_html_e( 'On this page', 'medhub' ); ?></p>
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
			</div>
		</main>
		<?php
	endif;
endwhile;

get_footer();
