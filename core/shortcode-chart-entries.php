<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Render shortcode [mt-chart-entries]
 */
function yk_mt_shortcode_chart_entries( $user_defined_arguments ) {

	if ( false === YK_MT_IS_PREMIUM ) {
		return yk_mt_display_premium_upgrade_notice_for_shortcode();
	}

	$shortcode_arguments = shortcode_atts( [    'user-id'           => get_current_user_id(),
	                                            'max-entries'       => 15,
												'text-no-entries'   => __( 'You currently have no entries.', YK_MT_SLUG ),
												'chart-height'	    => '150px',
	], $user_defined_arguments );

	$entries = yk_mt_db_entries_summary( [ 'user-id' => $shortcode_arguments[ 'user-id' ], 'limit' => (int) $shortcode_arguments[ 'max-entries' ], 'sort-order' => 'desc' ] );

	if ( true === empty( $entries ) ){
		return sprintf( '<p>%s</p>', esc_html( $shortcode_arguments[ 'text-no-entries' ] ) );
	}

	$entries                            = array_reverse( $entries );
	$shortcode_arguments[ 'entries' ]   = $entries;

	return yk_mt_chart_line_allowed_versus_used( $shortcode_arguments );
}
add_shortcode( 'mt-chart-entries', 'yk_mt_shortcode_chart_entries' );
