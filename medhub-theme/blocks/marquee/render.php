<?php
/**
 * Marquee: live departments or brands as a slow scrolling strip of links.
 * The list is rendered twice for a seamless loop; the copy is hidden from
 * assistive technology and removed from the tab order.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$items = array();

if ( 'brands' === $attributes['source'] && taxonomy_exists( 'product_brand' ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 16,
		)
	);
	foreach ( is_array( $terms ) ? $terms : array() as $term ) {
		$items[] = array( 'label' => $term->name, 'url' => get_term_link( $term ), 'count' => (int) $term->count );
	}
} else {
	foreach ( array_keys( medhub_departments_config() ) as $key ) {
		$dept = medhub_get_department( $key );
		if ( $dept ) {
			$items[] = array( 'label' => $dept['label'], 'url' => $dept['url'], 'count' => $dept['count'] );
		}
	}
}

if ( count( $items ) < 3 ) {
	return;
}

$render = static function ( array $items, bool $copy ): void {
	echo '<ul class="marquee__list"' . ( $copy ? ' aria-hidden="true"' : '' ) . '>';
	foreach ( $items as $item ) {
		printf(
			'<li><a class="marquee__item" href="%1$s"%2$s>%3$s<sup>%4$s</sup></a></li>',
			esc_url( $item['url'] ),
			$copy ? ' tabindex="-1"' : '',
			esc_html( $item['label'] ),
			esc_html( number_format_i18n( $item['count'] ) )
		);
	}
	echo '</ul>';
};
?>
<nav <?php echo get_block_wrapper_attributes( array( 'class' => 'marquee' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-label="<?php echo esc_attr( $attributes['label'] ? $attributes['label'] : ( 'brands' === $attributes['source'] ? __( 'Brands', 'medhub' ) : __( 'Departments', 'medhub' ) ) ); ?>" data-marquee>
	<div class="marquee__track">
		<?php
		$render( $items, false );
		$render( $items, true );
		?>
	</div>
</nav>
