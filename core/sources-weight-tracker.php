<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Has Weight Tracker been enabled?
 * @return bool
 */
function yk_mt_wlt_enabled() {
    return function_exists( 'ws_ls_activate' );
}

/**
 * Is Weight Tracker in Pro Plus mode?
 * @return bool
 */
function yk_mt_wlt_pro_plus_enabled() {
    return function_exists( 'ws_ls_harris_benedict_calculate_calories' );
}

/**
 * Is Weight Tracker enabled to be used with Meal Tracker?
 * @return bool
 */
function yk_mt_wlt_enabled_for_mt() {
    return true === yk_mt_wlt_pro_plus_enabled() &&
           yk_mt_site_options_as_bool('allow-calorie-external-wlt' );
}

/**
 * If we have a Pro version of Meal Tracker and Pro version of Weight Tracker (and enabled as a source) add Weight Tracker as a source.
 *
 * @param $sources
 * @return mixed
 */
function yk_mt_wlt_sources_add( $sources ) {

    // Weight Tracker activated on this site?
    if ( false === yk_mt_wlt_enabled_for_mt() ) {
        return $sources;
    }

    if ( true === yk_mt_site_options_as_bool('allow-calorie-external-wlt' ) ) {
        $sources['wlt']   = [
            'value'         => __( 'Weight Tracker', YK_MT_SLUG ),
            'admin-message' => __( 'from Weight Tracker', YK_MT_SLUG ),
            'func'          => 'yk_mt_sources_wlt_target'
        ];
    }

    return $sources;
}
add_filter( 'yk_mt_calories_sources_pre', 'yk_mt_wlt_sources_add' );

/**
 * If a Weight Tracker user changes an entry or their preferences then see if we need to update their allowed calories for today
 * @param $dummy
 */
function yk_mt_wlt_calories_allowed_refresh( $dummy ) {

    // Weight Tracker activated on this site?
    if ( false === yk_mt_wlt_enabled_for_mt() ) {
        return;
    }

    yk_mt_allowed_calories_refresh();
}
add_action( 'ws-ls-hook-user-preference-saved', 'yk_mt_wlt_calories_allowed_refresh' );             // When a user changes their user preferences in WT
add_action( 'wlt-hook-data-added-edited','yk_mt_wlt_calories_allowed_refresh' );                    // When a user adds / edits an entry
add_action( 'wlt-hook-data-entry-deleted','yk_mt_wlt_calories_allowed_refresh' );                   // When deletes an entry

/**
 * If plugin is enabled and allowed as an admin option, then fetch allowed calories from Weight Tracker (by YeKen.uk)
 *
 * @param null $user_id
 *
 * @return int
 */
function yk_mt_sources_wlt_target( $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    // Take Calories from WLT?
    if ( true === yk_mt_wlt_enabled_for_mt() ) {

        $yeken_aim =  ws_ls_get_progress_attribute_from_aim();

        $yeken_wt = ws_ls_harris_benedict_calculate_calories();

        if ( true === isset( $yeken_wt[ $yeken_aim ][ 'total' ] ) ) {
            return (int) $yeken_wt[ $yeken_aim ][ 'total' ];
        }
    }

    return NULL;
}


