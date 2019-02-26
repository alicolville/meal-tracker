<?php

	defined('ABSPATH') or die("Jog on!");

	define( 'YK_TEST_USER_ID', 1 );

	function yk_test_init() {

		if ( true === empty( $_GET['test'] ) ) {
			return;
		}

		yk_test_truncate_all();

		yk_test_add_meals();

		die;

	}
	add_action( 'init', 'yk_test_init' );


	function yk_test_add_meals() {

		$meal = [
					'added_by' => YK_TEST_USER_ID,
					'name' => 'Fish and Chips',
					'calories' => 1200,
					'quantity' => 300,
					'description' => 'From the chippy with Salt and Vineger'
		];

		if ( false === yk_mt_meal_add( $meal ) ) {
			yk_test_error( 'Failed to add meal', $meal );
		}

		$meal = [
			'added_by' => YK_TEST_USER_ID,
			'name' => 'Ham and Cheese Sandwich',
			'calories' => 600,
			'quantity' => 100,
			'description' => 'Brown Bread and Ham'
		];

		if ( false === yk_mt_meal_add( $meal ) ) {
			yk_test_error( 'Failed to add meal', $meal );
		}

		$meal = [
			'added_by' => YK_TEST_USER_ID,
			'name' => 'Snickers',
			'calories' => 210,
			'quantity' => 30,
			'description' => ''
		];

		if ( false === yk_mt_meal_add( $meal ) ) {
			yk_test_error( 'Failed to add meal', $meal );
		}

	}

	function yk_test_truncate_all() {

		global $wpdb;

		$wpdb->query( 'TRUNCATE ' . $wpdb->prefix . YK_WT_DB_MEALS );
		$wpdb->query( 'TRUNCATE ' . $wpdb->prefix . YK_WT_DB_MEALS );
		$wpdb->query( 'TRUNCATE ' . $wpdb->prefix . YK_WT_DB_MEALS );
	}

	function yk_test_error( $message, $data = NULL ) {

		echo esc_html( $message ) . PHP_EOL;

		if ( false == empty( $data ) ) {
			print_r( $data );
		}

	}