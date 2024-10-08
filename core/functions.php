<?php /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

defined('ABSPATH') or die('Naw ya dinnie!');

/**
 * Prep a meal for display
 * @param $meal
 *
 * @return mixed
 */
function yk_mt_meal_prep_for_display( $meal ) {

	if ( true === empty( $meal ) ) {
		return $meal;
	}

	$meal[ 'name' ] 		= stripslashes( $meal[ 'name' ] );
	$meal[ 'description' ] 	= stripslashes( $meal[ 'description' ] );

	return $meal;
}

/**
 * Meal fractions enabled?
 * @return bool
 */
function yk_mt_fractions_enabled() {

	if ( false === YK_MT_IS_PREMIUM ) {
		return false;
	}

	return yk_mt_site_options_as_bool('allow-fractions', false );
}

/**
 * Do we have a fraction?
 * @param $fraction
 *
 * @return bool
 */
function yk_mt_fractions_valid( $fraction ) {

	if ( false === yk_mt_fractions_enabled() ) {
		return false;
	}

	$fractions = yk_mt_fractions_all();

	return ! empty( $fractions[ $fraction  ] );
}

/**
 * Clone and fraction a meal
 * @param $meal_id
 * @param $fraction
 * @param null $user_id
 * @return bool|int
 */
function yk_mt_fraction_clone_meal( $meal_id, $fraction, $user_id = NULL ) {

	// Before trying to fraction a meal, has it already been done?
	if( $fractioned_id = yk_mt_db_meal_fraction_exist( $meal_id, $fraction, $user_id ) ) {
		return $fractioned_id;
	}

	$parent_meal = yk_mt_db_meal_get( $meal_id );

	if ( true === empty( $parent_meal ) ) {
		return false;
	}

	$parent_meal[ 'name' ]				= sprintf( '%s - %s', $parent_meal[ 'name' ], yk_mt_fraction_label( $fraction ) );
	$parent_meal[ 'fraction_parent' ] 	= $meal_id;
	$parent_meal[ 'fraction' ] 			= (float) $fraction;
	$parent_meal[ 'calories' ] 			= (int) ( $parent_meal[ 'calories' ] * $parent_meal[ 'fraction' ] );
	$parent_meal[ 'quantity' ] 			= ( $parent_meal[ 'quantity' ] > 0 ) ? (int) ( $parent_meal[ 'quantity' ] * $parent_meal[ 'fraction' ] ) : 0;

	if ( false === empty( $user_id ) ) {
		$parent_meal[ 'added_by' ] = $user_id;
		unset( $parent_meal[ 'added_by_admin' ] );
	}

	if ( true === yk_mt_meta_is_enabled() ) {

		$fractionable_meta_fields = yk_mt_meta_fields_where( 'fractionable', true, 'db_col' );

		foreach ( $fractionable_meta_fields as $column_name ) {
			$parent_meal[ $column_name ] = (int) ( $parent_meal[ $column_name ] * $parent_meal[ 'fraction' ] );
		}
	}

	return yk_mt_db_meal_add( $parent_meal );
}

/**
 * Return list of fractions allowed?
 *
 * @return string[]
 */
function yk_mt_fractions_all() {

	$fractions = [ '0.25' => '1/4', '0.5' => '1/2', '0.75' => '3/4' ];

	if ( true === YK_MT_IS_PREMIUM ) {
		$fractions = apply_filters( 'yk_mt_fractions', $fractions );
	}

	return $fractions;
}

/**
 * Return a label for a given fraction
 * @param $key
 * @return string
 */
function yk_mt_fraction_label( $key) {

	$all_fractions = yk_mt_fractions_all();

	return ( false === empty( $all_fractions[ $key ] ) ) ? $all_fractions[ $key ] : '';
}

/**
 * Get the URL to view / edit a certain entry ID
 * @param $entry_id
 * @return mixed
 */
function yk_mt_entry_url( $entry_id, $esc_url = true ) {
    $url = add_query_arg( 'entry-id', $entry_id, get_permalink() );

    return ( true === $esc_url ) ? esc_url( $url ) : $url;
}

/**
 * Fetch the entry ID for today if it already exists, otherwise create it!
 *
 * @param null $user_id
 * @param null $date
 *
 * @return null|int
 */
function yk_mt_entry_get_id_or_create( $user_id = NULL, $date = NULL ) {

	$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $entry_id = NULL;

    // If no date passed, we're only interested in today's date
	if ( NULL === $date || false === yk_mt_date_is_valid_iso( $date ) ) {
        $date       = yk_mt_date_iso_today();
        $entry_id   = yk_mt_db_entry_get_id_for_today( $user_id );
    } else {
	    $entry_id   = yk_mt_entry_for_given_date( $date );

    }

	// If we have an entry ID for the given date then just return.
    if ( false === empty( $entry_id ) ) {
        return $entry_id;
    }

	$entry = [
		'user_id'               => $user_id,
		'calories_allowed'      => yk_mt_user_calories_target(),
		'calories_used'         => 0,
		'date'                  => $date
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
 * Delete all entries for the given user
 * @param null $user_id
 */
function yk_mt_entry_delete_all_for_user( $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $entries = yk_mt_db_entry_get_ids_and_dates( $user_id );

    if ( false === empty( $entries ) ) {

        $entries = array_keys( $entries );

        array_map( 'yk_mt_db_entry_delete', $entries);
    }

	yk_mt_cache_user_delete( $user_id );
}

/**
 * Soft delete all meals for this user
 * @param null $user_id
 */
function yk_mt_meal_soft_delete_all_for_user( $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $meals = yk_mt_db_meal_for_user( $user_id );

    if ( false === empty( $meals ) ) {

        $meals = wp_list_pluck( $meals, 'id' );
        array_map( 'yk_mt_meal_update_delete', $meals );
    }

	yk_mt_cache_user_delete( $user_id );
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

    $user_id = yk_mt_db_entry_user_id( $entry_id );

    do_action( 'yk_mt_entry_calculate_refresh', $entry_id );

	yk_mt_cache_delete( 'entry-' . $entry_id );

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
 * Soft delete meal
 * @param $meal_id
 * @param bool $deleted
 * @return bool
 */
function yk_mt_meal_update_delete( $meal_id, $deleted = true ) {
    return yk_mt_db_meal_update( [ 'id' => $meal_id, 'deleted' => ( true === $deleted ) ? 1 : 0 ] );
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
 * Return the number of meals added for the user
 * @param null $user_id
 * @return int
 */
function yk_mt_meal_count( $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $meals = yk_mt_db_meal_for_user( $user_id );

    return ( false === empty( $meals ) ) ? count( $meals ) : 0;
}

/**
 * Get the allowed calories for the given user
 *
 * @param null $user_id
 *
 * @param bool $include_source
 *
 * @return int
 */
function yk_mt_user_calories_target( $user_id = NULL, $include_source = false ) {

	$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

	$allowed_calories = NULL;

	$selected_source = yk_mt_settings_get( 'calorie-source', NULL, $user_id );

	// If the user has no source selected and WT is enabled then use it
    if ( true === empty( $selected_source ) && yk_mt_wlt_enabled_for_mt() ) {
        $selected_source = 'wlt';
    }

	if ( false === empty( $selected_source ) ) {

		$calorie_sources = yk_mt_user_calories_sources();

		if ( true === array_key_exists( $selected_source, $calorie_sources ) ) {

			$function = $calorie_sources[ $selected_source ][ 'func' ];

			$allowed_calories = $function( $user_id );

			if  ( true === $include_source ) {
			    return  [ 'source' => $calorie_sources[ $selected_source ], 'value' => (int) $allowed_calories, 'key' => $selected_source ];
            }
		}
	}

	return (int) $allowed_calories;
}

/**
 * Return a list of the sources we can fetch the allowed calories
 * @return mixed
 */
function yk_mt_user_calories_sources() {

	$sources = apply_filters( 'yk_mt_calories_sources_pre', [] );;

	if ( true === YK_MT_IS_PREMIUM &&
            true === yk_mt_site_options_as_bool( 'allow-calorie-override-admin' ) ) {
		$sources[ 'admin' ] = [
		                            'value'         => esc_html__( 'As specified by Admin', 'meal-tracker' ),
                                    'admin-message' => esc_html__( 'by Admin', 'meal-tracker' ),
                                    'func'          => 'yk_mt_user_calories_target_admin_specified'
        ];
	}

    if ( true === yk_mt_site_options_as_bool( 'allow-calorie-override' ) ) {
        $sources[ 'own' ] = [
                                'value'         => esc_html__( 'Your own target', 'meal-tracker' ),
                                'admin-message' => esc_html__( 'by User', 'meal-tracker' ),
                                'func'          => 'yk_mt_user_calories_target_user_specified'
        ];
    }

    $sources = apply_filters( 'yk_mt_calories_sources', $sources );

	return $sources;
}

/**
 * Get allowed calories as specified by admin
 *
 * @param null $user_id
 *
 * @return int
 */
function yk_mt_user_calories_target_admin_specified( $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    return yk_mt_settings_get( 'allowed-calories-admin', NULL,  $user_id );
}

/**
 * Get allowed calories as specified by user
 *
 * @param null $user_id
 *
 * @return int
 */
function yk_mt_user_calories_target_user_specified( $user_id = NULL ) {

	$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

	return (int) yk_mt_settings_get( 'allowed-calories', 0,  $user_id );
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
	return ( true === defined('SCRIPT_DEBUG') && false == SCRIPT_DEBUG ) ? '.min' : '';
}

/**
 * Fetch a value from the $_GET array
 *
 * @param $key
 * @param null $default
 *
 * @return null
 */
function yk_mt_querystring_value( $key, $default = NULL ) {
	return ( false === empty( $_GET[ $key ] ) ) ? sanitize_text_field( $_GET[ $key ] ) : $default;
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
	return ( false === empty( $_POST[ $key ] ) ) ? sanitize_text_field( $_POST[ $key ] ) : $default;
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
 * Does the user ID passed match the one associated with the entry
 * @param $entry_id
 * @param $user_id
 *
 * @return bool
 */
function yk_mt_security_entry_owned_by_user( $entry_id, $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $all_entries = yk_mt_db_entry_get_ids_and_dates( $user_id );

	return ( true === array_key_exists( (int) $entry_id, $all_entries ) );
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

    $entry = yk_mt_db_entry_get( $entry_id );

    $entry[ 'chart_title' ] = sprintf( '%1$d%2$s (%3$s %4$d)',
        $entry[ 'calories_used'],
        esc_html__( 'kcal used', 'meal-tracker' ),
        esc_html__( 'out of', 'meal-tracker' ),
        $entry[ 'calories_allowed']
    );

    return $entry;
}

/**
 * Get current URL and fudge with a dummy QS value. This allows us to add as a base URL and just append QS values knowing the ? has already been added
 * @return string
 */
function yk_mt_current_url() {

	$url = $url = get_permalink();

	return add_query_arg( 'yk-mt', 'y', $url );
}
/**
 * Return an array for config values for AJAX localize
 * @return array
 */
function yk_mt_ajax_config() {
	$config = [
        'page-url'                          => yk_mt_current_url(),
        'plugin-url'                        => YK_MT_PLUGIN_URL,
        'ajax-url'                          => admin_url('admin-ajax.php'),
        'ajax-security-nonce'               => wp_create_nonce( 'yk-mt-nonce' ),
	    'units-hide-quantity'               => yk_mt_units_where( 'drop-quantity', true, true ),
		'external-source'					=> false
    ];

	return apply_filters( 'yk_mt_config', $config );
}

/**
 * Localise values for calendar
 * @return mixed|void
 */
function yk_mt_ajax_config_calendar() {

	$config = [
					'months'	=> [ 	esc_html__( 'January', 'meal-tracker' ),
										esc_html__( 'February', 'meal-tracker' ),
										esc_html__( 'March', 'meal-tracker' ),
										esc_html__( 'April', 'meal-tracker' ),
										esc_html__( 'May', 'meal-tracker' ),
										esc_html__( 'June', 'meal-tracker' ),
										esc_html__( 'July', 'meal-tracker' ),
										esc_html__( 'August', 'meal-tracker' ),
										esc_html__( 'September', 'meal-tracker' ),
										esc_html__( 'October', 'meal-tracker' ),
										esc_html__( 'November', 'meal-tracker' ),
										esc_html__( 'December', 'meal-tracker' )
					],
					'days'		=> [	esc_html__( 'Sunday', 'meal-tracker' ),
										esc_html__( 'Monday', 'meal-tracker' ),
										esc_html__( 'Tuesday', 'meal-tracker' ),
										esc_html__( 'Wednesday', 'meal-tracker' ),
										esc_html__( 'Thursday', 'meal-tracker' ),
										esc_html__( 'Friday', 'meal-tracker' ),
										esc_html__( 'Saturday', 'meal-tracker' ) ],
				'today-button' =>		esc_html__( 'Today', 'meal-tracker' )
    ];

	return apply_filters( 'yk_mt_config_calendar', $config );
}

/**
 * Return an array of localised strings
 * @return array
 */
function yk_mt_localised_strings( ) {

    $config = [
        'just-added'                    => esc_html__( 'Just Added:', 'meal-tracker' ),
        'calorie-unit'                  => esc_html__( 'kcal', 'meal-tracker' ),
        'remove-text'                   => esc_html__( 'Remove', 'meal-tracker' ),
		'total'                   		=> esc_html__( 'Total', 'meal-tracker' ),
        'edit-text'                     => esc_html__( 'Edit', 'meal-tracker' ),
        'no-data'                       => esc_html__( 'No data has been entered', 'meal-tracker' ),
        'meal-added-success'            => esc_html__( 'The meal has been added', 'meal-tracker' ),
        'meal-added-success-short'      => esc_html__( 'Added', 'meal-tracker' ),
        'meal-entry-added-success'      => esc_html__( 'The meal has been added', 'meal-tracker' ),
        'meal-entry-added-short'        => esc_html__( 'Added', 'meal-tracker' ),
        'meal-entry-missing-meal'       => esc_html__( 'Select a meal', 'meal-tracker' ),
        'meal-entry-deleted-success'    => esc_html__( 'The meal has been removed', 'meal-tracker' ),
        'db-error'                      => esc_html__( 'There was error saving your changes', 'meal-tracker' ),
        'db-error-loading'              => esc_html__( 'There was error loading your data', 'meal-tracker' ),
	    'settings-saved-success'        => esc_html__( 'Your settings have been saved', 'meal-tracker' ),
        'confirm-title'                 => esc_html__( 'Are you sure?', 'meal-tracker' ),
        'confirm-content'               => esc_html__( 'Proceeding will cause user data to be deleted. This data can not be recovered. Are you sure you wish to proceed?', 'meal-tracker' )
    ];

	return apply_filters( 'yk_mt_config_locale', $config );
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
        'na'        => [ 'label' => esc_html__( 'N/A', 'meal-tracker' ), 'drop-quantity' => true ],
		'g'         => [ 'label' => 'g' ],
		'ml'        => [ 'label' => 'ml' ],
		'oz'		=> [ 'label' => 'oz' ],
		'small'     => [ 'label' =>  esc_html__( 'Small', 'meal-tracker' ), 'drop-quantity' => true ],
		'medium'    => [ 'label' =>  esc_html__( 'Medium', 'meal-tracker' ), 'drop-quantity' => true ],
		'large'     => [ 'label' =>  esc_html__( 'Large', 'meal-tracker' ), 'drop-quantity' => true ],
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

		if ( false === empty( $details[ $field ] ) &&
				$equals === $details[ $field ] ) {
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
	       true === isset( $meal[ 'name'], $meal[ 'quantity'], $meal[ 'unit'], $meal[ 'calories'], $meal[ 'id'] );
}

/**
 * @param $title
 * @param $name
 * @param string $value
 * @param int $max_length
 *
 * @param bool $required
 *
 * @return string
 */
function yk_mt_form_text( $title, $name, $value ='', $max_length = 60, $required = true ) {

    $name = 'yk-mt-' . $name;

    return sprintf(
		'   <label class="yk-mt__label" for="%1$s">%2$s</label>
				<input type="text" class="yk-mt__input" name="%1$s" id="%1$s" maxlength="%3$d" value="%4$s" %5$s />',
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
 * @param string $previous_value
 * @param array $options
 * @param string $placeholder
 * @param bool $previous_value_is_key
 * @param array $keys_to_disable
 *
 * @return string
 */
function yk_mt_form_select( $title, $name, $previous_value ='', $options = [], $placeholder = '', $previous_value_is_key = false, $keys_to_disable = [] ) {

    $name = 'yk-mt-' . $name;

	$html = sprintf( '<div id="%1$s-row" class="yk-mt-form-row">
						<label class="yk-mt__label" for="%1$s">%2$s</label>
							<select name="%1$s" id="%1$s" class="yk-mt__select" %s>', $name, $title, $placeholder );

	if ( false === empty( $placeholder ) ) {
        $html .= '<option>' . $placeholder . '</option>';
    }

	foreach ( $options as $key => $value ) {

	    if ( true === is_array( $value ) ) {
            $value = $value[ 'value' ];
        }

	    $compare_against = ( true === $previous_value_is_key ) ? $key : $value;

        $selected = selected( $previous_value, $compare_against, false );

        $disabled = ( true === in_array( $key, $keys_to_disable ) ) ? ' disabled' : '';

		$html .= sprintf( '<option value="%1$s" %4$s %3$s>%2$s</option>', esc_attr( $key ), esc_attr( $value ), $selected, $disabled );
	}

	$html .= '</select></div>';

	return $html;
}

/**
 * @param $title
 * @param $name
 * @param string $value
 * @param string $css_class
 * @param int $step
 * @param int $min
 * @param int $max
 * @param bool $show_label
 *
 * @param bool $required
 * @param bool $disabled
 * @param null $trailing_html
 *
 * @return string
 */
// TODO: Refactor this to expect an array for arguments
function yk_mt_form_number( $title, $name, $value = '', $css_class = '', $step = 1, $min = 1,
                                $max = 99999, $show_label = true, $required = true, $disabled = false,
                                    $trailing_html = NULL
                                ) {

    $name = 'yk-mt-' . $name;

	$html = sprintf( '<div id="%1$s-row" class="yk-mt-form-row">', $name );

	if ( true === $show_label ) {
		$html .= sprintf( '<label for="%1$s" class="yk-mt__label %3$s">%2$s</label>', $name, $title, $css_class );
	}

	$html .= sprintf( '<input type="number" name="%1$s" id="%1$s" min="%2$s" max="%3$s" step="%4$s" value="%5$s" %6$s class="yk-mt__input %7$s" %8$s />',
		$name,
		(int) $min,
		(int) $max,
		(float) $step,
		$value,
        ( true === $required ) ? ' required' : '',
        $css_class,
        ( true === $disabled ) ? ' disabled' : ''
	);

	if ( false === empty( $trailing_html) ) {
	    $html .= $trailing_html;
    }

	return $html . '</div>';
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

/**
 * Fetch entry ID from QS and ensure it belongs to the logged in user
 *
 * @param bool $ensure_belongs_to_current_user
 * @param bool $create_entry_for_missing_date
 * @return null
 */
function yk_mt_entry_id_from_qs( $ensure_belongs_to_current_user = true,
                                    $create_entry_for_missing_date = true ) {

    $entry_id = yk_mt_querystring_value( 'entry-id' );

    if ( true === empty( $entry_id ) ) {
        return NULL;
    }

    // The entry-id can also store a date. If so, let's see if the user has an entry for this date. If so, use this entry ID, if not, we
    // need to create an entry for that date.
    if ( false === is_numeric( $entry_id ) ) {

        $date       = $entry_id;
        $entry_id   = yk_mt_entry_for_given_date( $date );

        if ( false === $entry_id &&
                true === $create_entry_for_missing_date ) {

            if ( false === yk_mt_entry_allowed_to_create_for_this_date( $date ) ) {
                return NULL;
            }

            $entry_id = yk_mt_entry_get_id_or_create( NULL, $date );
        }
    }

    if ( true === $ensure_belongs_to_current_user &&
            false === yk_mt_security_entry_owned_by_user( $entry_id, get_current_user_id() ) ) {
        return NULL;
    }

    return (int) $entry_id;
}

/**
 * For the given date, does the admin settings allow a new entry to be added for this date?
 * @param $entry_date
 * @return bool
 */
function yk_mt_entry_allowed_to_create_for_this_date( $entry_date ) {

    if ( true === empty( $entry_date ) ) {
        return false;
    }

    $entry_date     = new DateTime( $entry_date );
    $current_date   = new DateTime();

    $current_date->settime(0,0);

    // Today's date
    if ( $entry_date == $current_date ) {
        return true;
    }

    // Future date
    if ( $entry_date > $current_date && yk_mt_site_options_as_bool('new-entries-future' ) ) {
        return true;
    }

    // Past Date
    if ( $entry_date < $current_date && true === yk_mt_site_options_as_bool('new-entries-past' ) ) {
        return true;
    }

    return false;
}

/**
 * For the given entry, if today's allowed calorie does not match then update entry.
 * @param bool $entry_id
 * @return bool
 */
function yk_mt_allowed_calories_refresh( $entry_id = false ) {

    $entry_id = ( false !== $entry_id ) ? (int) $entry_id : yk_mt_db_entry_get_id_for_today();

    $entry = yk_mt_db_entry_get( $entry_id );

    if ( true === empty( $entry ) ) {
        return false;
    }

    $allowed_calories = yk_mt_user_calories_target();

    // Only bother to update DB if we have a difference
    if( (int) $allowed_calories === (int) $entry[ 'calories_allowed' ] ) {
        return false;
    }

    yk_mt_db_entry_update( [ 'id' => $entry_id, 'calories_allowed' => $allowed_calories ] );

    yk_mt_entry_calories_calculate_update_used( $entry_id );

    return true;
}

/**
 * Update an entry's allowance
 * @param $new_allowance
 * @param bool $entry_id
 * @return bool
 */
function yk_mt_allowed_calories_update_entry( $new_allowance, $entry_id = false ) {

    $entry_id = ( false !== $entry_id ) ? (int) $entry_id : yk_mt_db_entry_get_id_for_today();

    $entry = yk_mt_db_entry_get( $entry_id );

    if ( true === empty( $entry ) ) {
        return false;
    }

    // Only bother to update DB if we have a difference
    if( (int) $new_allowance === (int) $entry[ 'calories_allowed' ] ) {
        return true;
    }

    yk_mt_db_entry_update( [ 'id' => (int) $entry_id, 'calories_allowed' => (int) $new_allowance ] );

    yk_mt_entry_calories_calculate_update_used( $entry_id );

    return true;
}

/**
 * Fetch navigation links around the current entry ID
 * @param null $entry_id
 */
function yk_mt_navigation_links() {

    $todays_entry_id    =  yk_mt_entry_get_id_or_create();
    $links              = [];
    $links[ 'all' ]     = yk_mt_db_entry_get_ids_and_dates();
    $links[ 'nav' ]     = [];

    $links[ 'nav' ][ 'yesterday' ]  =  [ 'id' => date('Y-m-d', strtotime('-1 day' ) ), 'label' =>  esc_html__( 'Yesterday', 'meal-tracker' ) ];

    // Do we already have an entry for yesterday? IF so, swap in entry ID
    if ( $existing_id =  yk_mt_entry_for_given_date( $links[ 'nav' ][ 'yesterday' ][ 'id' ] ) ) {
        $links[ 'nav' ][ 'yesterday' ][ 'id' ] = $existing_id;
    } // Are we allowed to create entries in the past? IF not, and we don't have an actual entry for yesterday, then remove "Yesterday" link.
    else if ( false === yk_mt_site_options_as_bool('new-entries-past' ) ) {
        unset( $links[ 'nav' ][ 'yesterday' ] );
    }

    $links[ 'nav' ][ 'today' ]      =  [ 'id' => $todays_entry_id, 'label' =>  esc_html__( 'Today', 'meal-tracker' )  ];

    $links[ 'nav' ][ 'tomorrow' ]   =  [ 'id' => date('Y-m-d', strtotime('+1 day' ) ), 'label' =>  esc_html__( 'Tomorrow', 'meal-tracker' ) ];

    // Do we already have an entry for tomorrow? IF so, swap in entry ID
    if ( $existing_id =  yk_mt_entry_for_given_date( $links[ 'nav' ][ 'tomorrow' ][ 'id' ] ) ) {
        $links[ 'nav' ][ 'tomorrow' ][ 'id' ] = $existing_id;
    }   // Tomorrow - are future dates are permitted.
    elseif ( false === yk_mt_site_options_as_bool('new-entries-future' ) ) {
        unset( $links[ 'nav' ][ 'tomorrow' ] );
    }

    return $links;
}

/**
 * //TODO: Still needed?
 * Get label for entry
 * @param $date
 * @return mixed
 */
function yk_mt_navigation_links_get_label( $date ) {

    if ( true === empty( $date ) ) {
        return $date;
    }

    if ( $date === date('Y-m-d' ) ) {
        return esc_html__( 'Today', 'meal-tracker' );
    }

    if( $date === date('Y-m-d', strtotime('-1 day' ) ) ) {
        return esc_html__( 'Yesterday', 'meal-tracker' );
    }

    if( $date === date('Y-m-d', strtotime('+1 day' ) ) ) {
        return esc_html__( 'Tomorrow', 'meal-tracker' );
    }

    return yk_mt_date_format( $date );
}

/**
 * Look through the user's entries and see if they have an entry ID for that date
 *
 * @param $date
 * @param null $user_id
 * @return bool|false|int|string
 */
function yk_mt_entry_for_given_date( $date, $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    if ( false === yk_mt_date_is_valid_iso( $date ) ) {
        return false;
    }

    $all_dates = yk_mt_db_entry_get_ids_and_dates( $user_id );

    return array_search( $date, $all_dates );
}

/*
 * Format an ISO date
 */
function yk_mt_date_format( $iso_date, $format = NULL ) {

    if ( true === empty( $iso_date ) ) {
        return '-';
    }

    $time 			= strtotime( $iso_date );
   $date_format 	= ( true === empty( $format ) ) ? get_option( 'date_format' ) : $format;

    return date( $date_format, $time );
}

/**
 * Upgrade notice for shortcode
 * @return string
 */
function yk_mt_display_premium_upgrade_notice_for_shortcode () {

	return sprintf( '<blockquote class="error">%s <a href="%s">%s</a></blockquote>',
		esc_html__( 'To use this shortcode, you need to upgrade to the Premium version.', 'meal-tracker' ),
		esc_url( admin_url('admin.php?page=yk-mt-license') ),
		esc_html__( 'Upgrade now', 'meal-tracker' )
	);
}


/**
 * Display an upgrade button
 *
 * @param string $css_class
 * @param null $link
 */
function yk_mt_upgrade_button( $css_class = '', $link = NULL ) {

    $link = ( false === empty( $link ) ) ? $link : YK_MT_UPGRADE_LINK . '?hash=' . yk_mt_generate_site_hash() ;

    echo sprintf('<a href="%s" class="button-primary sh-cd-upgrade-button%s"><i class="far fa-credit-card"></i> %s £%d %s</a>',
        esc_url( $link ),
        esc_attr( ' ' . $css_class ),
        esc_html__( 'Upgrade to Premium for ', 'meal-tracker' ),
        esc_html( yk_mt_license_price() ),
        esc_html__( 'a year ', 'meal-tracker' )
    );
}

/**
 * Display an features button
 *
 * @param string $css_class
 * @param null $link
 */
function yk_mt_features_button( $css_class = '', $link = NULL ) {

	$link = ( false === empty( $link ) ) ? $link : 'https://mealtracker.yeken.uk/features';

	echo sprintf('<a href="%s" class="button-secondary sh-cd-upgrade-button%s" target="_blank" rel="noopener">%s</a>',
		esc_url( $link ),
		esc_attr( ' ' . $css_class ),
		esc_html__( 'Read more about features', 'meal-tracker' )
	);
}
/**
 * Display message in admin UI
 *
 * @param $text
 * @param bool $error
 */
function yk_mt_message_display( $text, $error = false ) {

    if ( true === empty( $text ) ) {
        return;
    }

    printf( '<div class="%s"><p>%s</p></div>',
        true === $error ? 'error' : 'updated',
        esc_html( $text )
    );
}

/**
 * Render features array into HTML
 * @param $features
 */
function yk_mt_features_display() {
    return yk_mt_display_features( yk_mt_feature_list_premium(), true );
}

/**
 * Return an array of all features
 */
function yk_mt_features_list() {

    return [
			[ 	
				'title'			=> esc_html__( 'Additional shortcodes', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Enhance your site with extra shortcodes.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'External APIs', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Allow your users to browse FatSecrets Food and Recipe APIs.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Own Meal collection', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Build your own meal collection for your users to explore.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Edit user\'s meals', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Manage your user\'s meal collections by viewing, editing, and deleting meals.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Create and view entries', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Enable your users to create and view meal entries for any date.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Edit entries', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Give your users the ability to edit their entries for any selected day.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Edit Meals', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Enable your users to modify their saved meals.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Calorie Allowance sources', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Retrieve daily calorie limits from external sources, such as YeKen\'s Weight Tracker.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Compress meal items', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Consolidate multiple meal lines into a single entry line.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Unlimited meals per user', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Users are no longer restricted to a maximum of 40 meals and can now add as many meals as they wish.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Access your user\'s data', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Access all their entries, meals, and calorie intake', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Set calorie allowances', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Assign daily calorie allowances for your users.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Summary Statistics', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Review summary statistics of your Meal Tracker data and analyze its usage by your users.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Fractional meal quantities', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'If enabled in the settings, you can use additional quantity options of 1/4, 1/2, and 3/4 when adding meals to an entry.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Admin Search', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Search for users by name or email address.', WE_LS_SLUG )
			],
			[ 	
				'title'			=> esc_html__( 'Additional settings', WE_LS_SLUG ), 
				'description'	=> esc_html__( 'Additional settings for tailoring your Meal Tracker experience.', WE_LS_SLUG )
			],
	];	
}

/**
 * HTML for mention of custom work
 */
function yk_mt_custom_notification_html() {
    ?>

    <p><img src="<?php yk_mt_echo( plugins_url( 'admin-pages/assets/images/yeken-logo.png', __FILE__ ) ); ?>" width="100" height="100" style="margin-right:20px" align="left" /><?php echo esc_html__( 'If require plugin modifications to Meal Tracker, or need a new plugin built, or perhaps you need a developer to help you with your website then please don\'t hesitate get in touch!', 'meal-tracker' ); ?></p>
    <p><strong><?php echo esc_html__( 'We provide fixed priced quotes.', 'meal-tracker' ); ?></strong></p>
    <p><a href="https://www.yeken.uk" rel="noopener noreferrer" target="_blank">YeKen.uk</a> /
        <a href="https://profiles.wordpress.org/aliakro" rel="noopener noreferrer" target="_blank">WordPress Profile</a> /
        <a href="mailto:email@yeken.uk" >email@yeken.uk</a></p>
    <br clear="both"/>
    <?php
}

/**
 * Display upgrade notice
 *
 * @param bool $pro_plus
 */
function yk_mt_display_pro_upgrade_notice( ) {
    ?>

    <div class="postbox yk-mt-advertise-premium">
        <h3 class="hndle"><span><?php echo esc_html__( 'Upgrade Meal Tracker and get more features!', 'meal-tracker' ); ?> </span></h3>
        <div style="padding: 0px 15px 0px 15px">
            <p><?php echo esc_html__( 'Upgrade to the Premium version of this plugin to view your user\'s data, record entries for multiple days, external data sources and much more!', 'meal-tracker' ); ?></p>
            <p><a href="<?php echo esc_url( admin_url('admin.php?page=yk-mt-license') ); ?>" class="button-primary"><?php echo esc_html__( 'Read more and upgrade to Premium Version', 'meal-tracker' ); ?></a></p>
        </div>
    </div>

    <?php
}

/**
 * Fetch the user's ID from the querystring key user-id
 *
 * @return int
 */
function yk_mt_get_user_id_from_qs(){
    return (int) yk_mt_querystring_value( 'user-id' );
}

/**
 * Helper function to determine if the user exists in WP
 *
 * @param $user_id
 * @return bool
 */
function yk_mt_user_exist( $user_id ) {

    if( false === is_numeric( $user_id ) ) {
        return false;
    }

    return ( false === get_userdata( $user_id ) ) ? false : true;
}

/**
 * Helper function to check if user ID exists, if not throws wp_die()
 *
 * @param $user_id
 * @return bool
 */
function yk_mt_exist_check( $user_id ) {

    if(false === yk_mt_user_exist( $user_id ) ) {
        wp_die( esc_html__( 'Error: The user does not appear to exist' , 'meal-tracker' ) );
    }
}

/**
 * Helper function for formatting numbers
 * @param $number
 * @param $decimals
 * @return string
 */
function yk_mt_format_number( $number, $decimals = 0 ) {
    return number_format( $number, $decimals );
}

/**
 * Helper function for formatting calories
 * @param $number
 * @return string
 */
function yk_mt_format_calories( $number ) {
    return sprintf( '%s%s', number_format( $number ), esc_html__( 'kcal', 'meal-tracker' ) );
}

/**
 * Helper function for building nutrition string
 * @param $number
 * @return string
 */
function yk_mt_format_nutrition_sting( $meal, $include_meta = true ) {

	if ( true === empty( $meal ) ) {
		return '';
	}

	$text = sprintf( '%s%s', number_format( $meal[ 'calories'] ), esc_html__( 'kcal', 'meal-tracker' ) );

	if ( true === $include_meta ) {

		$sep  	= ' / ';
		$text .= $sep;
		$text .= sprintf( '%s: %dg%s', esc_html__( 'fats', 'meal-tracker' ), $meal[ 'meta_fats' ], $sep );
		$text .= sprintf( '%s: %dg%s', esc_html__( 'protein', 'meal-tracker' ), $meal[ 'meta_proteins' ], $sep );
		$text .= sprintf( '%s: %dg', esc_html__( 'carbs', 'meal-tracker' ), $meal[ 'meta_carbs' ], $sep );

	}

	return $text;
}

/**
 * Translate known meal types from English into locale.
 * @param $meal_type
 * @return mixed|string
 */
function yk_mt_lang_translate_known_meal_type_from_english( $meal_type ) {

	if ( true === empty( $meal_type ) ) {
		return '';
	}

	$lookup = [
		'Breakfast'     => esc_html__( 'Breakfast', 'meal-tracker' ),
		'Mid-morning'   => esc_html__( 'Mid-morning', 'meal-tracker' ),
		'Lunch'         => esc_html__( 'Lunch', 'meal-tracker' ),
		'Afternoon'     => esc_html__( 'Afternoon', 'meal-tracker' ),
		'Dinner'        => esc_html__( 'Dinner', 'meal-tracker' ),
		'Evening'       => esc_html__( 'Evening', 'meal-tracker' )
	];

	return ( false === empty( $lookup[ $meal_type ] ) ) ? $lookup[ $meal_type ] : '';
}

/**
 * Log to PHP error log
 * @param $text
 */
function yk_mt_log_error( $text ) {
	if ( false === empty( $text ) ) {
		error_log( $text );
	}
}

/**
 * Get the server IP
 * @return mixed
 */
function yk_mt_server_ip() {
	return sanitize_text_field( $_SERVER['SERVER_ADDR'] );
}

/**
 * Convert string to bool
 * @param $string
 * @return mixed
 */
function yk_mt_to_bool( $string ) {
	return filter_var( $string, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Process a CSV attachment and import into database
 *
 * @param $attachment_id
 *
 * @param bool $dry_run
 *
 * @return string
 */
function yk_mt_import_csv_meal_collection( $attachment_id, $dry_run = true ) {

	if ( false === yk_mt_admin_permission_check() ) {
		return 'You do not have the correct admin permissions';
	}

	if ( false === YK_MT_IS_PREMIUM ) {
		return 'This is a premium feature';
	}

	$csv_path = get_attached_file( $attachment_id );
	$admin_id = get_current_user_id();

	if ( true === empty( $csv_path ) || false === file_exists( $csv_path )) {
		return 'Error: Error loading CSV from disk.';
	}

	$csv = array_map('str_getcsv', file( $csv_path ) );

	if ( true === empty( $csv ) ) {
		return 'Error: The CSV appears to be empty.';
	}

	array_walk($csv, function(&$a) use ($csv) {
		$a = array_combine($csv[0], $a);
	});

	$validate_header_result = yk_mt_import_csv_meal_collection_validate_header( $csv[0] );

	if ( true !== $validate_header_result ) {
		return $validate_header_result;
	}

	array_shift($csv );

	if ( true === empty( $csv ) ) {
		return 'Error: The CSV appears to be empty (when header hs been removed).';
	}

	$errors = 0;

	$output = sprintf( '%d rows to process...' . PHP_EOL, count( $csv ) );

	if ( true === $dry_run ) {
		$output .= 'DRY RUN MODE! No data will be imported.' . PHP_EOL;
	}

	$db_formats = [ '%d', '%d', '%s', '%s', '%d', '%d', '%s', '%f', '%f', '%f', '%d', '%s' ];

	foreach ( $csv as $row ) {

		if ( $errors >= 50 ) {
			$output .= 'Aborted! More than 50 errors have been detected in this file.' . PHP_EOL;
			break;
		}

		$row = array_change_key_case( $row ); // Force CSV headers to lowercase

		$validation_result = yk_mt_import_csv_meal_collection_validate_row( $row );

		// Validate a row before proceeding
		if ( true !== $validation_result ) {
			$output .= $validation_result . PHP_EOL;
			$errors++;
			continue;
		}

		if ( false === $dry_run ) {

			// Import into database
			$meal = [ 	'added_by' 			=> $admin_id,
						 'added_by_admin' 	=> 1,
						 'name'				=> $row[ 'name' ],
						 'description'		=> $row[ 'description' ],
						 'calories'			=> $row[ 'calories' ],
						 'quantity'			=> $row[ 'quantity' ],
						 'unit'				=> $row[ 'unit' ],
						 'meta_proteins'	=> $row[ 'proteins' ],
						 'meta_carbs'		=> $row[ 'carbs' ],
						 'meta_fats'		=> $row[ 'fats' ],
						 'imported_csv'		=> 1,
						 'source'			=> 'csv'
			];

			global $wpdb;

			$result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_MEALS , $meal, $db_formats );

			if ( false === $result ) {
				$output .= 'Skipped: Error inserting into database (most likely a field contains too many characters or in the wrong format): ' . $wpdb->last_error . ' . ' .  implode( ',', $row ) . PHP_EOL;
			}
		}

	}

	if ( $errors > 0 ) {
		$output .= sprintf( '%d errors were detected and the rows skipped.' . PHP_EOL, $errors );
	}

	$output .= 'Completed.';

	return $output;

}

/**
 * Verify header row
 * @param $header_row
 *
 * @return bool|string
 */
function yk_mt_import_csv_meal_collection_validate_header( $header_row ) {

	$expected_headers = [ 'name', 'description', 'calories', 'quantity', 'unit', 'proteins', 'carbs', 'fats' ];

	foreach ( $expected_headers as $column ) {

		if ( false === isset( $header_row[ $column ] ) ) {
			return 'Missing column: ' . $column . '. Expecting: ' . implode( ',', $expected_headers ) . PHP_EOL;
		}
	}

	return true;
}

/**
 * Validate CSV row
 * @param $csv_row
 *
 * @return bool|string
 */
function yk_mt_import_csv_meal_collection_validate_row( $csv_row ) {

	if ( true === empty( $csv_row[ 'name' ] ) ) {
		return 'Skipped: Missing name: ' . implode( ',', $csv_row );
	}

	if ( false === empty( $isset[ 'calories' ] ) ) {
		return 'Skipped: Calories: ' . implode( ',', $csv_row );
	}

	$allowed_units = yk_mt_units_raw();

	if ( false === empty( $csv_row[ 'unit' ] ) &&
			true === empty( $allowed_units[ $csv_row[ 'unit' ] ] ) ) {
			return 'Skipped: Invalid unit: ' . implode( ',', $csv_row );
	}

	return true;
}

/**
 * Santise and echo
 * 
 * A wrapper around PHP echo for WP's sake. Their automated scanner flags all sorts of issues with just use of echo() without their sanitising
 * functions called before it - even though the code is sanitising correctly in other places user input etc. 
 */
function yk_mt_echo( $value, $sanitiser = 'sanitize_text_field' ) {

	switch ( $sanitiser ) {

		case 'esc_html':
			echo esc_html( $value );
			break;
		case 'wp_kses':
			echo yk_mt_wp_kses( $value );
			break;	
		default:
			echo sanitize_text_field( $value );
	}
}

/**
 * Easy to use wrapper around yk_mt_echo()
 */
function yk_mt_echo_esc_html( $value ) {
	yk_mt_echo( $value, $sanitiser = 'esc_html' );
}

/**
 * Easy to use wrapper around yk_mt_wp_kses()
 */
function yk_mt_echo_wp_kses( $value ) {
	echo yk_mt_wp_kses( $value );
}

/**
 * Our version of kses and the HTML we are happy with
 */
function yk_mt_wp_kses( $value ) {
	
	$basic_tags = wp_kses_allowed_html( 'html' );

	$basic_tags[ 'a' ] 		= [ 'id' => true, 'class' => true, 'href' => true, 'title' => true, 'target' => true];
	$basic_tags[ 'canvas' ] = [ 'id' => true, 'class' => true ];
	$basic_tags[ 'div' ]	= [ 'id' => true, 'class' => true, 'style' => true ];	
	$basic_tags[ 'i' ]		= [ 'id' => true, 'class' => true ];	
	$basic_tags[ 'p' ]		= [ 'id' => true, 'class' => true ];		
	$basic_tags[ 'span' ]	= [ 'id' => true, 'class' => true ];			
	$basic_tags[ 'table' ]	= [ 'id' => true, 'class' => true ];	
	$basic_tags[ 'tr' ]		= [ 'id' => true, 'class' => true ];	
	$basic_tags[ 'td' ]		= [ 'id' => true, 'class' => true ];	
	$basic_tags[ 'li' ]		= [ 'class' => true ];	

	return wp_kses( $value, $basic_tags );
}