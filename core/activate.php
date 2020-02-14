<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 *  Run on every version change
	*/
	function yk_wt_upgrade() {

		if( update_option('yk-wt-version-number', YK_MT_PLUGIN_VERSION ) ) {

			// Build DB tables
			yk_wt_db_tables_create();

			// Clear all cache
			if ( true === function_exists( 'yk_mt_cache_delete_all' ) ) {
				yk_mt_cache_delete_all();
			}

			// Do we need to add the default meal types?
			if ( true === empty( yk_mt_db_meal_types_all() ) ) {
				yk_mt_db_defaults_meal_types();
			}
		}
	}
	add_action('admin_init', 'yk_wt_upgrade');

	/**
	 *  Add default Meal Types
	 */
	function yk_mt_db_defaults_meal_types() {
		yk_mt_db_meal_types_add( [ 'name' => __( 'Breakfast', YK_MT_SLUG ), 'sort' => 100 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Mid-morning', YK_MT_SLUG ), 'sort' => 200 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Lunch', YK_MT_SLUG ), 'sort' => 300 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Afternoon', YK_MT_SLUG ), 'sort' => 400 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Dinner', YK_MT_SLUG ), 'sort' => 500 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Evening', YK_MT_SLUG ), 'sort' => 600 ] );
	}