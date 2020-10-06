<?php

defined('ABSPATH') or die("Jog on!");

/**
 *  See bottom of file for hook listeners that clear cache.
 */

/**
 * Cache enabled?
 *
 * @return bool
 */
function yk_mt_cache_is_enabled() {
    return ( true === apply_filters('yk_mt_caching_enabled', true ) );
}

/**
 * Get cache for given group / key
 *
 * @param $group_key
 * @param $key
 * @return null
 */
function yk_mt_cache_group_get( $group_key, $key = NULL ) {

    $cache = yk_mt_cache_get( $group_key );

    if ( true === is_array( $cache ) ) {

        // Key specified?
        if ( NULL !== $key && true === isset( $cache[$key] ) ) {
            return $cache[$key];
        }

        return $cache;
    }

    return NULL;
}

/**
 * Cache a value for given group / key
 *
 * @param $group_key
 * @param $key
 * @param $value
 * @param int $time_to_expire
 *
 * @return bool
 */
function yk_mt_cache_group_set( $group_key, $key, $value, $time_to_expire = HOUR_IN_SECONDS ) {

    $cache = yk_mt_cache_get( $group_key );

    // Empty cache? Create array
    if ( false === is_array( $cache ) ) {
        $cache = [];
    }

    if ( false === empty( $key ) ) {

        $cache[ $key ] = $value;

        return yk_mt_cache_set( $group_key, $cache, $time_to_expire );

    }

    return false;
}

/**
 * Delete cache for given group / key
 *
 * @param $group_key
 * @param $key
 */
function yk_mt_cache_group_delete( $group_key, $key = NULL ) {

    $cache = yk_mt_cache_get( $group_key );

    if ( true === is_array( $cache ) && isset( $cache[$key] ) ) {

        unset( $cache[$key] );

        yk_mt_cache_set( $group_key, $cache );

        return $cache;
    }
}

/**
 * Fetch an item from cache
 *
 * @param $key
 *
 * @return bool|mixed
 */
function yk_mt_cache_get( $key ) {

    if( true === yk_mt_cache_is_enabled() ) {

    	$key = yk_mt_cache_generate_key( $key );

    	return get_transient( $key );
    }

    return false;
}

/**
 * Set cache
 *
 * @param $key
 * @param $data
 * @param int $time_to_expire
 *
 * @return bool
 */
function yk_mt_cache_set( $key, $data, $time_to_expire = HOUR_IN_SECONDS ) {

    if ( true === yk_mt_cache_is_enabled() ) {

        $key = yk_mt_cache_generate_key( $key );

        return set_transient( $key, $data, (int) $time_to_expire );

    }

    return false;
}

/**
 * Delete cache for given key
 *
 * @param $key
 *
 * @return bool
 */
function yk_mt_cache_delete( $key ){

    if ( true === yk_mt_cache_is_enabled() ) {

        $key = yk_mt_cache_generate_key($key);

        return delete_transient($key);
    }

    return false;
}

/**
 * Delete all cache for plugin
 */
function yk_mt_cache_delete_all() {

    global $wpdb;

    if ( true === yk_mt_cache_is_enabled() ){

        $sql = "Delete FROM  $wpdb->options
                WHERE option_name LIKE '%transient_" . YK_MT_SLUG ."%'";

        $wpdb->query($sql);

        $sql = "Delete FROM  $wpdb->options
                WHERE option_name LIKE '%transient_timeout_" . YK_MT_SLUG ."%'";

        $wpdb->query($sql);
    }
}

/**
 * Generate key for caching
 *
 * @param $key
 *
 * @return string
 */
function yk_mt_cache_generate_key( $key ){

	$cache_version = get_option( 'yk-mt-cache-number', YK_MT_INITIAL_CACHE_NUMBER );

    return sprintf( 'mt-%s-%s-%d-%s',  YK_MT_IS_PREMIUM, YK_MT_PLUGIN_VERSION, $cache_version, $key );
}

// -------------------------------------------------------------
// Listen for various hooks to maintain cache
// -------------------------------------------------------------

/**
 * Update / Set cache for given Meal ID
 *
 * @param $user_id
 * @param $options_key
 * @param $meals
 */
function yk_mt_cache_hook_meals_set( $user_id, $options_key, $meals ) {

    $cache = yk_mt_cache_get( 'meals-' . $user_id );

    if ( false === is_array( $cache ) ) {
        $cache = [];
    }

    $cache[ $options_key ] = $meals;

    yk_mt_cache_set( 'meals-' . $user_id, $cache, 600 ); // Cache for 10 minutes
}
add_action( 'yk_mt_meals_lookup', 'yk_mt_cache_hook_meals_set', 10, 3 );

/**
 * Get cache for of meals for given user / options
 *
 * @param $user_id
 * @param $options_key
 */
function yk_mt_cache_filter_meals_get( $default_value, $user_id, $options_key ) {

    $cache = yk_mt_cache_get( 'meals-' . $user_id );
    return ( false === empty( $cache[ $options_key ] ) ) ? $cache[ $options_key ] : NULL;
}
add_filter( 'yk_mt_db_meals', 'yk_mt_cache_filter_meals_get', 10, 3 );

/**
 * Clear cache for a given meals / certain user ID
 *
 * @param $id
 */
function yk_mt_cache_hook_meals_delete( $user_id ) {
    yk_mt_cache_delete( 'meals-' . $user_id );
}
add_action( 'yk_mt_meals_deleted', 'yk_mt_cache_hook_meals_delete' );

/**
 * Clear cache for a given meal
 *
 * @param $id
 */
function yk_mt_cache_hook_meal_delete( $id ) {

    yk_mt_cache_delete( 'meal-' . $id );
}
add_action( 'yk_mt_meal_deleted', 'yk_mt_cache_hook_meal_delete' );

/**
 * Update / Set cache for given Meal ID
 *
 * @param $id
 * @param $meal
 */
function yk_mt_cache_hook_meal_set( $id, $meal ) {

    if ( false === isset( $meal['id'] ) ) {
        $meal['id'] = $id;
    }

    yk_mt_cache_set( 'meal-' . $id, $meal );

}
add_action( 'yk_mt_meal_lookup', 'yk_mt_cache_hook_meal_set', 10, 2 );
add_action( 'yk_mt_meal_added', 'yk_mt_cache_hook_meal_set', 10, 2);
add_action( 'yk_mt_meal_updated', 'yk_mt_cache_hook_meal_set', 10, 2);

/**
 * Get cache for given Meal ID
 *
 * @param $id
 * @param $meal
 */
function yk_mt_cache_filter_meal_get( $default_value, $id ) {

    $cache = yk_mt_cache_get( 'meal-' . $id );

    return ( false === empty( $cache ) ) ? $cache : NULL;

}
add_filter( 'yk_mt_db_meal_get', 'yk_mt_cache_filter_meal_get', 10, 2 );

/**
 * Update / Set cache entry ids / date for a user
 *
 * @param $id
 * @param $meal
 */
function yk_mt_cache_entry_ids_and_date_set( $user_id, $results ) {
    yk_mt_cache_set( 'entry-ids-and-dates-' . $user_id, $results );
}
add_action( 'yk_mt_db_entry_ids_and_dates', 'yk_mt_cache_entry_ids_and_date_set', 10, 2 );

/**
 *  Get cache entry ids / date for a user
 *
 * @param $default_value
 * @param $user_id
 * @return bool|mixed|null
 */
function yk_mt_cache_entry_ids_and_date_get( $default_value, $user_id ) {

    $cache = yk_mt_cache_get( 'entry-ids-and-dates-' . $user_id );

    return ( false === empty( $cache ) ) ? $cache : NULL;
}
add_filter( 'yk_mt_db_entry_ids_and_dates_get', 'yk_mt_cache_entry_ids_and_date_get', 10, 2 );

/**
 * Clear cache for a given entry ids / date for a user
 *
 * @param $id
 */
function yk_mt_cache_entry_ids_and_date_delete( $entry_id, $user_id ) {
    yk_mt_cache_delete( 'entry-ids-and-dates-' . $user_id  );
}
add_action( 'yk_mt_entry_deleted', 'yk_mt_cache_entry_ids_and_date_delete', 10, 2 );
add_action( 'yk_mt_entry_cache_clear', 'yk_mt_cache_entry_ids_and_date_delete', 10, 2 );

function yk_mt_cache_entry_ids_and_date_delete_three( $entry_id, $entry, $user_id ) {
    yk_mt_cache_entry_ids_and_date_delete( $entry_id, $user_id );
}
add_action( 'yk_mt_entry_added', 'yk_mt_cache_entry_ids_and_date_delete_three', 10, 3);

/**
 * Update / Set cache for given Entry ID
 *
 * @param $id
 * @param $entry
 */
function yk_mt_cache_hook_entry_set( $id, $entry ) {

    if ( false === isset( $entry['id'] ) ) {
        $entry['id'] = $id;
    }

    yk_mt_cache_set( 'entry-' . $id, $entry );

}
add_action( 'yk_mt_entry_lookup', 'yk_mt_cache_hook_entry_set', 10, 2 );
add_action( 'yk_mt_entry_added', 'yk_mt_cache_hook_entry_set', 10, 2);
add_action( 'yk_mt_entry_updated', 'yk_mt_cache_hook_entry_set', 10, 2);

/**
 * Get cache for given Entry ID
 *
 * @param $id
 * @param $entry
 */
function yk_mt_cache_filter_entry_get( $default_value, $id ) {

    $cache = yk_mt_cache_get( 'entry-' . $id );

    return ( false === empty( $cache ) ) ? $cache : NULL;

}
add_filter( 'yk_mt_db_entry_get', 'yk_mt_cache_filter_entry_get', 10, 2 );

/**
 * Clear cache for a given entry
 *
 * @param $id
 */
function yk_mt_cache_hook_entry_delete( $id ) {
    yk_mt_cache_delete( 'entry-' . $id );
}
add_action( 'yk_mt_entry_deleted', 'yk_mt_cache_hook_entry_delete' );
add_action( 'yk_mt_entry_cache_clear', 'yk_mt_cache_hook_entry_delete' );

/**
 * Update / Set cache for meal types
 *
 * @param $meal_types
 */
function yk_mt_cache_hook_meal_types_set( $meal_types ) {

    yk_mt_cache_set( 'meal-types', $meal_types );

}
add_action( 'yk_mt_meal_types_all', 'yk_mt_cache_hook_meal_types_set' );

/**
 * Get cache for meal types
 *
 * @param $id
 * @param $meal
 */
function yk_mt_cache_filter_meal_types_get( $default_value ) {

    $cache = yk_mt_cache_get( 'meal-types' );

    return ( false === empty( $cache ) ) ? $cache : NULL;

}
add_filter( 'yk_mt_db_meal_types_all', 'yk_mt_cache_filter_meal_types_get' );

/**
 * Clear cache for meal types
 *
 * @param $id
 */
function yk_mt_cache_hook_meal_types_delete( $id ) {
    yk_mt_cache_delete( 'meal-types' );
}
add_action( 'yk_mt_meal_types_added', 'yk_mt_cache_hook_meal_types_delete' );

/**
 * Clear cache for a given settings
 *
 * @param $id
 */
function yk_mt_cache_hook_settings_delete( $user_id ) {

    yk_mt_cache_delete( 'settings-' . $user_id );
}
add_action( 'yk_mt_db_settings_deleted', 'yk_mt_cache_hook_settings_delete' );

/**
 * Update / Set cache for settings
 *
 * @param $id
 * @param $settings
 */
function yk_mt_cache_hook_settings_set( $user_id, $settings ) {
    yk_mt_cache_set( 'settings-' . $user_id, $settings );
}
add_action( 'yk_mt_db_settings_lookup', 'yk_mt_cache_hook_settings_set', 10, 2 );
add_action( 'yk_mt_db_settings_updated', 'yk_mt_cache_hook_settings_set', 10, 2);

/**
 * Get cache for given settings
 *
 * @param $id
 */
function yk_mt_cache_filter_settings_get( $default_value, $user_id ) {
    return yk_mt_cache_get( 'settings-' . $user_id );
}
add_filter( 'yk_mt_db_settings_get', 'yk_mt_cache_filter_settings_get', 10, 2 );

/**
 * Temp Cache - a temp caching mechanism for storing data for 5 minutes
 *
 * @param $key
 * @param $value
 * @param $duration
 */
function yk_mt_cache_temp_hook_set( $key, $value, $duration ) {
    yk_mt_cache_set( 'temp-' . $key, $value, $duration );
}
add_action( 'yk_mt_cache_temp_set', 'yk_mt_cache_temp_hook_set', 10, 3 );

/**
 * Get cache for temp storage
 *
 * @param $default_value
 * @param $key
 */
function yk_mt_cache_temp_hook_get( $default_value, $key ) {
    $cache = yk_mt_cache_get( 'temp-' . $key );
    return ( false === empty( $cache ) ) ? $cache : NULL;
}
add_filter( 'yk_mt_cache_temp_get', 'yk_mt_cache_temp_hook_get', 10, 2 );

/**
 * Update / Set cache entry summary
 * @param $cache_key
 * @param $results
 */
function yk_mt_cache_entries_summary_set( $cache_key, $results ) {
    yk_mt_cache_set( 'entries-summary-' . $cache_key, $results, 300 );
}
add_action( 'yk_mt_db_entries_summary', 'yk_mt_cache_entries_summary_set', 10, 2 );

/**
 *  Get cache entry summary
 *
 * @param $default_value
 * @param $cache_key
 * @return bool|mixed|null
 */
function yk_mt_cache_entries_summary_get( $default_value, $cache_key ) {

    $cache = yk_mt_cache_get( 'entries-summary-' . $cache_key );

    return ( false === empty( $cache ) ) ? $cache : NULL;
}
add_filter( 'yk_mt_db_entries_summary_get', 'yk_mt_cache_entries_summary_get', 10, 2 );
