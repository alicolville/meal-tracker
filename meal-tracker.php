<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Plugin Name: Meal Tracker
 * Description: // TODO
 * Version: 0.2
 * Author: YeKen
 * Author URI: http://www.YeKen.uk
 * License: GPL2
 * Text Domain: meal-tracker
 */
/*  Copyright 2019 YeKen.uk

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

define( 'YK_MT_PLUGIN_VERSION', '0.3' );
define( 'YK_MT_SLUG', 'meal-tracker' );

// -----------------------------------------------------------------------------------------
// AC: Include all relevant PHP files
// -----------------------------------------------------------------------------------------

include_once YK_MT_ABSPATH . 'core/functions.php';
include_once YK_MT_ABSPATH . 'core/license.php';
// include_once YK_MT_ABSPATH . 'core/caching.php'; //TODO
include_once YK_MT_ABSPATH . 'core/db.php';
include_once YK_MT_ABSPATH . 'core/shortcode-functions.php';
include_once YK_MT_ABSPATH . 'core/shortcode-meal-tracker.php';
include_once YK_MT_ABSPATH . 'core/ajax.php';
include_once YK_MT_ABSPATH . 'core/activate.php';

// TODO: Remove
include_once YK_MT_ABSPATH . 'tests.php';

// -----------------------------------------------------------------------------------------
// AC: Load relevant language files
// -----------------------------------------------------------------------------------------

load_plugin_textdomain( YK_MT_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/core/languages/' );