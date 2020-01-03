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

    $cache = apply_filters( 'yk_mt_db_settings_get', NULL, $user_id );

    if ( true === is_array( $cache ) ) {
        return $cache;
    }

    global $wpdb;

    $sql        = $wpdb->prepare('Select json from ' . $wpdb->prefix . YK_WT_DB_SETTINGS . ' where user_id = %d limit 0, 1', $user_id );
    $settings   = $wpdb->get_var( $sql );
    $settings   = ( false === empty( $settings ) ) ? json_decode( $settings, true ) : [];

    do_action( 'yk_mt_db_settings_lookup', $user_id, $settings );

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

    do_action( 'yk_mt_db_settings_deleted', $user_id );

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

    if ( $cache = apply_filters( 'yk_mt_db_entry_ids_and_dates_get', NULL, $user_id ) ) {
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

    do_action( 'yk_mt_db_entry_ids_and_dates', $user_id, $results );

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

    $cache_key = md5( json_encode( $args ) );

    if ( true === $args[ 'use-cache' ] &&
            $cache = apply_filters( 'yk_mt_db_entries_summary_get', NULL, $cache_key ) ) {
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

    do_action( 'yk_mt_db_entries_summary', $cache_key, $results );

    return $results;
}

/**
 * Get details for an entry
 *
 * @param $key
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

    if ( $cache = apply_filters( 'yk_mt_db_entry_get', NULL, $id ) ) {
        $cache[ 'cache' ] = true;
        return $cache;
    }

    global $wpdb;

    $sql = $wpdb->prepare( 'Select * from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where id = %d limit 0, 1', $id );

    $entry = $wpdb->get_row( $sql, ARRAY_A );

    $entry = ( false === empty( $entry ) ) ? $entry : false;

    // If an entry was found, fetch all the meals entered for it and additional relevant data
    if ( $entry !== false ) {

        $entry = yk_mt_db_entry_calculate_stats( $entry );

        $sql = $wpdb->prepare( 'Select m.id, m.name, m.calories, m.quantity, m.unit, m.description,
                                em.meal_type, em.id as meal_entry_id from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' m 
                                Inner Join ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL . ' em
                                on em.meal_id = m.id
                                where em.entry_id = %d
                                order by meal_type, em.id asc',
                                $id
        );

        $meal_type_ids = yk_mt_meal_types_ids();

        $entry['meals'] = [];
        $entry['counts'] = [];
        $entry['counts']['total-meals'] = 0;

        // Initiate an empty array
        foreach ( $meal_type_ids as $id ) {
            $entry['meals'][ $id ] = [];
            $entry['counts'][ $id ] = 0;
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

                    $entry['meals'][ $meal['meal_type'] ][ $meal['id' ] ][ 'd' ] = sprintf( '%4$s%1$d%2$s / %3$s',
                        $meal[ 'calories' ] * $meal_count,
                        __( 'kcal', YK_MT_SLUG ),
                        yk_mt_get_unit_string( $meal ),
                        ( $meal_count > 1 ) ? $meal_count . ' x ' : ''
                    );

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
    }

    do_action( 'yk_mt_entry_lookup', $id, $entry );

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

    global $wpdb;

    $sql = $wpdb->prepare( 'Select sum( calories ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY_MEAL . ' em 
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

    do_action( 'yk_mt_entry_cache_clear', $entry_meal[ 'entry_id' ], $user_id );

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

    global $wpdb;

    $formats = yk_mt_db_mysql_formats( $meal );

    $result = $wpdb->insert( $wpdb->prefix . YK_WT_DB_MEALS , $meal, $formats );

    if ( false === $result ) {
        return false;
    }

    $id = $wpdb->insert_id;

    do_action( 'yk_mt_meal_added', $id, $meal );
    do_action( 'yk_mt_meals_deleted', $meal[ 'added_by' ] );

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

    do_action( 'yk_mt_meal_updated', $id, $meal );
    do_action( 'yk_mt_meals_deleted', $meal_before[ 'added_by' ] );

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
 * @param $key
 */
function yk_mt_db_meal_get( $id, $added_by = false ) {

    if ( $cache = apply_filters( 'yk_mt_db_meal_get', NULL, $id ) ) {

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

    do_action( 'yk_mt_meal_lookup', $id, $meal );

    return $meal;
}

/**
 * Get meals added by a user
 *
 * @param null $user_id
 * @param array $options
 * @return array|null
 */
function yk_mt_db_meal_for_user( $user_id = NULL, $options  = []  ) {

    $options = wp_parse_args( $options, [
        'exclude-deleted'       => true,
        'sort'                  => 'name',
        'sort-order'            => 'asc',
        'search'                => NULL,
        'limit'                 => NULL,
        'count-only'            => false
    ]);

    $cache_key = md5( json_encode( $options ) );

    $cache = apply_filters( 'yk_mt_db_meals', [], $user_id, $cache_key );

    if ( false === empty( $cache ) ) {
        return $cache;
    }

    global $wpdb;

    $sql = ( true === $options[ 'count-only' ] ) ? 'Select count( id )' : 'select *';

    $sql .= ' from ' . $wpdb->prefix . YK_WT_DB_MEALS . ' where 1=1 ';

    // Restrict to a user?
    if ( false === empty( $user_id ) ) {
        $sql .= sprintf( ' and added_by = %d', $user_id );
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

    do_action( 'yk_mt_meals_lookup', $user_id, $cache_key, $meals );

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
add_action( 'yk_mt_meal_deleted', 'yk_mt_meal_delete_entries' );     // Delete all Meal / Entry relationships when a meal has been deleted

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

    do_action( 'yk_mt_meal_types_added', $id, $meal_type );

    return $id;
}

/**
 * Get all meal types
 *
 * @param $key
 */
function yk_mt_db_meal_types_all() {

    if ( $cache = apply_filters( 'yk_mt_db_meal_types_all', NULL ) ) {
        return $cache;
    }

    global $wpdb;

    $meal_types = $wpdb->get_results( 'Select * from ' . $wpdb->prefix . YK_WT_DB_MEAL_TYPES . ' where deleted = 0 order by sort asc', ARRAY_A );

    $meal_types = ( false === empty( $meal_types ) ) ? $meal_types : false;

    do_action( 'yk_mt_meal_types_all', $meal_types );

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
            $cache = yk_mt_cache_temp_get( 'db-count-' . $table ) ) {
        return $cache;
    }

    $result = $wpdb->get_var( 'Select count( id ) from ' . $wpdb->prefix . $table );

    yk_mt_cache_temp_set( 'db-count-' . $table, $result );

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

    $sql_statements = [
                            'unique-users'          => 'Select count( distinct( user_id ) ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY,
                            'successful-entries'    => 'Select count( id ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where calories_used <= calories_allowed',
                            'failed-entries'        => 'Select count( id ) from ' . $wpdb->prefix . YK_WT_DB_ENTRY . ' where calories_used > calories_allowed'
    ];

    if ( false === array_key_exists( $mode, $sql_statements ) ) {
        return -1;
    }

    if ( true === $use_cache &&
        $cache = yk_mt_cache_temp_get( 'sql-count-' . $mode ) ) {
        return $cache;
    }

    $result = $wpdb->get_var( $sql_statements[ $mode ] );

    yk_mt_cache_temp_set( 'unique-users', $result );

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
        'protein'               => '%f',
        'fat'                   => '%f',
        'carbs'                 => '%f'
    ];

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
                name varchar(60) NOT NULL, 
                calories float DEFAULT 0 NOT NULL,
                protein float DEFAULT 0 NOT NULL,
                fat float DEFAULT 0 NOT NULL,
                carbs float DEFAULT 0 NOT NULL,
                quantity float DEFAULT 0 NOT NULL,
                unit varchar(10) DEFAULT 'g' NOT NULL, 
                description varchar(200) NULL,
                deleted bit DEFAULT 0,
                favourite bit DEFAULT 0,
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