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
 * For the given entry, if today's allowed calorie does not match then update entry.
 * @param bool $entry_id
 * @return bool
 */
function yk_mt_allowed_calories_refresh( $entry_id = false ) {

    $entry_id = ( false !== $entry_id ) ? (int) $entry_id : yk_mt_db_entry_get_id_for_today();

    $entry = yk_mt_db_entry_get( $entry_id );

    if ( true === empty( $entry ) ) {
        return false;
    }

    $allowed_calories = yk_mt_user_calories_target();

    // Only bother to update DB if we have a difference
    if( (int) $allowed_calories === (int) $entry[ 'calories_allowed' ] ) {
        return false;
    }

    yk_mt_db_entry_update( [ 'id' => $entry_id, 'calories_allowed' => $allowed_calories ] );

    yk_mt_entry_calories_calculate_update_used( $entry_id );

    return true;
}