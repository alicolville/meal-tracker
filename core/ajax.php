<?php

defined('ABSPATH') or die("Jog on!");

/**
 * REST Handler for adding a meal to an entry
 *
 * @return WP_REST_Response
 */
function yk_mt_ajax_add_meal_to_entry() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

    $post_data = $_POST;

    $post_data[ 'user-id' ]     = get_current_user_id();
    $post_data[ 'entry-id' ]    = ( true === empty( $post_data[ 'entry-id' ] ) ) ? yk_mt_entry_get_id_or_create( (int) $post_data[ 'user-id' ]  ) : (int) $post_data[ 'entry-id' ];

	// Validate we have all the expected fields
    yk_mt_ajax_validate_post_data( $post_data, [ 'user-id', 'entry-id', 'meal-id', 'meal-type', 'quantity' ] );

	$quantity = (int) $post_data[ 'quantity' ];

    if ( $quantity > 50 ) {
        $quantity = 50;
    }

    $post_data = yk_mt_ajax_strip_incoming( $post_data );

	// Ensure the user is the owner of the entry.
	if ( false === yk_mt_security_entry_owned_by_user( $post_data[ 'entry-id' ], $post_data[ 'user-id' ] ) ) {
		return wp_send_json( [ 'error' => 'security' ] );
	}

    for ( $i = 0; $i < $quantity; $i++ ) {
        if ( false === yk_mt_entry_meal_add( (int) $post_data[ 'entry-id' ], (int) $post_data[ 'meal-id' ], (int) $post_data[ 'meal-type' ] ) ) {
            return wp_send_json( [ 'error' => 'updating-db' ] );
        }
    }

    wp_send_json( [ 'error' => false, 'entry' => yk_mt_entry( $post_data[ 'entry-id' ] ) ] );
}
add_action( 'wp_ajax_add_meal_to_entry', 'yk_mt_ajax_add_meal_to_entry' );

/**
 * Delete a meal from an entry
 */
function yk_mt_ajax_delete_meal_to_entry() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

    $post_data = $_POST;

    $post_data[ 'meal-entry-id' ]  =  yk_mt_ajax_get_post_value_int( 'meal-entry-id' );
    $post_data[ 'entry-id' ]       = ( true === empty( $post_data[ 'entry-id' ] ) ) ? yk_mt_entry_get_id_or_create() : (int) $post_data[ 'entry-id' ];

    // Validate we have all the expected fields
    yk_mt_ajax_validate_post_data( $post_data, [ 'meal-entry-id', 'entry-id' ] );

	// Ensure the user is the owner of the entry.
	if ( false === yk_mt_security_entry_owned_by_user( $post_data[ 'entry-id' ], get_current_user_id() ) ) {
		return wp_send_json( [ 'error' => 'security' ] );
	}

    $post_data = yk_mt_ajax_strip_incoming( $post_data );

    if ( true !== yk_mt_entry_meal_delete( $post_data[ 'meal-entry-id' ] ) ) {
        return wp_send_json( [ 'error' => 'updating-db' ] );
    }

    wp_send_json( [ 'error' => false, 'entry' => yk_mt_entry( $post_data[ 'entry-id' ] ) ] );
}
add_action( 'wp_ajax_delete_meal_to_entry', 'yk_mt_ajax_delete_meal_to_entry' );

/**
 * Add a new meal
 *
 * @return mixed
 */
function yk_mt_ajax_meal_add() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

    $post_data = $_POST;

    $post_data[ 'added_by' ] = get_current_user_id();

    $entry_id = ( true === empty( $post_data[ 'entry-id' ] ) ) ? yk_mt_entry_get_id_or_create() : (int) $post_data[ 'entry-id' ];

    $post_data = yk_mt_ajax_strip_incoming( $post_data, [ 'entry-id', 'meal-type' ] );

    // Validate we have all the expected fields
    yk_mt_ajax_validate_post_data( $post_data, [ 'name', 'unit' ] );

    // Ensure we have a calorie value (can be 0)
    if ( false === isset( $post_data[ 'calories'] ) ) {
        return wp_send_json( [ 'error' => 'missing-calories' ] );
    }

    // If a unit that doesn't expect a quantity, then clear quantity
	if ( true === in_array( $post_data[ 'unit' ], yk_mt_units_where( 'drop-quantity' ) ) ) {
		$post_data[ 'quantity' ] = '';
	} else {
		// Now check we have it if expected!
		yk_mt_ajax_validate_post_data( $post_data, [ 'quantity' ] );
	}

    // Are we updating a meal?
    $meal_id = yk_mt_ajax_get_post_value_int( 'id', false );

    if ( false === empty( $meal_id ) ) {

        if ( false === yk_mt_db_meal_update( $post_data ) ) {
            return wp_send_json( [ 'error' => 'updating-db' ] );
        }

        yk_mt_entry_calories_calculate_update_used( $entry_id );

    } else {
        $meal_id = yk_mt_db_meal_add( $post_data );

        // If we have an entry / meal type ID, then add the meal to the entry automatically
        if ( false === empty( $entry_id ) &&
            false === empty( $_POST[ 'meal-type' ] ) ) {

            yk_mt_entry_meal_add( $entry_id, $meal_id, (int) $_POST[ 'meal-type' ] );
        }

        if ( false === $meal_id ) {
            return wp_send_json( [ 'error' => 'updating-db' ] );
        }
    }

	$post_data['id'] = $meal_id;

    wp_send_json( [ 'error' => false, 'new-meal' => $post_data ] );
}
add_action( 'wp_ajax_add_meal', 'yk_mt_ajax_meal_add' );

/**
 * Fetch the data for a meal
 */
function yk_mt_ajax_meal() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

    $post_data = $_POST;

    $post_data[ 'meal-id' ]  = yk_mt_ajax_get_post_value_int( 'meal-id' );

    // Validate we have all the expected fields
    yk_mt_ajax_validate_post_data( $post_data, [ 'meal-id' ] );

    $meal = yk_mt_db_meal_get( $post_data[ 'meal-id' ], get_current_user_id() );

    if ( false === $meal ) {
	    return wp_send_json( [ 'error' => 'loading-meal' ] );
    }

    wp_send_json( [ 'error' => false, 'meal' => $meal ] );
}
add_action( 'wp_ajax_meal', 'yk_mt_ajax_meal' );

/**
 * Fetch all meals for a given user
 */
function yk_mt_ajax_meals() {

	check_ajax_referer( 'yk-mt-nonce', 'security' );

	// If performing a search, set options to look for string. Otherwise load load a max of 20 from DB for user.
	if ( false === empty( $_POST[ 'search' ] ) ) {
		$options = [ 'search' => $_POST[ 'search' ] ];
	} else {
		$options = [ 'limit' => 20 ];
	}

	$meals = yk_mt_db_meal_for_user( get_current_user_id(), $options );

	// Compress meal objects to reduce data returned via AJAX
	$meals = array_map( 'yk_mt_ajax_prep_meal', $meals );

	wp_send_json( $meals );
}
add_action( 'wp_ajax_meals', 'yk_mt_ajax_meals' );

/**
 * Save Settings for user
 */
function yk_mt_ajax_save_settings() {

	check_ajax_referer( 'yk-mt-nonce', 'security' );

	$updated = false;

	foreach ( $_POST as $key => $value ) {

		$key = str_replace( 'yk-mt-', '', $key );

		if ( false === in_array( $key, yk_mt_settings_allowed_keys() ) ) {
			continue;
		}

		yk_mt_settings_set( $key, $value );

		$updated = true;
	}

    do_action( 'yk_mt_settings_saved' );

	wp_send_json( [ 'error' => ! $updated ] );
}
add_action( 'wp_ajax_save_settings', 'yk_mt_ajax_save_settings' );

/**
 * REST Handler for fetching an entry
 *
 * @return WP_REST_Response
 */
function yk_mt_ajax_get_entry() {

	check_ajax_referer( 'yk-mt-nonce', 'security' );

	$entry_id = ( false === empty( $_POST[ 'entry-id' ] ) ) ? (int) $_POST[ 'entry-id' ] : false;

    $entry = yk_mt_entry( $entry_id );

    // Ensure the User is requesting their own entry!
	if ( get_current_user_id() !== (int) $entry[ 'user_id' ] ) {
		return wp_send_json( [ 'error' => 'security' ] );
	}

    wp_send_json( $entry );
}
add_action( 'wp_ajax_get_entry', 'yk_mt_ajax_get_entry' );

/**
 * Strip back a meal object ready for transmission via AJAX
 * @param $meal
 * @return mixed
 */
function yk_mt_ajax_prep_meal( $meal ) {

    if ( true === is_array( $meal ) ) {

    	$meal[ 'name' ] = sprintf( '%1$s ( %2$s / %3$d%4$s )',
                    $meal[ 'name' ],
		            yk_mt_get_unit_string( $meal ),
                    $meal[ 'calories' ],
                    __( 'kcal', YK_MT_SLUG )
        );

        $meal = yk_mt_array_strip_keys( $meal, [ 'added_by', 'calories', 'unit', 'quantity', 'description', 'deleted', 'favourite' ] );
    }

    return $meal;
}

/**
 * Tidy up post data
 * @param $postdata
 * @return mixed
 */
function yk_mt_ajax_strip_incoming( $post_data, $additional = [] ) {

    $defaults = [ 'security', 'action', 'entry' ];

    if ( false === empty( $additional ) ) {
        $defaults = array_merge( $defaults, $additional );
    }

	return yk_mt_array_strip_keys( $post_data, $defaults );
}

/**
 * For the given array of keys, ensure they are found within $post_data
 *
 * @param array $post_data
 * @param array $keys
 */
function yk_mt_ajax_validate_post_data( $post_data, $keys = [] ) {

    foreach ( $keys as $key ) {
        if ( true === empty( $post_data[ $key ] ) ) {
            wp_send_json( [ 'error' => 'missing-' . $key ] );
        }
    }
}

/**
 * Fetch data from $_POST and force to INT
 * @param $key
 * @param null $default
 * @return bool|int|mixed
 */
function yk_mt_ajax_get_post_value_int( $key, $default = NULL ) {
    return yk_mt_ajax_get_post_value( $key, $default, true );
}

/**
 * Fetch data from $_POST
 * @param $key
 * @param bool $default
 * @param bool $force_to_int
 * @return bool|int|mixed
 */
function yk_mt_ajax_get_post_value( $key, $default = NULL, $force_to_int = false ) {

    $value = NULL;

    if ( false === empty( $_POST[ $key ] ) ) {
        return ( true === $force_to_int ) ? (int) $_POST[ $key ] : $_POST[ $key ];
    }

    return $default ?: NULL;
}
