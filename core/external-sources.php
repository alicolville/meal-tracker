<?php

defined('ABSPATH') or die("Jog on!");

include_once YK_MT_ABSPATH . 'core/external-sources/base.php';
include_once YK_MT_ABSPATH . 'core/external-sources/fat-secret.php';

/**
 * Do we have any external sources enabled?
 * @return bool
 */
function yk_mt_ext_enabled() {

	if ( false === yk_mt_license_is_premium() ) {
		return false;
	}

	return true;	// todo
}

function yk_mt_ext_source_create_instance() {

	global $external_source;

	/**
	 * Notes:
	 *
	 * Fat Secret.
	 *
	 * 		Requires Client ID and Secret for Bearer token
	 * 		!! IP address of server must be whitelisted here: https://platform.fatsecret.com/api/Default.aspx?screen=mykd&id=16104
	 */

	$fat_secret_credentials = [	'client_id' => '7e823ae975674e68975177a282aa9516', 'client_secret' => '53962ac81c2f46e1ac77c171d0170c8c' ];

	$external_source = new YK_MT_EXT_FAT_SECRET( $fat_secret_credentials );

	return $external_source;
}


/**
 * Test the specified end point is working and display an error if not.
 * @return bool|string
 */
function yk_mt_ext_source_test() {

	$external_source = yk_mt_ext_source_create_instance();

	// An errors?
	if ( $external_source->has_error() ) {
		return sprintf( '%s: %s', __( 'Error', YK_MT_SLUG ), $external_source->get_error() );
	}

	// Perform a test search for something obvious. We should get results!
	$external_source->search('apple' );

	if ( $external_source->has_error() ) {
		return sprintf( '%s: %s', __( 'Error', YK_MT_SLUG ), $external_source->get_error() );
	}

	if ( false === $external_source->has_results() ) {
		return __( 'Error: No search results could be found for the term "apple"' );
	}

	return true;
}

function yk_mt_ext_source_get( $id ) {

	//TODO:

	// Check the following cache first
	//	yk_mt_cache_temp_get( 'ext-meal-' . $meal[ 'ext_id' ] );

	$external_source = yk_mt_ext_source_create_instance();

	// An errors?
	if ( $external_source->has_error() ) {
		return false;
	}

	return $external_source->get( $id );
}

/**
 * Perform a search and return results
 * @param $search_term
 * @return array|true
 */
function yk_mt_ext_source_search( $search_term ) {

	$external_source = yk_mt_ext_source_create_instance();

	// An errors?
	if ( $external_source->has_error() ) {
		return $external_source;
	}

	$external_source->search( $search_term );

	// Do we have an error?
	if ( $external_source->has_error() ) {

		// Log to PHP error log
		yk_mt_log_error( $external_source->get_error() );

		return 'ERR';
	}

	if ( ! $external_source->has_results() ) {
		return [];
	}

	return $external_source->results();
}

/**
 * Return default config used for external sources
 * @param $config
 * @return mixed
 */
//function yk_mt_ext_config( $config = [] ) {
//
//	if ( ! YK_MT_HAS_EXTERNAL_SOURCES ) {
//		return $config;
//	}
//
//	$config[ 'external' ] = [ 'max-results' => 20 ];
//
//	return $config;
//}

/**
 * Add locale strings to JS config
 * @param $locale
 * @return mixed
 */
function yk_mt_ext_filters_locale( $locale ) {

	if ( ! YK_MT_HAS_EXTERNAL_SOURCES ) {
		return $locale;
	}

	$locale[ 'search-no-results' ] 	= __( 'No meals could be found', YK_MT_SLUG );
	$locale[ 'search-error' ] 		= __( 'Then was an error searching our database. Please try again.', YK_MT_SLUG );

	return $locale;
}
add_filter( 'yk_mt_config_locale', 'yk_mt_ext_filters_locale' );

// TODO
//function test() {
//
//	$r = yk_mt_ext_source_search( 'cup cakes' ) ;
//
//	print_r( $r );
//die;
//}
//add_action( 'init', 'test' );


//
//
//function test() {
//
//	$r = yk_mt_ext_source_get( 447533 ) ;
//
//	print_r( $r );
//die;
//}
//add_action( 'init', 'test' );
