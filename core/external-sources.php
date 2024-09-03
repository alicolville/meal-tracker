<?php

defined('ABSPATH') or die("Jog on!");

include_once YK_MT_ABSPATH . 'core/external-sources/base.php';
include_once YK_MT_ABSPATH . 'core/external-sources/fat-secret-recipes.php';
include_once YK_MT_ABSPATH . 'core/external-sources/fat-secret-foods.php';
include_once YK_MT_ABSPATH . 'core/external-sources/wp-recipe-maker.php';
include_once YK_MT_ABSPATH . 'core/external-sources/meal-tracker.php';

/**
 * Do we have any external sources enabled?
 * @return bool
 */
function yk_mt_ext_enabled() {

	if ( false === yk_mt_license_is_premium() ) {
		return false;
	}

	if ( false === yk_mt_site_options_as_bool('external-enabled', false ) ) {
		return false;
	}

	if ( false === yk_mt_ext_source_credentials() ) {
		return false;
	}

	return true;
}

/**
 * Based on the settings, determine what externals source is enabled.
 * @return bool|string
 */
function yk_mt_ext_source_credentials() {

	// Another Meal Tracker instance
	$endpoint 		= yk_mt_site_options( 'external-meal-tracker-endpoint', '' );
	$bearer_token 	= yk_mt_site_options( 'external-meal-tracker-bearer-token', '' );

	if ( false === empty( $endpoint ) && false === empty( $bearer_token ) ) {
		return [ 'source' => 'meal-tracker', 'credentials' => [ 'endpoint' => $endpoint, 'bearer-token' => $bearer_token ] ];
	}

	// WP Recipe maker enabled?
	if ( true === yk_mt_site_options_as_bool('external-wprm-enabled', false ) &&
			true === yk_mt_ext_source_wprm_enabled() ) {
		return [ 'source' => 'wp-recipe-maker' ];
	}

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
 * @return YK_MT_EXT_FAT_SECRET_RECIPES
 */
function yk_mt_ext_source_create_instance() {

	$external_credentials = yk_mt_ext_source_credentials();

	// Do we have API credentials for an external source?
	if ( false === $external_credentials ) {
		return false;
	}

	if ( 'wp-recipe-maker' === $external_credentials[ 'source' ] ) {
		return new YK_MT_EXT_WP_RECIPE_MAKER( [] );
	} elseif ( 'meal-tracker' === $external_credentials[ 'source' ] ) {
		return new YK_MT_EXT_MEAL_TRACKER( $external_credentials[ 'credentials' ] );
	} elseif ( 'fat-secret' === $external_credentials[ 'source' ] ) {

		if ( 'recipes' === yk_mt_site_options('external-fatsecret-food-api', 'recipes' ) ) {
			return new YK_MT_EXT_FAT_SECRET_RECIPES( $external_credentials[ 'credentials' ] );
		} else {
			return new YK_MT_EXT_FAT_SECRET_FOODS( $external_credentials[ 'credentials' ] );
		}
	}

	return false;
}

/**
 * Do we show Servings drop down for this source?
 * @return bool
 */
function yk_mt_ext_source_show_servings() {

	$external_credentials = yk_mt_ext_source_credentials();

	// Do we have API credentials for an external source?
	if ( false === $external_credentials ) {
		return false;
	}

	return ( 'fat-secret' === $external_credentials[ 'source' ] &&
	        'recipes' !== yk_mt_site_options('external-fatsecret-food-api', 'recipes' ) );
}

/**
 * Test the specified end point is working and display an error if not.
 *
 * @param string $terms
 *
 * @return bool|string
 */
function yk_mt_ext_source_test( $terms = 'apples' ) {

	$external_source = yk_mt_ext_source_create_instance();

	if ( true === empty( $external_source ) ) {
		return sprintf( '%s', esc_html__( 'There are no valid external sources.', 'meal-tracker' ) );
	}

	// An errors?
	if ( $external_source->has_error() ) {
		return sprintf( "%s: %s\n\n", esc_html__( 'Error', 'meal-tracker' ), $external_source->get_error() );
	}

	// Perform a test search for something obvious. We should get results!
	$external_source->search( $terms );

	$details = '';

	if ( $external_source->has_error() ) {
		$details .= sprintf( '%s: %s', esc_html__( 'Error', 'meal-tracker' ), $external_source->get_error() );
	}

	if ( false === $external_source->has_results() ) {
		$details .= esc_html__( 'Error: No search results could be found for the term "apples"', 'meal-tracker' ). PHP_EOL;
	} else {
		$details .= esc_html__( 'Success: Results have been found for "apples"', 'meal-tracker' ) . PHP_EOL;

		$details .= print_r( $external_source->results(), true );
	}

	$details .= print_r( $external_source->get_api_response(), true );

	return $details;
}

/**
 * Fetch meal from external API
 *
 * @param $id
 * @param bool $use_cache
 *
 * @return array|bool|mixed
 */
function yk_mt_ext_source_get( $id, $use_cache = true ) {

	// Has the meal been cached? If so, don't bother calling out to the external API
	if ( true === $use_cache &&
	        $cache = yk_mt_cache_get( 'ext-meal-' . $id ) ) {
		return $cache;
	}

	$external_source = yk_mt_ext_source_create_instance();

	// An errors?
	if ( $external_source->has_error() ) {
		return false;
	}

	$meal = $external_source->get( $id );

	yk_mt_cache_set( 'ext-meal-' . $id, $meal );

	return $meal;
}

/**
 * Perform a search and return results
 * @param $search_term
 * @return array|true
 */
function yk_mt_ext_source_search( $search_term ) {

	if ( ! YK_MT_HAS_EXTERNAL_SOURCES ) {
		return [];
	}

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
 * Fetch servings for given food ID
 * @param $food_id
 *
 * @return array
 */
function yk_mt_ext_source_servings( $food_id ) {

	if ( ! YK_MT_HAS_EXTERNAL_SOURCES ) {
		return [];
	}

	$external_source = yk_mt_ext_source_create_instance();

	// An errors?
	if ( $external_source->has_error() ) {
		return $external_source;
	}

	$servings = $external_source->servings( $food_id );

	// Do we have an error?
	if ( $external_source->has_error() ) {

		// Log to PHP error log
		yk_mt_log_error( $external_source->get_error() );

		return 'ERR';
	}

	return ( false === empty( $servings ) ) ? $servings : [];
}

/**
 * Add settings to JS config
 * @param $locale
 * @return mixed
 */
function yk_mt_ext_filters_js_config( $config ) {

	if ( ! YK_MT_HAS_EXTERNAL_SOURCES ) {
		return $config;
	}

	$config[ 'external-source' ]        = true;
	$config[ 'external-show-servings' ] = yk_mt_ext_source_show_servings();

	return $config;
}
add_filter( 'yk_mt_config', 'yk_mt_ext_filters_js_config' );

/**
 * Add locale strings to JS config
 * @param $locale
 * @return mixed
 */
function yk_mt_ext_filters_js_config_locale( $locale ) {

	if ( ! YK_MT_HAS_EXTERNAL_SOURCES ) {
		return $locale;
	}

	$locale[ 'search-no-results' ] 	= esc_html__( 'No meals could be found', 'meal-tracker' );
	$locale[ 'search-error' ] 		= esc_html__( 'There was an error searching our database. Please try again.', 'meal-tracker' );
	$locale[ 'search-added' ] 		= esc_html__( 'Your meal collection has been updated', 'meal-tracker' );
	$locale[ 'serving-missing' ]    = esc_html__( 'Please select a serving size', 'meal-tracker' );

	return $locale;
}
add_filter( 'yk_mt_config_locale', 'yk_mt_ext_filters_js_config_locale' );

/**
 * Add an external meal to the user's meal collection
 *
 * @param $ext_id
 * @param null $serving_id
 * @param null $user_id
 *
 * @return bool
 */
function yk_mt_ext_add_meal_to_user_collection( $ext_id, $serving_id = NULL, $user_id = NULL ) {

	$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

	/*
	 * Does the user already have this external meal in their own collection? If so, let's not bother
	 * fetching it again from the external source. Instead, use the local ID to add to the entry!
	 */
	$existing_id = yk_mt_db_ext_meal_exist( $ext_id, $serving_id, $user_id );

	if ( false === empty( $existing_id ) ) {
		return $existing_id;
	}

	/**
	 * Call out to external source, if found, copy to local meal collection and return meal ID
	 */
	$ext_meal = yk_mt_ext_source_get( $ext_id, false ); // ignore cache this time as we want to fetch servings (which may not have been cached before)

	// Do we have a serving specified? If so, we want to use those macro values when adding.
	if ( false === empty( $serving_id ) && false === empty( $ext_meal[ 'servings' ] ) ) {

		$found = NULL;

		foreach ( $ext_meal[ 'servings' ] as $serving ) {

			if ( $serving[ 'serving_id' ] === $serving_id ) {
				$found = $serving;
				break;
			}
		}

		if( false === empty( $found ) ) {

			$ext_meal[ 'name' ]             .= ' - ' . $found[ 'serving_description' ];
			$ext_meal[ 'meta_proteins' ]    = $found[ 'protein' ];
			$ext_meal[ 'meta_fats' ]        = $found[ 'fat' ];
			$ext_meal[ 'meta_carbs' ]       = $found[ 'carbohydrate' ];
			$ext_meal[ 'calories' ]         = $found[ 'calories' ];
			$ext_meal[ 'unit' ]             = $found[ 'metric_serving_unit' ];
			$ext_meal[ 'quantity' ]         = $found[ 'metric_serving_amount' ];

		} else {
			$serving_id = NULL;
		}
	}

	// No meal found?
	if ( true === empty( $ext_meal ) ) {
		return false;
	}

	$ext_meal[ 'ext_serving_id' ]   = $serving_id;
	$ext_meal[ 'added_by' ]         = $user_id;
	$ext_meal[ 'added_by_admin' ]   = 0;

	return yk_mt_db_meal_add( $ext_meal );
}

/**
 * Take an external source and put into English
 * @param $slug
 *
 * @return string|void
 */
function yk_mt_ext_source_as_string( $slug ) {

	if ( true === empty( $slug ) ||
			'user' === $slug ) {
		return esc_html__( 'Manual', 'meal-tracker' );
	}

	switch ( $slug ) {

		case 'csv':
			return esc_html__( 'CSV Import', 'meal-tracker' );
			break;
		case 'fat-secrets-foods':
			return esc_html__( 'FatSecrets Food', 'meal-tracker' );
			break;
		case 'fat-secret':
			return esc_html__( 'FatSecrets Recipe', 'meal-tracker' );
			break;
		case 'meal-tracker':
			return esc_html__( 'Meal Tracker API', 'meal-tracker' );
			break;
		case 'wp-recipe-maker':
			return esc_html__( 'WP Recipe Maker', 'meal-tracker' );
			break;
	}

	return $slug;
}

/**
 * Is WP Recipe Maker enabled?
 * @return bool
 */
function yk_mt_ext_source_wprm_enabled() {
	return post_type_exists( 'wprm_recipe' );
}
