<?php
/**
 * Site footer and document close.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/footer/site-footer' );

// Modal dialogs live after the page content so the DOM (and heading order) starts with the page itself.
get_template_part( 'template-parts/navigation/drawer', null, array( 'nav' => medhub_navigation() ) );
get_template_part( 'template-parts/navigation/search' );
wp_footer();
?>
</body>
</html>
