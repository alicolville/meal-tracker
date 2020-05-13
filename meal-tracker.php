<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Plugin Name: Meal Tracker
 * Description: Allow your users to track their meals and calorie intake for a given day.
 * Version: 2.0
 * Author: YeKen
 * Author URI: http://www.YeKen.uk
 * License: GPL2
 * Text Domain: meal-tracker
 */
/*  Copyright 2020 YeKen.uk

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'YK_MT_ABSPATH', plugin_dir_path( __FILE__ ) );
define( 'YK_MT_PLUGIN_VERSION', '2.0' );
define( 'YK_MT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'YK_MT_SLUG', 'meal-tracker' );

// -----------------------------------------------------------------------------------------
// AC: Include all relevant PHP files
// -----------------------------------------------------------------------------------------

include_once YK_MT_ABSPATH . 'core/globals.php';
include_once YK_MT_ABSPATH . 'core/functions.php';
include_once YK_MT_ABSPATH . 'core/functions.pages.php';
include_once YK_MT_ABSPATH . 'core/functions.admin.php';
include_once YK_MT_ABSPATH . 'core/functions.settings.php';
include_once YK_MT_ABSPATH . 'core/functions.chart.php';
include_once YK_MT_ABSPATH . 'core/license.php';
include_once YK_MT_ABSPATH . 'core/setup.wizard.php';

$is_premium = yk_mt_license_is_premium();

define( 'YK_MT_IS_PREMIUM', $is_premium );

// Caching enabled?
if ( true === yk_mt_site_options_as_bool('caching-enabled' ) ) {
    include_once YK_MT_ABSPATH . 'core/caching.php';
}

include_once YK_MT_ABSPATH . 'core/db.php';
include_once YK_MT_ABSPATH . 'core/shortcode-functions.php';
include_once YK_MT_ABSPATH . 'core/shortcode-meal-tracker.php';
include_once YK_MT_ABSPATH . 'core/ajax.php';
include_once YK_MT_ABSPATH . 'core/activate.php';
include_once YK_MT_ABSPATH . 'core/hooks.php';
include_once YK_MT_ABSPATH . 'core/sources-weight-tracker.php';
include_once YK_MT_ABSPATH . 'core/cron.php';
include_once YK_MT_ABSPATH . 'core/meta.php';

$has_external = false;

// If Premium, include external databases
if ( true === $is_premium ) {
	include_once YK_MT_ABSPATH . 'core/external-sources.php';

	$has_external = yk_mt_ext_enabled();
}

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

// -----------------------------------------------------------------------------------------
// AC: Load relevant language files
// -----------------------------------------------------------------------------------------

load_plugin_textdomain( YK_MT_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/core/languages/' );
