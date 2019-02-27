<?php

	defined('ABSPATH') or die("Jog on!");

	define( 'YK_WT_DB_MEALS', 'yk_mt_meals');                   // Store all meal types
	define( 'YK_WT_DB_ENTRY', 'yk_mt_entry');                   // Store all entries for the given user
	define( 'YK_WT_DB_ENTRY_MEAL', 'yk_mt_entry_meals');        // Store all meals for given entry

	/**
	 * Add an entry
	 *
	 * @param $entry
	 *
	 * @return bool     true if success
	 */
	function yk_mt_entry_add( $entry ) {

		// Ensure we have the expected fields.
		if ( false === yk_mt_array_check_fields( $entry, [ 'user_id', 'calories_allowed', 'calories_used', 'date' ] ) ) {
			return false;
		}

		unset( $entry[ 'id' ] );

		// If an invalid ISO date, force to today's date
		if ( false === yk_mt_date_is_valid_iso( $entry['date'] ) ) {
			$entry['date'] = yk_mt_date_iso_today();
		}

		global $wpdb;

		$formats = yk_mt_mysql_formats( $entry );

		$result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_ENTRY , $entry, $formats );

		if ( false === $result ) {
			return false;
		}

		do_action( 'yk_mt_entry_added', $wpdb->insert_id, $entry );

		return $wpdb->insert_id;
	}

	/**
	 *
	 * Update an entry
	 *
	 * @param $entry
	 *
	 * @return bool     true if success
	 */
	function yk_mt_entry_update( $entry ) {

		if ( false === yk_mt_array_check_fields( $entry, [ 'id', 'user_id', 'calories_allowed', 'calories_used', 'date' ] ) ) {
			return false;
		}

		$id = $entry[ 'id' ];

		unset( $entry[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_mysql_formats( $entry );

		$result = $wpdb->update( $wpdb->prefix . YK_WT_DB_ENTRY, $entry, [ 'id' => $id ], $formats, [ '%d' ] );

		if ( false === $result ) {
			return false;
		}

		do_action( 'yk_mt_entry_updated', $id, $entry );

		return true;
	}

	/**
	 * Delete an entry
	 *
	 * @param $id       entry ID to delete
	 * @return bool     true if success
	 */
	function yk_mt_entry_delete( $id ) {

		global $wpdb;

		do_action( 'yk_mt_entry_deleting', $id );

		$result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY, [ 'id' => $id ], [ '%d' ] );

		if ( 1 !== $result ) {
			return false;
		}

		do_action( 'yk_mt_entry_deleted', $id );

		return true;
	}

	/**
	 * Delete all entries / meals relationships when an entry is deleted
	 *
	 * @param $meal_id
	 * @return bool
	 */
	function yk_mt_entry_delete_entries( $entry_id ) {

		global $wpdb;

		$result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, [ 'entry_id' => $entry_id ], [ '%d' ] );

		return ( 1 === $result );

	}
	add_action( 'yk_mt_entry_deleted', 'yk_mt_entry_delete_entries' );     // Delete all Meal / Entry relationships when a meal has been deleted

	/**
	 * Get details for an entry
	 *
	 * @param $key
	 */
	function yk_mt_entry_get( $id ) {

		if ( $cache = apply_filters( 'yk_mt_db_entry_get', NULL, $id ) ) {
			echo 'cache';
			return $cache;
		}

		global $wpdb;

		$sql = $wpdb->prepare('Select * from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where id = %s limit 0, 1', $id );

		$entry = $wpdb->get_row( $sql, ARRAY_A );

		$entry = ( false === empty( $entry ) ) ? $entry : false;

		do_action( 'yk_mt_entry_lookup', $id, $entry );

		return $entry;
	}

	/**
	 * Add an entry / meal relationship
	 *
	 * @param $entry_meal
	 *
	 * @return bool     true if success
	 */
	function yk_mt_entry_meal_add( $entry_meal ) {

		// Ensure we have the expected fields.
		if ( false === yk_mt_array_check_fields( $entry_meal, [ 'meal_time', 'meal_id', 'entry_id' ] ) ) {
			return false;
		}

		unset( $entry_meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_mysql_formats( $entry_meal );

		$result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL , $entry_meal, $formats );

		if ( false === $result ) {
			return false;
		}

		do_action( 'yk_mt_entry_meal_added', $wpdb->insert_id, $entry_meal );

		return $wpdb->insert_id;
	}

	/**
	 *
	 * Update an entry / meal
	 *
	 * @param $entry
	 *
	 * @return bool     true if success
	 */
	function yk_mt_entry_meal_update( $entry_meal ) {

		if ( false === yk_mt_array_check_fields( $entry_meal, [ 'id', 'meal_time', 'meal_id', 'entry_id' ] ) ) {
			return false;
		}

		$id = $entry_meal[ 'id' ];

		unset( $entry_meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_mysql_formats( $entry_meal );

		$result = $wpdb->update( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, $entry_meal, [ 'id' => $id ], $formats, [ '%d' ] );

		if ( false === $result ) {
			return false;
		}

		do_action( 'yk_mt_entry_meal_updated', $id, $entry_meal );

		return true;
	}

	/**
	 * Delete an entry / meal
	 *
	 * @param $id       entry ID to delete
	 * @return bool     true if success
	 */
	function yk_mt_entry_meal_delete( $id ) {

		global $wpdb;

		do_action( 'yk_mt_entry_meal_deleting', $id );

		$result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, [ 'id' => $id ], [ '%d' ] );

		if ( 1 !== $result ) {
			return false;
		}

		do_action( 'yk_mt_entry_meal_deleted', $id );

		return true;
	}

	/**
	 * Add a meal
	 *
	 * @param $meal
	 *
	 * @return bool     true if success
	 */
	function yk_mt_meal_add( $meal ) {

		// Ensure we have the expected fields.
		if ( false === yk_mt_array_check_fields( $meal, [ 'added_by', 'name', 'calories', 'quantity' ] ) ) {
			return false;
		}

		unset( $meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_mysql_formats( $meal );

		$result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_MEALS , $meal, $formats );

		if ( false === $result ) {
			return false;
		}

		do_action( 'yk_mt_meal_added', $wpdb->insert_id, $meal );

		return $wpdb->insert_id;
	}

	/**
	 *
	 * Update a meal
	 *
	 * @param $meal
	 *
	 * @return bool     true if success
	 */
	function yk_mt_meal_update( $meal ) {

		if ( false === yk_mt_array_check_fields( $meal, [ 'id', 'added_by', 'name', 'calories', 'quantity' ] ) ) {
			return false;
		}

		$id = $meal[ 'id' ];

		unset( $meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_mysql_formats( $meal );

		$result = $wpdb->update( $wpdb->prefix . YK_WT_DB_MEALS, $meal, [ 'id' => $id ], $formats, [ '%d' ] );

		if ( false === $result ) {
			return false;
		}

		do_action( 'yk_mt_meal_updated', $id, $meal );

		return true;
	}

	/**
	 * Delete a meal
	 *
	 * @param $id       meal ID to delete
	 * @return bool     true if success
	 */
	function yk_mt_meal_delete( $id ) {

		global $wpdb;

		do_action( 'yk_mt_meal_deleting', $id );

		$result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_MEALS, [ 'id' => $id ], [ '%d' ] );

		if ( 1 !== $result ) {
			return false;
		}

		do_action( 'yk_mt_meal_deleted', $id );

		return true;
	}

	/**
	 * Get details for a meal
	 *
	 * @param $key
	 */
	function yk_mt_meal_get( $id ) {

		if ( $cache = apply_filters( 'yk_mt_db_meal_get', NULL, $id ) ) {
			return $cache;
		}

		global $wpdb;

		$sql = $wpdb->prepare('Select * from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where id = %s limit 0, 1', $id );

		$meal = $wpdb->get_row( $sql, ARRAY_A );

		$meal = ( false === empty( $meal ) ) ? $meal : false;

		do_action( 'yk_mt_meal_lookup', $id, $meal );

		return $meal;
	}

	/**
	 * Delete all entries / meals relationships when a meal is deleted
	 *
	 * @param $meal_id
	 * @return bool
	 */
	function yk_mt_meal_delete_entries( $meal_id ) {

		global $wpdb;

		$result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, [ 'meal_id' => $meal_id ], [ '%d' ] );

		return ( 1 === $result );

	}
	add_action( 'yk_mt_meal_deleted', 'yk_mt_meal_delete_entries' );     // Delete all Meal / Entry relationships when a meal has been deleted

	/**
	 * @param null $table
	 *
	 * @return null|string
	 */
	function yk_mt_mysql_count_table( $table = NULL ) {

		global $wpdb;

		if ( false === in_array( $table, [ YK_WT_DB_MEALS, YK_WT_DB_ENTRY, YK_WT_DB_ENTRY_MEAL ] ) ) {
			$table = YK_WT_DB_MEALS;
		}

		$result = $wpdb->get_var( 'Select count( id ) from ' . $wpdb->prefix . $table );

		return (int) $result;
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
			'date' => '%s',
			'value' => '%s',
			'deleted' => '%d'
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
					deleted bit DEFAULT 0
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
					date DATE NOT NULL,
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
					meal_time int NOT NULL,
					meal_id int NOT NULL,
					entry_id int NOT NULL,
				  UNIQUE KEY id (id)
				) $charset_collate;";

		dbDelta( $sql );

	}