<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Listen for a user saving their settings, if so, do any follow up tasks.
 */
function yk_mt_actions_settings_post_save() {

    // Do we need to consider changing the calories for today's entry based upon the user settings?
    yk_mt_allowed_calories_refresh();

}
add_action( 'yk_mt_settings_saved', 'yk_mt_actions_settings_post_save' );

/**
 * Add a CSS classes to the <body>
 * @param $classes
 * @return array
 */
function yk_mt_body_class( $classes ) {

    if ( true === yk_mt_site_options( 'accordion-enabled' ) ) {
        $classes[] = 'yk-mt-accordion-enabled';
    }

    return $classes;
}
add_filter( 'body_class','yk_mt_body_class' );
