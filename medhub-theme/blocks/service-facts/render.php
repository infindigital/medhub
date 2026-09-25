<?php
/**
 * Delivery & service facts. Built only from config/business.php (status-gated)
 * and existing pages, so claims stay in one verifiable place.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$delivery = medhub_business( 'delivery' );
$address  = medhub_business( 'address' );
$payment  = medhub_business( 'payment' );
$service  = medhub_get_page( 'devilbiss-service-centre-in-dubai' );
$policy   = medhub_get_page( 'delivery-policy' );
$contact  = medhub_get_page( 'contact-us-medhub' );

$cells = array();

if ( $delivery ) {
	$cells[] = array(
		'class'  => 'is-feature',
		'icon'   => 'truck',
		'kicker' => __( 'Delivery', 'medhub' ),
		'big'    => $delivery['fee'],
		'text'   => sprintf(
			/* translators: 1: region, 2: timeline, 3: processing time */
			__( 'Delivery within the %1$s, usually %2$s after processing. Orders are processed %3$s.', 'medhub' ),
			$delivery['region'],
			$delivery['timeline'],
			$delivery['processing']
		),
		'link'   => $policy ? array( __( 'Delivery policy', 'medhub' ), get_permalink( $policy ) ) : null,
		'marker' => medhub_business_marker( 'delivery' ),
	);
}

if ( $address ) {
	$cells[] = array(
		'class'  => '',
		'icon'   => 'pin',
		'kicker' => __( 'Showroom', 'medhub' ),
		'big'    => $address['locality'],
		'text'   => $address['line1'] . ', ' . $address['line2'] . '.',
		'link'   => $contact ? array( __( 'Visit & contact', 'medhub' ), get_permalink( $contact ) ) : null,
		'marker' => medhub_business_marker( 'address' ),
	);
}

if ( $service ) {
	$cells[] = array(
		'class'  => 'is-dark',
		'icon'   => 'wrench',
		'kicker' => __( 'Service', 'medhub' ),
		'big'    => __( 'Repairs & maintenance', 'medhub' ),
		'text'   => __( 'Service support for DeVilbiss respiratory equipment.', 'medhub' ),
		'link'   => array( __( 'DeVilbiss Service Centre', 'medhub' ), get_permalink( $service ) ),
		'marker' => '',
	);
}

if ( $payment ) {
	$cells[] = array(
		'class'  => '',
		'icon'   => 'lock',
		'kicker' => __( 'Payment', 'medhub' ),
		'big'    => $payment,
		'text'   => __( 'Pay online at checkout on a secure payment page.', 'medhub' ),
		'link'   => null,
		'marker' => medhub_business_marker( 'payment' ),
	);
}

if ( ! $cells ) {
	return;
}

$uid = wp_unique_id( 'service-' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'section bento-section service' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>">
	<div class="container">
		<?php
		medhub_section_header(
			array(
				'eyebrow' => $attributes['eyebrow'],
				'heading' => $attributes['heading'] ? $attributes['heading'] : __( 'Delivery and support', 'medhub' ),
				'lede'    => $attributes['lead'],
				'id'      => $uid,
			)
		);
		?>
		<div class="bento bento--facts" data-reveal-group>
			<?php foreach ( $cells as $cell ) : ?>
				<div class="bento__cell <?php echo esc_attr( $cell['class'] ); ?>">
					<p class="bento__kicker"><?php echo medhub_icon( $cell['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $cell['kicker'] ); ?><?php echo $cell['marker']; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<p class="bento__big"><?php echo esc_html( $cell['big'] ); ?></p>
					<p class="bento__text"><?php echo esc_html( $cell['text'] ); ?></p>
					<?php
					if ( $cell['link'] ) {
						echo medhub_button( array( 'label' => $cell['link'][0], 'url' => $cell['link'][1], 'variant' => 'ghost' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
