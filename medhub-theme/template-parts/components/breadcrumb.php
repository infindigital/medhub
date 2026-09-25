<?php
/**
 * Visible breadcrumb.
 *
 * Uses Rank Math's breadcrumb trail when available, so the visible trail matches
 * Rank Math's BreadcrumbList schema exactly. Otherwise falls back to WooCommerce's
 * trail (visible only – the theme prints no breadcrumb schema of its own).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'rank_math_get_breadcrumbs' ) ) {
	$medhub_crumbs = rank_math_get_breadcrumbs();
	if ( $medhub_crumbs ) {
		echo '<div class="breadcrumb">' . $medhub_crumbs . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- Rank Math output.
		return;
	}
}

if ( ! class_exists( 'WC_Breadcrumb' ) ) {
	return;
}

$medhub_trail = new WC_Breadcrumb();
$medhub_trail->add_crumb( _x( 'Home', 'breadcrumb', 'medhub' ), home_url( '/' ) );
$medhub_items = $medhub_trail->generate();

if ( count( $medhub_items ) < 2 ) {
	return;
}
?>
<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'medhub' ); ?>">
	<ol>
		<?php foreach ( $medhub_items as $medhub_i => $medhub_crumb ) : ?>
			<li>
				<?php if ( $medhub_i < count( $medhub_items ) - 1 && ! empty( $medhub_crumb[1] ) ) : ?>
					<a href="<?php echo esc_url( $medhub_crumb[1] ); ?>"><?php echo esc_html( $medhub_crumb[0] ); ?></a>
				<?php else : ?>
					<span aria-current="page"><?php echo esc_html( $medhub_crumb[0] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
