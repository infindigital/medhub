<?php
/**
 * Hero block. Prints the page's single H1.
 *
 * Interactive layer (progressive, see assets/src/js/modules/interactive.js):
 *  - several live products rotate in the visual (auto-advance pauses on hover/focus,
 *    dots + pause control, no auto-advance with reduced motion)
 *  - pointer-reactive 3D tilt, depth parallax and a cursor-following glow
 *  - floating fact chips built only from confirmed/site-sourced business details
 *  - word-by-word headline reveal
 *
 * Template part: get_template_part( 'template-parts/sections/hero', null, $args ).
 *
 * @package MedHub
 *
 * @var array $args Section settings (defaults below).
 *
 */

defined( 'ABSPATH' ) || exit;

$attributes = wp_parse_args(
	$args ?? array(),
	array(
		'eyebrow' => '',
		'heading' => '',
		'lead' => '',
		'primaryLabel' => '',
		'primaryUrl' => '',
		'secondaryLabel' => '',
		'secondaryUrl' => '',
		'productSlug' => '',
		'productSlugs' => '',
		'showProducts' => false,
		'categories' => '',
		'limit' => 4,
	)
);

// Products shown in the visual, always live from WooCommerce:
// 1. an optional hand-picked list (Product slugs), skipping anything unpublished,
//    out of stock or without an image;
// 2. otherwise (or to fill up) the products starred as "Featured" in WooCommerce;
// 3. otherwise the newest in-stock products with an image.
// Steps 2 and 3 are limited to the block's Categories (product category slugs) when set,
// so a landing page shows its own equipment. Nothing depends on a particular product existing.
// Only cut-out product photos (transparent or plain white background) are used, so each
// product floats in the arch instead of showing a photo backdrop.
$limit    = max( 1, min( 6, (int) ( $attributes['limit'] ?? 4 ) ) );
$cats     = array_values( array_filter( array_map( 'trim', explode( ',', (string) ( $attributes['categories'] ?? '' ) ) ) ) );
$scope    = $cats ? array( 'category' => $cats ) : array();
$usable   = static fn( $p ) => $p instanceof WC_Product && 'publish' === $p->get_status() && $p->is_in_stock() && $p->get_image_id() && $p->is_visible() && medhub_image_is_cutout( (int) $p->get_image_id() );
$slugs    = array_filter( array_map( 'trim', explode( ',', (string) ( $attributes['productSlugs'] ?? '' ) ) ) );
$slugs    = $slugs ? $slugs : array_filter( array( (string) ( $attributes['productSlug'] ?? '' ) ) );
$products = array_values( array_filter( array_map( 'medhub_get_product_by_slug', $slugs ), $usable ) );

// Automatic products only when the block asks for them (Show products, or Categories set);
// otherwise a hero without hand-picked products stays a text hero (About, Contact…).
$auto = ! empty( $attributes['showProducts'] ) || $cats;

foreach ( array( array( 'featured' => true ), array( 'orderby' => 'date', 'order' => 'DESC' ) ) as $fill ) {
	if ( ! $auto || count( $products ) >= $limit || ( $products && $slugs ) ) {
		break;
	}
	$taken = array_map( static fn( $p ) => $p->get_id(), $products );
	foreach ( medhub_get_products( array_merge( array( 'limit' => $limit * 10, 'stock_status' => 'instock' ), $scope, $fill ) ) as $candidate ) {
		if ( count( $products ) < $limit && $usable( $candidate ) && ! in_array( $candidate->get_id(), $taken, true ) ) {
			$products[] = $candidate;
		}
	}
}

$products = array_slice( $products, 0, $limit );
$count    = count( $products );
$uid      = wp_unique_id( 'hero-' );

// Floating facts (never invented: business config + live catalogue size).
$facts    = array();
$delivery = medhub_business( 'delivery' );
$address  = medhub_business( 'address' );
if ( $address ) {
	/* translators: %s: locality */
	$facts[] = array( 'pin', sprintf( __( 'Showroom in %s', 'medhub' ), $address['locality'] ) );
}
if ( $delivery ) {
	/* translators: %s: delivery fee */
	$facts[] = array( 'truck', sprintf( __( 'UAE delivery · %s', 'medhub' ), $delivery['fee'] ) );
}
if ( function_exists( 'medhub_get_category' ) && medhub_get_category( MEDHUB_RENTAL_CATEGORY ) ) {
	$facts[] = array( 'swap', __( 'Rent monthly', 'medhub' ) );
}
$facts = array_slice( $facts, 0, 2 );
?>
<section <?php echo 'class="' . esc_attr( 'hero' . ( $count ? ' hero--interactive' : ' hero--text' ) ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput ?> data-hero>
	<div class="hero__grid container">
		<div class="hero__copy">
			<?php if ( $attributes['eyebrow'] ) : ?>
				<p class="eyebrow hero__eyebrow"><span class="hero__pulse" aria-hidden="true"></span><?php echo esc_html( $attributes['eyebrow'] ); ?></p>
			<?php endif; ?>

			<h1 class="hero__title"><?php echo medhub_reveal_words( $attributes['heading'] ? $attributes['heading'] : get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?></h1>

			<?php if ( $attributes['lead'] ) : ?>
				<p class="hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<?php endif; ?>

			<div class="hero__actions">
				<?php
				echo medhub_button( array( 'label' => $attributes['primaryLabel'], 'url' => $attributes['primaryUrl'], 'size' => 'lg', 'attrs' => 'data-magnetic' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo medhub_button( array( 'label' => $attributes['secondaryLabel'], 'url' => $attributes['secondaryUrl'], 'variant' => 'secondary', 'size' => 'lg', 'icon' => '', 'attrs' => 'data-magnetic' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
		</div>

		<?php if ( $count ) : ?>
			<div class="hero__visual" data-tilt data-hero-slides<?php echo $count > 1 ? ' role="region" aria-roledescription="carousel" aria-label="' . esc_attr__( 'Featured equipment', 'medhub' ) . '"' : ''; ?>>
				<div class="hero__arch" data-spotlight>
					<span class="hero__glow" aria-hidden="true"></span>
					<span class="hero__orbit" aria-hidden="true" data-depth="0.4"></span>
					<span class="hero__orbit hero__orbit--inner" aria-hidden="true" data-depth="0.7"></span>

					<?php foreach ( $products as $i => $product ) : ?>
						<div class="hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide data-depth="1.2"<?php echo $count > 1 ? ' role="group" aria-roledescription="slide" aria-label="' . esc_attr( sprintf( '%d / %d', $i + 1, $count ) ) . '"' : ''; ?><?php echo 0 === $i ? '' : ' aria-hidden="true"'; ?>>
							<?php
							echo $product->get_image( // phpcs:ignore WordPress.Security.EscapeOutput
								'woocommerce_single',
								array(
									'class'         => 'hero__img',
									'loading'       => 0 === $i ? 'eager' : 'lazy',
									'fetchpriority' => 0 === $i ? 'high' : 'auto',
									'decoding'      => 'async',
									'sizes'         => '(min-width: 1024px) 40vw, 80vw',
								)
							);
							?>
						</div>
					<?php endforeach; ?>
				</div>

				<?php foreach ( $facts as $f => $fact ) : ?>
					<span class="hero__float hero__float--<?php echo (int) $f; ?>" data-depth="<?php echo 0 === $f ? '2' : '1.6'; ?>">
						<?php echo medhub_icon( $fact[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $fact[1] ); ?>
					</span>
				<?php endforeach; ?>

				<div class="hero__chips">
					<?php
					foreach ( $products as $i => $product ) :
						$stock = medhub_stock_state( $product );
						$cat   = medhub_primary_category( $product );
						?>
						<a class="hero__chip-link<?php echo 0 === $i ? ' is-active' : ''; ?>" href="<?php echo esc_url( $product->get_permalink() ); ?>" data-slide-chip<?php echo 0 === $i ? '' : ' aria-hidden="true" tabindex="-1"'; ?>>
							<span class="hero__chip-main">
								<?php if ( $cat ) : ?>
									<span class="hero__chip-cat"><?php echo esc_html( $cat->name ); ?></span>
								<?php endif; ?>
								<span class="hero__chip-name"><?php echo esc_html( $product->get_name() ); ?></span>
							</span>
							<span class="hero__chip-side">
								<?php if ( $stock ) : ?>
									<span class="stock stock--<?php echo esc_attr( $stock['state'] ); ?> stock--pulse"><?php echo esc_html( $stock['label'] ); ?></span>
								<?php endif; ?>
								<?php echo medhub_price_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>

				<?php if ( $count > 1 ) : ?>
					<div class="hero__controls">
						<div class="hero__dots" role="group" aria-label="<?php esc_attr_e( 'Choose product', 'medhub' ); ?>">
							<?php foreach ( $products as $i => $product ) : ?>
								<button class="hero__dot" type="button" data-slide-to="<?php echo (int) $i; ?>" aria-controls="<?php echo esc_attr( $uid ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>>
									<span class="screen-reader-text"><?php echo esc_html( $product->get_name() ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
						<button class="hero__pause" type="button" data-slide-pause aria-pressed="false">
							<span class="hero__pause-icon" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Pause rotation', 'medhub' ); ?></span>
						</button>
					</div>
					<p class="screen-reader-text" id="<?php echo esc_attr( $uid ); ?>" aria-live="polite" data-slide-status></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
