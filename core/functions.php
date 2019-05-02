<?php

	/**
	 * Add a meal to an entry
	 *
	 * @param $entry_id
	 * @param $meal_id
	 * @param $meal_type
	 *
	 * @return bool
	 */
	function yk_mt_entry_meal_add( $entry_id, $meal_id, $meal_type ) {

		$entry = yk_mt_db_entry_get( $entry_id );

		// Does entry exist?
		if ( false === $entry ) {
			return false;
		}

		$meal = yk_mt_db_meal_get( $meal_id );

		// Does meal exist?
		if ( false === $meal ) {
			return false;
		}

		// Valid meal time?
		if ( false === in_array( $meal_type, yk_mt_meal_types_ids() ) ) {
			return false;
		}

		// Add Meal to Entry
		$result = yk_mt_db_entry_meal_add([
			'entry_id' => $entry_id,
			'meal_id' => $meal_id,
			'meal_type' => $meal_type
		]);

		// Did the DB insert work?
		if ( false === $result ) {
			return false;
		}

		return yk_mt_entry_calories_calculate_update_used( $entry_id );
	}

	/**
	 * Delete a meal for a given entry_meal_id
	 *
	 * @param $entry_meal_id
	 *
	 * @return bool
	 */
	function yk_mt_entry_meal_delete( $entry_meal_id ) {

		$entry_meal = yk_mt_db_entry_meal_get( $entry_meal_id );

		if ( false === $entry_meal ) {
			return false;
		}

		if ( false === yk_mt_db_entry_meal_delete( $entry_meal_id ) ) {
			return false;
		}

		return yk_mt_entry_calories_calculate_update_used( $entry_meal['entry_id'] );
	}

	/**
	 * Total up the calories used for an entry (sum all meals added) and update.
	 *
	 * @param $entry_id
	 *
	 * @return bool
	 */
	function yk_mt_entry_calories_calculate_update_used( $entry_id ) {

		if ( false === is_numeric( $entry_id ) ) {
			return false;
		}

		$calories = yk_mt_db_entry_calories_count( $entry_id );

		// If no calories for entry then we have an issue!
		if ( NULL === $calories ) {
			return false;
		}

		$result = yk_mt_db_entry_update( [ 'id' => $entry_id, 'calories_used' => $calories ] );

		do_action( 'yk_mt_entry_cache_clear', $entry_id );

		return $result;
	}

	/**
	 * Set fave status for a meal
	 *
	 * @param $meal_id
	 * @param bool $favourite
	 *
	 * @return bool
	 */
	function yk_mt_meal_update_fave( $meal_id, $favourite = true ) {
		return yk_mt_db_meal_update( [ 'id' => $meal_id, 'favourite' => ( true === $favourite ) ? 1 : 0 ] );
	}

	/**
	 * Fetch all IDs for Meal Types
	 *
	 * @return array
	 */
	function yk_mt_meal_types_ids() {

		$meal_types = yk_mt_db_meal_types_all();

		return ( false === empty( $meal_types ) ) ? wp_list_pluck( $meal_types, 'id' ) : [];
	}

	/**
	 * Get the allowed calories for the given user
	 *
	 * @param null $id
	 *
	 * @return int
	 */
	function yk_mt_user_calories_target( $id = NULL ) {

		$id = ( NULL === $id ) ? get_current_user_id() : $id;

		// TODO: Look this up? Hook this into WLT
		$allowed_calories = 2000;

		$allowed_calories = apply_filters( 'yk_mt_user_allowed_calories', $allowed_calories );

		return (int) $allowed_calories;
	}

	/**
	 * Helper function to ensure all fields have expected keys
	 *
	 * @param $data
	 * @param $expected_fields
	 * @return bool
	 */
	function yk_mt_array_check_fields($data, $expected_fields ) {

		foreach ( $expected_fields as $field ) {
			if ( false === isset( $data[ $field ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate an ISO date
	 *
	 * @param $iso
	 *
	 * @return bool
	 */
	function yk_mt_date_is_valid_iso( $iso ) {

		if ( true === empty( $iso ) ) {
			return false;
		}

		$iso = explode( '-', $iso );

		if ( 3 !== count( $iso ) ) {
			return false;
		}

		return checkdate ( $iso[ 1 ], $iso[ 2 ], $iso[ 0 ] );
	}

	/**
	 * Get today's date in ISO
	 *
	 * @return string
	 */
	function yk_mt_date_iso_today() {
		return date( 'Y-m-d' );
	}

	/**
	 * Use minified scripts?
	 *
	 * @return string
	 */
	function yk_mt_use_minified() {
		return ''; //TODO
		return ( true === defined('SCRIPT_DEBUG') && false == SCRIPT_DEBUG ) ? '.min' : '';
	}