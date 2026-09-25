<?php
/**
 * Header navigation structure (desktop + mobile drawer).
 *
 * Items reference departments (config/departments.php), existing page paths or
 * WooCommerce category slugs. Anything that doesn't resolve on the current site
 * is skipped. A WordPress menu assigned to the "primary" location can replace
 * this later without code changes to the templates (step 2D follow-up).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

return array(
	array(
		'label'       => 'Medical Equipment',
		'type'        => 'mega',
		'departments' => array( 'hospital', 'patient-care', 'monitoring', 'supplies' ),
		'all_link'    => true, // Adds "Shop all equipment" (WooCommerce shop page).
	),
	array(
		'label'       => 'Respiratory Care',
		'type'        => 'mega',
		'departments' => array( 'respiratory', 'oxygen', 'sleep' ),
		'compact'     => true, // Shows only the first terms of each department.
	),
	array(
		'label'       => 'Oxygen Therapy',
		'type'        => 'mega',
		'departments' => array( 'oxygen' ),
		'feature'     => true,
	),
	array(
		'label'       => 'Sleep Care',
		'type'        => 'mega',
		'departments' => array( 'sleep' ),
		'feature'     => true,
	),
	array(
		'label' => 'Rental',
		'type'  => 'term',
		'slug'  => 'medical-equipment-rental',
	),
	array(
		'label' => 'Services',
		'type'  => 'dropdown',
		'items' => array(
			array( 'label' => 'Medical Equipment Rental', 'term' => 'medical-equipment-rental' ),
			array( 'label' => 'DeVilbiss Service Centre', 'page' => 'devilbiss-service-centre-in-dubai' ),
		),
	),
	array(
		'label' => 'About',
		'type'  => 'page',
		'path'  => 'medical-equipment',
	),
	array(
		'label' => 'Contact',
		'type'  => 'page',
		'path'  => 'contact-us-medhub',
	),
);
