<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Is Weight Tracker Pro enabled
 * @return bool
 */
function yk_mt_wlt_enabled() {
    return function_exists( 'ws_ls_harris_benedict_calculate_calories' );
}

/**
 * If we have a Pro version of Meal Tracker and Pro version of Weight Tracker (and enabled as a source) add Weight Tracker as a source.
 *
 * @param $sources
 * @return mixed
 */
function yk_mt_wlt_sources_add( $sources ) {

    // Weight Tracker activated on this site?
    if ( false === yk_mt_wlt_enabled() ) {
        return $sources;
    }

    if ( true === yk_mt_site_options('allow-calorie-external-wlt' ) ) {
        $sources['wlt']   = [ 'value' => __( 'Weight Tracker', YK_MT_SLUG ), 'func' => 'yk_mt_user_calories_target_from_wlt' ];
    }

    return $sources;
}
add_filter( 'yk_mt_calories_sources_pre', 'yk_mt_wlt_sources_add' );

/**
 * If plugin is enabled and allowed as an admin option, then fetch allowed calories from Weight Tracker (by YeKen.uk)
 *
 * @param null $user_id
 *
 * @return int
 */
function yk_mt_user_calories_target_from_wlt( $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    // Take Calories from WLT?
    if ( true === yk_mt_wlt_enabled() ) {

        $yeken_aim =  ws_ls_get_progress_attribute_from_aim();

        $yeken_wt = ws_ls_harris_benedict_calculate_calories();

        if ( true === isset( $yeken_wt[ $yeken_aim ][ 'total' ] ) ) {
            $allowed_calories = $yeken_wt[ $yeken_aim ][ 'total' ];
        }

        return (int) $allowed_calories;
    }

    return NULL;
}

