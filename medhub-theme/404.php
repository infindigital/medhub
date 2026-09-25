<?php
/**
 * Not found.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header();

$medhub_depts = array_values( array_filter( array_map( 'medhub_get_department', array_keys( medhub_departments_config() ) ) ) );
?>
<main id="main" class="site-main site-main--404">
	<div class="container not-found">
		<p class="eyebrow"><?php esc_html_e( 'Error 404', 'medhub' ); ?></p>
		<h1 class="not-found__title"><?php echo medhub_accent_text( __( 'This page has *moved or gone.*', 'medhub' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
		<p class="not-found__lead"><?php esc_html_e( 'Search the catalogue or pick a department below.', 'medhub' ); ?></p>
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-search">
			<label class="screen-reader-text" for="nf-search"><?php esc_html_e( 'Search products', 'medhub' ); ?></label>
			<input class="field-input" id="nf-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search products…', 'medhub' ); ?>">
			<input type="hidden" name="post_type" value="product">
			<button class="btn btn--primary btn--md" type="submit"><span><?php esc_html_e( 'Search', 'medhub' ); ?></span></button>
		</form>
		<?php if ( $medhub_depts ) : ?>
			<ul class="chips not-found__links">
				<?php foreach ( $medhub_depts as $medhub_d ) : ?>
					<li><a class="chip" href="<?php echo esc_url( $medhub_d['url'] ); ?>"><?php echo esc_html( $medhub_d['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
