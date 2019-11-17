<?php

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
 * @return null|int
 */
function yk_mt_entry_get_id_or_create( $user_id = NULL, $date = NULL ) {

	$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $entry_id = NULL;

    // If no date passed, we're only interested in today's date
	if ( NULL === $date || false === yk_mt_date_is_valid_iso( $date ) ) {
        $date       = yk_mt_date_iso_today();
        $entry_id   = yk_mt_db_entry_get_id_for_today($user_id);
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

	do_action( 'yk_mt_entry_cache_clear', $entry_id, $user_id );

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
 * @return int
 */
function yk_mt_user_calories_target( $user_id = NULL ) {

	$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

	$allowed_calories = NULL;

	$selected_source = yk_mt_settings_get( 'calorie-source' );

	// TODO:    This is a temp hack. New user's don't have a calorie source specified in their settings yet.
    //          Therefore, we will need to determine where we should get their calorie source from. From now, hard code to Weight Tracker.
    if ( true === empty( $selected_source ) ) {
        $selected_source = 'wlt';
    }

	if ( false === empty( $selected_source ) ) {

		$calorie_sources = yk_mt_user_calories_sources();

		if ( true === array_key_exists( $selected_source, $calorie_sources ) ) {

			$function = $calorie_sources[ $selected_source ][ 'func' ];

			$allowed_calories = $function();
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
		$sources[ 'admin' ] = [ 'value' => 'As specified by Admin', 'func' => 'yk_mt_user_calories_target_admin_specified' ];
	}

    if ( true === yk_mt_site_options_as_bool( 'allow-calorie-override' ) ) {
        $sources[ 'own' ] = [ 'value' => 'Your own target', 'func' => 'yk_mt_user_calories_target_user_specified' ];
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
//TODO: Built into admin interface
	return 2000;
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
	return ''; //TODO
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
function yk_mt_querystring_value($key, $default = NULL ) {
	return ( false === empty( $_GET[ $key ] ) ) ? $_GET[ $key ] : $default;
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
        __( 'kcal used', YK_MT_SLUG ),
        __( 'out of', YK_MT_SLUG ),
        $entry[ 'calories_allowed']
    );

    return $entry;
}

/**
 * Return an array for config values for AJAX localize
 * @return array
 */
function yk_mt_ajax_config() {
    return [
        'page-url'                          => get_permalink(),
        'plugin-url'                        => YK_MT_PLUGIN_URL,
        'ajax-url'                          => admin_url('admin-ajax.php'),
        'ajax-security-nonce'               => wp_create_nonce( 'yk-mt-nonce' ),
	    'units-hide-quantity'               => yk_mt_units_where( 'drop-quantity', true, true )
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
        'edit-text'                     => __( 'Edit', YK_MT_SLUG ),
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
        'db-error'                      => __( 'There was error saving your changes', YK_MT_SLUG ),
        'db-error-loading'              => __( 'There was error loading your data', YK_MT_SLUG ),
	    'settings-saved-success'        => __( 'Your settings have been saved', YK_MT_SLUG )
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
        'na'        => [ 'label' => __( 'N/A', YK_MT_SLUG ), 'drop-quantity' => true ],
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
function yk_mt_form_select( $title, $name, $previous_value ='', $options = [], $placeholder = '', $previous_value_is_key = false ) {

    $name = 'yk-mt-' . $name;

	$html = sprintf( '<div id="%1$s-row" class="yk-mt-form-row">
						<label for="%1$s">%2$s</label>
							<select name="%1$s" id="%1$s" class="" %s>', $name, $title, $placeholder );

	if ( false === empty( $placeholder ) ) {
        $html .= '<option>' . $placeholder . '</option>';
    }

	foreach ( $options as $key => $value ) {

	    if ( true === is_array( $value ) ) {
            $value = $value[ 'value' ];
        }

	    $compare_against = ( true === $previous_value_is_key ) ? $key : $value;

        $selected = selected( $previous_value, $compare_against, false );

		$html .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $key ), esc_attr( $value ), $selected );
	}

	$html .= '</select></div>';

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
// TODO: Refactor this to expect an array for arguments
function yk_mt_form_number( $title, $name, $value = '', $css_class = '', $step = 1, $min = 1,
                                $max = 99999, $show_label = true, $required = true, $disabled = false,
                                    $trailing_html = NULL
                                ) {

    $name = 'yk-mt-' . $name;

	$html = sprintf( '<div id="%1$s-row" class="yk-mt-form-row">', $name );

	if ( true === $show_label ) {
		$html .= sprintf( '<label for="%1$s" class="%3$s">%2$s</label>', $name, $title, $css_class );
	}

	$html .= sprintf( '<input type="number" name="%1$s" id="%1$s" min="%2$s" max="%3$s" step="%4$s" value="%5$s" %6$s class="%7$s" %8$s />',
		$name,
		(int) $min,
		(int) $max,
		(int) $step,
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
 * Fetch navigation links around the current entry ID
 * @param null $entry_id
 */
function yk_mt_navigation_links() {

    $todays_entry_id    =  yk_mt_entry_get_id_or_create();
    $links              = [];
    $links[ 'all' ]     = yk_mt_db_entry_get_ids_and_dates();
    $links[ 'nav' ]     = [];

    $links[ 'nav' ][ 'yesterday' ]  =  [ 'id' => date('Y-m-d', strtotime('-1 day' ) ), 'label' =>  __( 'Yesterday', YK_MT_SLUG  ) ];

    // Do we already have an entry for yesterday? IF so, swap in entry ID
    if ( $existing_id =  yk_mt_entry_for_given_date( $links[ 'nav' ][ 'yesterday' ][ 'id' ] ) ) {
        $links[ 'nav' ][ 'yesterday' ][ 'id' ] = $existing_id;
    }

    $links[ 'nav' ][ 'today' ]      =  [ 'id' => $todays_entry_id, 'label' =>  __( 'Today', YK_MT_SLUG )  ];
    $links[ 'nav' ][ 'tomorrow' ]   =  [ 'id' => date('Y-m-d', strtotime('+1 day' ) ), 'label' =>  __( 'Tomorrow', YK_MT_SLUG ) ];

    // Do we already have an entry for tomorrow? IF so, swap in entry ID
    if ( $existing_id =  yk_mt_entry_for_given_date( $links[ 'nav' ][ 'tomorrow' ][ 'id' ] ) ) {
        $links[ 'nav' ][ 'tomorrow' ][ 'id' ] = $existing_id;
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
        return __( 'Today', YK_MT_SLUG );
    }

    if( $date === date('Y-m-d', strtotime('-1 day' ) ) ) {
        return __( 'Yesterday', YK_MT_SLUG );
    }

    if( $date === date('Y-m-d', strtotime('+1 day' ) ) ) {
        return __( 'Tomorrow', YK_MT_SLUG );
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
function yk_mt_date_format( $iso_date ) {

    $time = strtotime( $iso_date );

    // TODO: Look up user option to render date
    return date('d/m/Y', $time );
}

/**
 * Display an upgrade button
 */
function yk_mt_upgrade_button( $css_class = '', $link = NULL ) {

    $link = ( false === empty( $link ) ) ? $link : YK_MT_UPGRADE_LINK . '?hash=' . yk_mt_generate_site_hash() ;

    echo sprintf('<a href="%s" class="button-primary sh-cd-upgrade-button%s"><i class="far fa-credit-card"></i> %s £%d %s</a>',
        esc_url( $link ),
        esc_attr( ' ' . $css_class ),
        __( 'Upgrade to Premium for ', YK_MT_SLUG ),
        esc_html( yk_mt_license_price() ),
        __( 'a year ', YK_MT_SLUG )
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

    $features = yk_mt_features_list();

    $html = '';

    if ( false === empty( $features ) ) {

        $class  = '';
        $html   = '<table class="form-table" >';

        foreach ( $features as $title => $description ) {

            if ( false === empty( $title ) ) {

                $class = ('alternate' == $class) ? '' : 'alternate';

                $html .= sprintf( '<tr valign="top" class="%1$s">
                                            <td scope="row" style="padding-left:30px"><label for="tablecell">
                                                    &middot; <strong>%2$s:</strong> %3$s.
                                                </label></td>
                            
                                        </tr>',
                    $class,
                    esc_html( $title ),
                    esc_html( $description )
                );
            }
        }

        $html .= '</table>';
    }
    return $html;
}

/**
 * Return an array of all features
 */
function yk_mt_features_list() {

    return [
                __( 'Create and view entries', YK_MT_SLUG )     => __( 'Allow your users to create and view entries for any day', YK_MT_SLUG ),
                __( 'Edit entries', YK_MT_SLUG )                => __( 'Allow your users to edit their entries for any given day', YK_MT_SLUG ),
                __( 'Edit meals', YK_MT_SLUG )                  => __( 'Allow your users to edit their stored meals', YK_MT_SLUG ),
                __( 'Calorie sources', YK_MT_SLUG )             => __( 'Fetch daily calorie limits from other sources e.g. YeKen\'s Weight Tracker', YK_MT_SLUG ),
                __( 'Compress meal items', YK_MT_SLUG )         => __( 'Compress multiple meal lines for an entry into one line', YK_MT_SLUG ),
                __( 'Unlimited meals per user', YK_MT_SLUG )    => __( 'Your users are no longer limited to a maximum of 40 meals and may add as many as they wish', YK_MT_SLUG ),
                __( '', YK_MT_SLUG )     => __( '', YK_MT_SLUG ),
                __( '', YK_MT_SLUG )     => __( '', YK_MT_SLUG ),
     ];
}

/**
 * HTML for mention of custom work
 */
function yk_mt_custom_notification_html() {
    ?>

    <p><img src="<?php echo plugins_url( 'admin-pages/assets/images/yeken-logo.png', __FILE__ ); ?>" width="100" height="100" style="margin-right:20px" align="left" /><?php echo __( 'If require plugin modifications to Meal Tracker, or need a new plugin built, or perhaps you need a developer to help you with your website then please don\'t hesitate get in touch!', YK_MT_SLUG ); ?></p>
    <p><strong><?php echo __( 'We provide fixed priced quotes.', WE_LS_SLUG); ?></strong></p>
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
        <h3 class="hndle"><span><?php echo __( 'Upgrade Meal Tracker and get more features!', WE_LS_SLUG); ?> </span></h3>
        <div style="padding: 0px 15px 0px 15px">
            <p><?php echo __( 'Upgrade to the Premium version of this plugin to view your user\'s data, record entries for multiple days, extrernal data sources and much more!', WE_LS_SLUG); ?></p>
            <p><a href="<?php echo esc_url( admin_url('admin.php?page=yk-mt-license') ); ?>" class="button-primary"><?php echo __( 'Read more and upgrade to Premium Version', WE_LS_SLUG); ?></a></p>
        </div>
    </div>

    <?php
}

/**
Used to display a jQuery dialog box in the admin panel
*/
function yk_mt_create_dialog_jquery_code( $title, $message, $class_used_to_prompt_confirmation, $js_call = false ) {

    global $wp_scripts;

    $queryui = $wp_scripts->query('jquery-ui-core');

    $url = sprintf( '//ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css', $queryui->ver );

    wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_style('jquery-ui-smoothness', $url, false, null);

    $id_hash = md5($title . $message . $class_used_to_prompt_confirmation );

    printf('<div id="%1$s" title="%2$s">
                        <p>%3$s</p>
                    </div>
                    <script>
                        jQuery( function( $ ) {
                            let $info = $( "#%1$s" );
                            $info.dialog({
                                "dialogClass"   : "wp-dialog",
                                "modal"         : true,
                                "autoOpen"      : false
                            });
                            
                            $( ".%4$s" ).click( function( event ) {
                                event.preventDefault();
                                target_url = $( this ).attr( "href" );
                                let  $info = $( "#%1$s" );
                                $info.dialog({
                                    "dialogClass"   : "wp-dialog",
                                    "modal"         : true,
                                    "autoOpen"      : false,
                                    "closeOnEscape" : true,
                                    "buttons"       : {
                                        "Yes": function() {
                                            %5$s
                                        },
                                        "No": function() {
                                            $(this).dialog( "close" );
                                        }
                                    }
                                });
                                $info.dialog("open");
                            });

                        });
                    </script>',
                    $id_hash,
                    esc_attr( $title ),
                    esc_html( $message ),
                    esc_attr( $class_used_to_prompt_confirmation ),
                    ( true === $js_call ) ? $js_call : 'window.location.href = target_url;'
    );

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
        wp_die( __( 'Error: The user does not appear to exist' , YK_MT_SLUG ) );
    }
}