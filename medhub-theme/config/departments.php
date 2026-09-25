<?php
/**
 * Departments: the navigation grouping of MedHub's flat WooCommerce categories.
 *
 * STRUCTURE ONLY – no product data. Category names, links, counts and images are
 * resolved live from WooCommerce (inc/helpers/departments.php). A category or page
 * that does not exist (or is empty) is skipped automatically, never linked.
 *
 *   terms    – product_cat slugs, in display order (first = primary link if no hub)
 *   hub      – where the department itself links: ['page' => path] or ['term' => slug]
 *   guides   – existing landing pages (paths) shown in the mega menu
 *   visual   – optional product slug for the department image; otherwise the most
 *              expensive in-stock product with an image is used
 *   tagline  – short editorial line (UI microcopy, not SEO content)
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

return array(

	'oxygen'       => array(
		'label'   => 'Oxygen Therapy',
		'tagline' => 'Home, portable and rental oxygen',
		'terms'   => array( 'oxygen-concentrator', 'buy-oxygen-concentrator', 'portable-oxygen-concentrator', 'oxygen-concentrator-accessories' ),
		'hub'     => array( 'term' => 'oxygen-concentrator' ),
		'guides'  => array( 'oxygen-concentrator-in-dubai', 'portable-oxygen-machine-in-dubai', 'oxygen-machine-in-dubai' ),
		'visual'  => 'inogen-rove-6-portable-oxygen-concentrator',
	),

	'sleep'        => array(
		'label'   => 'Sleep Care',
		'tagline' => 'CPAP, BiPAP, masks and accessories',
		'terms'   => array( 'auto-cpap-machine', 'cpap', 'travel-cpap-machine', 'bipap', 'auto-bipap-machine', 'buy-cpap-bipap-masks', 'cpap-bipap-accessories', 'sleep-support-comfort-solutions' ),
		'hub'     => array( 'page' => 'cpap-in-dubai' ),
		'guides'  => array( 'cpap-in-dubai', 'bipap-in-dubai' ),
		'visual'  => 'philips-dreamstation-auto-bipap-with-humidifier-and-mask',
	),

	'respiratory'  => array(
		'label'   => 'Respiratory Care',
		'tagline' => 'Ventilation, suction and airway care',
		'terms'   => array( 'ventilator-accessories-in-dubai', 'portable-ventilator', 'suction-machine', 'tracheostomy-care-products-in-dubai' ),
		'hub'     => array( 'term' => 'suction-machine' ),
		'guides'  => array(),
		'visual'  => 'devilbiss-7325p-ur-vacu-aide-suction-machine',
	),

	'hospital'     => array(
		'label'   => 'Hospital Equipment',
		'tagline' => 'Electric beds and hospital furniture',
		'terms'   => array( 'hospital-furniture', 'patient-bed' ),
		'hub'     => array( 'term' => 'hospital-furniture' ),
		'guides'  => array(),
		'visual'  => 'b6e-three-function-electric-bed-healthward',
	),

	'patient-care' => array(
		'label'   => 'Patient Care',
		'tagline' => 'Feeding, infusion and therapy',
		'terms'   => array( 'enteral-feeding-pump', 'feeding-pump-accessories', 'feeding-milk-nutritional-supplement', 'infusion-pump', 'nasogastric-tubes', 'therapy-and-massage-devices-in-dubai' ),
		'hub'     => array( 'term' => 'enteral-feeding-pump' ),
		'guides'  => array(),
		'visual'  => 'covidien-kangaroo-epump-enteral-feeding-pump',
	),

	'monitoring'   => array(
		'label'   => 'Monitoring',
		'tagline' => 'Vital signs, ECG and pulse oximetry',
		'terms'   => array( 'health-monitoring-devices', 'patient-monitor', 'vital-sign-monitor-in-dubai', 'pulse-oximeter', 'electrocardiogram-machine' ),
		'hub'     => array( 'term' => 'health-monitoring-devices' ),
		'guides'  => array(),
		'visual'  => '',
	),

	'supplies'     => array(
		'label'   => 'Medical Disposables',
		'tagline' => 'Wound care, hygiene and consumables',
		'terms'   => array( 'medical-disposables', 'hygiene-and-infection-control-products', 'wound-care' ),
		'hub'     => array( 'term' => 'medical-disposables' ),
		'guides'  => array(),
		'visual'  => 'molnlycke-mepilex-border-sacrum-16x20cm-6-3x7-9-inch-5-pack',
	),

	'rental'       => array(
		'label'   => 'Medical Equipment Rental',
		'tagline' => 'Monthly rental plans',
		'terms'   => array( 'medical-equipment-rental' ),
		'hub'     => array( 'term' => 'medical-equipment-rental' ),
		'guides'  => array(),
		'visual'  => 'philips-everflo-5-ltr-oxygen-concentrator-rental',
	),
);
