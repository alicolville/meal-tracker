<?php

	defined('ABSPATH') or die("Jog on!");

	define( 'YK_WT_DB_MEALS', 'yk_mt_meals');                   // Store all meal types
	define( 'YK_WT_DB_ENTRY', 'yk_mt_entry');                   // Store all entries for the given user
	define( 'YK_WT_DB_ENTRY_MEAL', 'yk_mt_entry_meals');        // Store all meals for given entry
	define( 'YK_WT_DB_MEAL_TYPES', 'yk_mt_meal_types');         // Store all meal types

	/**
	 * Add an entry
	 *
	 * @param $entry
	 *
	 * @return bool     true if success
	 */
	function yk_mt_db_entry_add( $entry ) {

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

		$formats = yk_mt_db_mysql_formats( $entry );

		$result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_ENTRY , $entry, $formats );

		if ( false === $result ) {
			return false;
		}

		$id = $wpdb->insert_id;

		do_action( 'yk_mt_entry_added', $id, $entry );

		return $id;
	}

	/**
	 *
	 * Update an entry
	 *
	 * @param $entry
	 *
	 * @return bool     true if success
	 */
	function yk_mt_db_entry_update( $entry ) {

		if ( false === yk_mt_array_check_fields( $entry, [ 'id' ] ) ) {
			return false;
		}

		$id = $entry[ 'id' ];

		unset( $entry[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_db_mysql_formats( $entry );

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
	function yk_mt_db_entry_delete( $id ) {

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
	function yk_mt_db_entry_delete_entries( $entry_id ) {

		global $wpdb;

		$result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, [ 'entry_id' => $entry_id ], [ '%d' ] );

		return ( 1 === $result );

	}
	add_action( 'yk_mt_entry_deleted', 'yk_mt_entry_delete_entries' );     // Delete all Meal / Entry relationships when a meal has been deleted

	/**
	 * Get Entry ID
	 *
	 * @param null $user_id
	 *
	 * @return null|string
	 */
	function yk_mt_db_entry_get_id_for_today( $user_id = NULL ) {

		$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

		$todays_date = yk_mt_date_iso_today();

		global $wpdb;

		$sql = $wpdb->prepare( 'Select id from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where user_id = %d and date = %s', $user_id, $todays_date );

		$result = $wpdb->get_var( $sql );

		return ( false === empty( $result ) ) ? (int) $result : NULL ;
	}

	/**
	 * Get details for an entry
	 *
	 * @param $key
	 */
	function yk_mt_db_entry_get( $id = NULL ) {

		if ( NULL === $id ) {
			$id = yk_mt_db_entry_get_id_for_today();
		}

		if ( true === empty( $id ) ) {
			return NULL;
		}

		if ( $cache = apply_filters( 'yk_mt_db_entry_get', NULL, $id ) ) {
			return $cache;
		}

		global $wpdb;

		$sql = $wpdb->prepare('Select * from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where id = %d limit 0, 1', $id );

		$entry = $wpdb->get_row( $sql, ARRAY_A );

		$entry = ( false === empty( $entry ) ) ? $entry : false;

        $entry[ 'percentage_used' ] = ( $entry[ 'calories_used' ] / $entry[ 'calories_allowed' ] ) * 100;
        $entry[ 'percentage_used' ] = round( $entry[ 'percentage_used' ], 1);

		$entry[ 'calories_remaining' ] = $entry[ 'calories_allowed' ] - $entry[ 'calories_used' ];
		$entry[ 'calories_remaining' ] = ( $entry[ 'calories_remaining' ] < 0 ) ? 0 : $entry[ 'calories_remaining' ];

        // If an entry was found, fetch all the meals entered for it.
		if ( $entry !== false ) {

			$sql = $wpdb->prepare( 'Select m.id, m.name, m.calories, m.quantity,
                                    em.meal_type, em.id as meal_entry_id from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' m 
									Inner Join ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL . ' em
									on em.meal_id = m.id
									where em.entry_id = %d
									order by meal_type, em.id asc',
									$id
			);

            $meal_type_ids = yk_mt_meal_types_ids();

            $entry['meals'] = [];
            $entry['counts'] = [];

            // Initiate an empty array
            foreach ( $meal_type_ids as $id ) {
                $entry['meals'][ $id ] = [];
                $entry['counts'][ $id ] = 0;
            }

			$meals = $wpdb->get_results( $sql, ARRAY_A );

			if ( false === empty( $meals ) ) {
				foreach ( $meals as $meal ) {
                    $entry['meals'][ $meal['meal_type'] ][] = $meal;
                    $entry['counts'][ $meal['meal_type'] ] += $meal['calories'];
				}
			}
		}

		do_action( 'yk_mt_entry_lookup', $id, $entry );

		return $entry;
	}

	/**
	 * Count calories for given entry
	 *
	 * @param $entry_id
	 *
	 * @return null|string
	 */
	function yk_mt_db_entry_calories_count( $entry_id ) {

		global $wpdb;

		$sql = $wpdb->prepare( 'Select sum( calories ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL . ' em 
				inner join ' . $wpdb->prefix . YK_WT_DB_MEALS . ' m
				on em.meal_id = m.id where entry_id = %d', $entry_id );

		return $wpdb->get_var( $sql );
	}

	/**
	 * Add an entry / meal relationship
	 *
	 * @param $entry_meal
	 *
	 * @return bool     true if success
	 */
	function yk_mt_db_entry_meal_add( $entry_meal ) {

		// Ensure we have the expected fields.
		if ( false === yk_mt_array_check_fields( $entry_meal, [ 'meal_type', 'meal_id', 'entry_id' ] ) ) {
			return false;
		}

		unset( $entry_meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_db_mysql_formats( $entry_meal );

		$result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL , $entry_meal, $formats );

		if ( false === $result ) {
			return false;
		}

		$id = $wpdb->insert_id;

		do_action( 'yk_mt_entry_meal_added', $id, $entry_meal );
		do_action( 'yk_mt_entry_cache_clear', $entry_meal[ 'entry_id' ] );

		return $id;
	}

	/**
	 * Get details for an entry_meal
	 *
	 * @param $key
	 */
	function yk_mt_db_entry_meal_get( $id ) {

		global $wpdb;

		$sql = $wpdb->prepare('Select * from ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL . ' where id = %d limit 0, 1', $id );

		$entry_meal = $wpdb->get_row( $sql, ARRAY_A );

		return ( false === empty( $entry_meal ) ) ? $entry_meal : false;
	}

	/**
	 *
	 * Update an entry / meal
	 *
	 * @param $entry
	 *
	 * @return bool     true if success
	 */
	function yk_mt_db_entry_meal_update( $entry_meal ) {

		if ( false === yk_mt_array_check_fields( $entry_meal, [ 'id', 'meal_type', 'meal_id', 'entry_id' ] ) ) {
			return false;
		}

		$id = $entry_meal[ 'id' ];

		unset( $entry_meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_db_mysql_formats( $entry_meal );

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
	function yk_mt_db_entry_meal_delete( $id ) {

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
	function yk_mt_db_meal_add( $meal ) {

		// Ensure we have the expected fields.
		if ( false === yk_mt_array_check_fields( $meal, [ 'added_by', 'name', 'calories', 'quantity' ] ) ) {
			return false;
		}

		unset( $meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_db_mysql_formats( $meal );

		$result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_MEALS , $meal, $formats );

		if ( false === $result ) {
			return false;
		}

		$id = $wpdb->insert_id;

		do_action( 'yk_mt_meal_added', $id, $meal );

		return $id;
	}

	/**
	 *
	 * Update a meal
	 *
	 * @param $meal
	 *
	 * @return bool     true if success
	 */
	function yk_mt_db_meal_update( $meal ) {

		if ( false === yk_mt_array_check_fields( $meal, [ 'id' ] ) ) {
			return false;
		}

		$id = $meal[ 'id' ];

		unset( $meal[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_db_mysql_formats( $meal );

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
	function yk_mt_db_meal_delete( $id ) {

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
	function yk_mt_db_meal_get( $id ) {

		if ( $cache = apply_filters( 'yk_mt_db_meal_get', NULL, $id ) ) {
			return $cache;
		}

		global $wpdb;

		$sql = $wpdb->prepare('Select * from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where id = %d limit 0, 1', $id );

		$meal = $wpdb->get_row( $sql, ARRAY_A );

		$meal = ( false === empty( $meal ) ) ? $meal : false;

		do_action( 'yk_mt_meal_lookup', $id, $meal );

		return $meal;
	}

	/**
	 * Get meals added by a user
	 *
	 * @param null $user_id
	 * @param bool $include_deleted
	 *
	 * @return array|null
	 */
	function yk_mt_db_meal_for_user( $user_id = NULL, $options  = []  ) {
// TODO: Cache?
		$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

		$options = wp_parse_args( $options, [
			'exclude-deleted' => true,
			'sort' => 'name',
			'sort-order' => 'asc'
		]);

		global $wpdb;

		$sql = $wpdb->prepare('Select * from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where added_by = %d', $user_id );

		// Exclude deleted?
		if ( true === $options[ 'exclude-deleted' ] ) {
			$sql .= ' and deleted = 0';
		}

		$sort = ( true === in_array( $options[ 'sort' ], [ 'name', 'calories' ] ) ) ?  $options[ 'sort' ] : 'name';

		$sort_order = ( true === in_array( $options[ 'sort-order' ], [ 'asc', 'desc' ] ) ) ? $options[ 'sort-order' ] : 'asc';

		$sql .= sprintf( ' order by %s %s', $sort, $sort_order );

		$meals = $wpdb->get_results( $sql, ARRAY_A );

		$meals = ( false === empty( $meals ) ) ? $meals : false;

		return $meals;
	}

	/**
	 * Delete all entries / meals relationships when a meal is deleted
	 *
	 * @param $meal_id
	 * @return bool
	 */
	function yk_mt_db_meal_delete_entries( $meal_id ) {

		global $wpdb;

		$result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, [ 'meal_id' => $meal_id ], [ '%d' ] );

		return ( 1 === $result );

	}
	add_action( 'yk_mt_meal_deleted', 'yk_mt_meal_delete_entries' );     // Delete all Meal / Entry relationships when a meal has been deleted

	/**
	 * Add a meal type
	 *
	 * @param $meal
	 *
	 * @return bool     true if success
	 */
	function yk_mt_db_meal_types_add( $meal_type ) {

		// Ensure we have the expected fields.
		if ( false === yk_mt_array_check_fields( $meal_type, [ 'name', 'sort'  ] ) ) {
			return false;
		}

		unset( $meal_type[ 'id' ] );

		global $wpdb;

		$formats = yk_mt_db_mysql_formats( $meal_type );

		$result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_MEAL_TYPES , $meal_type, $formats );

		if ( false === $result ) {
			return false;
		}

		$id = $wpdb->insert_id;

		do_action( 'yk_mt_meal_types_added', $id, $meal_type );

		return $id;
	}

	/**
	 * Get all meal types
	 *
	 * @param $key
	 */
	function yk_mt_db_meal_types_all() {

		if ( $cache = apply_filters( 'yk_mt_db_meal_types_all', NULL ) ) {
			return $cache;
		}

		global $wpdb;

		$meal_types = $wpdb->get_results( 'Select * from ' . $wpdb->prefix . YK_WT_DB_MEAL_TYPES . ' where deleted = 0 order by sort asc', ARRAY_A );

		$meal_types = ( false === empty( $meal_types ) ) ? $meal_types : false;

		do_action( 'yk_mt_meal_types_all', $meal_types );

		return $meal_types;
	}

	/**
	 * @param null $table
	 *
	 * @return null|string
	 */
	function yk_mt_db_mysql_count_table( $table = NULL ) {

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
	function yk_mt_db_mysql_formats( $data ) {

		$formats = [
			'id' => '%d',
			'name' => '%s',
			'added_by' => '%d',
			'entry_id' => '%d',
			'gain_loss' => '%s',
			'calories' => '%f',
			'quantity' => '%f',
			'description' => '%s',
			'user_id' => '%d',
			'calories_allowed' => '%f',
			'calories_used' => '%f',
			'meal_type' => '%d',
			'meal_id' => '%d',
			'date' => '%s',
			'value' => '%s',
			'deleted' => '%d',
			'favourite' => '%d',
            'unit' => '%s'
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
	function yk_wt_db_tables_create() {

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
					unit varchar(10) DEFAULT 'g' NOT NULL, 
					description varchar(200) NOT NULL,
					deleted bit DEFAULT 0,
					favourite bit DEFAULT 0,
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
					meal_type int NOT NULL,
					meal_id int NOT NULL,
					entry_id int NOT NULL,
				  UNIQUE KEY id (id)
				) $charset_collate;";

		dbDelta( $sql );

		// -------------------------------------------------
		// Store Meal Types
		// -------------------------------------------------

		$table_name = $wpdb->prefix . YK_WT_DB_MEAL_TYPES;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					name varchar(60) NOT NULL, 
					sort int DEFAULT 100 NOT NULL,
					deleted bit DEFAULT 0,
				  UNIQUE KEY id (id)
				) $charset_collate;";

		dbDelta( $sql );

	}