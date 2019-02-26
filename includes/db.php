<?php

	defined('ABSPATH') or die("Jog on!");

	define( 'YK_WT_DB_MEALS', 'yk_mt_meals');                   // Store all meal types
	define( 'YK_WT_DB_ENTRY', 'yk_mt_entry');                   // Store all entries for the given user
	define( 'YK_WT_DB_ENTRY_MEAL', 'yk_mt_entry_meals');        // Store all meals for given entry

	/**
	 * Add a meal
	 *
	 * @param $meal
	 *
	 * @return bool     true if success
	 */
	function yk_mt_meal_add( $meal ) {

		// Ensure we have the expected fields.
		if ( false === yk_mt_array_check_fields( $meal, [ 'added_by', 'name', 'calories', 'quantity', 'description' ] ) ) {
			return false;
		}

		unset( $meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_mysql_formats( $meal );

		$result = $wpdb->insert( $wpdb->prefix . WE_LS_MYSQL_AWARDS , $meal, $formats );

		return ( false === $result ) ? false : $wpdb->insert_id;
	}

	/**
	 * Return data formats
	 *
	 * @param $data
	 * @return array
	 */
	function yk_mt_mysql_formats( $data ) {

		$formats = [
			'id' => '%d',
			'name' => '%s',
			'added_by' => '%d',
			'gain_loss' => '%s',
			'calories' => '%f',
			'quantity' => '%f',
			'description' => '%s',
			'user_id' => '%d',
			'calories_allowed' => '%f',
			'calories_used' => '%f',
			'meal_time' => '%d',
			'meal_id' => '%d',
			'user_id' => '%d',
			'value' => '%s'
		];

		$return = [];

		foreach ( $data as $key => $value) {
			if ( false === empty( $formats[ $key ] ) ) {
				$return[] = $formats[ $key ];
			}
		}

		return $return;
	}

	/**
	 *  Build the relevant database tables
	 */
	function yk_wt_mysql_tables_create() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// -------------------------------------------------
		// Meals
		// -------------------------------------------------

		$table_name = $wpdb->prefix . YK_WT_DB_MEALS;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					added_by int NOT NULL,
					name varchar(60) NOT NULL, 
					calories float DEFAULT 0 NOT NULL,
					quantity float DEFAULT 0 NOT NULL,
					description varchar(40) NOT NULL,
				  UNIQUE KEY id (id)
				) $charset_collate;";

		dbDelta( $sql );

		// -------------------------------------------------
		// Daily Entry
		// -------------------------------------------------

		$table_name = $wpdb->prefix . YK_WT_DB_ENTRY;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					user_id int NOT NULL,
					calories_allowed float DEFAULT 0 NOT NULL,
					calories_used float DEFAULT 0 NOT NULL,
				  UNIQUE KEY id (id)
				) $charset_collate;";

		dbDelta( $sql );

		// -------------------------------------------------
		// Meals for Daily Entry
		// -------------------------------------------------

		$table_name = $wpdb->prefix . YK_WT_DB_ENTRY_MEAL;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					user_id int NOT NULL,
					meal_time int NOT NULL,
					meal_id int NOT NULL,
					entry_id int NOT NULL,
				  UNIQUE KEY id (id)
				) $charset_collate;";

		dbDelta( $sql );

	}