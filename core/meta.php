<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Return all meta fields where key meets certain value
 *
 * @param $key
 * @param $value
 * @return array
 */
function yk_mt_meta_fields_where( $key, $value ) {

	// TODO: This should be cached when the number of fields gets larger.

	$fields = yk_mt_meta_fields();
	$return = [];

	foreach ( $fields as $field ) {

		if (  true === array_key_exists( $key, $field ) &&
				$field[ $key ] === $value ) {

			$return[] = $field;
		}
	}

	return $return;
}

/**
 * Load additional fields tat define a meal.
 * @return array
 */
function yk_mt_meta_fields() {

	$fields 	= [];
	$is_premium = yk_mt_license_is_premium();

	// Macro Nutrient columns: Protein, fat and carbs
	if ( true === $is_premium &&
			true === yk_mt_site_options_as_bool( 'macronutrients-enabled', false ) ) {

		// Protein
		$fields[] = [
			'db_col' 			=> 'meta_proteins',
			'title' 			=> __( 'Proteins', YK_MT_SLUG ),
			'visible_user' 		=> true,
			'visible_admin' 	=> true,
			'type'				=> 'int'
		];

		// Fats
		$fields[] = [
			'db_col' 			=> 'meta_fats',
			'title' 			=> __( 'Fats', YK_MT_SLUG ),
			'visible_user' 		=> true,
			'visible_admin' 	=> true,
			'type'				=> 'int'
		];

		// Carbs
		$fields[] = [
			'db_col' 			=> 'meta_carbs',
			'title' 			=> __( 'Carbs', YK_MT_SLUG ),
			'visible_user' 		=> true,
			'visible_admin' 	=> true,
			'type'				=> 'int'
		];

	}

	if ( true === $is_premium ) {
		$fields = apply_filters( 'yk_mt_meta_fields', $fields );
	}

	return $fields;
}

/**
 * Fetch all keys for meta fields that are visible to the user.
 * @return bool
 */
function yk_mt_meta_fields_visible_user_keys() {

	$meta_fields = yk_mt_meta_fields_where( 'visible_user', true );

	return ( false === empty( $meta_fields ) ) ?
			wp_list_pluck( $meta_fields, 'db_col') : false;
}

/**
 * Return meta fields for JS config
 * @return array
 */
function yk_mt_meta_js_config() {

	$config 		= [];
	$meta_fields 	= yk_mt_meta_fields_where( 'visible_user', true );

	foreach ( $meta_fields as $field ) {

		switch ( $field[ 'type' ] ) {
			case 'int':
				$default = 0;
				break;
			default:
				$default = '';
		}

		$config[ $field[ 'db_col'] ] = $default;
	}

	return $config;
}

/**
 * Return an array of all existing meta columns in the database
 * @return mixed
 */
function yk_mt_meta_db_columns_existing() {

	global $wpdb;

	$meta_fields = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . $wpdb->prefix . YK_WT_DB_MEALS .
											"' and COLUMN_NAME like 'meta_%'", ARRAY_A );

	return wp_list_pluck( $meta_fields, 'COLUMN_NAME' );
}

/**
 * ADd meta columns to Entry and Meta tables where they don't exist
 */
function yk_mt_meta_db_columns_create() {

	// Fetch all meta fields that we wish to track data for.
	$required_fields 	= yk_mt_meta_fields();
	$existing_fields 	= yk_mt_meta_db_columns_existing();
	$sql_to_execute		= [];

	foreach ( $required_fields as $field ) {

		if ( false === in_array( $field[ 'db_col' ], $existing_fields ) ) {

			switch ( $field[ 'type' ] ) {
				case 'int':
					$sql_to_execute[] = ' ADD ' . $field[ 'db_col' ]. ' INT(1) NULL DEFAULT 0';
					break;
				default:
					$default = '';
			}
		}

	}

	if ( true === empty( $sql_to_execute ) ) {
		return;
	}

	global $wpdb;

	// Add the column to the Meals and Entry tables.
	foreach ( $sql_to_execute as $sql ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . YK_WT_DB_MEALS . $sql );
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . YK_WT_DB_ENTRY . $sql );
	}
}
add_action( 'yk_mt_settings_saved', 'yk_mt_meta_db_columns_create' );	// Admin settings page saved
add_action( 'yk_mt_db_upgrade', 'yk_mt_meta_db_columns_create' );		// Fresh install / Version change
