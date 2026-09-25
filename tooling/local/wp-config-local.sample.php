<?php
/**
 * LOCAL COPY ONLY – paste into the LocalWP site's wp-config.php,
 * ABOVE the line "That's all, stop editing!".
 *
 * Copy to wp-config-local.php if you want a private edited version (git-ignored).
 */

// Identify the environment. The theme and the safety guard both read this.
define( 'WP_ENVIRONMENT_TYPE', 'local' );

// No scheduled jobs on page load (the safety guard also blocks due events).
define( 'DISABLE_WP_CRON', true );

// Block ALL outbound HTTP from PHP (Telr API, Hostinger, Jetpack, WooCommerce.com,
// Google, Rank Math API…). Add hosts here only when a test needs them,
// e.g. 'secure.telr.com' while testing Telr in TEST mode.
define( 'WP_HTTP_BLOCK_EXTERNAL', true );
define( 'WP_ACCESSIBLE_HOSTS', '' );

// Debugging.
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );

// Never auto-update anything on the local copy.
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );

// --- Guards read by tooling/local/mu-plugins/medhub-local-safety.php ---

// Set to true ONLY after Telr has been switched to TEST mode in
// WooCommerce > Settings > Payments on this local copy (decision D2).
define( 'MEDHUB_LOCAL_TELR_TEST_CONFIRMED', false );

// Set to true only if these plugins conflict with the MedHub theme (decision D2).
define( 'MEDHUB_LOCAL_DISABLE_HFE', false );
define( 'MEDHUB_LOCAL_DISABLE_NASA_CORE', false );

// Temporarily set to true to test one specific scheduled job, then back to false.
define( 'MEDHUB_LOCAL_ALLOW_CRON', false );
