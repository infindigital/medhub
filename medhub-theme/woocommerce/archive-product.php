<?php
/**
 * Product archive: shop, product categories, brands and product search.
 *
 * Override of WooCommerce templates/archive-product.php (@version 8.6.0).
 * Every WooCommerce archive/loop hook still fires in its usual place; only the
 * default markup callbacks are unhooked in inc/woocommerce/archive.php.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$medhub_desc    = medhub_archive_description();
$medhub_subnav  = medhub_archive_subnav();
$medhub_term    = get_queried_object();
$medhub_below   = $medhub_term instanceof WP_Term ? (string) get_term_meta( $medhub_term->term_id, 'medhub_below_content', true ) : '';
$medhub_active  = medhub_active_filters();
$medhub_is_brand = is_tax( 'product_brand' );
?>
<main id="main" class="site-main site-main--shop">
	<?php do_action( 'woocommerce_before_main_content' ); ?>

	<header class="archive-head">
		<div class="container">
			<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>

			<div class="archive-head__main">
				<div class="archive-head__text">
					<?php if ( $medhub_is_brand ) : ?>
						<p class="eyebrow"><?php esc_html_e( 'Brand', 'medhub' ); ?></p>
					<?php elseif ( is_search() ) : ?>
						<p class="eyebrow"><?php esc_html_e( 'Search results', 'medhub' ); ?></p>
					<?php endif; ?>

					<h1 class="archive-head__title">
						<?php
						if ( is_search() ) {
							/* translators: %s: search query */
							printf( esc_html__( 'Results for “%s”', 'medhub' ), esc_html( get_search_query() ) );
						} else {
							echo esc_html( medhub_archive_heading() );
						}
						?>
					</h1>

					<?php if ( $medhub_desc['intro'] ) : ?>
						<div class="archive-head__intro"><?php echo wp_kses_post( $medhub_desc['intro'] ); ?></div>
					<?php endif; ?>

					<?php do_action( 'woocommerce_archive_description' ); ?>
				</div>

				<?php
				if ( $medhub_is_brand && $medhub_term instanceof WP_Term ) {
					$medhub_logo = (int) get_term_meta( $medhub_term->term_id, 'thumbnail_id', true );
					if ( $medhub_logo ) {
						echo '<div class="archive-head__logo">' . wp_get_attachment_image( $medhub_logo, 'medium', false, array( 'alt' => $medhub_term->name . ' logo', 'loading' => 'eager' ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				}
				?>
			</div>

			<?php if ( $medhub_subnav['items'] ) : ?>
				<nav class="subnav" aria-label="<?php echo esc_attr( $medhub_is_brand ? __( 'Categories for this brand', 'medhub' ) : __( 'Related categories', 'medhub' ) ); ?>">
					<ul class="subnav__list">
						<?php foreach ( $medhub_subnav['items'] as $medhub_item ) : ?>
							<li>
								<a class="chip<?php echo $medhub_item['current'] ? ' is-current' : ''; ?>" href="<?php echo esc_url( $medhub_item['url'] ); ?>"<?php echo $medhub_item['current'] ? ' aria-current="page"' : ''; ?>>
									<?php echo esc_html( $medhub_item['label'] ); ?>
									<?php if ( null !== $medhub_item['count'] ) : ?>
										<small><?php echo esc_html( number_format_i18n( $medhub_item['count'] ) ); ?></small>
									<?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</header>

	<div class="archive-body container">
		<?php get_template_part( 'template-parts/shop/filters' ); ?>

		<div class="archive-results">
			<div class="toolbar">
				<p class="toolbar__count" aria-live="polite">
					<?php
					$medhub_total = (int) wc_get_loop_prop( 'total' );
					/* translators: %s: number of products */
					echo esc_html( sprintf( _n( '%s product', '%s products', $medhub_total, 'medhub' ), number_format_i18n( $medhub_total ) ) );
					?>
				</p>
				<div class="toolbar__actions">
					<button class="btn btn--secondary btn--sm toolbar__filters" type="button" data-open-dialog="filters-dialog" aria-haspopup="dialog">
						<?php echo medhub_icon( 'sliders' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span><?php esc_html_e( 'Filters', 'medhub' ); ?></span>
						<?php if ( $medhub_active ) : ?>
							<span class="toolbar__badge"><?php echo esc_html( count( $medhub_active ) ); ?></span>
						<?php endif; ?>
					</button>
					<?php woocommerce_catalog_ordering(); ?>
				</div>
			</div>

			<?php if ( $medhub_active ) : ?>
				<ul class="active-filters" aria-label="<?php esc_attr_e( 'Active filters', 'medhub' ); ?>">
					<?php foreach ( $medhub_active as $medhub_chip ) : ?>
						<li>
							<a class="chip chip--active" href="<?php echo esc_url( $medhub_chip['url'] ); ?>">
								<?php echo esc_html( $medhub_chip['label'] ); ?>
								<?php echo medhub_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span class="screen-reader-text"><?php esc_html_e( '(remove filter)', 'medhub' ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
					<li><a class="btn btn--ghost" href="<?php echo esc_url( medhub_archive_clean_url() ); ?>"><span><?php esc_html_e( 'Clear all', 'medhub' ); ?></span></a></li>
				</ul>
			<?php endif; ?>

			<?php if ( woocommerce_product_loop() ) : ?>
				<?php do_action( 'woocommerce_before_shop_loop' ); ?>

				<ul class="product-grid product-grid--archive">
					<?php
					if ( wc_get_loop_prop( 'total' ) ) {
						while ( have_posts() ) {
							the_post();
							do_action( 'woocommerce_shop_loop' );
							wc_get_template_part( 'content', 'product' );
						}
					}
					?>
				</ul>

				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			<?php else : ?>
				<div class="empty-state">
					<?php do_action( 'woocommerce_no_products_found' ); ?>
					<?php if ( $medhub_active ) : ?>
						<p><a class="btn btn--primary" href="<?php echo esc_url( medhub_archive_clean_url() ); ?>"><span><?php esc_html_e( 'Clear filters', 'medhub' ); ?></span></a></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $medhub_desc['more'] || $medhub_below || $medhub_subnav['guides'] ) : ?>
		<section class="archive-more" aria-labelledby="archive-more-title">
			<div class="container archive-more__grid">
				<div class="archive-more__copy prose">
					<h2 id="archive-more-title">
						<?php
						/* translators: %s: category or brand name */
						printf( esc_html__( 'About %s', 'medhub' ), esc_html( $medhub_term instanceof WP_Term ? $medhub_term->name : __( 'our range', 'medhub' ) ) );
						?>
					</h2>
					<?php echo wp_kses_post( $medhub_desc['more'] ); ?>
					<?php echo wp_kses_post( wpautop( $medhub_below ) ); ?>
				</div>

				<?php if ( $medhub_subnav['guides'] ) : ?>
					<aside class="archive-more__aside">
						<p class="eyebrow"><?php esc_html_e( 'Guides', 'medhub' ); ?></p>
						<ul class="link-list">
							<?php foreach ( $medhub_subnav['guides'] as $medhub_guide ) : ?>
								<li><a href="<?php echo esc_url( $medhub_guide['url'] ); ?>"><?php echo esc_html( $medhub_guide['label'] ); ?><?php echo medhub_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
							<?php endforeach; ?>
						</ul>
					</aside>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	do_action( 'woocommerce_after_main_content' );
	do_action( 'woocommerce_sidebar' ); // Default sidebar output is unhooked; the hook remains for plugins.
	?>
</main>
<?php
get_footer( 'shop' );
