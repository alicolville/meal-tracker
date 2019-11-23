<?php

defined('ABSPATH') or die('Naw ya dinnie!');

/**
 * Depending on the admin page being viewed, embed some data in JS
 */
function yk_mt_admin_localise() {

    // Do we have an entry ID in the URL?
    $entry = yk_mt_querystring_value( 'entry-id', false );

    if ( false !== $entry ) {
        $entry =  yk_mt_entry( $entry );
    }

    wp_localize_script( 'yk-mt-admin', 'yk_mt_sc_meal_tracker', [
        'localise'          => yk_mt_localised_strings(),
        'todays-entry'      => $entry,
        'load-entry'        => ! empty( $entry ),
        'is-admin'          => true
    ]);
}

/**
 * Blur string if incorrect license
 *
 * @param $text
 */
function yk_mt_blur_text( $text, $number_format = true ) {

    if ( true === YK_MT_IS_PREMIUM ) {

        return ( true === $number_format ) ?
                yk_mt_format_number( $text ) :
                        $text;
    }

    $text = str_repeat( '0', strlen( $text ) + 1 );

    return $text;
}
/**
 * Return base URL for user data
 * @return string
 */
function yk_mt_link_user_data() {
    return admin_url( 'admin.php?page=yk-mt-user' );
}
