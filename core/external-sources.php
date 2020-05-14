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

	if ( false !== yk_mt_ext_source_credentials() ) {
		return false;
	}

	return true;
}

/**
 * Based on the settings, determine what externals source is enabled.
 * @return bool|string
 */
function yk_mt_ext_source_credentials() {

	// FatSecret
	$client_id 		= yk_mt_site_options( 'external-fatsecret-id', '' );
	$client_secret 	= yk_mt_site_options( 'external-fatsecret-secret', '' );

	if ( false === empty( $client_id ) && false === empty( $client_secret ) ){
		return [ 'source' => 'fat-secret', 'credentials' => [ 'client_id' => $client_id, 'client_secret' => $client_secret ] ];
	}

	return false;
}

/**
 * Depending on external source settings, create an instance of external search class
 * @return YK_MT_EXT_FAT_SECRET
 */
function yk_mt_ext_source_create_instance() {

	$external_credentials = yk_mt_ext_source_credentials();

	// Do we have API credentials for an external source?
	if ( false === $external_credentials ) {
		return false;
	}

	$external_source = new YK_MT_EXT_FAT_SECRET( $external_credentials[ 'credentials' ] );

	// TODO: Add support for more External APIs

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
		return sprintf( "%s: %s\n\n", __( 'Error', YK_MT_SLUG ), $external_source->get_error() );
	}

	// Perform a test search for something obvious. We should get results!
	$external_source->search('apples' );

	$details = '';

	if ( $external_source->has_error() ) {
		$details .= sprintf( '%s: %s', __( 'Error', YK_MT_SLUG ), $external_source->get_error() );
	}

	if ( false === $external_source->has_results() ) {
		$details .= __( 'Error: No search results could be found for the term "apples"' );
	} else {
		$details .= __( 'Success: Results have been found for "apples"' );

		$details .= print_r( $external_source->results(), true );
	}

	$details .= print_r( $external_source->get_api_response(), true );

	return $details;
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
