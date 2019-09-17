<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Pro user?
 * @return bool
 */
function yk_mt_is_pro() {
    return true; //TODO
}

/**
 * Add a CSS class to the body depending on whether we have a Pro license or not
 * @param $classes
 * @return array
 */
function yk_mt_license_body_class( $classes ) {

    $class = ( true === yk_mt_is_pro() ) ? 'pro' : 'not-pro';

    $classes[] = 'yk-mt-' . $class;

    return $classes;
}
add_filter( 'body_class','yk_mt_license_body_class' );