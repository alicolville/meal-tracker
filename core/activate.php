<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 *  Run on every version change
	*/
	function yk_wt_upgrade() {

		if( update_option( 'yk-wt-version-number-' . (int) YK_MT_IS_PREMIUM, YK_MT_PLUGIN_VERSION ) ) {

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

			do_action( 'yk_mt_db_upgrade' );
		}
	}
	add_action('admin_init', 'yk_wt_upgrade');

	/**
	 * If we have missing database tables then attempt to fix!
	 */
	function yk_mt_missing_database_table_fix() {

		yk_wt_db_tables_create();

		yk_mt_db_defaults_meal_types();

		do_action( 'yk_mt_db_fixed' );
	}

	/**
	 * Check all database tables exist!
	 * @return bool - return true if any tables are missing
	 */
	function yk_mt_missing_database_table_any_issues() {

		$error_text = '';
		global $wpdb;

		$tables_to_check = [    $wpdb->prefix . YK_WT_DB_MEALS,
			$wpdb->prefix . YK_WT_DB_ENTRY,
			$wpdb->prefix . YK_WT_DB_ENTRY_MEAL,
			$wpdb->prefix . YK_WT_DB_MEAL_TYPES,
			$wpdb->prefix . YK_WT_DB_SETTINGS
		];

		$count = $wpdb->get_var( 'SELECT COUNT(1) FROM information_schema.tables WHERE table_schema="' . DB_NAME .'" AND table_name in ( "' . implode('", "', $tables_to_check ) . '" )' );

		return ! ( count( $tables_to_check ) === (int) $count );
	}

	/**
	 *  Add default Meal Types
	 */
	function yk_mt_db_defaults_meal_types() {

		// Ensure we have meal types already!
		if ( false === empty( yk_mt_db_meal_types_all( false ) ) ) {
			return;
		}

		yk_mt_db_meal_types_add( [ 'name' => esc_html__( 'Breakfast', 'meal-tracker' ), 'sort' => 100 ] );
		yk_mt_db_meal_types_add( [ 'name' => esc_html__( 'Mid-morning', 'meal-tracker' ), 'sort' => 200 ] );
		yk_mt_db_meal_types_add( [ 'name' => esc_html__( 'Lunch', 'meal-tracker' ), 'sort' => 300 ] );
		yk_mt_db_meal_types_add( [ 'name' => esc_html__( 'Afternoon', 'meal-tracker' ), 'sort' => 400 ] );
		yk_mt_db_meal_types_add( [ 'name' => esc_html__( 'Dinner', 'meal-tracker' ), 'sort' => 500 ] );
		yk_mt_db_meal_types_add( [ 'name' => esc_html__( 'Evening', 'meal-tracker' ), 'sort' => 600 ] );
	}
