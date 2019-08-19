<?php

defined('ABSPATH') or die("Jog on!");

/**
 * REST Handler for adding a meal to an entry
 *
 * @return WP_REST_Response
 */
function yk_mt_ajax_add_meal_to_entry() {

    check_ajax_referer( 'yk-mt-nonce', 'security' );

	$user_id = NULL;
	$post_data = $_POST;

    $post_data[ 'user-id' ]     = get_current_user_id();
    $post_data[ 'entry-id' ]    = ( true === empty( $post_data[ 'entry-id' ] ) ) ? yk_mt_entry_get_id_or_create( (int) $post_data[ 'user-id' ]  ) : (int) $post_data[ 'entry-id' ];

	// Validate we have all the expected fields
	foreach ( [ 'user-id', 'entry-id', 'meal-id', 'meal-type', 'quantity' ] as $key ) {
		if ( true === empty( $post_data[ $key ] ) ) {
			wp_send_json( [ 'error' => 'missing-' . $key ] );
		}
	}

    for ( $i = 0; $i < (int) $post_data[ 'quantity' ]; $i++ ) {
        if ( false === yk_mt_entry_meal_add( (int) $post_data[ 'entry-id' ], (int) $post_data[ 'meal-id' ], (int) $post_data[ 'meal-type' ] ) ) {
            // wp_send_json( [ (int) $post_data[ 'entry-id' ], (int) $post_data[ 'meal-id' ], (int) $post_data[ 'meal-type' ] ]); //todo

            return wp_send_json( [ 'error' => 'updating-db' ] );
        }
    }

    wp_send_json( true );
}
add_action( 'wp_ajax_add_meal_to_entry', 'yk_mt_ajax_add_meal_to_entry' );

/**
 * Extra layer to ensure an admin call to the API is allowed.
 */
function yk_mt_api_admin_allowed() {
	return true; //TODO! Still needed? If in Admin, ensure they have the correct capability to be editing user records.
}