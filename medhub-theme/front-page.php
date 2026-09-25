<?php
/**
 * Front page.
 *
 * The homepage is built from MedHub blocks stored in the front page's content,
 * so every heading, intro and FAQ is edited in WordPress. The theme only decides
 * how the blocks look.
 *
 * If the front page has no block content yet (e.g. it is still an Elementor page),
 * administrators see a notice and the "MedHub Homepage" pattern is rendered as a
 * starting preview. Visitors on production never see starter copy.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main site-main--home">
	<?php
	while ( have_posts() ) {
		the_post();

		if ( has_blocks( get_the_content() ) ) {
			the_content();
			continue;
		}

		$medhub_pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'medhub/home' );
		$medhub_preview = $medhub_pattern && ( current_user_can( 'edit_pages' ) || 'production' !== wp_get_environment_type() );

		if ( $medhub_preview ) {
			echo '<p class="admin-notice container">' . esc_html__( 'This front page has no MedHub blocks yet. Showing the "Homepage (MedHub)" pattern as a preview. Insert it in the block editor to make the copy editable.', 'medhub' ) . '</p>';
			echo do_blocks( $medhub_pattern['content'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- block output.
		} else {
			the_content();
		}
	}
	?>
</main>
<?php
get_footer();
