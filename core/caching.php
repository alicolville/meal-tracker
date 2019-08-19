<?php

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
			$key = yk_mt_cache_generate_key($key);
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
		return sprintf( '%s-%s-%s', YK_MT_SLUG, YK_MT_PLUGIN_VERSION, $key);
	}

	// -------------------------------------------------------------
	// Listen for various hooks to maintain cache
	// -------------------------------------------------------------

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
	 * Update / Set cache for given Entry ID
	 *
	 * @param $id
	 * @param $meal
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