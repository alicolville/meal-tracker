<?php
defined('ABSPATH') or die("Jog on!");

define( 'YK_MT_CACHE_ENABLED', yk_mt_site_options_as_bool('caching-enabled' ) );
define( 'YK_MT_CACHE_TIME', DAY_IN_SECONDS );
define( 'YK_MT_CACHE_SHORT_TIME', 5 * MINUTE_IN_SECONDS );
define( 'YK_MT_INITIAL_CACHE_NUMBER', 1 );

/**
 * When settings are saved, invalidate existing cache by incrementing cache version number.
 */
function yk_mt_cache_admin_hooks_update_cache_version() {

	$current_version = get_option( 'yk-mt-cache-number', YK_MT_INITIAL_CACHE_NUMBER );

	$current_version++;

	update_option( 'yk-mt-cache-number', $current_version );

}
add_action( 'yk_mt_settings_saved', 'yk_mt_cache_admin_hooks_update_cache_version');

/**
 * User caching. From now on, store an array for each user in cache. Each caache key can then be stored in an array element.
 * To remove all use cache, just need to delete the cache key.
 *
 * @param $user_id
 * @param $key
 * @return null
 */
function yk_mt_cache_user_get( $user_id, $key ) {

	// Cache enabled?
	if( false === YK_MT_CACHE_ENABLED ) {
		return NULL;
	}

	if ( true === empty( $user_id ) ) {
		$user_id = -1;
	}

	$user_lookup_table = yk_mt_cache_get( $user_id );

	if ( false === is_array( $user_lookup_table ) ) {
		return NULL;
	}

	// Do we have any data for this cache key?
	if ( true === empty( $user_lookup_table[ $key ] ) ) {
		return NULL;
	}

	// Take the cache key and dig further!
	$data_key   = $user_lookup_table[ $key ];
	$data_value = yk_mt_cache_get( $data_key );

	// If no data is found at this key, presume the cache entry has expired, so remove from lookup.
	if ( false === $data_value ) {
		unset( $user_lookup_table[ $key ] );
		yk_mt_cache_set( $user_id, $user_lookup_table, YK_MT_CACHE_TIME );
	}

	return $data_value;
}

/**
 * Return all cache for the given user
 * @param $user_id
 * @return array|bool|mixed|null
 */
function yk_mt_cache_user_get_all( $user_id = NULL ) {

	if ( true === empty( $user_id ) ) {
		$user_id = -1;
	}

	$user_cache = yk_mt_cache_get( $user_id) ;

	return ( true === is_array( $user_cache ) ) ? $user_cache : NULL;
}

/**
 * Cache for user
 * @param $user_id
 * @param $key
 * @param $value
 * @param float|int $time_to_expire
 */
function yk_mt_cache_user_set( $user_id, $key, $value, $time_to_expire = YK_MT_CACHE_TIME ) {

	// Cache enabled?
	if( false === YK_MT_CACHE_ENABLED ) {
		return;
	}

	if ( true === empty( $user_id ) ) {
		$user_id        = -1;
		$time_to_expire = YK_MT_CACHE_SHORT_TIME;
	}

	$user_cache = yk_mt_cache_get( $user_id );

	// Empty cache? Create array
	if ( false === is_array( $user_cache ) ) {
		$user_cache = [];
	}

	/*
	 *  This Cache array will be a lookup. It will contain an array of keys to further cache entries. That way,
	 *  we don't have a monolithic cache object to load on every cache lookup. Just an array of keys. If the relevant key exists, then
	 *  once again, drill down.
	 */

	/*
	 * $key will be the clear text key passed in.
	 * $cache_key will be the subsequent cache key where the data is actually stored.
	 */

	$cache_key          = sprintf( 'mt-item-%s-%s', $user_id, $key );
	$user_cache[ $key ] = $cache_key;

	// Store data
	yk_mt_cache_set( $cache_key, $value, $time_to_expire );

	// Update lookup table
	yk_mt_cache_set( $user_id, $user_cache, $time_to_expire );
}

/**
 * Helper function for use in shortcodes etc. Cache value and return value.
 * @param $user_id
 * @param $key
 * @param $value
 *
 * @return mixed
 */
function yk_mt_cache_user_set_and_return( $user_id, $key, $value ) {

	if ( true === empty( $user_id ) ) {
		$user_id = -1;
	}

	yk_mt_cache_user_set( $user_id, $key, $value );

	return $value;
}

/**
 * Fetch all keys associated with the user and delete
 * @param $user_id
 */
function yk_mt_cache_user_delete( $user_id = NULL ) {

	if ( true === empty( $user_id ) ) {
		$user_id = -1;
	}

	$all_keys = yk_mt_cache_user_get_all( $user_id );

	if ( true === is_array( $all_keys ) ) {
		$all_keys = array_values( $all_keys );
		array_map( 'yk_mt_cache_delete', $all_keys );
	}

	// Delete cache lookup table
	yk_mt_cache_delete( $user_id );
}

/**
 * Fetch Cache
 * @param $key
 * @return bool|mixed
 */
function yk_mt_cache_get( $key ) {

	if( true === YK_MT_CACHE_ENABLED ) {
		$key = yk_mt_cache_generate_key( $key );
		return get_transient( $key );
	}

	return false;
}

/**
 * Set Cache
 * @param $key
 * @param $data
 * @param float|int $time_to_expire
 * @return bool
 */
function yk_mt_cache_set( $key, $data, $time_to_expire = YK_MT_CACHE_TIME ) {

	if( true === YK_MT_CACHE_ENABLED ) {
		$key = yk_mt_cache_generate_key( $key );
		set_transient( $key, $data, $time_to_expire );
	}

	return false;
}

/**
 * Delete cache key
 * @param $key
 * @return bool
 */
function yk_mt_cache_delete( $key ){

	$key = yk_mt_cache_generate_key($key);
	return delete_transient($key);
}

/**
 * Delete admin cache
 */
function yk_mt_cache_delete_admin() {

	yk_mt_cache_user_delete();

	do_action( 'wlt-cache-admin-delete' );
}

/**
 * Delete the user cache for each user id within the array
 *
 * @param $user_ids
 */
function yk_mt_delete_cache_for_given_users( $user_ids ) {

	if ( true === is_array( $user_ids ) && false === empty( $user_ids ) ) {
		foreach ( $user_ids as $id ) {
			yk_mt_cache_user_delete( $id );
		}
	}
}

/**
 * Delete all weight tracker cache
 */
function yk_mt_cache_delete_all() {

	global $wpdb;

	$sql = "Delete FROM  $wpdb->options WHERE option_name LIKE '%transient_" . YK_MT_SLUG ."%'";

	$wpdb->query($sql);

	$sql = "Delete FROM  $wpdb->options WHERE option_name LIKE '%transient_timeout_" . YK_MT_SLUG ."%'";

	$wpdb->query($sql);

}

/**
 * Generate key for cache
 * @param $key
 *
 * @return string
 */
function yk_mt_cache_generate_key( $key ){

	$cache_version = get_option( 'yk-mt-cache-number', YK_MT_INITIAL_CACHE_NUMBER );

	return sprintf( 'mt-%s-%s-%d-%s',  YK_MT_IS_PREMIUM, YK_MT_PLUGIN_VERSION, $cache_version, $key );
}

/**
 * Generate an array key based on an array
 * @param string $prefix
 * @param $array
 *
 * @return string
 */
function yk_mt_cache_generate_key_from_array( $prefix = 'mt', $array ) {

	if ( false === is_array( $array ) ) {
		return '';
	}

	return sprintf( '%s-%s', $prefix, md5( json_encode( $array ) ) );
}
