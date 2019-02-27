<?php

	defined('ABSPATH') or die("Jog on!");

	define( 'YK_TEST_USER_ID', 1 );

	function yk_test_init() {

		if ( true === empty( $_GET['test'] ) ) {
			return;
		}

		yk_test_clear_database();

		yk_test_meals();

		yk_test_entries();

		yk_test_entries_meals();

		//var_dump ( yk_mt_entry_get(4) );

		echo '<p>Finished!</p>';

	//	die;

	}
	add_action( 'init', 'yk_test_init' );

	function yk_test_entries_meals() {

		$entry_meal = [
			'meal_time' => 1,
			'meal_id' => 2,
			'entry_id' => 3
		];

		if ( false === yk_mt_entry_meal_add( $entry_meal ) ) {
			yk_test_error( 'Failed to add entry / meal', $entry_meal );
		}

		$entry_meal = [
			'meal_time' => 3,
			'meal_id' => 4,
			'entry_id' => 1
		];

		if ( false === yk_mt_entry_meal_add( $entry_meal ) ) {
			yk_test_error( 'Failed to add entry / meal', $entry_meal );
		}

		$entry_meal = [
			'meal_time' => 5,
			'meal_id' => 3,
			'entry_id' => 4
		];

		if ( false === yk_mt_entry_meal_add( $entry_meal ) ) {
			yk_test_error( 'Failed to add entry / meal', $entry_meal );
		}

		$entry_meal = [
			'id' => 1,
			'meal_time' => 9,
			'meal_id' => 3,
			'entry_id' => 4
		];

		if ( false === yk_mt_entry_meal_update( $entry_meal ) ) {
			yk_test_error( 'Failed to update entry / meal', $entry_meal );
		}

	}

	function yk_test_entries() {

		$entry = [
					'user_id' => 1,
					'calories_allowed' => 1600,
					'calories_used' => 800,
					'date' => '2019-02-27'
		];

		if ( false === yk_mt_entry_add( $entry ) ) {
			yk_test_error( 'Failed to add entry', $entry );
		}

		$entry = [
			'user_id' => 1,
			'calories_allowed' => 1700,
			'calories_used' => 900,
			'date' => '2019-02-28'
		];

		if ( false === yk_mt_entry_add( $entry ) ) {
			yk_test_error( 'Failed to add entry', $entry );
		}

		$entry = [
			'user_id' => 1,
			'calories_allowed' => 1600,
			'calories_used' => 800,
			'date' => '2019-02-29'
		];

		if ( false === yk_mt_entry_add( $entry ) ) {
			yk_test_error( 'Failed to add entry', $entry );
		}

		$entry = [
			'user_id' => 1,
			'calories_allowed' => 9999,
			'calories_used' => 9999,
			'date' => '2019-02-29'
		];

		if ( false === yk_mt_entry_add( $entry ) ) {
			yk_test_error( 'Failed to add entry', $entry );
		}

		$entry = [
			'id' => 4,
			'user_id' => 5,
			'calories_allowed' => 1200,
			'calories_used' => 300,
			'date' => '2019-05-01'
		];

		if ( false === yk_mt_entry_update( $entry ) ) {
			yk_test_error( 'Failed to update entry', $entry );
		}

		if ( 4 !== yk_mt_mysql_count_table( YK_WT_DB_ENTRY ) ) {
			yk_test_error( 'Expecting 4 entries to have been added but there was an issue!' );
		}

		if ( false === yk_mt_entry_delete( 3 ) ) {
			yk_test_error( 'Failed to delete entry', $entry );
		}

		if ( 3 !== yk_mt_mysql_count_table( YK_WT_DB_ENTRY ) ) {
			yk_test_error( 'Expecting 3 entries to have been added but there was an issue!' );
		}

	}


	function yk_test_meals() {

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

		$meal = [
			'added_by' => YK_TEST_USER_ID,
			'name' => 'Meal to UPDATE',
			'calories' => 210,
			'quantity' => 9999,
			'description' => ''
		];

		if ( false === yk_mt_meal_add( $meal ) ) {
			yk_test_error( 'Failed to add meal', $meal );
		}

		$meal = [
			'id' => 4,
			'added_by' => YK_TEST_USER_ID,
			'name' => 'Spag Bol',
			'calories' => 210,
			'quantity' => 400,
			'description' => 'Some pasta and red sauce!',
			'deleted' => 1
		];

		if ( false === yk_mt_meal_update( $meal ) ) {
			yk_test_error( 'Failed to update meal', $meal );
		}

		if ( 4 !== yk_mt_mysql_count_table() ) {
			yk_test_error( 'Expecting 4 meals to have been added but there was an issue!' );
		}

	}

	function yk_test_clear_database() {

		global $wpdb;

		$wpdb->query( 'TRUNCATE ' . $wpdb->prefix . YK_WT_DB_MEALS );
		$wpdb->query( 'TRUNCATE ' . $wpdb->prefix . YK_WT_DB_ENTRY );
		$wpdb->query( 'TRUNCATE ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL );

		yk_mt_cache_delete_all();

	}

	function yk_test_error( $message, $data = NULL ) {

		echo esc_html( $message ) . PHP_EOL;

		if ( false == empty( $data ) ) {
			print_r( $data );
		}

	}