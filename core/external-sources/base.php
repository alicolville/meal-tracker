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
	protected $auth			= false;
	protected $error		= false;

	/**
	 * Initialise class
	 * @param $args
	 * @return mixed
	 */
	public function __construct( $args ){

		$this->args = $args;

		$this->authenticate();
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
	 * Once search() has been performed, call this for results.
	 * @return mixed
	 */
	abstract protected function results();

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
	 * Caching function for setting. Wrapping around Meal Tracker caching function in case we wish to use something else in the future
	 * @param $key
	 * @param $value
	 * @param int $duration
	 */
	protected function cache_set( $key, $value, $duration = 3600 ) {
		yk_mt_cache_temp_set( $key, $value, $duration );
	}

	/**
	 * Fetch a cached value
	 * @param $key
	 * @param null $default
	 * @return void|null
	 */
	protected function cache_get( $key, $default = NULL ) {

		$value = yk_mt_cache_temp_get( $key );

		return ( false === empty( $value ) ) ? $value : $default;
	}

}
