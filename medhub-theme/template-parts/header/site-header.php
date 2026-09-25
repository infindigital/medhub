<?php
/**
 * Site header: logo, primary navigation (mega menus), search, cart, contact CTA.
 *
 * Mega menus follow the disclosure pattern: a <button aria-expanded> controls a panel.
 * Without JS the panels stay reachable through the mobile drawer and the hub links.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$medhub_nav      = medhub_navigation();
$medhub_whatsapp = medhub_business( 'whatsapp' );
$medhub_contact  = medhub_get_page( 'contact-us-medhub' );
?>
<header class="site-header" data-header>
	<div class="site-header__bar container">
		<div class="site-header__brand">
			<?php get_template_part( 'template-parts/header/logo' ); ?>
		</div>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Main', 'medhub' ); ?>" data-nav>
			<ul class="primary-nav__list">
				<?php foreach ( $medhub_nav as $medhub_item ) : ?>
					<?php if ( in_array( $medhub_item['type'], array( 'mega', 'dropdown' ), true ) ) : ?>
						<li class="primary-nav__item primary-nav__item--<?php echo esc_attr( $medhub_item['type'] ); ?>">
							<button class="primary-nav__trigger" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $medhub_item['id'] ); ?>" data-nav-trigger>
								<?php echo esc_html( $medhub_item['label'] ); ?>
								<?php echo medhub_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</button>
							<?php
							get_template_part(
								'mega' === $medhub_item['type'] ? 'template-parts/navigation/mega-panel' : 'template-parts/navigation/dropdown-panel',
								null,
								array( 'item' => $medhub_item )
							);
							?>
						</li>
					<?php else : ?>
						<li class="primary-nav__item">
							<a class="primary-nav__link" href="<?php echo esc_url( $medhub_item['url'] ); ?>"><?php echo esc_html( $medhub_item['label'] ); ?></a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="site-header__tools">
			<button class="icon-btn" type="button" data-open-dialog="search-dialog" aria-haspopup="dialog">
				<?php echo medhub_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'medhub' ); ?></span>
			</button>

			<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
				<a class="icon-btn icon-btn--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
					<?php echo medhub_icon( 'bag' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Cart', 'medhub' ); ?></span>
					<?php echo medhub_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endif; ?>

			<?php
			if ( $medhub_whatsapp ) {
				printf(
					'<a class="btn btn--whatsapp btn--sm site-header__cta" href="%s" target="_blank" rel="noopener">%s<span>%s</span></a>',
					esc_url( 'https://wa.me/' . rawurlencode( (string) $medhub_whatsapp ) ),
					medhub_icon( 'chat' ), // phpcs:ignore WordPress.Security.EscapeOutput
					esc_html__( 'WhatsApp', 'medhub' )
				);
			} elseif ( $medhub_contact ) {
				// WhatsApp number not confirmed yet (config/business.php) → contact page instead.
				echo medhub_button( // phpcs:ignore WordPress.Security.EscapeOutput
					array(
						'label'   => __( 'Contact us', 'medhub' ),
						'url'     => get_permalink( $medhub_contact ),
						'variant' => 'primary',
						'size'    => 'sm',
						'icon'    => '',
						'attrs'   => 'data-cta="header"',
					)
				);
			}
			?>

			<button class="icon-btn site-header__menu" type="button" data-open-dialog="nav-drawer" aria-haspopup="dialog">
				<?php echo medhub_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'medhub' ); ?></span>
			</button>
		</div>
	</div>
	<div class="mega-backdrop" data-mega-backdrop hidden></div>
</header>
