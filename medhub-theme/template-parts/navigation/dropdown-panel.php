<?php
/**
 * Small dropdown panel (e.g. Services).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$item = $args['item'];
?>
<div class="dropdown" id="<?php echo esc_attr( $item['id'] ); ?>" data-mega hidden>
	<ul class="dropdown__list">
		<?php foreach ( $item['items'] as $link ) : ?>
			<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?><?php echo medhub_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
