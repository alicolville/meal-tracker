<?php

	defined('ABSPATH') or die("Jog on!");

/**
 * Display login prompt
 *
 * @param null $login_link
 *
 * @return string
 */
function yk_mt_shortcode_log_in_prompt( $login_link = NULL ) {

	if( true === empty( $login_link ) ) {
		$login_link = wp_login_url( get_permalink() );
	}

	return sprintf( '<p class="yk-mt-need-logged-in">%s. <a href="%s">%s</a>.</p>',
		esc_html__( 'You must be logged in to view and log meal entries', 'meal-tracker' ),
		esc_url( $login_link ),
		esc_html__( 'Log in to your account', 'meal-tracker' )
	);
}

