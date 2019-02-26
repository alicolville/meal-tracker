<?php

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
	 * @param $user_id
	 * @param $key
	 * @return null
	 */
	function yk_mt_cache_group_get( $group_key, $key = NULL ) {

		$cache = ws_ls_get_cache( $group_key );

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
	function ws_ls_cache_group_set( $group_key, $key, $value, $time_to_expire = HOUR_IN_SECONDS ) {

		$cache = ws_ls_get_cache( $group_key );

		// Empty cache? Create array
		if ( false === is_array( $cache ) ) {
			$cache = [];
		}

		if ( false === empty( $key ) ) {

			$cache[ $key ] = $value;

			return yk_mt_set_cache( $group_key, $cache, $time_to_expire );

		}

		return false;
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
			$key = yk_mt_generate_cache_key($key);
			return get_transient($key);
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
	function yk_mt_cache_set($key, $data, $time_to_expire = HOUR_IN_SECONDS) {

		if ( true === yk_mt_cache_is_enabled() ) {

			$key = yk_mt_generate_cache_key( $key );

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

			$key = yk_mt_generate_cache_key($key);

			return delete_transient($key);
		}

		return false;
	}

	/**
	 * Delete all cache for plugin
	 */
	function yk_mt_cache_delete_all() {

		global $wpdb;

		if ( true === ws_ls_cache_is_enabled() ){

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
	function yk_mt_generate_cache_key( $key ){
		return sprintf( '%s-%s-%s', YK_MT_SLUG, YK_MT_PLUGIN_VERSION, $key);
	}

