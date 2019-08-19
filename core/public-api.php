<?php

defined('ABSPATH') or die("Jog on!");

define( 'YK_WT_API_ENDPOINT', 'yeken-meal-tracker/v1' );

// TODO: Remove this file.

/**
 * Register all the public endpoints for the API
 */
function yk_mt_api_register_end_points() {

	register_rest_route( YK_WT_API_ENDPOINT, '/entry-meal/add', array(
		'methods' => 'POST',
		'callback' => 'yk_mt_api_post_add_meal_to_entry',
//		'permission_callback' => function () {
//			return current_user_can( 'read' );
//		}
	));
}
add_action( 'rest_api_init', 'yk_mt_api_register_end_points' );

/**
 * REST Handler for adding a meal to an entry
 *
 * @return WP_REST_Response
 */
function yk_mt_api_post_add_meal_to_entry() {

	$user_id = NULL;

	$post_data = $_POST;

	// If non admin, then force user Id to one currently logged in
	if ( false === is_admin() || false === yk_mt_api_admin_allowed() ) {
		$post_data[ 'user-id' ] = get_current_user_id();
	}

	// Validate we have all the expected fields
	foreach ( [ 'user-id', 'entry-id', 'meal-id', 'meal-type' ] as $key ) {
		if ( true === empty( $post_data[ $key ] ) ) {
			return new WP_REST_Response( [ 'error' => 'missing-' . $key ], 400 );
		}
	}

	$entry_id = yk_mt_entry_get_id_or_create( (int) $post_data[ 'user-id' ]  );

	if ( true === yk_mt_entry_meal_add( $entry_id, (int) $post_data[ 'meal-id' ], (int) $post_data[ 'meal-type' ] ) ) {
		return new WP_REST_Response( true, 200 );
	}

	return new WP_REST_Response( [ 'error' => 'updating-db' ], 400 );
}


/**
 * Extra layer to ensure an admin call to the API is allowed.
 */
function yk_mt_api_admin_allowed() {
	return true; //TODO! If in Admin, ensure they have the correct capability to be editing user records.
}