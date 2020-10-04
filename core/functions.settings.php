<?php


/**
 * Fetch a setting for a user
 * @param $key
 * @param null $default
 * @param null $user_id
 *
 * @return |null
 */
function yk_mt_settings_get( $key, $default = NULL, $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $user_settings = yk_mt_db_settings_get( $user_id );

    if ( true === isset( $user_settings[ $key ] ) ) {
        return $user_settings[ $key ];
    }

    return $default ?: NULL;
}

/**
 * Save a user setting
 * @param $key
 * @param $value
 * @param null $user_id
 *
 * @return bool
 */
function yk_mt_settings_set( $key, $value, $user_id = NULL ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    if ( false === in_array( $key, yk_mt_settings_allowed_keys() ) ) {
        return false;
    }

    $user_settings = yk_mt_db_settings_get( $user_id );

    if ( false === is_array( $user_settings ) ) {
        $user_settings = [];
    }

    $user_settings[ $key ] = $value;

    return yk_mt_db_settings_update( $user_id, $user_settings );
}

/**
 * Allowed setting keys
 * @return array
 */
function yk_mt_settings_allowed_keys() {
    return [ 'allowed-calories', 'allowed-calories-admin', 'calorie-source' ];
}

/**
 * Fetch a site option
 * @param $key
 * @param bool $default
 * @return bool|mixed|void
 */
function yk_mt_site_options( $key, $default = true ) {
    return get_option( $key, $default );
}

/**
 * Get a site option as a bool
 * @param $key
 * @param bool $default
 * @return mixed
 */
function yk_mt_site_options_as_bool( $key, $default = true ) {

    $value = yk_mt_site_options( $key, $default );

    return filter_var($value, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Get a site option ready for JS embed
 *
 * @param $key
 *
 * @param bool $default
 *
 * @return bool|string
 */
function yk_mt_site_options_for_js_bool( $key, $default = false ) {
    return ( true === yk_mt_site_options_as_bool( $key, $default ) ) ? 'true' : 'false';
}
