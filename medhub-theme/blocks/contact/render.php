<?php
/**
 * Contact details + form.
 *
 * Details come only from config/business.php via medhub_business(), so anything
 * unconfirmed is hidden on production. The form comes from medhub_contact_form_html().
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$address  = medhub_business( 'address' );
$map_url  = medhub_business( 'map_url' );
$phone    = medhub_business( 'phone' );
$whatsapp = medhub_business( 'whatsapp' );
$email    = medhub_business( 'email' );
$hours    = medhub_business( 'hours' );
$socials  = medhub_business( 'socials', array() );
$form     = medhub_contact_form_html();
$uid      = wp_unique_id( 'contact-' );

// Admin-only list of details still waiting for confirmation (never shown to visitors).
$pending = array();
$labels  = array(
	'phone'        => __( 'phone', 'medhub' ),
	'whatsapp'     => __( 'WhatsApp', 'medhub' ),
	'email'        => __( 'email', 'medhub' ),
	'hours'        => __( 'opening hours', 'medhub' ),
	'map_url'      => __( 'map link', 'medhub' ),
	'contact_form' => __( 'contact form', 'medhub' ),
);
foreach ( $labels as $key => $label ) {
	if ( ! medhub_business_can_show( $key ) ) {
		$pending[] = $label;
	}
}

$item = static function ( string $icon, string $label, string $body, string $marker = '' ): void {
	printf(
		'<li class="contact-item"><span class="contact-item__icon">%1$s</span><span class="contact-item__body"><span class="contact-item__label">%2$s%3$s</span>%4$s</span></li>',
		medhub_icon( $icon ), // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html( $label ),
		$marker, // phpcs:ignore WordPress.Security.EscapeOutput
		$body // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by callers.
	);
};
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section contact' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
	<div class="container contact__grid">
		<div class="contact__details">
			<h2 class="contact__heading" id="<?php echo esc_attr( $uid ); ?>"><?php echo medhub_accent_text( $attributes['heading'] ? $attributes['heading'] : __( 'Reach the team', 'medhub' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php if ( $attributes['lead'] ) : ?>
				<p class="contact__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<?php endif; ?>

			<ul class="contact__list">
				<?php
				if ( $whatsapp ) {
					$item( 'chat', __( 'WhatsApp', 'medhub' ), sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( 'https://wa.me/' . rawurlencode( (string) $whatsapp ) ), esc_html__( 'Message MedHub', 'medhub' ) ) );
				}
				if ( $phone ) {
					$item( 'phone', __( 'Phone', 'medhub' ), sprintf( '<a href="%s">%s</a>', esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', (string) $phone ) ), esc_html( $phone ) ) );
				}
				if ( $email ) {
					$item( 'mail', __( 'Email', 'medhub' ), sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $email ) ) );
				}
				if ( $address ) {
					$body = sprintf(
						'<address>%s<br>%s<br>%s</address>',
						esc_html( $address['line1'] ),
						esc_html( $address['line2'] ),
						esc_html( $address['locality'] . ', ' . $address['country'] )
					);
					if ( $map_url ) {
						$body .= sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $map_url ), esc_html__( 'Open in Google Maps', 'medhub' ) );
					}
					$item( 'pin', __( 'Showroom', 'medhub' ), $body, medhub_business_marker( 'address' ) );
				}
				if ( $hours ) {
					$item( 'clock', __( 'Opening hours', 'medhub' ), is_array( $hours ) ? implode( '<br>', array_map( 'esc_html', $hours ) ) : esc_html( (string) $hours ) );
				}
				?>
			</ul>

			<?php if ( $socials ) : ?>
				<ul class="socials" aria-label="<?php esc_attr_e( 'MedHub on social media', 'medhub' ); ?>">
					<?php foreach ( $socials as $network => $url ) : ?>
						<li><a class="icon-btn icon-btn--ghost" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo medhub_icon( $network, ucfirst( $network ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $pending && current_user_can( 'manage_options' ) ) : ?>
				<?php /* translators: %s: list of business details */ ?>
				<p class="admin-notice"><?php echo esc_html( sprintf( __( 'Admins only: %s hidden until confirmed in config/business.php.', 'medhub' ), implode( ', ', $pending ) ) ); ?></p>
			<?php endif; ?>
		</div>

		<div class="contact__form">
			<h2 class="contact__form-title"><?php echo esc_html( $attributes['formHeading'] ? $attributes['formHeading'] : __( 'Send an enquiry', 'medhub' ) ); ?></h2>
			<?php if ( $attributes['formLead'] ) : ?>
				<p class="contact__form-lead"><?php echo esc_html( $attributes['formLead'] ); ?></p>
			<?php endif; ?>
			<?php
			if ( $form ) {
				echo $form; // phpcs:ignore WordPress.Security.EscapeOutput -- form plugin / theme template output.
			} else {
				echo '<p class="contact__form-fallback">' . esc_html__( 'The enquiry form is being updated. Please use the contact details on this page.', 'medhub' ) . '</p>';
			}
			?>
		</div>
	</div>
</section>
