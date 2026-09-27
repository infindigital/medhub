<?php
/**
 * Floating WhatsApp / call buttons (replaces the old "MedHub Floating Contact Buttons" and
 * "MedHub Mobile Contact Banner" plugins).
 *
 * Numbers come ONLY from config/business.php via medhub_business():
 *  - confirmed numbers                      → working buttons everywhere;
 *  - not confirmed, outside production      → a clearly labelled, non-working preview so the
 *                                             layout can be reviewed locally;
 *  - not confirmed, on production           → nothing is printed.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$medhub_whatsapp = medhub_business( 'whatsapp' );
$medhub_phone    = medhub_business( 'phone' );
$medhub_preview  = ! $medhub_whatsapp && ! $medhub_phone && 'production' !== wp_get_environment_type();

if ( ! $medhub_whatsapp && ! $medhub_phone && ! $medhub_preview ) {
	return;
}
?>
<div class="floating-contact<?php echo $medhub_preview ? ' floating-contact--preview' : ''; ?>" role="complementary" aria-label="<?php esc_attr_e( 'Contact MedHub', 'medhub' ); ?>">
	<?php if ( $medhub_preview ) : ?>
		<p class="floating-contact__note"><?php esc_html_e( 'Local preview · numbers not confirmed', 'medhub' ); ?></p>
		<span class="floating-contact__btn floating-contact__btn--whatsapp" aria-disabled="true" title="<?php esc_attr_e( 'WhatsApp: number not confirmed yet (config/business.php)', 'medhub' ); ?>">
			<?php echo medhub_icon( 'chat', __( 'WhatsApp (number not confirmed)', 'medhub' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</span>
		<span class="floating-contact__btn floating-contact__btn--call" aria-disabled="true" title="<?php esc_attr_e( 'Call: number not confirmed yet (config/business.php)', 'medhub' ); ?>">
			<?php echo medhub_icon( 'phone', __( 'Call (number not confirmed)', 'medhub' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</span>
	<?php else : ?>
		<?php if ( $medhub_whatsapp ) : ?>
			<a class="floating-contact__btn floating-contact__btn--whatsapp" href="<?php echo esc_url( 'https://wa.me/' . rawurlencode( (string) $medhub_whatsapp ) ); ?>" target="_blank" rel="noopener">
				<?php echo medhub_icon( 'chat', __( 'Chat on WhatsApp', 'medhub' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
		<?php endif; ?>
		<?php if ( $medhub_phone ) : ?>
			<a class="floating-contact__btn floating-contact__btn--call" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', (string) $medhub_phone ) ); ?>">
				<?php
				/* translators: %s: phone number */
				echo medhub_icon( 'phone', sprintf( __( 'Call %s', 'medhub' ), $medhub_phone ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</a>
		<?php endif; ?>
	<?php endif; ?>
</div>
