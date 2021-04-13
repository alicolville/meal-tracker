<?php

defined('ABSPATH') or die("Jog on!");

define( 'YK_WT_DB_MEALS', 'yk_mt_meals');                   // Store all meal types
define( 'YK_WT_DB_ENTRY', 'yk_mt_entry');                   // Store all entries for the given user
define( 'YK_WT_DB_ENTRY_MEAL', 'yk_mt_entry_meals');        // Store all meals for given entry
define( 'YK_WT_DB_MEAL_TYPES', 'yk_mt_meal_types');         // Store all meal types
define( 'YK_WT_DB_SETTINGS', 'yk_mt_settings');             // Store all settings for the given user

/**
 * Get settings
 *
 * @param $key
 */
function yk_mt_db_settings_get( $user_id ) {

    $cache = yk_mt_cache_user_get( $user_id, 'settings' );

    if ( true === is_array( $cache ) ) {
        return $cache;
    }

    global $wpdb;

    $sql        = $wpdb->prepare('Select json from ' . $wpdb->prefix . YK_WT_DB_SETTINGS . ' where user_id = %d limit 0, 1', $user_id );
    $settings   = $wpdb->get_var( $sql );
    $settings   = ( false === empty( $settings ) ) ? json_decode( $settings, true ) : [];

    yk_mt_cache_user_set( $user_id, 'settings', $settings );

    return $settings;
}

/**
 * Add settings for the user
 * @param $user_id
 * @param array $settings
 *
 * @return bool
 */
function yk_mt_db_settings_update( $user_id, $settings = [] ) {

    global $wpdb;

    $settings = ( true === is_array( $settings ) ) ? json_encode( $settings ) : [];

    $result = $wpdb->replace(   $wpdb->prefix . YK_WT_DB_SETTINGS ,
                                [ 'user_id' => $user_id, 'json' => $settings ],
                                [ '%d', '%s' ]
    );

    if ( false === $result ) {
        return false;
    }

	yk_mt_cache_user_delete( $user_id );

    return true;
}

/**
 * Add an entry
 *
 * @param $entry
 *
 * @return bool     true if success
 */
function yk_mt_db_entry_add( $entry ) {

    // Ensure we have the expected fields.
    if ( false === yk_mt_array_check_fields( $entry, [ 'user_id', 'calories_allowed', 'calories_used', 'date' ] ) ) {
        return false;
    }

    unset( $entry[ 'id' ] );

    // If an invalid ISO date, force to today's date
    if ( false === yk_mt_date_is_valid_iso( $entry['date'] ) ) {
        $entry['date'] = yk_mt_date_iso_today();
    }

    global $wpdb;

    $formats = yk_mt_db_mysql_formats( $entry );

    $result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_ENTRY , $entry, $formats );

    if ( false === $result ) {
        return false;
    }

    $id = $wpdb->insert_id;

    $entry = yk_mt_db_entry_calculate_stats( $entry );

	yk_mt_cache_delete( 'entry-' . $id, $entry );

    do_action( 'yk_mt_entry_added', $id, $entry, $entry[ 'user_id' ] );

    return $id;
}

/**
 *
 * Update an entry
 *
 * @param $entry
 *
 * @return bool     true if success
 */
function yk_mt_db_entry_update( $entry ) {

    if ( false === yk_mt_array_check_fields( $entry, [ 'id' ] ) ) {
        return false;
    }

    $id = $entry[ 'id' ];

    unset( $entry[ 'id' ] );

    global $wpdb;

    $formats = yk_mt_db_mysql_formats( $entry );

    $result = $wpdb->update( $wpdb->prefix . YK_WT_DB_ENTRY, $entry, [ 'id' => $id ], $formats, [ '%d' ] );

    if ( false === $result ) {
        return false;
    }

	yk_mt_cache_delete( 'entry-' . $id, $entry );

    do_action( 'yk_mt_entry_updated', $id, $entry );

    return true;
}

/**
 * Delete an entry
 *
 * @param $id       entry ID to delete
 * @return bool     true if success
 */
function yk_mt_db_entry_delete( $id ) {

    global $wpdb;

    $user_id = yk_mt_db_entry_user_id( $id );

    do_action( 'yk_mt_entry_deleting', $id );

    $result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY, [ 'id' => $id ], [ '%d' ] );

    if ( 1 !== $result ) {
        return false;
    }

    do_action( 'yk_mt_entry_deleted', $id, $user_id );

    return true;
}

/**
 * Delete all entries / meals relationships when an entry is deleted
 *
 * @param $meal_id
 * @return bool
 */
function yk_mt_db_entry_delete_entries( $entry_id ) {

    global $wpdb;

    $result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, [ 'entry_id' => $entry_id ], [ '%d' ] );

    return ( 1 === $result );

}
add_action( 'yk_mt_entry_deleted', 'yk_mt_entry_delete_entries' );     // Delete all Meal / Entry relationships when a meal has been deleted

/**
 * Get Entry ID
 *
 * @param null $user_id
 *
 * @return null|string
 */
function yk_mt_db_entry_get_id_for_today( $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $todays_date = yk_mt_date_iso_today();

    global $wpdb;

    $sql = $wpdb->prepare( 'Select id from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where user_id = %d and date = %s', $user_id, $todays_date );

    $result = $wpdb->get_var( $sql );

    return ( false === empty( $result ) ) ? (int) $result : NULL ;
}

/**
 * Return User ID for entry ID
 * @param $entry_id
 *
 * @return mixed
 */
function yk_mt_db_entry_user_id( $entry_id ) {

    global $wpdb;

    $sql = $wpdb->prepare( 'Select user_id from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where id = %d', $entry_id );

    return $wpdb->get_var( $sql );
}

/**
 * Get Entry IDs and Dates for a user
 *
 * @param null $user_id
 *
 * @return null|string
 */
function yk_mt_db_entry_get_ids_and_dates( $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    if ( $cache = yk_mt_cache_user_get( $user_id, 'entry-ids-dates' ) ) {
        return $cache;
    }

    global $wpdb;

    $sql = $wpdb->prepare( 'Select id, date from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where user_id = %d order by date asc', $user_id );

    $results = $wpdb->get_results( $sql, ARRAY_A );

    if ( false === empty( $results ) ) {
        $results = wp_list_pluck( $results, 'date', 'id' );
    } else {
        $results = [];
    }

	yk_mt_cache_user_set( $user_id, 'entry-ids-dates', $results );

    return $results;
}

/**
 * Get summary data for user's entries
 *
 * @param $args
 * @return null|string
 */
function yk_mt_db_entries_summary( $args ) {

    $args = wp_parse_args( $args, [
        'user-id'       => NULL,
        'last-x-days'   => NULL,
        'limit'         => NULL,
        'sort-order'    => 'asc',
        'sort'          => 'date',
        'use-cache'     => true
    ]);

    $cache_key = 'entry-summary-' . md5( json_encode( $args ) );

    if ( true === $args[ 'use-cache' ] &&
            $cache = yk_mt_cache_user_get( $args[ 'user-id' ], $cache_key ) ) {
        return $cache;
    }

    global $wpdb;

    $sql = 'Select id, user_id, calories_allowed, calories_used, date from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where 1=1 ';

    if ( false === empty( $args[ 'user-id' ] ) ) {
        $sql .= sprintf( ' and user_id = %d', $args[ 'user-id' ] );
    }

    if ( false === empty( $args[ 'last-x-days' ] ) ) {
        $sql .= sprintf( ' and date >= NOW() - INTERVAL %d DAY and date <= NOW()', $args[ 'last-x-days' ] );
    }

    $sort = ( true === in_array( $args[ 'sort' ], [ 'date', 'calories_allowed', 'calories_used' ] ) ) ?  $args[ 'sort' ] : 'date';

    $sort_order = ( true === in_array( $args[ 'sort-order' ], [ 'asc', 'desc' ] ) ) ? $args[ 'sort-order' ] : 'asc';

    $sql .= sprintf( ' order by %s %s', $sort, $sort_order );

    // Limit
    if ( false === empty( $args[ 'limit' ] ) ) {
        $sql .= sprintf( ' limit 0, %d', $args[ 'limit' ] ) ;
    }

    $results = $wpdb->get_results( $sql, ARRAY_A );

    if ( false === empty( $results ) ) {
        $results = array_map( 'yk_mt_db_entry_calculate_stats', $results );
    }

	yk_mt_cache_user_set( $args[ 'user-id' ], $cache_key, $results );

    return $results;
}

/**
 * Get details for an entry
 *
 * @param null $id
 * @return array|bool|mixed|object|void|null
 */
function yk_mt_db_entry_get( $id = NULL ) {

    // If this is never the case, we need to deal with this value being true or false in caching.
    $compress_multiple_meals = yk_mt_license_is_premium();

    if ( NULL === $id ) {
        $id = yk_mt_db_entry_get_id_for_today();
    }

    if ( true === empty( $id ) ) {
        return NULL;
    }

    $entry_id = $id;

    if ( $cache = yk_mt_cache_get( 'entry-' . $entry_id ) ) {
    	$cache[ 'cache' ] = true;
        return $cache;
    }

    global $wpdb;

    $sql = $wpdb->prepare( 'Select * from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where id = %d limit 0, 1', $id );

    $entry = $wpdb->get_row( $sql, ARRAY_A );

    $entry = ( false === empty( $entry ) ) ? $entry : false;

    // If an entry was found, fetch all the meals entered for it and additional relevant data
    if ( $entry !== false ) {

        $entry		 = yk_mt_db_entry_calculate_stats( $entry );
		$meta_sql	 = '';

		// Which meta fields do we want to display on a line
		$meta_per_line 	= yk_mt_meta_fields_where( 'visible_user', true );

		if ( false === empty( $meta_per_line ) ) {
			$meta_sql = ' , m.' . implode( ' , m.', wp_list_pluck( $meta_per_line, 'db_col' ) );
		}

        $sql = $wpdb->prepare( 'Select m.id, m.name, m.calories, m.quantity, m.unit, m.description, m.added_by_admin, m.added_by' . $meta_sql . ',
								em.meal_type, em.id as meal_entry_id from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' m
                                Inner Join ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL . ' em
                                on em.meal_id = m.id
                                where em.entry_id = %d
                                order by meal_type, em.id asc',
                                $id
        );
		//echo $sql;
        $meal_type_ids = yk_mt_meal_types_ids();

		$meta_to_total 	= ( true === YK_MT_IS_PREMIUM ) ? yk_mt_meta_fields_where( 'total-these', true ) : [];

        $entry['meals'] = [];
        $entry['counts'] = [];
        $entry['counts']['total-meals'] = 0;

        // Initiate an empty array
        foreach ( $meal_type_ids as $id ) {
            $entry['meals'][ $id ] = [];
            $entry['counts'][ $id ] = 0;

            foreach ( $meta_to_total as $meta ) {
				$entry['meta_counts'][ $id ][ $meta[ 'db_col' ] ] = 0;
			}
        }

        $meals = $wpdb->get_results( $sql, ARRAY_A );

        if ( false === empty( $meals ) ) {

            foreach ( $meals as $meal ) {

                $entry['counts']['total-meals']++;

                // Compress meals that are the same into one row?
                if ( true === $compress_multiple_meals ) {

                    // Does the meal need to be initially added to the array for this meal type?
                    if ( true === empty(  $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ] ) ) {
                        $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ] = $meal;
                    }

                    $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'entry_meal_ids' ][] = $meal[ 'meal_entry_id' ];

                    $meal_count = count( $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'entry_meal_ids' ] );

                    $meta_detail = '';

	                $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ] =
		                                                yk_mt_meal_prep_for_display( $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ] );

                    // Do we have any meta fields to add to this line item?
					if ( false === empty( $meta_per_line ) ) {
						foreach ( $meta_per_line as $meta ) {
							$meta_detail .= sprintf( ' <span><em>%s</em>: %s%s</span>', $meta[ 'prefix' ], yk_mt_format_number( $meal[ $meta[ 'db_col'] ] * $meal_count ), $meta[ 'unit' ] );
						}
					}

					foreach ( $meta_to_total as $meta ) {
						$entry['meta_counts'][ $meal['meal_type'] ][ $meta[ 'db_col' ] ] += $meal[ $meta[ 'db_col'] ];
					}

                    $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'd' ] = sprintf( '%5$s%1$d%2$s%3$s <span><em>%6$s</em>: %4$s</span>',
                        $meal[ 'calories' ] * $meal_count,
                        __( 'kcal', YK_MT_SLUG ),
						$meta_detail,
                        yk_mt_get_unit_string( $meal ),
                        ( $meal_count > 1 ) ? $meal_count . ' x ' : '',
						__( 's', YK_MT_SLUG )
                    );

	                $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'css_class' ] = '';

                    // Hide edit button if the meal was added by admin
	                if ( false === empty( $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'added_by_admin' ] ) ) {
		                $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'css_class' ] = 'yk-mt-hide';
	                }

	                // Hide edit button if the meal was added by another user
	                if ( $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'added_by' ] <> get_current_user_id() ) {
		                $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'css_class' ] = 'yk-mt-hide';
	                }

                } else {

                    // Note: This ELSE at the moment shall never be hit. Left in for now just in case!

                    $meal[ 'd' ] = sprintf( '%d%s / %s',
                        $meal[ 'calories' ],
                        __( 'kcal', YK_MT_SLUG ),
                        yk_mt_get_unit_string( $meal )
                    );

                    $entry['meals'][ $meal['meal_type'] ][ $meal[ 'meal_entry_id' ] ] = $meal;
                }

                // Update calorie count for meal type
                $entry['counts'][ $meal['meal_type'] ] += $meal['calories'];
            }

            // Remove keys from meal arrays (saves extra effort in jQuery)
            foreach ( $entry[ 'meals' ] as &$meal_types ) {
                $meal_types = array_values( $meal_types );
            }
        }

		// Update meta summary
		foreach ( $meal_type_ids as $meal_type_id ) {

			$entry[ 'meta_counts' ][ $meal_type_id ][ 'summary' ] = '';
			$meta_detail								= '';

			foreach ( $meta_to_total as $meta ) {

				$meta_detail .= sprintf( ' <span><em>%s</em>: %s%s</span>', $meta[ 'prefix' ], yk_mt_format_number( $entry[ 'meta_counts' ][ $meal_type_id ][ $meta[ 'db_col'] ] ), $meta[ 'unit' ] );

				$entry[ 'meta_counts' ][ $meal_type_id ][ 'summary' ] = $meta_detail;
			}
		}
    }

	yk_mt_cache_set( 'entry-' . $entry_id, $entry );

    return $entry;
}

/**
 * Calculate additional data for an entry
 *
 * @param $entry
 *
 * @return mixed
 */
function yk_mt_db_entry_calculate_stats( $entry ) {

    if ( true === isset( $entry[ 'calories_allowed' ], $entry[ 'calories_used' ] ) ) {

        $entry[ 'percentage_used' ] = ( 0 !== (int) $entry[ 'calories_allowed' ] ) ? ( $entry[ 'calories_used' ] / $entry[ 'calories_allowed' ] ) * 100 : 0;
        $entry[ 'percentage_used' ] = round( $entry[ 'percentage_used' ], 1);

        $entry[ 'calories_remaining' ] = $entry[ 'calories_allowed' ] - $entry[ 'calories_used' ];
        $entry[ 'calories_remaining' ] = ( $entry[ 'calories_remaining' ] < 0 ) ? 0 : $entry[ 'calories_remaining' ];

    }

    return $entry;
}

/**
 * Count calories for given entry
 *
 * @param $entry_id
 *
 * @return null|string
 */
function yk_mt_db_entry_calories_count( $entry_id ) {

    return yk_mt_db_entry_sum_int_column( $entry_id );
}

/**
 * Count an integer columnn for given entry
 *
 * @param $entry_id
 *
 * @param string $column
 * @return null|string
 */
function yk_mt_db_entry_sum_int_column( $entry_id, $column = 'calories' ) {

	global $wpdb;

	$sql = $wpdb->prepare( 'Select sum( ' . $column . ' ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL . ' em
            inner join ' . $wpdb->prefix . YK_WT_DB_MEALS . ' m
            on em.meal_id = m.id where entry_id = %d', $entry_id );

	return $wpdb->get_var( $sql );
}

/**
 * Add an entry / meal relationship
 *
 * @param $entry_meal
 *
 * @return bool     true if success
 */
function yk_mt_db_entry_meal_add( $entry_meal ) {

    // Ensure we have the expected fields.
    if ( false === yk_mt_array_check_fields( $entry_meal, [ 'meal_type', 'meal_id', 'entry_id' ] ) ) {
        return false;
    }

    unset( $entry_meal[ 'id' ] );

    global $wpdb;

    $formats = yk_mt_db_mysql_formats( $entry_meal );

    $result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL , $entry_meal, $formats );

    if ( false === $result ) {
        return false;
    }

    $id = $wpdb->insert_id;

    do_action( 'yk_mt_entry_meal_added', $id, $entry_meal );

    $user_id = yk_mt_db_entry_user_id( $entry_meal[ 'entry_id' ] );

	yk_mt_cache_delete( 'entry-' . $entry_meal[ 'entry_id' ] );

    return $id;
}

/**
 * Get details for an entry_meal
 *
 * @param $key
 */
function yk_mt_db_entry_meal_get( $id ) {

    global $wpdb;

    $sql = $wpdb->prepare('Select * from ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL . ' where id = %d limit 0, 1', $id );

    $entry_meal = $wpdb->get_row( $sql, ARRAY_A );

    return ( false === empty( $entry_meal ) ) ? $entry_meal : false;
}

/**
 *
 * Update an entry / meal
 *
 * @param $entry
 *
 * @return bool     true if success
 */
function yk_mt_db_entry_meal_update( $entry_meal ) {

    if ( false === yk_mt_array_check_fields( $entry_meal, [ 'id', 'meal_type', 'meal_id', 'entry_id' ] ) ) {
        return false;
    }

    $id = $entry_meal[ 'id' ];

    unset( $entry_meal[ 'id' ] );

    global $wpdb;

    $formats = yk_mt_db_mysql_formats( $entry_meal );

    $result = $wpdb->update( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, $entry_meal, [ 'id' => $id ], $formats, [ '%d' ] );

    if ( false === $result ) {
        return false;
    }

    do_action( 'yk_mt_entry_meal_updated', $id, $entry_meal );

    return true;
}

/**
 * Delete an entry / meal
 *
 * @param $id       entry ID to delete
 * @return bool     true if success
 */
function yk_mt_db_entry_meal_delete( $id ) {

    global $wpdb;

    do_action( 'yk_mt_entry_meal_deleting', $id );

    $result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, [ 'id' => $id ], [ '%d' ] );

    if ( 1 !== $result ) {
        return false;
    }

    do_action( 'yk_mt_entry_meal_deleted', $id );

    return true;
}

/**
 * Add a meal
 *
 * @param $meal
 *
 * @return bool     true if success
 */
function yk_mt_db_meal_add( $meal ) {

    // Ensure we have the expected fields.
    if ( false === yk_mt_array_check_fields( $meal, [ 'added_by', 'name', 'calories', 'quantity' ] ) ) {
        return false;
    }

    unset( $meal[ 'id' ] );
	unset( $meal[ 'hide-nutrition' ] );
	unset( $meal[ 'servings' ] );
	unset( $meal[ 'added' ] );

    global $wpdb;

    $formats = yk_mt_db_mysql_formats( $meal );

    $result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_MEALS , $meal, $formats );

    if ( false === $result ) {
        return false;
    }

    $id = $wpdb->insert_id;

	yk_mt_cache_set( 'meal-' . $id, $meal );

    do_action( 'yk_mt_meals_updated', $meal[ 'added_by' ] );

    return $id;
}

/**
 *
 * Update a meal
 *
 * @param $meal
 *
 * @return bool     true if success
 */
function yk_mt_db_meal_update( $meal ) {

    if ( false === yk_mt_array_check_fields( $meal, [ 'id' ] ) ) {
        return false;
    }

    $id = $meal[ 'id' ];

    $meal_before = yk_mt_db_meal_get(  $id );

    unset( $meal[ 'id' ] );

    global $wpdb;

    $formats = yk_mt_db_mysql_formats( $meal );

    $result = $wpdb->update( $wpdb->prefix . YK_WT_DB_MEALS, $meal, [ 'id' => $id ], $formats, [ '%d' ] );

    if ( false === $result ) {
        return false;
    }

	yk_mt_cache_set( 'meal-' . $id, $meal );

    do_action( 'yk_mt_meals_updated', $meal_before[ 'added_by' ] );

    return true;
}

/**
 * Delete a meal
 *
 * @param $id       meal ID to delete
 * @return bool     true if success
 */
function yk_mt_db_meal_delete( $id ) {

    global $wpdb;

    do_action( 'yk_mt_meal_deleting', $id );

    $meal_before_delete = yk_mt_db_meal_get(  $id );

    $result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_MEALS, [ 'id' => $id ], [ '%d' ] );

    if ( 1 !== $result ) {
        return false;
    }

    do_action( 'yk_mt_meal_deleted', $id );
    do_action( 'yk_mt_meals_deleted', $meal_before_delete[ 'added_by' ] );

    return true;
}

/**
 * Get details for a meal
 *
 * @param $id
 * @param bool $added_by
 *
 * @return array|bool|mixed|object|void
 */
function yk_mt_db_meal_get( $id, $added_by = false ) {

    if ( $cache = yk_mt_cache_get( 'meal-' . $id ) ) {

        // Ensure, if user ID specified, that we are only letting cache through for that user
        if ( false !== $added_by && (int) $cache[ 'added_by' ] !== (int) $added_by ) {
            return false;
        }

        return $cache;
    }

    global $wpdb;

    if ( false !== $added_by ) {
        $sql = $wpdb->prepare(  'Select * from ' . $wpdb->prefix . YK_WT_DB_MEALS .
                                ' where id = %d and added_by = %d limit 0, 1', $id, $added_by );
    } else {
        $sql = $wpdb->prepare('Select * from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where id = %d limit 0, 1', $id );
    }

    $meal = $wpdb->get_row( $sql, ARRAY_A );

    $meal = ( false === empty( $meal ) ) ? $meal : false;

	yk_mt_cache_set( 'meal-' . $id, $meal );

    return $meal;
}

/**
 * Get meal ID for a fractioned meal
 *
 * @param $meal_id
 * @param $fraction
 * @param bool $added_by
 * @return int
 */
function yk_mt_db_meal_fraction_exist( $meal_id, $fraction, $added_by = false ) {

	global $wpdb;

	$sql 	= $wpdb->prepare(  'Select id from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where fraction_parent = %d and fraction = %f', $meal_id, $fraction );

	if ( false === empty( $added_by ) ) {
		$sql .= ' and added_by = ' . (int) $added_by;
	}

	$sql    .= ' limit 0, 1';
	$id 	= $wpdb->get_var( $sql );

	return ( false === empty( $id ) ) ? (int) $id : null;
}
/**
 * Get internal meal ID for external meal
 *
 * @param $ext_id
 * @param null $serving_id
 * @param bool $added_by
 *
 * @return int
 */
function yk_mt_db_ext_meal_exist( $ext_id, $serving_id = NULL, $added_by = false ) {

	global $wpdb;

	$sql 	= $wpdb->prepare(  'Select id from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where ext_id = %d and added_by = %d', $ext_id, $added_by );

	if ( false === empty( $serving_id ) ) {
		$sql .= ' and ext_serving_id = ' . (int) $serving_id;
	}

	$sql    .= ' limit 0, 1';
	$id 	= $wpdb->get_var( $sql );

	return ( false === empty( $id ) ) ? (int) $id : null;
}

/**
 * Get meals added by a user
 *
 * @param null $user_id
 * @param array $options
 * @return array|null
 */
function yk_mt_db_meal_for_user( $user_id = NULL, $options = []  ) {

    $options = wp_parse_args( $options, [
        'exclude-deleted'       	=> true,
        'sort'                  	=> 'name',
        'sort-order'            	=> 'asc',
        'search'                	=> NULL,
        'limit'                 	=> NULL,
        'count-only'            	=> false,
	    'admin-meals-only'      	=> false,
		'include-admin-meals'  		=> false,
	    'use-cache'             	=> true,
	    'last-x-days'          	 	=> NULL
    ]);

    $cache_key = md5( json_encode( $options ) );

    $cache = yk_mt_cache_user_get( $user_id, $cache_key );

    if ( false === empty( $cache ) &&
            true === $options[ 'use-cache' ] ) {
    	return $cache;
    }

    global $wpdb;

    $sql = ( true === $options[ 'count-only' ] ) ? 'Select count( id )' : 'select *';

    $sql .= ' from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where 1=1 ';

    // Restrict to a user?
    if ( false === empty( $user_id ) ) {

    	if ( false === $options[ 'include-admin-meals' ] ) {
		    $sql .= sprintf( ' and ( added_by = %d and added_by_admin is null )', $user_id );
	    } else {
		    $sql .= sprintf( ' and ( added_by = %d or added_by_admin = 1 )', $user_id );
	    }

    }

    // Admin only meals?
    if ( true === $options[ 'admin-meals-only' ] ) {
	    $sql .= ' and added_by_admin = 1';
    }

	if ( false === empty( $options[ 'last-x-days' ] ) ) {
		$sql .= sprintf( ' and added >= NOW() - INTERVAL %d DAY and added <= NOW()', $options[ 'last-x-days' ] );
	}

    // Exclude deleted?
    if ( true === $options[ 'exclude-deleted' ] ) {
        $sql .= ' and deleted = 0';
    }

    if ( true === $options[ 'count-only' ] ) {
        $meals = $wpdb->get_var( $sql );
    } else {

        // Search Name?
        if ( false === empty( $options[ 'search' ] ) ) {
            $name = '%' . $wpdb->esc_like( $options[ 'search' ] ) . '%';
            $sql .= ' and `name` like "' . $name . '"';
        }

        $sort = ( true === in_array( $options[ 'sort' ], [ 'name', 'calories' ] ) ) ?  $options[ 'sort' ] : 'name';

        $sort_order = ( true === in_array( $options[ 'sort-order' ], [ 'asc', 'desc' ] ) ) ? $options[ 'sort-order' ] : 'asc';

        $sql .= sprintf( ' order by %s %s', $sort, $sort_order );

        // Limit
        if ( false === empty( $options[ 'limit' ] ) ) {
            $sql .= sprintf( ' limit 0, %d', $options[ 'limit' ] ) ;
        }

        $meals = $wpdb->get_results( $sql, ARRAY_A );
    }

    $meals = ( false === empty( $meals ) ) ? $meals : false;

	yk_mt_cache_user_set( $user_id, $cache_key, $meals );

    return $meals;
}

/**
 * Delete all entries / meals relationships when a meal is deleted
 *
 * @param $meal_id
 * @return bool
 */
function yk_mt_db_meal_delete_entries( $meal_id ) {

    global $wpdb;

    $result = $wpdb->delete( $wpdb->prefix . YK_WT_DB_ENTRY_MEAL, [ 'meal_id' => $meal_id ], [ '%d' ] );

    return ( 1 === $result );

}
add_action( 'yk_mt_meal_deleted', 'yk_mt_db_meal_delete_entries' );     // Delete all Meal / Entry relationships when a meal has been deleted

/**
 * Add a meal type
 *
 * @param $meal
 *
 * @return bool     true if success
 */
function yk_mt_db_meal_types_add( $meal_type ) {

    // Ensure we have the expected fields.
    if ( false === yk_mt_array_check_fields( $meal_type, [ 'name', 'sort'  ] ) ) {
        return false;
    }

    unset( $meal_type[ 'id' ] );

    global $wpdb;

    $formats = yk_mt_db_mysql_formats( $meal_type );

    $result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_MEAL_TYPES , $meal_type, $formats );

    if ( false === $result ) {
        return false;
    }

    $id = $wpdb->insert_id;

	yk_mt_cache_delete( 'meal-types' );

    return $id;
}

/**
 * Get all meal types
 *
 * @param bool $use_cache
 * @return array|bool|mixed|object|void
 */
function yk_mt_db_meal_types_all( $use_cache = true) {

	$cache = ( true === $use_cache ) ? yk_mt_cache_get( 'meal-types' ) : NULL;

    if ( false === empty( $cache ) ) {
        return $cache;
    }

    global $wpdb;

    $meal_types = $wpdb->get_results( 'Select * from ' . $wpdb->prefix . YK_WT_DB_MEAL_TYPES . ' where deleted = 0 order by sort asc', ARRAY_A );

    $meal_types = ( false === empty( $meal_types ) ) ? $meal_types : false;

	yk_mt_cache_set( 'meal-types', $meal_types );

    return $meal_types;
}

/**
 * @param null $table
 *
 * @param bool $use_cache
 * @return null|string
 */
function yk_mt_db_mysql_count_table( $table = YK_WT_DB_ENTRY, $use_cache = true ) {

    global $wpdb;

    if ( false === in_array( $table, [ YK_WT_DB_MEALS, YK_WT_DB_ENTRY, YK_WT_DB_ENTRY_MEAL, 'users' ] ) ) {
        $table = YK_WT_DB_MEALS;
    }

    if ( true === $use_cache &&
            $cache = yk_mt_cache_get( 'db-count-' . $table ) ) {
        return $cache;
    }

    $result = $wpdb->get_var( 'Select count( id ) from ' . $wpdb->prefix . $table );

    yk_mt_cache_set( 'db-count-' . $table, $result );

    return (int) $result;
}

/**
* Fetch the count of users that have made an entry
* @param string $mode
* @param bool $use_cache
* @return int|mixed
*/
function yk_mt_db_mysql_count( $mode = 'unique-users', $use_cache = true ) {

    global $wpdb;

    $sql_statements = [     'meals-user'            => 'Select count( id ) from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where added_by_admin is null',
                            'meals-admin'           => 'Select count( id ) from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where added_by_admin = 1',
                            'unique-users'          => 'Select count( distinct( user_id ) ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY,
                            'successful-entries'    => 'Select count( id ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where calories_used <= calories_allowed',
                            'failed-entries'        => 'Select count( id ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where calories_used > calories_allowed'
    ];

    if ( false === array_key_exists( $mode, $sql_statements ) ) {
        return -1;
    }

    if ( true === $use_cache &&
        $cache = yk_mt_cache_get( 'sql-count-' . $mode ) ) {
        return $cache;
    }

    $result = $wpdb->get_var( $sql_statements[ $mode ] );

	yk_mt_cache_set( 'unique-users', $result );

    return (int) $result;
}

/**
 * Return data formats
 *
 * @param $data
 * @return array
 */
function yk_mt_db_mysql_formats( $data ) {

    $formats = [
        'id'                    => '%d',
        'name'                  => '%s',
        'added_by'              => '%d',
        'added_by_admin'        => '%d',
        'entry_id'              => '%d',
        'gain_loss'             => '%s',
        'calories'              => '%f',
        'quantity'              => '%f',
        'description'           => '%s',
        'user_id'               => '%d',
        'calories_allowed'      => '%f',
        'calories_used'         => '%f',
        'meal_type'             => '%d',
        'meal_id'               => '%d',
        'date'                  => '%s',
        'value'                 => '%s',
        'deleted'               => '%d',
        'favourite'             => '%d',
        'unit'                  => '%s',
        'json'                  => '%s',
		'source'				=> '%s',
		'ext_id'				=> '%d',
		'ext_serving_id'		=> '%d',
		'ext_image'				=> '%s',
		'ext_url'				=> '%s',
		'fraction_parent'		=> '%d',
		'fraction'				=> '%f'
    ];

    $formats = apply_filters( 'yk_mt_db_formats', $formats );

    $return = [];

    foreach ( $data as $key => $value) {
        if ( false === empty( $formats[ $key ] ) ) {
            $return[] = $formats[ $key ];
        }
    }

    return $return;
}

/**
 *  Build the relevant database tables
 */
function yk_wt_db_tables_create() {

    global $wpdb;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // -------------------------------------------------
    // Meals
    // -------------------------------------------------

    $table_name = $wpdb->prefix . YK_WT_DB_MEALS;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                added_by int NOT NULL,
                added_by_admin int NULL,
                name varchar(100) NOT NULL,
                calories float DEFAULT 0 NOT NULL,
                quantity float DEFAULT 0 NOT NULL,
                fraction_parent int NULL,
                fraction float DEFAULT 0 NOT NULL,
                unit varchar(10) DEFAULT 'g' NOT NULL,
                description varchar(200) NULL,
                deleted bit DEFAULT 0,
                favourite bit DEFAULT 0,
                source varchar(20) DEFAULT 'user' NOT NULL,
                ext_id int NULL,
                ext_serving_id int NULL,
                ext_image varchar( 300 ) NULL,
                ext_url varchar( 300 ) NULL,
                imported_csv bit DEFAULT 0,
                added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
             UNIQUE KEY id (id)
            ) $charset_collate;";

    dbDelta( $sql );

    // -------------------------------------------------
    // Daily Entry
    // -------------------------------------------------

    $table_name = $wpdb->prefix . YK_WT_DB_ENTRY;

    $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id int NOT NULL,
                calories_allowed float DEFAULT 0 NOT NULL,
                calories_used float DEFAULT 0 NOT NULL,
                date DATE NOT NULL,
              UNIQUE KEY id (id)
            ) $charset_collate;";

    dbDelta( $sql );

    // -------------------------------------------------
    // Meals for Daily Entry
    // -------------------------------------------------

    $table_name = $wpdb->prefix . YK_WT_DB_ENTRY_MEAL;

    $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                meal_type int NOT NULL,
                meal_id int NOT NULL,
                entry_id int NOT NULL,
              UNIQUE KEY id (id)
            ) $charset_collate;";

    dbDelta( $sql );

    // -------------------------------------------------
    // Store Meal Types
    // -------------------------------------------------

    $table_name = $wpdb->prefix . YK_WT_DB_MEAL_TYPES;

    $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                name varchar(60) NOT NULL,
                sort int DEFAULT 100 NOT NULL,
                deleted bit DEFAULT 0,
              UNIQUE KEY id (id)
            ) $charset_collate;";

    dbDelta( $sql );

    // -------------------------------------------------
    // Settings
    // -------------------------------------------------

    $table_name = $wpdb->prefix . YK_WT_DB_SETTINGS;

    $sql = "CREATE TABLE $table_name (
                user_id int NOT NULL,
                json varchar(2000) NOT NULL,
              UNIQUE KEY user_id (user_id)
            ) $charset_collate;";

    dbDelta( $sql );

}
