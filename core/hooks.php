<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Listen for a user saving their settings, if so, do any follow up tasks.
 */
function yk_mt_actions_settings_post_save() {

    // Do we need to consider changing the calories for today's entry based upon the user settings?
    yk_mt_allowed_calories_refresh();

}
add_action( 'yk_mt_settings_saved', 'yk_mt_actions_settings_post_save' );
add_action( 'yk_mt_settings_admin_saved', 'yk_mt_actions_settings_post_save' );

/**
 * Add a CSS classes to the <body>
 * @param $classes
 * @return array
 */
function yk_mt_body_class( $classes ) {

    if ( true === yk_mt_site_options_as_bool( 'accordion-enabled', false ) ) {
        $classes[] = 'yk-mt__accordion-enabled';
    }

    if ( true === YK_MT_HAS_EXTERNAL_SOURCES ) {
		$classes[] = 'yk-mt__ext-enabled';
	}

	$classes[] = sprintf( 'yk-mt-meta-%s', ( true === yk_mt_meta_is_enabled() ) ? 'enabled' : 'disabled' );

    return $classes;
}
add_filter( 'body_class','yk_mt_body_class' );

/**
 * Build admin menu
 */
function yk_mt_build_admin_menu() {

    $permission_level = yk_mt_admin_permission_check_setting();

    add_menu_page( YK_MT_PLUGIN_NAME, YK_MT_PLUGIN_NAME, $permission_level, 'yk-mt-main-menu', '', 'dashicons-chart-pie' );

    // Hide duplicated sub menu (wee hack!)
    add_submenu_page( 'yk-mt-main-menu', '', '', $permission_level, 'yk-mt-main-menu', 'yk_mt_admin_page_data_home');

    add_submenu_page( 'yk-mt-main-menu', __( 'User Data', YK_MT_SLUG ),  __( 'User Data', YK_MT_SLUG ), $permission_level, 'yk-mt-user', 'yk_mt_admin_page_data_home' );

	add_submenu_page( 'yk-mt-main-menu', __( 'Meals', YK_MT_SLUG ),  __( 'Meal Collection', YK_MT_SLUG ), $permission_level, 'yk-mt-meals', 'yk_mt_admin_page_meals_home' );

	if ( true === yk_mt_setup_wizard_show_notice() ) {
        add_submenu_page( 'yk-mt-main-menu', __('Setup Wizard', YK_MT_SLUG ),  __('Setup Wizard', YK_MT_SLUG ), 'manage_options', 'yk-mt-setup-wizard', 'yk_mt_setup_wizard_page' );
    }

    $menu_text = ( true === yk_mt_license_is_premium() ) ? __( 'Your License', YK_MT_SLUG ) : __( 'Upgrade to Pro', YK_MT_SLUG );
    add_submenu_page( 'yk-mt-main-menu', $menu_text,  $menu_text, 'manage_options', 'yk-mt-license', 'yk_mt_advertise_pro');

    add_submenu_page( 'yk-mt-main-menu', __( 'Settings', YK_MT_SLUG ),  __( 'Settings', YK_MT_SLUG ), 'manage_options', 'yk-mt-settings', 'yk_mt_settings_page_generic' );
    add_submenu_page( 'yk-mt-main-menu', __( 'Help', YK_MT_SLUG ),  __( 'Help', YK_MT_SLUG ), $permission_level, 'yk-mt-help', 'yk_mt_help_page' );
}
add_action( 'admin_menu', 'yk_mt_build_admin_menu' );

/**
 * Enqueue admin JS / CSS
 */
function yk_mt_enqueue_admin_files() {

    // Only include MT dependencies on our pages.
    if ( true === empty( $_GET['page'] ) ||
    	  false === in_array( $_GET['page'], [ 'yk-mt-user', 'yk-mt-main-menu', 'yk-mt-settings', 'yk-mt-setup-wizard', 'yk-mt-meals' ] ) ) {
        return;
    }

    wp_enqueue_style( 'yk-mt-admin', plugins_url('../assets/css/admin.css', __FILE__), [], YK_MT_PLUGIN_VERSION );

    // Enqueue admin.js regardless (needed to dismiss notices)
    wp_enqueue_script( 'yk-mt-admin', plugins_url('../assets/js/admin.js', __FILE__), [ 'jquery' ], YK_MT_PLUGIN_VERSION );

    wp_localize_script( 'yk-mt-admin', 'yk_mt_settings', [ 'premium' => YK_MT_IS_PREMIUM, 'meals-url' => admin_url( 'admin.php?page=yk-mt-meals' ) ] );

    // Settings page
    if ( false === empty( $_GET['page'] ) && true === in_array( $_GET[ 'page' ], [ 'yk-mt-settings', 'yk-mt-setup-wizard' ] ) ) {
        wp_enqueue_script( 'jquery-tabs', plugins_url( '../assets/js/tabs.min.js', __FILE__ ), ['jquery'], YK_MT_PLUGIN_VERSION );
        wp_enqueue_style( 'mt-tabs', plugins_url( '../assets/css/tabs.min.css', __FILE__ ), [], YK_MT_PLUGIN_VERSION );
        wp_enqueue_style( 'mt-tabs-flat', plugins_url( '../assets/css/tabs.flat.min.css', __FILE__ ), [], YK_MT_PLUGIN_VERSION );
    }

    if ( false === empty( $_GET['page'] ) && true === in_array( $_GET['page'], [ 'yk-mt-user', 'yk-mt-main-menu', 'yk-mt-meals' ] ) ) {

	    wp_enqueue_style( 'mt-core', plugins_url( 'assets/css/yk-mt-core.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );

        wp_enqueue_style( 'mt-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', [], YK_MT_PLUGIN_VERSION );

        // Confirmation dialogs
        wp_enqueue_script( 'mt-confirm', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js', [ 'jquery' ], YK_MT_PLUGIN_VERSION );
        wp_enqueue_style( 'mt-confirm', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css', [], YK_MT_PLUGIN_VERSION );

	    yk_mt_chart_enqueue();

        yk_mt_enqueue_scripts_footable();

        yk_mt_admin_localise();
    }
}
add_action( 'admin_enqueue_scripts', 'yk_mt_enqueue_admin_files');

/**
 * Enqueue Footable scripts
 */
function yk_mt_enqueue_scripts_footable() {
	wp_enqueue_style( 'mt-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', [], YK_MT_PLUGIN_VERSION );
	wp_enqueue_script( 'mt-moment', plugins_url( '/assets/js/moment.min.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION, true );
    wp_enqueue_style( 'mt-footable', plugins_url( '/assets/css/footable.standalone.min.css', __DIR__  ), [], YK_MT_PLUGIN_VERSION );
    wp_enqueue_script( 'mt-footable', plugins_url( '/assets/js/footable.min.js', __DIR__ ), [ 'jquery', 'mt-moment' ], YK_MT_PLUGIN_VERSION, true );
}

/**
 * Add view link alongside WP action links
 * @param $actions
 * @param $user_object
 * @return mixed
 */
function yk_mt_user_action_links( $actions, $user_object ) {

    $profile_url = yk_mt_link_admin_page_user( $user_object->ID );
    $profile_url = yk_mt_link_add_back_link( $profile_url );

    $actions[ 'meal-tracker' ] = sprintf(  '<a href="%s">%s</a>',
        $profile_url,
        __( 'Meal entries', YK_MT_SLUG )
    );

    return $actions;
}
add_filter( 'user_row_actions', 'yk_mt_user_action_links', 10, 2 );

/**
 * Add MT profile button to WT header link
 * @param $links
 * @return string
 */
function yk_mt_wlt_user_profile_add_header_link( $links, $user_id ) {

    $links .= sprintf( '<a href="%1$s" class="button-secondary"><i class="fa fa-pie-chart"></i> <span>%2$s</span></a>',
        yk_mt_link_admin_page_user( $user_id ),
        __('Meal Tracker Record', YK_MT_SLUG )
    );

    return $links;
}
add_filter( 'wt_ls_user_profile_header_links', 'yk_mt_wlt_user_profile_add_header_link', 10, 2 );

/**
 * Add class to admin body if Premium or not
 * @param $classes
 * @return array
 */
function yk_mt_admin_add_body_classes( $existing_classes ) {

    $class = ( true === YK_MT_IS_PREMIUM ) ? 'yk-mt-is-premium' : 'yk-mt-not-premium' ;

    return sprintf('%1$s %2$s', $existing_classes, $class );
}
add_filter( 'admin_body_class', 'yk_mt_admin_add_body_classes' );

/**
 * Save changed to allowed calorie for given entry
 */
function yk_mt_admin_entry_allowed_calorie_save() {

    if ( false === is_admin() ) {
        return;
    }

    $entry_id       = yk_mt_post_value( 'yk-mt-update-allowance', false );
    $new_allowance  = yk_mt_post_value( 'yk-mt-calories_allowed', false );

    // New allowance for entry?
    if ( false === empty( $new_allowance ) && false === empty( $entry_id ) ) {
        yk_mt_allowed_calories_update_entry( $new_allowance, $entry_id );
    }
}
add_action( 'init', 'yk_mt_admin_entry_allowed_calorie_save' );

/**
 * When settings are saved, invalidate existing cache by incrementing cache version number.
 */
function yk_mt_admin_hooks_update_cache_version() {

	$current_version = get_option( 'yk-mt-cache-number', YK_MT_INITIAL_CACHE_NUMBER );

	$current_version++;

	update_option( 'yk-mt-cache-number', $current_version );

}
add_action( 'yk_mt_settings_saved', 'yk_mt_admin_hooks_update_cache_version');

/**
 * Clear cache for a given meals / certain user ID
 *
 * @param $user_id
 */
function yk_mt_cache_clear_for_user( $user_id ) {
	yk_mt_cache_user_delete( $user_id );
}
add_action( 'yk_mt_meals_deleted', 'yk_mt_cache_clear_for_user' );
add_action( 'yk_mt_meal_added_to_entry', 'yk_mt_cache_clear_for_user' );
add_action( 'yk_mt_meal_deleted_from_entry', 'yk_mt_cache_clear_for_user' );

/**
 * Clear cache for a given entry ids / date for a user
 *
 * @param $entry_id
 * @param $user_id
 */
function yk_mt_cache_entry_ids_and_date_delete( $entry_id, $user_id ) {
	yk_mt_cache_user_delete( $user_id );
}
add_action( 'yk_mt_entry_deleted', 'yk_mt_cache_entry_ids_and_date_delete', 10, 2 );

/**
 * Clear cache for a given entry ids / date for a user
 *
 * @param $entry_id
 * @param $entry
 * @param $user_id
 */
function yk_mt_cache_entry_ids_and_date_delete_three( $entry_id, $entry, $user_id ) {
	yk_mt_cache_user_delete( $user_id );
}
add_action( 'yk_mt_entry_added', 'yk_mt_cache_entry_ids_and_date_delete_three', 10, 3);

/**
 * Clear cache for a given entry
 *
 * @param $id
 */
function yk_mt_cache_hook_entry_delete( $id ) {
	yk_mt_cache_delete( 'entry-' . $id );
}
add_action( 'yk_mt_entry_deleted', 'yk_mt_cache_hook_entry_delete' );

/**
 * Clear cache for a given meal
 *
 * @param $id
 */
function yk_mt_cache_hook_meal_delete( $id ) {

	yk_mt_cache_delete( 'meal-' . $id );
}
add_action( 'yk_mt_meal_deleted', 'yk_mt_cache_hook_meal_delete' );
add_action( 'yk_mt_meals_updated', 'yk_mt_cache_hook_meal_delete' );
