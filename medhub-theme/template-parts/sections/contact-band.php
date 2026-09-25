<?php
/**
 * Compact "talk to MedHub" band used under articles and on utility pages.
 * UI microcopy only; contact options come from the business config.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$medhub_contact = medhub_get_page( 'contact-us-medhub' );
$medhub_shop    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
?>
<section class="section cta cta--compact" aria-labelledby="contact-band-title">
	<div class="container">
		<div class="cta__panel">
			<div class="cta__copy">
				<p class="eyebrow"><?php esc_html_e( 'Questions?', 'medhub' ); ?></p>
				<h2 class="cta__title" id="contact-band-title"><?php echo medhub_accent_text( __( 'Talk to MedHub *before* you buy.', 'medhub' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<div class="cta__actions">
				<?php
				echo medhub_button( array( 'label' => __( 'Shop equipment', 'medhub' ), 'url' => $medhub_shop, 'variant' => 'accent', 'size' => 'lg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				if ( $medhub_contact ) {
					echo medhub_button( array( 'label' => __( 'Contact MedHub', 'medhub' ), 'url' => get_permalink( $medhub_contact ), 'variant' => 'light', 'size' => 'lg', 'icon' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</div>
		</div>
	</div>
</section>
