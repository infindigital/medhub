<?php
/**
 * Front page: the designed layout (config/pages.php "home") around the front page's
 * content (H1, intro and the bento / FAQ groups, edited with built-in blocks).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/layout/landing', null, array( 'post' => get_post() ) );
}

get_footer();
