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
			'db_col' 			=> 'proteins',
			'title' 			=> __( 'Proteins', YK_MT_SLUG ),
			'visible_user' 		=> true,
			'visible_admin' 	=> true,
			'type'				=> 'int'
		];

		// Fats
		$fields[] = [
			'db_col' 			=> 'fats',
			'title' 			=> __( 'Fats', YK_MT_SLUG ),
			'visible_user' 		=> true,
			'visible_admin' 	=> true,
			'type'				=> 'int'
		];

		// Carbs
		$fields[] = [
			'db_col' 			=> 'carbs',
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
