<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 * Display login prompt
	 *
	 * @return string
	 */
	function yk_mt_shortcode_log_in_prompt() {

		$login_link = wp_login_url( get_permalink() );

		return sprintf( '<p class="yk-mt-need-logged-in">%s <a href="%s">%s</a>.</p>',
			__( 'You must be logged in to record and view meal entries for today.', YK_MT_SLUG ),
			esc_url( $login_link ),
			__( 'Log in to your account', YK_MT_SLUG )
		);
	}

