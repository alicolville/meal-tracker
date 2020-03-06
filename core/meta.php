<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Load additional fields tat define a meal.
 * @return array
 */
function yk_mt_meta_fields() {

	$fields = [];

	// Macro Nutrient columns: Protein, fat and carbs
	if ( true === yk_mt_license_is_premium() &&
			true === yk_mt_site_options_as_bool( 'macronutrients-enabled', false ) ) {

		// Protein
		$fields[] = [
			'db_col' => 'proteins',
			'title' => __( 'Proteins', YK_MT_SLUG ),
			'visible_user' => true,
			'visible_admin' => true
		];

		// Fats
		$fields[] = [
			'db_col' => 'fats',
			'title' => __( 'Fats', YK_MT_SLUG ),
			'visible_user' => true,
			'visible_admin' => true
		];

		// Carbs
		$fields[] = [
			'db_col' => 'carbs',
			'title' => __( 'Carbs', YK_MT_SLUG ),
			'visible_user' => true,
			'visible_admin' => true
		];

	}

	return $fields;
}
