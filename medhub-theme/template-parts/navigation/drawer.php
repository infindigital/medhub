<?php
/**
 * Mobile navigation drawer (native <dialog>: focus trap, Esc and inert background built in).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

$nav = $args['nav'] ?? array();
?>
<dialog class="drawer" id="nav-drawer" aria-label="<?php esc_attr_e( 'Menu', 'medhub' ); ?>">
	<div class="drawer__head">
		<?php get_template_part( 'template-parts/header/logo' ); ?>
		<button class="icon-btn" type="button" data-close-dialog>
			<?php echo medhub_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'medhub' ); ?></span>
		</button>
	</div>

	<nav class="drawer__nav" aria-label="<?php esc_attr_e( 'Main', 'medhub' ); ?>">
		<ul class="drawer__list">
			<?php foreach ( $nav as $item ) : ?>
				<li>
					<?php if ( 'mega' === $item['type'] ) : ?>
						<details class="drawer__group">
							<summary><?php echo esc_html( $item['label'] ); ?><?php echo medhub_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></summary>
							<?php foreach ( $item['departments'] as $dept ) : ?>
								<div class="drawer__dept">
									<a class="drawer__dept-link" href="<?php echo esc_url( $dept['url'] ); ?>"><?php echo esc_html( $dept['label'] ); ?></a>
									<ul>
										<?php foreach ( $dept['terms'] as $term ) : ?>
											<li><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
										<?php endforeach; ?>
										<?php foreach ( $dept['guides'] as $guide ) : ?>
											<li><a href="<?php echo esc_url( $guide['url'] ); ?>"><?php echo esc_html( $guide['label'] ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>
						</details>
					<?php elseif ( 'dropdown' === $item['type'] ) : ?>
						<details class="drawer__group">
							<summary><?php echo esc_html( $item['label'] ); ?><?php echo medhub_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></summary>
							<ul class="drawer__sub">
								<?php foreach ( $item['items'] as $link ) : ?>
									<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</details>
					<?php else : ?>
						<a class="drawer__link" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<div class="drawer__foot">
		<?php get_template_part( 'template-parts/components/contact-actions' ); ?>
	</div>
</dialog>
