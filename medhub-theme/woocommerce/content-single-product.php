<?php
/**
 * Single product layout.
 *
 * Override of WooCommerce templates/content-single-product.php (@version 3.6.0).
 * All standard single-product hooks fire in their usual order; the theme renders
 * the gallery, summary, details, related products and links (inc/woocommerce/single.php).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}

$medhub_related = medhub_related_products( $product );
$medhub_explore = medhub_product_explore_links( $product );
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-page', $product ); ?>>
	<div class="container">
		<?php get_template_part( 'template-parts/components/breadcrumb' ); ?>

		<div class="product-layout">
			<div class="product-layout__media">
				<?php
				do_action( 'woocommerce_before_single_product_summary' );
				get_template_part( 'template-parts/product/gallery', null, array( 'product' => $product ) );
				?>
			</div>

			<div class="product-layout__summary">
				<?php get_template_part( 'template-parts/product/buy-box', null, array( 'product' => $product ) ); ?>
			</div>
		</div>

		<div class="product-details">
			<?php
			woocommerce_output_product_data_tabs();
			do_action( 'woocommerce_after_single_product_summary' );
			?>
		</div>
	</div>

	<?php if ( $medhub_related ) : ?>
		<section class="section product-related" aria-labelledby="related-title">
			<div class="container">
				<?php
				medhub_section_header(
					array(
						'eyebrow' => __( 'Also consider', 'medhub' ),
						'heading' => __( 'Related *equipment*', 'medhub' ),
						'id'      => 'related-title',
					)
				);
				?>
				<ul class="product-grid">
					<?php foreach ( $medhub_related as $medhub_rel ) : ?>
						<li><?php medhub_product_card( $medhub_rel, array( 'context' => 'grid', 'cta' => 'view' ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $medhub_explore ) : ?>
		<nav class="product-explore container" aria-label="<?php esc_attr_e( 'Explore related categories and guides', 'medhub' ); ?>">
			<p class="eyebrow"><?php esc_html_e( 'Explore', 'medhub' ); ?></p>
			<ul class="chips">
				<?php foreach ( $medhub_explore as $medhub_link ) : ?>
					<li><a class="chip" href="<?php echo esc_url( $medhub_link['url'] ); ?>"><?php echo esc_html( $medhub_link['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>
</div>

<?php
get_template_part( 'template-parts/product/sticky-buy', null, array( 'product' => $product ) );
do_action( 'woocommerce_after_single_product' );
