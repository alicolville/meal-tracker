<?php

defined('ABSPATH') or die('Naw ya dinnie!');

class YK_MT_EXT_FAT_SECRET_RECIPES extends YK_MT_EXT_SOURCE {

	private $base_url = 'https://platform.fatsecret.com/rest/server.api';

	public function search( $terms ) {

		$this->search_reset();

		$args = [ 'body' => [ 'method' => 'recipes.search', 'search_expression' => $terms ] ];

		$results = $this->api_get( $args );

		// Error hit?
		if ( true === $this->has_error() ) {
			return $this->get_error();
		}

		if ( false === isset( $results[ 'recipes' ][ 'total_results' ] ) ) {
			$this->error = 'There was an issue processing the results.';
			return false;
		}

		if ( 0 == $results[ 'recipes' ][ 'total_results' ] ) {
			return false;
		}

		$this->results 		= $results[ 'recipes' ][ 'recipe' ];
		$this->no_results 	= $results[ 'recipes' ][ 'total_results' ];
		$this->page_number 	= $results[ 'recipes' ][ 'page_number' ];
		$this->page_size 	= $results[ 'recipes' ][ 'max_results' ];

		$this->results = array_map( array( $this, 'format_result' ), $this->results );

		return true;

	}

	public function servings( $id ) {
		return NULL;
	}

	function format_result( $result ) {

		return [
			'name'			=> $result[ 'recipe_name' ],
			'description'	=> $result[ 'recipe_description' ],
			'calories'		=> $result[ 'recipe_nutrition' ][ 'calories' ],
			'meta_proteins' => $result[ 'recipe_nutrition' ][ 'protein' ],
			'meta_fats'		=> $result[ 'recipe_nutrition' ][ 'fat' ],
			'meta_carbs'	=> $result[ 'recipe_nutrition' ][ 'carbohydrate' ],
			'source'		=> 'fat-secrets',
			'ext_id'		=> $result[ 'recipe_id' ],
			'ext_url'		=> $result[ 'recipe_url' ],
			'ext_image'		=> ( false === empty( $result[ 'recipe_image' ] ) ) ? $result[ 'recipe_image' ] : '',
			'quantity'		=> '0',
			'unit'			=> 'na',
			'source'		=> 'fat-secret'
		];
	}

	/**
	 * This isn't supported as there is no need to fetch a recipe at the moment.
	 * @param $id
	 * @return bool|mixed
	 */
	public function get( $id ){

		$args = [ 'body' => [ 'method' => 'recipe.get', 'recipe_id' => $id ] ];

		$result = $this->api_get( $args );

		// Error hit?
		if ( true === $this->has_error() ) {
			return false;
		}

		if ( true === empty( $result[ 'recipe' ] ) )  {
			return false;
		}

		// Shoe horn the array that comes back to the format expected by format_result
		$result = $result[ 'recipe' ];

		if ( false === empty( $result[ 'serving_sizes' ][ 'serving' ] ) ) {
			$result[ 'recipe_nutrition' ] = $result[ 'serving_sizes' ][ 'serving' ];
		}

		if ( false === empty( $result[ 'recipe_images' ][ 'recipe_image' ] ) ) {
			$result[ 'recipe_image' ] = $result[ 'recipe_images' ][ 'recipe_image' ];
		}

		return $this->format_result( $result );
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

		$cache_key 	= 'fatsecret-api-get-' . md5( json_encode( $args ) );

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
