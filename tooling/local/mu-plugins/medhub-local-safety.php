<?php
/**
 * Plugin Name: MedHub – Local Safety Guard
 * Description: LOCAL COPY ONLY. Isolates the imported medhub.ae copy: no real payments, no tracking, no marketing, no outbound email to customers, no scheduled jobs, not indexable. Makes no database writes.
 * Version:     1.0.0
 *
 * Install: copy into the LocalWP site's wp-content/mu-plugins/ (see docs/local-setup.md).
 * NEVER deploy this file to production. It lives in /tooling, outside the theme.
 *
 * Every guard works at runtime (filters), so nothing in the imported database is changed.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

/*
 * Hard stop: only act on a local environment whose host is not medhub.ae.
 * If this file were ever copied to a real server by mistake, it does nothing.
 */
$medhub_host = (string) wp_parse_url( get_option( 'home' ), PHP_URL_HOST );

if (
	'local' !== wp_get_environment_type()
	|| '' === $medhub_host
	|| preg_match( '/(^|\.)medhub\.ae$/i', $medhub_host )
) {
	return;
}

define( 'MEDHUB_LOCAL_SAFETY', true );

/*
 * 1. Plugins switched off at runtime (the active_plugins option is NOT rewritten).
 *    Folder names are matched, so exact plugin file names don't matter.
 *    Optional extras (HFE, nasa-core) are enabled via constants in wp-config.php
 *    only if they conflict with the theme – decision D2.
 */
function medhub_local_blocked_plugin_dirs(): array {
	$dirs = array(
		'hostinger-reach',                                  // Email marketing.
		'woocommerce-google-adwords-conversion-tracking-tag', // Pixel Manager: Google Ads / GA4 conversions.
		'jetpack',                                          // WordPress.com connection + stats.
	);

	if ( defined( 'MEDHUB_LOCAL_DISABLE_HFE' ) && MEDHUB_LOCAL_DISABLE_HFE ) {
		$dirs[] = 'header-footer-elementor';
	}
	if ( defined( 'MEDHUB_LOCAL_DISABLE_NASA_CORE' ) && MEDHUB_LOCAL_DISABLE_NASA_CORE ) {
		$dirs[] = 'nasa-core';
	}

	return $dirs;
}

add_filter(
	'option_active_plugins',
	static function ( $plugins ) {
		$blocked = medhub_local_blocked_plugin_dirs();

		return array_values(
			array_filter(
				(array) $plugins,
				static fn( $plugin ) => ! in_array( strtok( (string) $plugin, '/' ), $blocked, true )
			)
		);
	}
);

/*
 * 2. Payments: until Telr has been switched to TEST mode on this copy and
 *    MEDHUB_LOCAL_TELR_TEST_CONFIRMED is set, only offline gateways are offered.
 *    This prevents a real card being charged with the live Telr keys in the imported DB.
 */
add_filter(
	'woocommerce_available_payment_gateways',
	static function ( $gateways ) {
		if ( defined( 'MEDHUB_LOCAL_TELR_TEST_CONFIRMED' ) && MEDHUB_LOCAL_TELR_TEST_CONFIRMED ) {
			return $gateways;
		}

		$offline = array( 'cod', 'bacs', 'cheque' );

		return array_intersect_key( (array) $gateways, array_flip( $offline ) );
	},
	PHP_INT_MAX
);

/*
 * 3. Email: force PHP mail(), which LocalWP routes to Mailpit, even if an SMTP
 *    plugin in the imported DB tries to use real SMTP credentials.
 */
add_action(
	'phpmailer_init',
	static function ( $phpmailer ) {
		$phpmailer->isMail();
		$phpmailer->addCustomHeader( 'X-MedHub-Local', 'captured-by-mailpit' );
	},
	PHP_INT_MAX
);

/*
 * 4. Scheduled jobs: no cron events run (abandoned-cart mails, feeds, syncs…).
 *    Set MEDHUB_LOCAL_ALLOW_CRON to true temporarily to test a specific job.
 */
add_filter(
	'pre_get_ready_cron_jobs',
	static function ( $pre ) {
		return ( defined( 'MEDHUB_LOCAL_ALLOW_CRON' ) && MEDHUB_LOCAL_ALLOW_CRON ) ? $pre : array();
	}
);

/*
 * 5. Never indexable.
 */
add_filter( 'pre_option_blog_public', static fn() => '0' );
add_filter(
	'wp_robots',
	static function ( $robots ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		return $robots;
	},
	PHP_INT_MAX
);

/*
 * 6. Browser-side tracking: neutralise tag URLs that may be printed by the theme
 *    options, snippets or plugins still active (GTM-52GJSMTM etc.).
 *    Front end only; admin and REST/AJAX responses are untouched.
 */
add_action(
	'template_redirect',
	static function () {
		if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
			return;
		}

		ob_start(
			static function ( $html ) {
				return str_replace(
					array(
						'googletagmanager.com',
						'google-analytics.com',
						'googleadservices.com',
						'googleads.g.doubleclick.net',
						'connect.facebook.net',
						'analytics.tiktok.com',
					),
					'tracking-blocked.invalid',
					$html
				);
			}
		);
	},
	0
);

/*
 * 7. Visible reminder in the admin bar.
 */
add_action(
	'admin_bar_menu',
	static function ( $bar ) {
		$telr = defined( 'MEDHUB_LOCAL_TELR_TEST_CONFIRMED' ) && MEDHUB_LOCAL_TELR_TEST_CONFIRMED ? 'Telr test' : 'offline gateways only';

		$bar->add_node(
			array(
				'id'    => 'medhub-local-safety',
				'title' => 'LOCAL COPY · safety on · ' . $telr,
				'meta'  => array( 'title' => 'MedHub local safety guard is active (tooling/local/mu-plugins).' ),
			)
		);
	},
	1
);
