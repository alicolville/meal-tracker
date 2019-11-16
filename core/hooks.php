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

/**
 * Add a CSS classes to the <body>
 * @param $classes
 * @return array
 */
function yk_mt_body_class( $classes ) {

    if ( true === yk_mt_site_options( 'accordion-enabled' ) ) {
        $classes[] = 'yk-mt-accordion-enabled';
    }

    return $classes;
}
add_filter( 'body_class','yk_mt_body_class' );

/**
 * Build admin menu
 */
function yk_mt_build_admin_menu() {

    add_menu_page( YK_MT_PLUGIN_NAME, YK_MT_PLUGIN_NAME, 'manage_options', 'yk-mt-main-menu', '', 'dashicons-chart-pie' );

    // Hide duplicated sub menu (wee hack!)
    add_submenu_page( 'yk-mt-main-menu', '', '', 'manage_options', 'yk-mt-main-menu', '');

    $menu_text = ( true === yk_mt_license_is_premium() ) ? __( 'Your License', SH_CD_SLUG ) : __( 'Upgrade to Pro', YK_MT_SLUG );

    add_submenu_page( 'yk-mt-main-menu', $menu_text,  $menu_text, 'manage_options', 'yk-mt-license', 'yk_mt_advertise_pro');
    add_submenu_page( 'yk-mt-main-menu', __( 'Help', YK_MT_SLUG ),  __( 'Help', YK_MT_SLUG ), 'manage_options', 'yk-mt-help', 'yk_mt_help_page');
}
add_action( 'admin_menu', 'yk_mt_build_admin_menu' );