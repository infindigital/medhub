<?php
/**
 * Search dialog. Submits to the standard WordPress/WooCommerce product search
 * (?s=…&post_type=product). Live suggestions come from the same-origin
 * WooCommerce Store API (assets/src/js/modules/search.js) – debounced, min 3 chars.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$medhub_popular = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 6,
		'exclude'    => array_filter( array( (int) get_option( 'default_product_cat' ) ) ),
	)
);
?>
<dialog class="search-dialog" id="search-dialog" aria-labelledby="search-dialog-title">
	<div class="search-dialog__inner container">
		<div class="search-dialog__head">
			<h2 class="search-dialog__title" id="search-dialog-title"><?php esc_html_e( 'Search equipment', 'medhub' ); ?></h2>
			<button class="icon-btn" type="button" data-close-dialog>
				<?php echo medhub_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'medhub' ); ?></span>
			</button>
		</div>

		<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-search>
			<label class="screen-reader-text" for="search-dialog-input"><?php esc_html_e( 'Search products', 'medhub' ); ?></label>
			<?php echo medhub_icon( 'search', '', 'search-form__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<input class="search-form__input" id="search-dialog-input" type="search" name="s" autocomplete="off"
				placeholder="<?php esc_attr_e( 'CPAP mask, oxygen concentrator, patient bed…', 'medhub' ); ?>"
				aria-controls="search-results" aria-autocomplete="list" data-search-input>
			<input type="hidden" name="post_type" value="product">
			<button class="btn btn--primary btn--md" type="submit"><span><?php esc_html_e( 'Search', 'medhub' ); ?></span></button>
		</form>

		<div class="search-results" id="search-results" aria-live="polite" data-search-results
			data-endpoint="<?php echo esc_url( rest_url( 'wc/store/v1/products' ) ); ?>"
			data-label-none="<?php esc_attr_e( 'No products match that search yet.', 'medhub' ); ?>"></div>

		<?php if ( $medhub_popular && ! is_wp_error( $medhub_popular ) ) : ?>
			<div class="search-dialog__popular">
				<p class="eyebrow"><?php esc_html_e( 'Popular categories', 'medhub' ); ?></p>
				<ul class="chips">
					<?php foreach ( $medhub_popular as $term ) : ?>
						<li><a class="chip" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</dialog>
