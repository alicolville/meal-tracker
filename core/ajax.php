<?php

defined('ABSPATH') or die("Jog on!");

/**
 * REST Handler for adding a meal to an entry
 *
 * @return WP_REST_Response
 */
function yk_mt_ajax_add_meal_to_entry() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

    // TODO: Check the logged in user is adding a meal to an entry of theirs?

	$user_id = NULL;
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

    // TODO: Check the logged in user is deleting a meal entry of theirs?

    $post_data[ 'meal-entry-id' ]  = ( false === empty( $post_data[ 'meal-entry-id' ] ) ) ? (int) $post_data[ 'meal-entry-id' ]  : false;
    $post_data[ 'entry-id' ]       = ( true === empty( $post_data[ 'entry-id' ] ) ) ? yk_mt_entry_get_id_or_create() : (int) $post_data[ 'entry-id' ];

    // Validate we have all the expected fields
    yk_mt_ajax_validate_post_data( $post_data, [ 'meal-entry-id', 'entry-id' ] );

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
function yk_mt_ajax_add_meal() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

    $post_data = $_POST;

    $post_data[ 'added_by' ] = get_current_user_id();

    $post_data = yk_mt_ajax_strip_incoming( $post_data );

    // Validate we have all the expected fields
    yk_mt_ajax_validate_post_data( $post_data, [ 'name', 'calories', 'quantity', 'unit' ] );

    $meal_id = yk_mt_db_meal_add( $post_data );

    if ( false === $meal_id ) {
        return wp_send_json( [ 'error' => 'updating-db' ] );
    }

	$post_data['id'] = $meal_id;

    wp_send_json( [ 'error' => false, 'new-meal' => $post_data ] );
}
add_action( 'wp_ajax_add_meal', 'yk_mt_ajax_add_meal' );

/**
 * Fetch all meals for a given user
 *
 * TODO: expand to support search
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

	// TODO: Do some sort of caching. A lot of processing has occurred here.

	wp_send_json( $meals );
}
add_action( 'wp_ajax_meals', 'yk_mt_ajax_meals' );

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
function yk_mt_ajax_strip_incoming( $post_data ) {
	return yk_mt_array_strip_keys( $post_data, [ 'security', 'action' ] );
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
 * REST Handler for fetching an entry
 *
 * @return WP_REST_Response
 */
function yk_mt_ajax_get_entry() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

    $entry_id = ( false === empty( $_POST[ 'entry-id' ] ) ) ? (int) $_POST[ 'entry-id' ] : false;

    $entry = yk_mt_entry( $entry_id );

    wp_send_json( $entry );
}
add_action( 'wp_ajax_get_entry', 'yk_mt_ajax_get_entry' );

/**
 * Extra layer to ensure an admin call to the API is allowed.
 */
function yk_mt_api_admin_allowed() {
	return true; //TODO! Still needed? If in Admin, ensure they have the correct capability to be editing user records.
}