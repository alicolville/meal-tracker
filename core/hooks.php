<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Listen for a user saving their settings, if so, do any follow up tasks.
 */
function yk_mt_actions_settings_post_save() {

    /*
     * Update the allowed calories for today's entry
     */
    $allowed_calories   = yk_mt_settings_get( 'allowed-calories' );
    $todays_entry_id    = yk_mt_db_entry_get_id_for_today();

    if ( false === empty( $todays_entry_id ) ) {

        yk_mt_db_entry_update( [ 'id' => $todays_entry_id, 'calories_allowed' => $allowed_calories ] );

        yk_mt_entry_calories_calculate_update_used( $todays_entry_id );
    }

}
add_action( 'yk_mt_settings_saved', 'yk_mt_actions_settings_post_save' );