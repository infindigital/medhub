<?php
/**
 * Site footer. Links are resolved live (departments, top categories, existing pages);
 * business details come from config/business.php and respect their status flags.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$medhub_departments = array_values( array_filter( array_map( 'medhub_get_department', array_keys( medhub_departments_config() ) ) ) );

$medhub_popular = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 7,
		'exclude'    => array_filter( array( (int) get_option( 'default_product_cat' ) ) ),
	)
);

$medhub_company = array_values(
	array_filter(
		array_map(
			'medhub_resolve_link',
			array(
				array( 'page' => 'medical-equipment', 'label' => __( 'About MedHub', 'medhub' ) ),
				array( 'term' => 'medical-equipment-rental', 'label' => __( 'Equipment rental', 'medhub' ) ),
				array( 'page' => 'devilbiss-service-centre-in-dubai', 'label' => __( 'DeVilbiss Service Centre', 'medhub' ) ),
				array( 'page' => 'contact-us-medhub', 'label' => __( 'Contact', 'medhub' ) ),
			)
		)
	)
);

$medhub_help = array_values(
	array_filter(
		array_map(
			'medhub_resolve_link',
			array(
				array( 'page' => 'delivery-policy', 'label' => __( 'Delivery', 'medhub' ) ),
				array( 'page' => 'refund-policy', 'label' => __( 'Refunds & returns', 'medhub' ) ),
				array( 'page' => 'cancellation-policy', 'label' => __( 'Cancellations', 'medhub' ) ),
				array( 'page' => 'privacy-policy', 'label' => __( 'Privacy policy', 'medhub' ) ),
				array( 'page' => 'terms-conditions', 'label' => __( 'Terms & conditions', 'medhub' ) ),
				array( 'page' => 'cookie-policy', 'label' => __( 'Cookie policy', 'medhub' ) ),
			)
		)
	)
);

$medhub_address = medhub_business( 'address' );
$medhub_socials = medhub_business( 'socials', array() );
$medhub_legal   = medhub_business( 'legal_name' );
?>
<footer class="site-footer">
	<div class="container">
		<div class="site-footer__top">
			<div class="site-footer__brand">
				<?php get_template_part( 'template-parts/header/logo' ); ?>
				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<p class="site-footer__tagline"><?php bloginfo( 'description' ); ?></p>
				<?php endif; ?>

				<?php if ( $medhub_address ) : ?>
					<address class="site-footer__address">
						<?php echo esc_html( implode( ', ', array_filter( array( $medhub_address['line1'], $medhub_address['line2'] ) ) ) ); ?><br>
						<?php echo esc_html( $medhub_address['locality'] . ', ' . $medhub_address['country'] ); ?>
						<?php echo medhub_business_marker( 'address' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</address>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/components/contact-actions', null, array( 'variant' => 'inline' ) ); ?>

				<?php if ( $medhub_socials ) : ?>
					<ul class="socials" aria-label="<?php esc_attr_e( 'MedHub on social media', 'medhub' ); ?>">
						<?php foreach ( $medhub_socials as $medhub_network => $medhub_url ) : ?>
							<li><a class="icon-btn icon-btn--ghost" href="<?php echo esc_url( $medhub_url ); ?>" target="_blank" rel="noopener"><?php echo medhub_icon( $medhub_network, ucfirst( $medhub_network ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'medhub' ); ?>">
				<div class="footer-col">
					<h2 class="footer-col__title"><?php esc_html_e( 'Medical equipment', 'medhub' ); ?></h2>
					<ul>
						<?php foreach ( $medhub_departments as $medhub_dept ) : ?>
							<li><a href="<?php echo esc_url( $medhub_dept['url'] ); ?>"><?php echo esc_html( $medhub_dept['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php if ( $medhub_popular && ! is_wp_error( $medhub_popular ) ) : ?>
					<div class="footer-col">
						<h2 class="footer-col__title"><?php esc_html_e( 'Popular categories', 'medhub' ); ?></h2>
						<ul>
							<?php foreach ( $medhub_popular as $medhub_term ) : ?>
								<li><a href="<?php echo esc_url( get_term_link( $medhub_term ) ); ?>"><?php echo esc_html( $medhub_term->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php foreach ( array( __( 'Company', 'medhub' ) => $medhub_company, __( 'Help', 'medhub' ) => $medhub_help ) as $medhub_title => $medhub_links ) : ?>
					<?php if ( $medhub_links ) : ?>
						<div class="footer-col">
							<h2 class="footer-col__title"><?php echo esc_html( $medhub_title ); ?></h2>
							<ul>
								<?php foreach ( $medhub_links as $medhub_link ) : ?>
									<li><a href="<?php echo esc_url( $medhub_link['url'] ); ?>"><?php echo esc_html( $medhub_link['label'] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>
		</div>

		<div class="site-footer__bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $medhub_legal ? $medhub_legal : get_bloginfo( 'name' ) ); ?></p>
			<p><?php esc_html_e( 'Prices in AED.', 'medhub' ); ?> <?php echo esc_html( (string) medhub_business( 'medicines_note', '' ) ); ?></p>
		</div>
	</div>
</footer>
