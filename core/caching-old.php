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
