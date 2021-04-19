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

/**
 * Return calorie allowance for today
 * @return string
 */
function yk_mt_shortcode_entry_calories_allowance(){
	return yk_mt_entry_get_value( 'calories_allowed');
}
add_shortcode( 'mt-calories-allowance', 'yk_mt_shortcode_entry_calories_allowance' );

/**
 * Return calories remaining for today
 * @return string
 */
function yk_mt_shortcode_entry_calories_remaining(){
	return yk_mt_entry_get_value( 'calories_remaining');
}
add_shortcode( 'mt-calories-remaining', 'yk_mt_shortcode_entry_calories_remaining' );

/**
 * Return calories used for today
 * @return string
 */
function yk_mt_shortcode_entry_calories_used(){
	return yk_mt_entry_get_value( 'calories_used');
}
add_shortcode( 'mt-calories-used', 'yk_mt_shortcode_entry_calories_used' );

/**
 * Return calories % used for today
 * @return string
 */
function yk_mt_shortcode_entry_calories_used_percentage(){
	return yk_mt_entry_get_value( 'percentage_used');
}
add_shortcode( 'mt-calories-used-percentage', 'yk_mt_shortcode_entry_calories_used_percentage' );

/**
 * Fetch data about an entry for shortcode usage
 * @param $key
 *
 * @return string
 */
function yk_mt_entry_get_value( $key ) {

	// This is used to create an empty entry if one doesn't already exist for this user / day
	yk_mt_entry_get_id_or_create();

	$entry = yk_mt_entry();

	if ( false === isset( $entry[ $key ] ) ) {
		return '';
	}

	// Display a calorie value?
	if ( true === in_array( $key, [ 'calories_remaining', 'calories_used', 'calories_allowed' ] ) ) {
		return yk_mt_format_calories( $entry[ $key ] );
	}

	if ( 'percentage_used' === $key ) {
		return $entry[ 'percentage_used' ] . '%';
	}

	return '';
}
