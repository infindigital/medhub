<?php
/**
 * MedHub theme bootstrap.
 *
 * This file only loads modules from /inc. Keep logic out of here.
 * The theme is a presentation layer: it never writes product, order,
 * customer or WooCommerce settings data, and never prints SEO tags
 * (Rank Math owns titles, meta, canonicals, Open Graph and schema).
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

define( 'MEDHUB_VERSION', '0.1.0' );
define( 'MEDHUB_DIR', get_template_directory() );
define( 'MEDHUB_URI', get_template_directory_uri() );

$medhub_modules = array(
	'inc/helpers/business.php',
	'inc/helpers/icons.php',
	'inc/helpers/template-tags.php',
	'inc/helpers/departments.php',
	'inc/woocommerce/products.php',
	'inc/setup/theme-supports.php',
	'inc/setup/menus.php',
	'inc/setup/image-sizes.php',
	'inc/setup/cleanup.php',
	'inc/assets.php',
	'inc/blocks.php',
	'inc/seo/faq-schema.php',
	'inc/dev.php',
);

// Hooks into WooCommerce only when it is active.
if ( class_exists( 'WooCommerce' ) ) {
	$medhub_modules[] = 'inc/woocommerce/support.php';
}

foreach ( $medhub_modules as $medhub_module ) {
	require_once MEDHUB_DIR . '/' . $medhub_module;
}

unset( $medhub_modules, $medhub_module );
