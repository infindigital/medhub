<?php
/**
 * Product gallery: native scroll-snap slides + thumbnails + full-size lightbox.
 * Replaces WooCommerce's flexslider/zoom/photoswipe (no jQuery, ~2 KB JS).
 * Works without JS: slides remain swipeable/scrollable and images are in the HTML.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'];
$ids     = array_values( array_unique( array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) ) );
$name    = $product->get_name();
$count   = count( $ids );
?>
<div class="gallery" data-gallery>
	<div class="gallery__stage">
		<?php if ( $product->is_on_sale() && ! medhub_is_quote_only( $product ) ) : ?>
			<span class="badge badge--sale gallery__badge"><?php esc_html_e( 'Sale', 'medhub' ); ?></span>
		<?php elseif ( medhub_is_rental( $product ) ) : ?>
			<span class="badge badge--rent gallery__badge"><?php esc_html_e( 'Rental', 'medhub' ); ?></span>
		<?php endif; ?>

		<div class="gallery__track" data-gallery-track tabindex="0" aria-label="<?php esc_attr_e( 'Product images', 'medhub' ); ?>">
			<?php if ( ! $ids ) : ?>
				<figure class="gallery__slide"><?php echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
			<?php endif; ?>

			<?php foreach ( $ids as $i => $id ) : ?>
				<figure class="gallery__slide" data-index="<?php echo (int) $i; ?>">
					<?php
					$alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
					echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput
						$id,
						'woocommerce_single',
						false,
						array(
							'class'         => 'gallery__img',
							'alt'           => $alt ? $alt : ( $count > 1 ? sprintf( '%1$s – image %2$d of %3$d', $name, $i + 1, $count ) : $name ),
							'loading'       => 0 === $i ? 'eager' : 'lazy',
							'fetchpriority' => 0 === $i ? 'high' : 'auto',
							'decoding'      => 'async',
							'sizes'         => '(min-width: 1024px) 50vw, 100vw',
							'data-full'     => wp_get_attachment_image_url( $id, 'full' ),
						)
					);
					?>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( $ids ) : ?>
			<button class="icon-btn gallery__zoom" type="button" data-gallery-open>
				<?php echo medhub_icon( 'expand' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php esc_html_e( 'View full-size image', 'medhub' ); ?></span>
			</button>
		<?php endif; ?>
	</div>

	<?php if ( $count > 1 ) : ?>
		<ul class="gallery__thumbs" aria-label="<?php esc_attr_e( 'Choose image', 'medhub' ); ?>">
			<?php foreach ( $ids as $i => $id ) : ?>
				<li>
					<button class="gallery__thumb" type="button" data-gallery-thumb="<?php echo (int) $i; ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>>
						<?php echo wp_get_attachment_image( $id, 'woocommerce_gallery_thumbnail', false, array( 'alt' => '', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php /* translators: 1: image number, 2: total */ ?>
						<span class="screen-reader-text"><?php echo esc_html( sprintf( __( 'Show image %1$d of %2$d', 'medhub' ), $i + 1, $count ) ); ?></span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<dialog class="lightbox" data-gallery-lightbox aria-label="<?php echo esc_attr( $name ); ?>">
		<button class="icon-btn lightbox__close" type="button" data-close-dialog>
			<?php echo medhub_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Close', 'medhub' ); ?></span>
		</button>
		<img class="lightbox__img" alt="" data-gallery-lightbox-img>
	</dialog>
</div>
