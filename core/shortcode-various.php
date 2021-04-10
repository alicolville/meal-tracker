<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Render shortcode [mt-date-latest-entry]
 */
function yk_mt_shortcode_date_latest_entry( $user_defined_arguments ) {

	if ( false === YK_MT_IS_PREMIUM ) {
		return yk_mt_display_premium_upgrade_notice_for_shortcode();
	}

	$shortcode_arguments = shortcode_atts( [    'user-id'           => get_current_user_id(),
	                                            'text-no-entries'   => ''
	], $user_defined_arguments );

	$stats = yk_mt_user_stats( $shortcode_arguments[ 'user-id' ] );

	return ( false === empty( $stats[ 'date-last' ] ) ) ?
			yk_mt_date_format( $stats[ 'date-last' ] ) :
				esc_html( $shortcode_arguments[ 'text-no-entries' ] );
}
add_shortcode( 'mt-date-latest-entry', 'yk_mt_shortcode_date_latest_entry' );

/**
 * Render shortcode [mt-date-oldest-entry]
 */
function yk_mt_shortcode_date_oldest_entry( $user_defined_arguments ) {

	if ( false === YK_MT_IS_PREMIUM ) {
		return yk_mt_display_premium_upgrade_notice_for_shortcode();
	}

	$shortcode_arguments = shortcode_atts( [    'user-id'           => get_current_user_id(),
	                                            'text-no-entries'   => ''
	], $user_defined_arguments );

	$stats = yk_mt_user_stats( $shortcode_arguments[ 'user-id' ] );

	return ( false === empty( $stats[ 'date-first' ] ) ) ?
		yk_mt_date_format( $stats[ 'date-first' ] ) :
		esc_html( $shortcode_arguments[ 'text-no-entries' ] );
}
add_shortcode( 'mt-date-oldest-entry', 'yk_mt_shortcode_date_oldest_entry' );

/**
 * Render shortcode [mt-count-entries]
 */
function yk_mt_shortcode_count_entries( $user_defined_arguments ) {

	if ( false === YK_MT_IS_PREMIUM ) {
		return yk_mt_display_premium_upgrade_notice_for_shortcode();
	}

	$shortcode_arguments = shortcode_atts( [    'user-id'           => get_current_user_id(),
	                                            'text-no-entries'   => ''
	], $user_defined_arguments );

	$stats = yk_mt_user_stats( $shortcode_arguments[ 'user-id' ] );

	return ( false === empty( $stats[ 'count-entries' ] ) ) ?
				(int) $stats[ 'count-entries' ] :
					esc_html( $shortcode_arguments[ 'text-no-entries' ] );
}
add_shortcode( 'mt-count-entries', 'yk_mt_shortcode_count_entries' );

/**
 * Render shortcode [mt-count-meals]
 */
function yk_mt_shortcode_count_meals( $user_defined_arguments ) {

	if ( false === YK_MT_IS_PREMIUM ) {
		return yk_mt_display_premium_upgrade_notice_for_shortcode();
	}

	$shortcode_arguments = shortcode_atts( [    'user-id'           => get_current_user_id(),
	                                            'text-no-entries'   => ''
	], $user_defined_arguments );

	$stats = yk_mt_user_stats( $shortcode_arguments[ 'user-id' ] );

	return ( false === empty( $stats[ 'count-meals' ] ) ) ?
		(int) $stats[ 'count-meals' ] :
			esc_html( $shortcode_arguments[ 'text-no-entries' ] );
}
add_shortcode( 'mt-count-meals', 'yk_mt_shortcode_count_meals' );
