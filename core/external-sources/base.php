<?php

defined('ABSPATH') or die('Naw ya dinnie!');

/**
 * Abstract class (may change to an interface) that all external sources implement.
 *
 * Class YK_MT_EXT_SOURCE
 */
abstract class YK_MT_EXT_SOURCE {

	protected $args 		= [];
	protected $results		= [];
	protected $no_results 	= 0;
	protected $page_number 	= 0;
	protected $page_size 	= 0;
	protected $cache_hit	= false;
	protected $auth			= false;
	protected $error		= false;
	protected $api_response	= false;

	/**
	 * Initialise class
	 * @param $args
	 * @return mixed
	 */
	public function __construct( $args ){

		$this->args = $args;

		$this->authenticate();

		$this->search_reset();
	}

	/**
	 * Authenticate against API
	 * @return bool
	 */
	abstract protected function authenticate();

	/**
	 * Authenticated against API?
	 *  @return bool
	 */
	abstract protected function is_authenticated();

	/**
	 * Search API endpoint for given string
	 * @param $terms
	 * @return mixed
	 */
	abstract protected function search( $terms );

	/**
	 * Fetch servings for ID
	 * @param $id
	 *
	 * @return mixed
	 */
	abstract protected function servings( $id );

	/**
	 * Reset search
	 */
	protected function search_reset() {
		$this->results 		= 0;
		$this->no_results 	= 0;
		$this->page_number 	= 0;
		$this->page_size 	= 0;
		$this->cache_hit	= false;
	}

	/**
	 * Each child class shall have a formatter to return an API result into a generic format
	 * @param $result
	 * @return mixed
	 */
	abstract protected function format_result( $result );

	/**
	 * Once search() has been performed, call this for results.
	 * @return mixed
	 */
	public function results() {
		return $this->results;
	}

	/**
	 * Any results?
	 * @return bool
	 */
	public function has_results() {
		return ( false === empty( $this->no_results ) );
	}

	/**
	 * Fetch an individual meal from API endpoint
	 * @param $id
	 * @return mixed
	 */
	abstract protected function get( $id );

	/**
	 * Have we hit a fatal error?
	 * @return bool
	 */
	public function has_error() {
		return ( false !== $this->error );
	}

	/**
	 * Get error
	 * @return mixed
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Get API response
	 * @return bool
	 */
	public function get_api_response() {
		return $this->api_response;
	}

	/**
	 * Caching function for setting. Wrapping around Meal Tracker caching function in case we wish to use something else in the future
	 * @param $key
	 * @param $value
	 * @param int $duration
	 */
	protected function cache_set( $key, $value, $duration = 3600 ) {
		yk_mt_cache_set( $key, $value, $duration );
	}

	/**
	 * Fetch a cached value
	 * @param $key
	 * @param null $default
	 * @return void|null
	 */
	protected function cache_get( $key, $default = NULL ) {

		$value = yk_mt_cache_get( $key );

		return ( false === empty( $value ) ) ? $value : $default;
	}

	/**
	 * Was data fetched from cache?
	 * @return bool
	 */
	protected function cache_hit() {
		return $this->cache_hit;
	}

}
