<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 * Enqueue front end scripts
	 */
	function yk_mt_enqueue_front_end_dependencies() {

		$minified = yk_mt_use_minified();

        wp_enqueue_script( 'meal-tracker', plugins_url( 'assets/js/core' . $minified . '.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION, true );
		wp_enqueue_style( 'meal-tracker', plugins_url( 'assets/css/frontend' . $minified . '.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );

        wp_localize_script( 'meal-tracker', 'yk_mt', yk_mt_ajax_config() );

    }
	add_action( 'wp_enqueue_scripts', 'yk_mt_enqueue_front_end_dependencies' );


	//TODO: Delete this function?
	/**
	 * AJAX handler for looking up meals
	 */
	function yk_mt_ajax_meal_lookup() {

		check_ajax_referer( 'yk-mt-nonce', 'security' );

		$data = [
			0 => [
				'id' => 1,
				'name' => 'Bandung',
			],
			1 => [
				'id' => 2,
				'name' => 'Cimahi',
			]
		];


		wp_send_json( $data );
	}
	add_action( 'wp_ajax_meal_lookup', 'yk_mt_ajax_meal_lookup' );


//	function yk_mt_ajax_meal_add() {
//
//		check_ajax_referer( 'yk-mt-nonce', 'security' );
//
//		// All post data there?
//		if ( false === yk_mt_post_values_exist( [ 'meal-id', 'quantity' ] ) ) {
//			wp_send_json( -100 );
//		}
//
//		//TODO: Save to DB
//
//		wp_send_json( 0 );
//	}
//	add_action( 'wp_ajax_meal_lookup', 'yk_mt_ajax_meal_lookup' );

