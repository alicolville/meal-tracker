<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Plugin Name:         Meal Tracker
 * Description:         Allow your users to track their meals and calorie intake for a given day.
 * Version:             3.3.1
 * Requires at least:   5.7
 * Tested up to:		6.8
 * Requires PHP:        7.2
 * Author:              Ali Colville
 * Author URI:          https://www.YeKen.uk
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         meal-tracker
 * Domain Path:         /core/languages
 */

define( 'YK_MT_ABSPATH', plugin_dir_path( __FILE__ ) );
define( 'YK_MT_PLUGIN_VERSION', '3.3.1' );
define( 'YK_MT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'YK_MT_SLUG', 'meal-tracker' );
define( 'YK_MT_BASE_URL', plugin_dir_url( __FILE__ ) );
define( 'YK_MT_CDN_CHART_JS', YK_MT_BASE_URL . 'assets/js/chart-4.4.4.min.js' );
define( 'YK_MT_CDN_FONT_AWESOME_CSS', YK_MT_BASE_URL . 'assets/css/fontawesome-4.7.0.min.css' );
define( 'YK_MT_YEKEN_UPDATES_URL', 'https://yeken.uk/downloads/_updates/meal-tracker.json' );
define( 'YK_MT_YEKEN_LATEST_RELEASE_MANIFEST', 'https://raw.githubusercontent.com/alicolville/meal-tracker/refs/heads/master/release.json' );

// -----------------------------------------------------------------------------------------
// AC: Include all relevant PHP files
// -----------------------------------------------------------------------------------------

include_once YK_MT_ABSPATH . 'core/globals.php';
include_once YK_MT_ABSPATH . 'core/functions.php';
require_once YK_MT_ABSPATH . 'plugin-update-checker/plugin-update-checker.php';
include_once YK_MT_ABSPATH . 'core/functions.pages.php';
include_once YK_MT_ABSPATH . 'core/functions.admin.php';
include_once YK_MT_ABSPATH . 'core/functions.settings.php';
include_once YK_MT_ABSPATH . 'core/functions.chart.php';
include_once YK_MT_ABSPATH . 'core/license.php';
include_once YK_MT_ABSPATH . 'core/setup.wizard.php';
include_once YK_MT_ABSPATH . 'core/marketing.php';

$is_premium = yk_mt_license_is_premium();

define( 'YK_MT_IS_PREMIUM', $is_premium );

include_once YK_MT_ABSPATH . 'core/caching.php';
include_once YK_MT_ABSPATH . 'core/db.php';
include_once YK_MT_ABSPATH . 'core/shortcode-functions.php';
include_once YK_MT_ABSPATH . 'core/shortcode-meal-tracker.php';
include_once YK_MT_ABSPATH . 'core/shortcode-chart.php';
include_once YK_MT_ABSPATH . 'core/shortcode-chart-entries.php';
include_once YK_MT_ABSPATH . 'core/shortcode-table-entries.php';
include_once YK_MT_ABSPATH . 'core/shortcode-various.php';
include_once YK_MT_ABSPATH . 'core/ajax.php';
include_once YK_MT_ABSPATH . 'core/activate.php';
include_once YK_MT_ABSPATH . 'core/hooks.php';
include_once YK_MT_ABSPATH . 'core/sources-weight-tracker.php';
include_once YK_MT_ABSPATH . 'core/cron.php';
include_once YK_MT_ABSPATH . 'core/meta.php';
include_once YK_MT_ABSPATH . 'core/external-sources.php';

if( true === YK_MT_IS_PREMIUM ) {
	include_once YK_MT_ABSPATH . 'core/gamification.php';
}

$has_external = yk_mt_ext_enabled();

define( 'YK_MT_HAS_EXTERNAL_SOURCES', $has_external );

// Admin pages
include_once YK_MT_ABSPATH . 'core/admin-pages/user/data-dashboard.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/user/data-entry.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/user/data-home.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/user/data-user.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/user/data-search-results.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/page.license.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/page.settings.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/page.help.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/page.setup.wizard.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/meals/meals-home.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/meals/meals-dashboard.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/meals/meals-add-edit.php';
include_once YK_MT_ABSPATH . 'core/admin-pages/meals/meals-import.php';

// -----------------------------------------------------------------------------------------
// AC: Load relevant language files
// -----------------------------------------------------------------------------------------

load_plugin_textdomain( YK_MT_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/core/languages/' );

// -----------------------------------------------------------------------------------------
// Since we're no longer hosted on WordPress.org, use the following for auto updates
// -----------------------------------------------------------------------------------------
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$mt_plugin_updater = PucFactory::buildUpdateChecker( YK_MT_YEKEN_LATEST_RELEASE_MANIFEST, __FILE__, YK_MT_SLUG );