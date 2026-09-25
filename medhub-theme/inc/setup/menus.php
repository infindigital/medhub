<?php
/**
 * Menu locations.
 *
 * Registering locations writes nothing to the database. Menus are only created
 * and assigned later, on staging, with approval. Until then the header and footer
 * fall back to the department map in config/departments.php (step 2D).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		register_nav_menus(
			array(
				'primary' => __( 'Primary navigation', 'medhub' ),
				'footer'  => __( 'Footer navigation', 'medhub' ),
				'legal'   => __( 'Legal links', 'medhub' ),
			)
		);
	}
);
