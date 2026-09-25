<?php
/**
 * DEVELOPMENT FORM – sandbox only (see medhub_contact_form_html()).
 *
 * Shows the intended design and fields of the enquiry form. It is not connected to
 * any email or form system: submitting only shows a notice (contact.js). Replace by
 * configuring the real form plugin in config/business.php → contact_form.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$product = isset( $_GET['product'] ) ? sanitize_text_field( wp_unslash( $_GET['product'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<form class="enquiry-form" action="#" method="post" novalidate data-dev-form>
	<p class="dev-note" role="note"><strong><?php esc_html_e( 'Development form', 'medhub' ); ?></strong> · <?php esc_html_e( 'Sandbox preview only. Nothing is sent.', 'medhub' ); ?></p>

	<div class="enquiry-form__grid">
		<div class="field">
			<label for="enq-name"><?php esc_html_e( 'Full name', 'medhub' ); ?></label>
			<input class="field-input" id="enq-name" name="name" type="text" autocomplete="name" required>
		</div>
		<div class="field">
			<label for="enq-phone"><?php esc_html_e( 'Mobile number', 'medhub' ); ?></label>
			<input class="field-input" id="enq-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required aria-describedby="enq-phone-hint">
			<small class="field-hint" id="enq-phone-hint"><?php esc_html_e( 'UAE number, e.g. 05X XXX XXXX', 'medhub' ); ?></small>
		</div>
		<div class="field field--full">
			<label for="enq-email"><?php esc_html_e( 'Email (optional)', 'medhub' ); ?></label>
			<input class="field-input" id="enq-email" name="email" type="email" autocomplete="email">
		</div>
		<fieldset class="field field--full">
			<legend><?php esc_html_e( 'I would like to', 'medhub' ); ?></legend>
			<div class="choice-row">
				<?php
				$options = array(
					'buy'     => __( 'Buy', 'medhub' ),
					'rent'    => __( 'Rent', 'medhub' ),
					'service' => __( 'Service / repair', 'medhub' ),
					'other'   => __( 'Something else', 'medhub' ),
				);
				foreach ( $options as $value => $label ) :
					?>
					<label class="choice"><input type="radio" name="intent" value="<?php echo esc_attr( $value ); ?>"<?php checked( 'buy', $value ); ?>><span><?php echo esc_html( $label ); ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<div class="field field--full">
			<label for="enq-product"><?php esc_html_e( 'Equipment (optional)', 'medhub' ); ?></label>
			<input class="field-input" id="enq-product" name="product" type="text" value="<?php echo esc_attr( $product ); ?>" placeholder="<?php esc_attr_e( 'e.g. portable oxygen concentrator', 'medhub' ); ?>">
		</div>
		<div class="field field--full">
			<label for="enq-message"><?php esc_html_e( 'Message', 'medhub' ); ?></label>
			<textarea class="field-input" id="enq-message" name="message" rows="5" required></textarea>
		</div>
	</div>

	<button class="btn btn--primary btn--lg" type="submit"><span><?php esc_html_e( 'Send enquiry', 'medhub' ); ?></span><?php echo medhub_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	<p class="enquiry-form__status" role="status" aria-live="polite" data-dev-form-status></p>
</form>
