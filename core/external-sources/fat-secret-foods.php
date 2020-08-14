<?php

defined('ABSPATH') or die('Naw ya dinnie!');

class YK_MT_EXT_FAT_SECRET_FOODS extends YK_MT_EXT_SOURCE {

	private $base_url = 'https://platform.fatsecret.com/rest/server.api';

	public function search( $terms ) {

		$this->search_reset();

		$args = [ 'body' => [ 'method' => 'foods.search', 'search_expression' => $terms ] ];

		$results = $this->api_get( $args );

		// Error hit?
		if ( true === $this->has_error() ) {
			return $this->get_error();
		}

		if ( false === isset( $results[ 'foods' ][ 'total_results' ] ) ) {
			$this->error = 'There was an issue processing the results.';
			return false;
		}

		if ( 0 == $results[ 'foods' ][ 'total_results' ] ) {
			return false;
		}

		$this->results 		= $results[ 'foods' ][ 'food' ];
		$this->no_results 	= $results[ 'foods' ][ 'total_results' ];
		$this->page_number 	= $results[ 'foods' ][ 'page_number' ];
		$this->page_size 	= $results[ 'foods' ][ 'max_results' ];

		$this->results = array_map( array( $this, 'format_result' ), $this->results );

		return true;

	}

	public function servings( $id ) {

		$food = $this->get( $id );

		return ( false === empty( $food[ 'servings' ] ) ) ? $food[ 'servings' ] : [];
	}

	function format_result( $result ) {

		return [
			'name'			    => $result[ 'food_name' ],
			'description'	    => '',
			'calories'		    => 0,
			'meta_proteins'     => 0,
			'meta_fats'		    => 0,
			'meta_carbs'	    => 0,
			'source'		    => 'fat-secrets-foods',
			'ext_id'		    => $result[ 'food_id' ],
			'ext_url'		    => $result[ 'food_url' ],
			'ext_image'		    => ( false === empty( $result[ 'recipe_image' ] ) ) ? $result[ 'recipe_image' ] : '',
			'quantity'		    => '0',
			'unit'			    => '',
			'source'		    => 'fat-secret',
			'hide-nutrition'    => 'yes',
			'servings'          => ( false === empty( $result[ 'servings' ] ) ) ? $result[ 'servings' ] : []
		];
	}

	/**
	 * This isn't supported as there is no need to fetch a recipe at the moment.
	 * @param $id
	 * @return bool|mixed
	 */
	public function get( $id ){

		$result = $this->api_get( [ 'body' => [ 'method' => 'food.get.v2', 'food_id' => $id ] ] );

		// Error hit?
		if ( true === $this->has_error() ) {
			return false;
		}

		if ( true === empty( $result[ 'food' ] ) )  {
			return false;
		}

		// Shoe horn the array that comes back to the format expected by format_result
		$result = $result[ 'food' ];

		$no_servings = ( true === empty( $result[ 'servings' ][ 'serving' ][ 'serving_id' ] ) ) ? count( $result[ 'servings' ][ 'serving' ] ) : 1;

		$result[ 'servings' ] = $result[ 'servings' ][ 'serving' ];

		if ( 1 === $no_servings ) {
			$result[ 'servings' ] = [ $result[ 'servings' ] ];
		}

		$result[ 'servings' ]  = array_map( array( $this, 'serving_format' ), $result[ 'servings' ] );

		return $this->format_result( $result );
	}

	private function serving_format( $serving ) {

		$serving[ 'display' ] = sprintf( '%s - %s %s', $serving[ 'serving_description' ], $serving[ 'calories' ], __( 'kcal', YK_MT_SLUG ) );

		//$quantity  = ( 'oz' === $serving[ 'metric_serving_unit' ] ) ? $serving[ 'metric_serving_amount' ] * 0.035274 : $serving[ 'metric_serving_amount' ];
		//$serving[ 'quantity' ] = round( $quantity, 2 );

		return $serving;
	}

	public function is_authenticated(){
		return ( false !== $this->auth );
	}

	/**
	 * Call out to FatSecrets API
	 * @param $args
	 * @param bool $use_cache
	 * @return bool
	 */
	private function api_get( $args, $use_cache = true ) {

		$cache_key 	= 'fs-api-get-' . md5( json_encode( $args ) );

		if ( true === $use_cache ) {
			$cache = $this->cache_get( $cache_key, false );

			if ( false !== $cache ) {

				$this->cache_hit = true;

				return $cache;
			}
		}

		if ( true === empty( $args ) || false === is_array( $args ) ) {
			$this->error = 'Missing arguments for API call';
			return false;
		}

		if ( true === $this->has_error() ) {
			return false;
		}

		$args[ 'headers' ] 			= [  'Authorization' => 'Bearer ' . $this->auth ];
		$args[ 'body' ][ 'format' ] = 'json';

		$response = wp_remote_get( $this->base_url, $args );

		$this->api_response = $response;

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {

			$error = sprintf( 'There was an issue communicating with FatSecrets. HTTP Code %d.', $response_code );

			if ( false === empty( $response[ 'body' ] ) ) {
				$error .= $response[ 'body' ];
			}

			$this->error = sprintf( 'There was an issue communicating with FatSecrets. HTTP Code %d. %s' , $response_code, $error );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		$body = json_decode( $body, true );

		if( false === empty( $body[ 'error' ][ 'message' ] ) ) {
			$this->error = $body[ 'error' ][ 'message' ];
			return false;
		}

		// Cache API call for 30 minutes
		$this->cache_set( $cache_key, $body, 300 );

		return $body;
	}

	/**
	 * Get Bearer Token.
	 *
	 * Based on Fat Secret's documentation (https://platform.fatsecret.com/api/Default.aspx?screen=rapiauth2), their Bearer token lasts for one day.
	 *
	 * To save calls, we will cache the bearer token.
	 */
	protected function authenticate() {

		if ( true === empty( $this->args[ 'client_id' ] ) ||
				true === empty( $this->args[ 'client_secret' ] ) ) {
			return false;
		}

		// Bearer token already cached? Save firing another request to Fat Secret?
		$cache_key 	= 'fatsecret-bearer-token-' . md5( json_encode( $this->args ) );
		$cache 		= $this->cache_get( $cache_key, false );

		if ( false !== $cache ) {
			$this->auth = $cache;
			return true;
		}

		// Headers
		$args = [ 'headers' => [  'Authorization' => 'Basic ' . base64_encode( $this->args[ 'client_id' ] . ':' . $this->args[ 'client_secret' ] ) ] ];

		$args[ 'body' ][ 'grant_type' ]	= 'client_credentials';
		$args[ 'body' ][ 'scope' ]		= 'basic';

		$response = wp_remote_post( 'https://oauth.fatsecret.com/connect/token', $args );

		$http_code = wp_remote_retrieve_response_code( $response );

		if ( true === is_wp_error( $response ) || 200 !== $http_code ) {

			$this->error = 'Could not authorise against FatSecrets using Client ID and secret';

			return false;

		} else {

			$body = wp_remote_retrieve_body( $response );

			$body = json_decode( $body, true );

			if ( false === empty( $body[ 'access_token' ] ) ) {

				$this->auth = $body[ 'access_token' ];

				$this->cache_set( $cache_key, $this->auth );

				return true;
			}
		}
	}
}
