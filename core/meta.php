<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Are meta fields enabled?
 * @return bool
 */
function yk_mt_meta_is_enabled() {

	if ( false ===  yk_mt_license_is_premium() ) {
		return false;
	}

	// TODO: This is a temporary boolean. Currently meta fields only support MacroN. The meta framework is built, just not utilised yet.
	return ( true === yk_mt_site_options_as_bool( 'macronutrients-enabled', false ) );
}

/**
 * Return all meta fields where key meets certain value
 *
 * @param $key
 * @param $value
 * @param null $column
 * @return array
 */
function yk_mt_meta_fields_where( $key, $value, $column = NULL ) {

	// TODO: This should be cached when the number of fields gets larger.

	$fields = yk_mt_meta_fields();
	$return = [];

	foreach ( $fields as $field ) {

		if (  true === array_key_exists( $key, $field ) &&
				$field[ $key ] === $value ) {

			$return[] = $field;
		}
	}

	if ( false === empty( $return ) &&
			NULL !== $column ) {
		$return = wp_list_pluck( $return, $column );
	}

	return $return;
}

/**
 * Load additional fields tat define a meal.
 * @return array
 */
function yk_mt_meta_fields() {

	$fields 				= [];
	$is_premium 			= yk_mt_license_is_premium();

	// Macro Nutrient columns: Protein, fat and carbs
	$meta_enabled 			= ( true === $is_premium && true === yk_mt_meta_is_enabled() );
	$meta_fields_required 	= yk_mt_site_options_as_bool('macronutrients-required', false );

	// Protein
	$fields[] = [
		'db_col' 			=> 'meta_proteins',
		'title' 			=> __( 'Proteins', YK_MT_SLUG ),
		'prefix'			=> __( 'p', YK_MT_SLUG ),
		'unit'				=> __( 'g', YK_MT_SLUG ),
		'visible_user' 		=> $meta_enabled,
		'visible_admin' 	=> $meta_enabled,
		'type'				=> 'float',
		'required'			=> $meta_fields_required,
		'fractionable'      => true,
		'total-these'		=> $meta_enabled
	];

	// Fats
	$fields[] = [
		'db_col' 			=> 'meta_fats',
		'title' 			=> __( 'Fats', YK_MT_SLUG ),
		'prefix'			=> __( 'f', YK_MT_SLUG ),
		'unit'				=> __( 'g', YK_MT_SLUG ),
		'visible_user' 		=> $meta_enabled,
		'visible_admin' 	=> $meta_enabled,
		'type'				=> 'float',
		'required'			=> $meta_fields_required,
		'fractionable'      => true,
		'total-these'		=> $meta_enabled
	];

	// Carbs
	$fields[] = [
		'db_col' 			=> 'meta_carbs',
		'title' 			=> __( 'Carbs', YK_MT_SLUG ),
		'prefix'			=> __( 'c', YK_MT_SLUG ),
		'unit'				=> __( 'g', YK_MT_SLUG ),
		'visible_user' 		=> $meta_enabled,
		'visible_admin' 	=> $meta_enabled,
		'type'				=> 'float',
		'required'			=> $meta_fields_required,
		'fractionable'      => true,
		'total-these'		=> $meta_enabled
	];

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

	if ( false === yk_mt_meta_is_enabled() ) {
		return [];
	}

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
			case 'float':
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
				case 'float':
					$sql_to_execute[] = ' ADD ' . $field[ 'db_col' ]. ' float NULL DEFAULT 0';
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
add_action( 'yk_mt_db_fixed', 'yk_mt_meta_db_columns_create' );			// Admin clicked "Rebuild now" for missing columns

/**
 * Extend core DB formats to include int meta fields
 * @param $formats
 * @return mixed
 */
function yk_mt_meta_db_formats( $formats ) {

	$columns = yk_mt_meta_fields_where( 'type', 'float', 'db_col' );

	foreach( $columns as $column ) {
		$formats[ $column ] = '%f';
	}

	return $formats;
}
add_filter( 'yk_mt_db_formats', 'yk_mt_meta_db_formats' );

/**
 * When updating calories used for an entry, total any (int) meta fields and update entry table
 * @param $entry_id
 */
function yk_mt_meta_db_entry_totals_refresh( $entry_id ) {

	$columns = yk_mt_meta_fields_where( 'type', 'int', 'db_col' );

	if ( true === empty( $columns ) ) {
		return;
	}

	$data 	= [ 'id' => $entry_id ];

	foreach ( $columns as $column ) {

		$data[ $column ] = yk_mt_db_entry_sum_int_column( $entry_id, $column );
	}

	yk_mt_db_entry_update( $data );
}
add_action( 'yk_mt_entry_calculate_refresh', 'yk_mt_meta_db_entry_totals_refresh' );
