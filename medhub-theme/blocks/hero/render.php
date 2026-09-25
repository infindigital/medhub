<?php
/**
 * Hero block. Prints the page's single H1.
 *
 * @package MedHub
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$product = medhub_get_product_by_slug( $attributes['productSlug'] );
$stock   = $product ? medhub_stock_state( $product ) : null;
$cat     = $product ? medhub_primary_category( $product ) : null;
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'hero' . ( $product ? '' : ' hero--text' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="hero__grid container">
		<div class="hero__copy">
			<?php if ( $attributes['eyebrow'] ) : ?>
				<p class="eyebrow"><?php echo esc_html( $attributes['eyebrow'] ); ?></p>
			<?php endif; ?>

			<h1 class="hero__title"><?php echo medhub_accent_text( $attributes['heading'] ? $attributes['heading'] : get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>

			<?php if ( $attributes['lead'] ) : ?>
				<p class="hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<?php endif; ?>

			<div class="hero__actions">
				<?php
				echo medhub_button( array( 'label' => $attributes['primaryLabel'], 'url' => $attributes['primaryUrl'], 'size' => 'lg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo medhub_button( array( 'label' => $attributes['secondaryLabel'], 'url' => $attributes['secondaryUrl'], 'variant' => 'secondary', 'size' => 'lg', 'icon' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
		</div>

		<?php if ( $product ) : ?>
			<figure class="hero__visual">
				<div class="hero__arch">
					<?php
					echo $product->get_image( // phpcs:ignore WordPress.Security.EscapeOutput
						'woocommerce_single',
						array(
							'class'         => 'hero__img',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
							'sizes'         => '(min-width: 1024px) 40vw, 80vw',
						)
					);
					?>
				</div>
				<figcaption class="hero__chip">
					<a class="hero__chip-link" href="<?php echo esc_url( $product->get_permalink() ); ?>">
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
				</figcaption>
			</figure>
		<?php endif; ?>
	</div>
</section>
