<?php
/**
 * Call / WhatsApp / email / contact-page actions, built only from business
 * details that may be shown in this environment (config/business.php).
 *
 * $args['variant'] = 'stack' (default) | 'inline'
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$variant  = $args['variant'] ?? 'stack';
$phone    = medhub_business( 'phone' );
$whatsapp = medhub_business( 'whatsapp' );
$email    = medhub_business( 'email' );
$contact  = medhub_get_page( 'contact-us-medhub' );

$actions = array();
if ( $whatsapp ) {
	$actions[] = sprintf( '<a class="btn btn--whatsapp btn--md" href="%s" target="_blank" rel="noopener">%s<span>%s</span></a>', esc_url( 'https://wa.me/' . rawurlencode( (string) $whatsapp ) ), medhub_icon( 'chat' ), esc_html__( 'WhatsApp', 'medhub' ) );
}
if ( $phone ) {
	$actions[] = sprintf( '<a class="btn btn--secondary btn--md" href="%s">%s<span>%s</span></a>', esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', (string) $phone ) ), medhub_icon( 'phone' ), esc_html( $phone ) );
}
if ( $email ) {
	$actions[] = sprintf( '<a class="btn btn--ghost" href="%s"><span>%s</span></a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
}
if ( $contact ) {
	$actions[] = medhub_button(
		array(
			'label'   => __( 'Contact MedHub', 'medhub' ),
			'url'     => get_permalink( $contact ),
			'variant' => $actions ? 'ghost' : 'primary',
		)
	);
}

if ( ! $actions ) {
	return;
}
?>
<div class="contact-actions contact-actions--<?php echo esc_attr( $variant ); ?>">
	<?php echo implode( '', $actions ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>
	<?php echo medhub_business_marker( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
