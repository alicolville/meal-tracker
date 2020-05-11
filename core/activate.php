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
	}

	/**
	 * Check all database tables exist!
	 * @return bool|int
	 */
	function yk_mt_missing_database_table_check() {

		$error_text = '';
		global $wpdb;

		$tables_to_check = [    $wpdb->prefix . YK_WT_DB_MEALS,
			$wpdb->prefix . YK_WT_DB_ENTRY,
			$wpdb->prefix . YK_WT_DB_ENTRY_MEAL,
			$wpdb->prefix . YK_WT_DB_MEAL_TYPES,
			$wpdb->prefix . YK_WT_DB_SETTINGS
		];

		// Check each table exists!
		foreach( $tables_to_check as $table_name ) {

			$count = $wpdb->get_var( 'SELECT COUNT(1) FROM information_schema.tables WHERE table_schema="dbname" AND table_name="' . $table_name . '"' );

			if ( true === empty( $count ) ) {
				$error_text .= sprintf( '<li>%s</li>', $table_name );
			}
		}

		// Return error message if tables missing
		return ( false === empty( $error_text ) ) ?
				printf('%s: <ul>%s</ul>', __( 'The following MySQL tables are missing for this plugin' , YK_MT_SLUG ), $error_text ) :
					false;
	}

	/**
	 *  Add default Meal Types
	 */
	function yk_mt_db_defaults_meal_types() {

		// Ensure we have meal types already!
		if ( false === empty( yk_mt_db_meal_types_all( false ) ) ) {
			return;
		}

		yk_mt_db_meal_types_add( [ 'name' => __( 'Breakfast', YK_MT_SLUG ), 'sort' => 100 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Mid-morning', YK_MT_SLUG ), 'sort' => 200 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Lunch', YK_MT_SLUG ), 'sort' => 300 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Afternoon', YK_MT_SLUG ), 'sort' => 400 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Dinner', YK_MT_SLUG ), 'sort' => 500 ] );
		yk_mt_db_meal_types_add( [ 'name' => __( 'Evening', YK_MT_SLUG ), 'sort' => 600 ] );
	}
