<?php
/**
 * Inline SVG icon sprite + helper.
 *
 * The sprite is printed once at wp_body_open; icons reference it with <use>.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icon markup. Decorative by default (aria-hidden); pass $label for a meaningful icon.
 *
 * @param string $name  Symbol id without the "i-" prefix.
 * @param string $label Accessible label (optional).
 * @param string $class Extra class.
 */
function medhub_icon( string $name, string $label = '', string $class = '' ): string {
	$a11y = $label
		? sprintf( 'role="img" aria-label="%s"', esc_attr( $label ) )
		: 'aria-hidden="true" focusable="false"';

	return sprintf(
		'<svg class="icon icon--%1$s %2$s" %3$s><use href="#i-%1$s"></use></svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		$a11y
	);
}

add_action(
	'wp_body_open',
	static function () {
		?>
<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
	<symbol id="i-arrow" viewBox="0 0 16 16"><path d="M2 8h11M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
	<symbol id="i-chevron" viewBox="0 0 16 16"><path d="m4 6 4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
	<symbol id="i-search" viewBox="0 0 20 20"><circle cx="9" cy="9" r="6" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m14 14 4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
	<symbol id="i-bag" viewBox="0 0 20 20"><path d="M4 7h12l-1 10H5L4 7Zm3 0a3 3 0 0 1 6 0" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></symbol>
	<symbol id="i-menu" viewBox="0 0 20 20"><path d="M3 6h14M3 10h14M3 14h9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
	<symbol id="i-close" viewBox="0 0 20 20"><path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
	<symbol id="i-pin" viewBox="0 0 20 20"><path d="M10 18s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="8" r="2" fill="currentColor"/></symbol>
	<symbol id="i-truck" viewBox="0 0 20 20"><path d="M2 5h10v9H2zM12 8h3l3 3v3h-6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="6" cy="15" r="1.6" fill="currentColor"/><circle cx="15" cy="15" r="1.6" fill="currentColor"/></symbol>
	<symbol id="i-swap" viewBox="0 0 20 20"><path d="M4 7h12l-3-3M16 13H4l3 3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
	<symbol id="i-lock" viewBox="0 0 20 20"><rect x="4" y="9" width="12" height="8" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M7 9V6a3 3 0 0 1 6 0v3" fill="none" stroke="currentColor" stroke-width="1.6"/></symbol>
	<symbol id="i-phone" viewBox="0 0 20 20"><path d="M5 3h3l1.5 4-2 1.2a9 9 0 0 0 4.3 4.3L13 10.5l4 1.5v3a2 2 0 0 1-2 2A13 13 0 0 1 3 5a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></symbol>
	<symbol id="i-chat" viewBox="0 0 20 20"><path d="M10 3a7 7 0 0 0-6 10.6L3 17l3.5-1A7 7 0 1 0 10 3Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></symbol>
	<symbol id="i-wrench" viewBox="0 0 20 20"><path d="M12.5 3.5a4 4 0 0 0-4.8 5.3L3 13.5V17h3.5l4.7-4.7a4 4 0 0 0 5.3-4.8l-2.3 2.3-2.4-.6-.6-2.4 2.3-2.3Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></symbol>
	<symbol id="i-shield" viewBox="0 0 20 20"><path d="M10 2.5 4 5v5c0 3.6 2.5 6.4 6 7.5 3.5-1.1 6-3.9 6-7.5V5l-6-2.5Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m7.5 10 1.8 1.8 3.2-3.3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
	<symbol id="i-sliders" viewBox="0 0 20 20"><path d="M3 6h8M15 6h2M3 14h2M9 14h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="13" cy="6" r="2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="14" r="2" fill="none" stroke="currentColor" stroke-width="1.6"/></symbol>
	<symbol id="i-minus" viewBox="0 0 16 16"><path d="M3 8h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
	<symbol id="i-expand" viewBox="0 0 20 20"><path d="M12 3h5v5M8 17H3v-5M17 3l-6 6M3 17l6-6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
	<symbol id="i-mail" viewBox="0 0 20 20"><rect x="3" y="5" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m4 6 6 5 6-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></symbol>
	<symbol id="i-clock" viewBox="0 0 20 20"><circle cx="10" cy="10" r="7" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M10 6v4l3 2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
	<symbol id="i-plus" viewBox="0 0 16 16"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
	<symbol id="i-facebook" viewBox="0 0 20 20"><path d="M11.5 18v-6.5h2.2l.3-2.6h-2.5V7.3c0-.7.2-1.3 1.3-1.3H14V3.7a18 18 0 0 0-2-.1c-2 0-3.3 1.2-3.3 3.4v1.9H6.5v2.6h2.2V18h2.8Z" fill="currentColor"/></symbol>
	<symbol id="i-instagram" viewBox="0 0 20 20"><rect x="3" y="3" width="14" height="14" rx="4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.2" cy="5.8" r="1" fill="currentColor"/></symbol>
	<symbol id="i-tiktok" viewBox="0 0 20 20"><path d="M13 3c.3 1.9 1.6 3.3 3.5 3.5v2.6a6.2 6.2 0 0 1-3.5-1.1v5.3A4.8 4.8 0 1 1 8.2 8.5v2.7a2.2 2.2 0 1 0 2.2 2.2V3H13Z" fill="currentColor"/></symbol>
</svg>
		<?php
	},
	1
);
