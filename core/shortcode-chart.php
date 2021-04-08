<?php

defined('ABSPATH') or die('Naw ya dinnie!');

/**
 * Render shortcode [mt-chart-today]
 * @param $user_defined_arguments
 *
 * @return string
 */
function yk_mt_shortcode_chart( $user_defined_arguments ) {

	if ( false === YK_MT_IS_PREMIUM ) {
		return yk_mt_display_premium_upgrade_notice_for_shortcode();
	}

	$shortcode_arguments = shortcode_atts( [    'chart-height'	        => '200px',     // Set height of progress chart
	                                            'chart-type'            => 'doughnut',  // pie / doughnut
	                                            'chart-hide-legend'     => false,       // Hide chart legend
	                                            'chart-hide-title'      => true         // Hide chart title
	], $user_defined_arguments );

	$entry = yk_mt_entry();

	yk_mt_chart_enqueue();

	wp_localize_script( 'mt-chart', 'yk_mt_sc_meal_tracker', [ 'todays-entry' => $entry, 'localise' => yk_mt_chart_localise_strings() ] );

	$js = sprintf( '    yk_mt_chart_config = {
					        calories_allowed:   %1$d,
					        calories_remaining: %2$d,
					        calories_used:      %3$d,
					        percentage_used:    %4$f,
					        chart_title:        "%5$s"
					    };


						yk_mt_chart_render();',
						$entry[ 'calories_allowed' ],
						$entry[ 'calories_remaining' ],
						$entry[ 'calories_used' ],
						$entry[ 'percentage_used' ],
						$entry[ 'chart_title' ]
	);

	wp_add_inline_script( 'mt-chart', $js, 'after' );

	return yk_mt_chart_progress_canvas( $shortcode_arguments );
}
add_shortcode( 'mt-chart-today', 'yk_mt_shortcode_chart' );
