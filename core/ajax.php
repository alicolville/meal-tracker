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

    if ( true !== yk_mt_entry_meal_delete( $post_data[ 'meal-entry-id' ] ) ) {
        return wp_send_json( [ 'error' => 'updating-db' ] );
    }

    wp_send_json( [ 'error' => false, 'entry' => yk_mt_entry( $post_data[ 'entry-id' ] ) ] );
}
add_action( 'wp_ajax_delete_meal_to_entry', 'yk_mt_ajax_delete_meal_to_entry' );

function yk_mt_ajax_add_meal() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

    $post_data = $_POST;

    unset( $post_data[ 'security' ] );
    unset( $post_data[ 'action' ] );

    $post_data[ 'added_by' ] = get_current_user_id();

    // Validate we have all the expected fields
    yk_mt_ajax_validate_post_data( $post_data, [ 'name', 'description', 'calories', 'quantity', 'unit' ] );

    $meal_id = yk_mt_db_meal_add( $post_data );

    if ( false === $meal_id ) {
        return wp_send_json( [ 'error' => 'updating-db' ] );
    }

    wp_send_json( [ 'error' => false, 'id' => $meal_id ] );
}
add_action( 'wp_ajax_add_meal', 'yk_mt_ajax_add_meal' );

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