<?php

defined('ABSPATH') or die('Naw ya dinnie!');

class YK_MT_EXT_MEAL_TRACKER extends YK_MT_EXT_SOURCE {

	public function search( $term ) {

		$this->search_reset();

		$args = [ 'method' => 'search', 'term' => $term ];

		$results = $this->api_get( $args );

		// Error hit?
		if ( true === $this->has_error() ) {
			return $this->get_error();
		}

		if ( true === empty( $results ) ) {
			return false;
		}

		$no_results = count( $results );

		$this->results     = $results;
		$this->no_results  = $no_results;
		$this->page_number = $no_results;
		$this->page_size   = $no_results;

		$this->results = array_map( array( $this, 'format_result' ), $this->results );

		return true;

	}

	public function servings( $id ) {
		return null;
	}

	function format_result( $result ) {

		$result[ 'ext_id' ] = $result['id'];
		$result[ 'source' ] = 'meal-tracker-api';

		return $result;
	}

	/**
	 * This isn't supported as there is no need to fetch a recipe at the moment.
	 *
	 * @param $id
	 *
	 * @return bool|mixed
	 */
	public function get( $id ) {

		$args = [ 'method' => 'get', 'id' => $id ];

		$result = $this->api_get( $args );

		// Error hit?
		if ( true === $this->has_error() ) {
			return false;
		}

		if ( true === empty( $result['name'] ) ) {
			return false;
		}

		return $this->format_result( $result );
	}

	public function is_authenticated() {
		return ( false !== $this->auth );
	}

	/**
	 * Call out to FatSecrets API
	 *
	 * @param $args
	 * @param bool $use_cache
	 *
	 * @return bool
	 */
	private function api_get( $args, $use_cache = true ) {

		$cache_key = 'mealtracker-api-get-' . md5( json_encode( $args ) );

		if ( true === $use_cache ) {
			$cache = $this->cache_get( $cache_key, false );

			if ( false !== $cache ) {

				$this->cache_hit = true;

				return $cache;
			}
		}

		if ( true === empty( $args ) ) {
			$this->error = 'Missing arguments for API call';

			return false;
		}

		if ( true === $this->has_error() ) {
			return false;
		}

		$api_call = ( false === isset( $args[ 'method' ] ) || 'search' === $args[ 'method' ] ) ? 'search' : 'get';

		$http_args['headers'] = [ 'Authorization' => 'Bearer ' . $this->auth, 'Content-type' => 'application/json' ];

		if ( 'search' === $api_call ) {
			$http_args[ 'body' ]    = json_encode( [ 'term' => $args[ 'term' ] ] );
			$request_url            = sprintf( '%smeal-tracker/v1/search', $this->args[ 'endpoint' ] );
			$response               = wp_remote_post( $request_url, $http_args );
		} else {
			$request_url        = sprintf( '%smeal-tracker/v1/meal/%d', $this->args[ 'endpoint' ], $args[ 'id' ] );
			$response           = wp_remote_get( $request_url, $http_args );
		}

		$this->api_response = $response;

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 204 === $response_code ) {
			return false;   // No matching content found at endpoint
		} else if ( 200 !== $response_code ) {

			$error = sprintf( 'There was an issue communicating with another instance of Meal Tracker. HTTP Code %d.', $response_code );

			if ( false === empty( $response['body'] ) ) {
				$error .= $response['body'];
			}

			$this->error = sprintf( 'There was an issue communicating with another instance of Meal Tracker. HTTP Code %d. %s', $response_code, $error );

			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		$body = json_decode( $body, true );

		// Cache API call for 30 minutes
		$this->cache_set( $cache_key, $body, 300 );

		return $body;
	}

	/**
	 * Set Bearer Token.
	 *
	 */
	protected function authenticate() {

		if ( true === empty( $this->args['endpoint'] ) ||
		     true === empty( $this->args['bearer-token'] ) ) {
			return false;
		}

		$this->auth = $this->args['bearer-token'];
	}
}