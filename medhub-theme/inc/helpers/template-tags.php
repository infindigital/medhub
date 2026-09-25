<?php
/**
 * Small rendering helpers shared by templates and blocks.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Turns editor text into safe HTML where *words* become the serif accent (<em>).
 * Only this one inline convention is supported; everything else is escaped.
 *
 * @param string $text Plain text from a block attribute.
 */
function medhub_accent_text( string $text ): string {
	$escaped = esc_html( $text );
	return (string) preg_replace( '/\*(.+?)\*/u', '<em>$1</em>', $escaped );
}

/**
 * Site-relative or absolute URL from a block attribute ("/shop/" → home URL based).
 *
 * @param string $url Attribute value.
 */
function medhub_url( string $url ): string {
	if ( '' === $url ) {
		return '';
	}
	return str_starts_with( $url, '/' ) ? home_url( $url ) : $url;
}

/**
 * Button markup.
 *
 * @param array $args label, url, variant (primary|accent|secondary|ghost|light), size (sm|md|lg), icon (arrow|…|''), attrs.
 */
function medhub_button( array $args ): string {
	$args = wp_parse_args(
		$args,
		array(
			'label'   => '',
			'url'     => '',
			'variant' => 'primary',
			'size'    => 'md',
			'icon'    => 'arrow',
			'attrs'   => '',
		)
	);

	if ( '' === $args['label'] || '' === $args['url'] ) {
		return '';
	}

	return sprintf(
		'<a class="btn btn--%1$s btn--%2$s" href="%3$s" %4$s><span>%5$s</span>%6$s</a>',
		esc_attr( $args['variant'] ),
		esc_attr( $args['size'] ),
		esc_url( medhub_url( $args['url'] ) ),
		$args['attrs'],
		esc_html( $args['label'] ),
		$args['icon'] ? medhub_icon( $args['icon'] ) : ''
	);
}

/**
 * Section header (eyebrow, heading, lede, optional link).
 *
 * @param array $args eyebrow, heading, lede, level (2), link_label, link_url, class, id.
 */
function medhub_section_header( array $args ): void {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow'    => '',
			'heading'    => '',
			'lede'       => '',
			'level'      => 2,
			'link_label' => '',
			'link_url'   => '',
			'class'      => '',
			'id'         => '',
		)
	);

	if ( '' === $args['heading'] ) {
		return;
	}

	$level = max( 2, min( 4, (int) $args['level'] ) );
	?>
	<header class="section-head <?php echo esc_attr( $args['class'] ); ?>">
		<div class="section-head__text">
			<?php if ( $args['eyebrow'] ) : ?>
				<p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
			<?php endif; ?>
			<h<?php echo (int) $level; ?> class="section-head__title"<?php echo $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?>><?php echo medhub_accent_text( $args['heading'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?></h<?php echo (int) $level; ?>>
			<?php if ( $args['lede'] ) : ?>
				<p class="section-head__lede"><?php echo esc_html( $args['lede'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		if ( $args['link_label'] && $args['link_url'] ) {
			echo medhub_button( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper.
				array(
					'label'   => $args['link_label'],
					'url'     => $args['link_url'],
					'variant' => 'ghost',
				)
			);
		}
		?>
	</header>
	<?php
}

/**
 * Number formatted for display in labels ("18 products").
 *
 * @param int $count Count.
 */
function medhub_count_label( int $count ): string {
	/* translators: %s: number of products */
	return sprintf( _n( '%s product', '%s products', $count, 'medhub' ), number_format_i18n( $count ) );
}
