<?php

/**
 * Fetch the entry ID for today if it already exists, otherwise create it!
 *
 * @return null|int
 */
function yk_mt_entry_get_id_or_create( $user_id = NULL ) {

	$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

	$entry_id = yk_mt_db_entry_get_id_for_today( $user_id );

	if ( NULL !== $entry_id ) {
		return $entry_id;
	}

	$entry = [
		'user_id'               => $user_id,
		'calories_allowed'      => yk_mt_user_calories_target(),
		'calories_used'         => 0,
		'date'                  => yk_mt_date_iso_today()
	];

	$id = yk_mt_db_entry_add( $entry );

	return ( false === empty( $id ) ) ? (int) $id : NULL;
}

/**
 * Add a meal to an entry
 *
 * @param $entry_id
 * @param $meal_id
 * @param $meal_type
 *
 * @return bool
 */
function yk_mt_entry_meal_add( $entry_id, $meal_id, $meal_type ) {

	$entry = yk_mt_db_entry_get( $entry_id );

	// Does entry exist?
	if ( false === $entry ) {
		return false;
	}

	$meal = yk_mt_db_meal_get( $meal_id );

	// Does meal exist?
	if ( false === $meal ) {
		return false;
	}

	// Valid meal time?
	if ( false === in_array( $meal_type, yk_mt_meal_types_ids() ) ) {
		return false;
	}

	// Add Meal to Entry
	$result = yk_mt_db_entry_meal_add([
		'entry_id' => $entry_id,
		'meal_id' => $meal_id,
		'meal_type' => $meal_type
	]);

	// Did the DB insert work?
	if ( false === $result ) {
		return false;
	}

	return yk_mt_entry_calories_calculate_update_used( $entry_id );
}

/**
 * Delete a meal for a given entry_meal_id
 *
 * @param $entry_meal_id
 *
 * @return bool
 */
function yk_mt_entry_meal_delete( $entry_meal_id ) {

	$entry_meal = yk_mt_db_entry_meal_get( $entry_meal_id );

	if ( false === $entry_meal ) {
		return false;
	}

	if ( false === yk_mt_db_entry_meal_delete( $entry_meal_id ) ) {
		return false;
	}

	return yk_mt_entry_calories_calculate_update_used( $entry_meal['entry_id'] );
}

/**
 * Total up the calories used for an entry (sum all meals added) and update.
 *
 * @param $entry_id
 *
 * @return bool
 */
function yk_mt_entry_calories_calculate_update_used( $entry_id ) {

	if ( false === is_numeric( $entry_id ) ) {
		return false;
	}

	$calories = yk_mt_db_entry_calories_count( $entry_id );

	// If no calories, set to zero
	if ( NULL === $calories ) {
        $calories = 0;
	}

	$result = yk_mt_db_entry_update( [ 'id' => $entry_id, 'calories_used' => $calories ] );

	do_action( 'yk_mt_entry_cache_clear', $entry_id );

	return $result;
}

/**
 * Set fave status for a meal
 *
 * @param $meal_id
 * @param bool $favourite
 *
 * @return bool
 */
function yk_mt_meal_update_fave( $meal_id, $favourite = true ) {
	return yk_mt_db_meal_update( [ 'id' => $meal_id, 'favourite' => ( true === $favourite ) ? 1 : 0 ] );
}

/**
 * Fetch all IDs for Meal Types
 *
 * @return array
 */
function yk_mt_meal_types_ids() {

	$meal_types = yk_mt_db_meal_types_all();

	return ( false === empty( $meal_types ) ) ? wp_list_pluck( $meal_types, 'id' ) : [];
}

/**
 * Get the allowed calories for the given user
 *
 * @param null $user_id
 *
 * @return int
 */
function yk_mt_user_calories_target( $user_id = NULL ) {

	$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

	// TODO: We need to store this locally unless there is an override by another system
	$allowed_calories = 2000;

	// Take Calories from WLT?
    if ( true === function_exists( 'ws_ls_harris_benedict_calculate_calories' ) ) {
        $yeken_wt = ws_ls_harris_benedict_calculate_calories();

        // TODO: We need to have an option to select whether to use / lose / gain / maintain
        if ( true === isset( $yeken_wt[ 'lose' ][ 'total' ] ) ) {
            $allowed_calories = $yeken_wt[ 'lose' ][ 'total' ];
        }
    }

	$allowed_calories = apply_filters( 'yk_mt_user_allowed_calories', $allowed_calories, $user_id );

	return (int) $allowed_calories;
}

/**
 * Helper function to ensure all fields have expected keys
 *
 * @param $data
 * @param $expected_fields
 * @return bool
 */
function yk_mt_array_check_fields($data, $expected_fields ) {

	foreach ( $expected_fields as $field ) {
		if ( false === isset( $data[ $field ] ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Validate an ISO date
 *
 * @param $iso
 *
 * @return bool
 */
function yk_mt_date_is_valid_iso( $iso ) {

	if ( true === empty( $iso ) ) {
		return false;
	}

	$iso = explode( '-', $iso );

	if ( 3 !== count( $iso ) ) {
		return false;
	}

	return checkdate ( $iso[ 1 ], $iso[ 2 ], $iso[ 0 ] );
}

/**
 * Get today's date in ISO
 *
 * @return string
 */
function yk_mt_date_iso_today() {
	return date( 'Y-m-d' );
}

/**
 * Use minified scripts?
 *
 * @return string
 */
function yk_mt_use_minified() {
	return ''; //TODO
	return ( true === defined('SCRIPT_DEBUG') && false == SCRIPT_DEBUG ) ? '.min' : '';
}

/**
 * Fetch a value from the $_POST array
 *
 * @param $key
 * @param null $default
 *
 * @return null
 */
function yk_mt_post_value( $key, $default = NULL ) {
	return ( false === empty( $_POST[ $key ] ) ) ? $_POST[ $key ] : $default;
}

/**
 * Check for $_POST keys
 *
 * @param $keys
 *
 * @return bool
 */
function yk_mt_post_values_exist( $keys ) {

	foreach ( $keys as $key ) {
		if ( true === empty( $_POST[ $key ] ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Return an array that represents the entry
 *
 * @param null $entry_id
 *
 * @return array|bool
 */
function yk_mt_entry( $entry_id = NULL ) {

    // If we have no entry ID, then lets fetch today's entry for the given user or create a new entry!
    $entry_id = ( false === empty( $entry_id ) ) ? (int) $entry_id :  yk_mt_db_entry_get_id_for_today( get_current_user_id() );

    if ( true === empty( $entry_id ) ) {
        return false;
    }

    return yk_mt_db_entry_get( $entry_id );
}

/**
 * Return an array for config values for AJAX localize
 * @return array
 */
function yk_mt_ajax_config() {

    $site_url = site_url();

    return [
        'site-url'                          => $site_url,
        'ajax-url'                          => admin_url('admin-ajax.php'),
        'ajax-security-nonce'               => wp_create_nonce( 'yk-mt-nonce' ),
    ];
}

/**
 * Return an array of localised strings
 * @return array
 */
function yk_mt_localised_strings( ) {
    return [
        'just-added'                    => __( 'Just Added:', YK_MT_SLUG ),
        'calorie-unit'                  => __( 'kcal', YK_MT_SLUG ),
        'remove-text'                   => __( 'Remove', YK_MT_SLUG ),
        'chart-label-used'              => __( 'used', YK_MT_SLUG ),
        'chart-label-remaining'         => __( 'remaining', YK_MT_SLUG ),
        'chart-label-target'            => __( 'Target', YK_MT_SLUG ),
        'no-data'                       => __( 'No data has been entered', YK_MT_SLUG ),
        'meal-added-success'            => __( 'The meal has been added', YK_MT_SLUG ),
        'meal-added-success-short'      => __( 'Added', YK_MT_SLUG ),
        'meal-entry-added-success'      => __( 'The meal has been added', YK_MT_SLUG ),
        'meal-entry-added-short'        => __( 'Added', YK_MT_SLUG ),
        'meal-entry-missing-meal'       => __( 'Select a meal', YK_MT_SLUG ),
        'meal-entry-deleted-success'    => __( 'The meal has been removed', YK_MT_SLUG ),
        'db-error'                      => __( 'There was error saving your changes', YK_MT_SLUG )
    ];
}

/**
 * Return an array of units
 * @return array
 */
function yk_mt_units() {

    $units = [];

    foreach ( yk_mt_units_raw() as $unit => $details ) {
		$units[ $unit ] = $details[ 'label' ];
    }

    return $units;
}

/**
 * Return an array of Units with full data
 * @return array
 */
function yk_mt_units_raw() {
	$units = [
		'g'         => [ 'label' => 'g' ],
		'ml'        => [ 'label' => 'ml' ],
		'small'     => [ 'label' =>  __( 'Small', YK_MT_SLUG ), 'drop-quantity' => true ],
		'medium'    => [ 'label' =>  __( 'Medium', YK_MT_SLUG ), 'drop-quantity' => true ],
		'large'     => [ 'label' =>  __( 'Large', YK_MT_SLUG ), 'drop-quantity' => true ],
	];

	$units = apply_filters( 'yk_mt_units', $units );

	return $units;
}

/**
 * Return all fields where the data is met
 *
 * @param $field
 * @param bool $equals
 * @param bool $just_keys
 *
 * @return array
 */
function yk_mt_units_where( $field, $equals = true, $just_keys = true ) {

	$units = [];

	foreach ( yk_mt_units_raw() as $unit => $details ) {

		if ( $equals === $details[ $field ] ) {
			$units[ $unit ] = $details[ 'label' ];
		}
	}

	if ( true === $just_keys ) {
		$units  = array_keys( $units );
	}

	return $units;
}

/**
 * Fetch the field for a given unit
 *
 * @param $unit
 * @param string $field
 *
 * @return null
 */
function yk_mt_unit_get( $unit, $field = 'label' ) {

	$units = yk_mt_units_raw();

	return ( true === isset( $units[ $unit ][ $field ] ) ) ? $units[ $unit ][ $field ] : NULL;
}

/**
 * For a given meal, render the unit / quantity string
 * @param $meal
 *
 * @return null|string
 */
function yk_mt_get_unit_string( $meal ) {

	if ( true === yk_mt_is_meal_object( $meal ) ) {

		$units_to_drop_quantity_for = yk_mt_units_where( 'drop-quantity' );

		$drop_quantity = in_array( $meal[ 'unit' ], $units_to_drop_quantity_for );

		$label = yk_mt_unit_get( $meal[ 'unit' ] );

		return ( true === $drop_quantity ) ?
			$label :
			sprintf( '%d%s', $meal[ 'quantity' ], $label );
	}

	return 'Err';
}

/**
 * Do we have a meal object?
 * @param $meal
 *
 * @return bool
 */
function yk_mt_is_meal_object( $meal ) {
	return true === is_array( $meal ) &&
	       true === isset( $meal[ 'name'], $meal[ 'quantity'], $meal[ 'unit'], $meal[ 'calories'], $meal[ 'description'], $meal[ 'id'] );
}

/**
 * @param $title
 * @param $name
 * @param int $max_length
 *
 * @return string
 */
function yk_mt_form_text( $title, $name, $value ='', $max_length = 60, $required = true ) {

    $name = 'yk-mt-' . $name;

    return sprintf(
		'   <label for="%1$s">%2$s</label>
				<input type="text" name="%1$s" id="%1$s" maxlength="%3$d" value="%4$s" %5$s />',
		$name,
		$title,
		(int) $max_length,
		esc_attr( $value ),
        ( true === $required ) ? ' required' : ''
	);
}

/**
 * @param $title
 * @param $name
 * @param string $value
 * @param array $options
 *
 * @return string
 */
function yk_mt_form_select( $title, $name, $previous_value ='', $options = [], $placeholder = '' ) {

    $name = 'yk-mt-' . $name;

	$html = sprintf( '<label for="%1$s">%2$s</label>
							<select name="%1$s" id="%1$s" class="" %s>', $name, $title, $placeholder );

	if ( false === empty( $placeholder ) ) {
        $html .= '<option>' . $placeholder . '</option>';
    }

	foreach ( $options as $key => $value ) {
		$html .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $key ), esc_attr( $value ), selected( $previous_value, $value, false ) );
	}

	$html .= '</select>';

	return $html;
}

/**
 * @param $title
 * @param $name
 * @param string $value
 * @param int $step
 * @param int $min
 * @param int $max
 * @param bool $show_label
 *
 * @return string
 */
function yk_mt_form_number( $title, $name, $value = '', $step = 1, $min = 1, $max = 99999, $show_label = true, $required = true ) {

    $name = 'yk-mt-' . $name;

	$html = '';

	if ( true === $show_label ) {
		$html .= sprintf( '<label for="%1$s">%2$s</label>', $name, $title );
	}

	$html .= sprintf( '<input type="number" name="%1$s" id="%1$s" min="%2$s" max="%3$s" step="%4$s" value="%5$s" %6$s />',
		$name,
		(int) $min,
		(int) $max,
		(int) $step,
		$value,
        ( true === $required ) ? ' required' : ''
	);

	return $html;
}

/**
 * Enqueue front end JS / CSS
 */
function yk_mt_enqueue_front_end_dependencies() {

//	$minified = yk_mt_use_minified();
//
//	wp_enqueue_script( 'meal-tracker', plugins_url( 'assets/js/core' . $minified . '.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION, true );
//	wp_enqueue_style( 'meal-tracker', plugins_url( 'assets/css/frontend' . $minified . '.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );
//
//	wp_localize_script( 'meal-tracker', 'yk_mt', yk_mt_ajax_config() );
}

/**
 * Remove certain keys from array
 * @param $array
 * @param $keys
 *
 * @return mixed
 */
function yk_mt_array_strip_keys( $array, $keys ) {

	if ( true === is_array( $array ) ) {

		foreach ( $keys as $key ) {
			unset( $array[ $key ] );
		}
	}

	return $array;
}