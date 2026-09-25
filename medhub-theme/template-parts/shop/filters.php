<?php
/**
 * Archive filters: a plain GET form that works without JavaScript.
 * Desktop: sidebar. Mobile: the same form is moved into a <dialog> by filters.js.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$state  = medhub_filter_state();
$brands = medhub_archive_brand_options();
$range  = medhub_archive_price_range();

// Keep non-filter query args (search term, post type, sort) when the form submits.
$keep = array();
foreach ( array( 's', 'post_type', 'orderby' ) as $key ) {
	if ( isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$keep[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
?>
<aside class="filters" aria-labelledby="filters-title" data-filters-home>
	<form class="filters__form" method="get" action="<?php echo esc_url( medhub_archive_clean_url() ); ?>" data-filters-form>
		<div class="filters__head">
			<h2 class="filters__title" id="filters-title"><?php esc_html_e( 'Filters', 'medhub' ); ?></h2>
			<button class="icon-btn filters__close" type="button" data-close-dialog>
				<?php echo medhub_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close filters', 'medhub' ); ?></span>
			</button>
		</div>

		<?php foreach ( $keep as $key => $value ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php endforeach; ?>

		<fieldset class="filters__group">
			<legend><?php esc_html_e( 'Availability', 'medhub' ); ?></legend>
			<label class="check">
				<input type="checkbox" name="stock" value="instock" <?php checked( $state['stock'] ); ?>>
				<span><?php esc_html_e( 'In stock only', 'medhub' ); ?></span>
			</label>
		</fieldset>

		<?php if ( $range && $range['max'] > $range['min'] ) : ?>
			<fieldset class="filters__group">
				<legend><?php esc_html_e( 'Price (AED)', 'medhub' ); ?></legend>
				<div class="price-range">
					<label>
						<span><?php esc_html_e( 'Min', 'medhub' ); ?></span>
						<input type="number" name="min_price" inputmode="numeric" min="0" step="1" placeholder="<?php echo esc_attr( $range['min'] ); ?>" value="<?php echo null !== $state['min'] ? esc_attr( (int) $state['min'] ) : ''; ?>">
					</label>
					<span aria-hidden="true">–</span>
					<label>
						<span><?php esc_html_e( 'Max', 'medhub' ); ?></span>
						<input type="number" name="max_price" inputmode="numeric" min="0" step="1" placeholder="<?php echo esc_attr( $range['max'] ); ?>" value="<?php echo null !== $state['max'] ? esc_attr( (int) $state['max'] ) : ''; ?>">
					</label>
				</div>
			</fieldset>
		<?php endif; ?>

		<?php if ( count( $brands ) > 1 ) : ?>
			<fieldset class="filters__group">
				<legend><?php esc_html_e( 'Brand', 'medhub' ); ?></legend>
				<ul class="filters__options">
					<?php foreach ( $brands as $option ) : ?>
						<li>
							<label class="check">
								<input type="checkbox" name="filter_brand[]" value="<?php echo esc_attr( $option['term']->slug ); ?>" <?php checked( in_array( $option['term']->slug, $state['brands'], true ) ); ?>>
								<span><?php echo esc_html( $option['term']->name ); ?></span>
								<small><?php echo esc_html( number_format_i18n( $option['count'] ) ); ?></small>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
		<?php endif; ?>

		<div class="filters__actions">
			<button class="btn btn--primary btn--md" type="submit"><span><?php esc_html_e( 'Apply filters', 'medhub' ); ?></span></button>
			<a class="btn btn--ghost" href="<?php echo esc_url( medhub_archive_clean_url() ); ?>"><span><?php esc_html_e( 'Reset', 'medhub' ); ?></span></a>
		</div>
	</form>
</aside>

<dialog class="filters-dialog" id="filters-dialog" aria-labelledby="filters-title" data-filters-dialog></dialog>
