<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Enqueue relevant CSS / JS for charting
 */
function yk_mt_chart_enqueue() {

	// Styles > Core > Vars
	$chart_font  = '\'HelveticaNeue-Light\', \'Helvetica Neue Light\', \'Helvetica Neue\', Helvetica, Arial, sans-serif';
	$chart_color = '#000000';

	$minified = yk_mt_use_minified();

	// Polyfill required for older browsers for chart.js 3+
	wp_enqueue_script( 'mt-chart-js', YK_MT_CDN_CHART_JS, [ 'jquery' ], YK_MT_PLUGIN_VERSION );
	wp_enqueue_script( 'mt-chart', plugins_url( 'assets/js/core.chart' . $minified . '.js', __DIR__ ), [ 'jquery', 'mt-chart-js' ], YK_MT_PLUGIN_VERSION, true );

	// Scripts > ChartJS > Localized scripts
	if ( true === yk_mt_site_options_as_bool('css-theme-enabled' ) ) {

		// Styles > Theme > Fonts
		wp_enqueue_style( 'mt-font-nunito', 'https://fonts.googleapis.com/css?family=Nunito:700,800&display=swap', [], YK_MT_PLUGIN_VERSION );

		// Styles > Theme > Vars
		$chart_font  = apply_filters( 'yk-mt-filter-chart-font', '\'Nunito\', \'HelveticaNeue-Light\', \'Helvetica Neue Light\', \'Helvetica Neue\', Helvetica, Arial, sans-serif' );
		$chart_color = get_option( 'ws-ls-calories-allowed-colour', '#fb8e2e' );
		$chart_color = apply_filters( 'yk-mt-filter-chart-color', $chart_color );
	}

	wp_localize_script( 'mt-chart', 'yk_mt_chart', [
		'chartFont'  => $chart_font,
		'chartColor' => $chart_color,
	] );
}

/**
 * Return an array of localised strings for charting
 * @return array
 */
function yk_mt_chart_localise_strings() {

	return [
		'chart-label-used'              => esc_html__( 'used', 'meal-tracker' ),
		'chart-label-remaining'         => esc_html__( 'remaining', 'meal-tracker' ),
		'chart-label-target'            => esc_html__( 'Target', 'meal-tracker' )
	];
}

/**
 * Filter locale strings and add charting labels
 * @param $strings
 *
 * @return array
 */
function yk_mt_chart_localise_apply( $strings ) {
	return array_merge( $strings, yk_mt_chart_localise_strings() );
}
add_filter( 'yk_mt_config_locale', 'yk_mt_chart_localise_apply' );

/**
 * Place a chart placeholder
 *
 * @param array $args
 *
 * @return string
 */
function yk_mt_chart_placeholder( $args = [] ) {

    $default_options =  [
                    'responsive'            => true,
	                'maintainAspectRatio'   => false,
	                'tension'               => 0.4,
	                'plugins'               => [ 'title' => [
			                                                        'display'   => false,
			                                                        'text'      => esc_html__( 'In a chart', 'meal-tracker' )
			                                                    ]
	                ]
    ];

    $args = wp_parse_args( $args, [
        'id'            => sprintf( 'yk_mt_chart_%s', uniqid() ),
        'type'          => 'line',
        'chart-height'  => '300px',
        'labels'        => [],
        'options'       => $default_options,
        'datasets'      => [],
        'title'         => NULL
    ]);

    if ( NULL !== $args[ 'title' ] ) {
        $args[ 'options' ][ 'title' ][ 'text' ] = $args[ 'title' ];
    }

	yk_mt_chart_enqueue();

    wp_localize_script( 'mt-chart', $args[ 'id' ] . '_data', $args );

	$args[ 'chart-height' ] = sprintf( 'style="height:%s"', esc_attr( $args[ 'chart-height' ] ) );

    return sprintf( '<div class="yk-mt-chart-container" %2$s>
						<canvas id="%1$s" class="yk-mt-line-chart"></canvas>
					</div>', esc_attr( $args[ 'id' ] ), $args[ 'chart-height' ] );
}


/**
 * Render <canvas> for Chart
 * @param array $arguments
 *
 * @return string
 */
function yk_mt_chart_progress_canvas( $arguments = [] ) {

	$arguments = wp_parse_args( $arguments, [   'chart-hide-legend' => false,
	                                            'chart-hide-title'  => true,
	                                            'chart-height'	    => '',
	                                            'chart-type'        => 'doughnut',
												'css-class'         => 'yk-mt-chart',
	                                            'id'		        => 'yk-mt-chart'
	]);

	$responsive = false;

	if ( false === empty( $arguments[ 'chart-height' ] ) ) {
		$arguments[ 'chart-height' ] = sprintf( 'style="height:%s"', esc_attr( $arguments[ 'chart-height' ] ) );
		$responsive = true;
	}

	return sprintf( '<div class="yk-mt-chart-container" %3$s>
						<canvas id="%1$s" class="%2$s" data-responsive="%4$s" data-hide-legend="%6$s" data-hide-title="%7$s" data-chart-type="%8$s"
							aria-label="%5$s" role="img"></canvas>
					</div>',
		esc_attr( $arguments[ 'id'] ),
		esc_attr( $arguments[ 'css-class' ] ),
		$arguments[ 'chart-height' ],
		yk_mt_to_bool( $responsive ),
		esc_html__( 'Chart showing user\'s progress', 'meal-tracker' ),
		yk_mt_to_bool( $arguments[ 'chart-hide-legend' ] ),
		yk_mt_to_bool( $arguments[ 'chart-hide-title' ] ),
		esc_attr( $arguments[ 'chart-type' ] )
	);
}

/**
 * Display a line chart of allowed versus used
 * @param $args
 */
function yk_mt_chart_line_allowed_versus_used( $args ) {

    $args = wp_parse_args( $args, [
                                        'user-id'       => get_current_user_id(),
                                        'entries'       => NULL,
                                        'max'           => NULL,
                                        'title'         => '',
                                        'chart-height'  => '250px'
    ]);

    // Fetch entries if non specified
    if ( NULL === $args[ 'entries' ] ) {
        $args[ 'entries' ] = yk_mt_db_entries_summary( [ 'user-id' => $args[ 'user-id' ] ] );
    }

    if ( true === is_numeric( $args[ 'max' ] ) ) {

        $start = count( $args[ 'entries' ] ) - $args[ 'max' ];

        $args[ 'entries' ] = array_splice( $args[ 'entries' ], $start, $args[ 'max' ] );
    }

    // Build the X Axis (Date)
    $dates = wp_list_pluck( $args[ 'entries' ], 'date' );
    $dates = array_map( 'yk_mt_date_format', $dates );

    $datasets = [];

    // Add Calories Allowed
    $datasets[] = [
        'label'             => esc_html__( 'Calories Allowed', 'meal-tracker' ),
        'fill'              => false,
        'backgroundColor'   => '#62a16c',
        'borderColor'       => '#62a16c',
        'data'              => wp_list_pluck( $args[ 'entries' ], 'calories_allowed' )
    ];

    // Calories Used
    $datasets[] = [
        'label'             => esc_html__( 'Calories Used', 'meal-tracker' ),
        'fill'              => false,
        'showline'          => false,
        'backgroundColor'   => '#ff6384',
        'borderColor'       => '#ff6384',
        'data'  => wp_list_pluck( $args[ 'entries' ], 'calories_used' )
    ];

    // TODO
    // Add Calories Remaining
//    $datasets[] = [
//        'label'             => esc_html__( 'Calories Remaining', 'meal-tracker' ),
//        'fill'              => false,
//        'backgroundColor'   => '#cccccc',
//        'borderColor'       => '#cccccc',
//        'data'              => wp_list_pluck( $args[ 'entries' ], 'calories_remaining' )
//    ];

    $args[ 'labels' ]   = $dates;
    $args[ 'data' ]     = $datasets;

    return yk_mt_chart_placeholder( $args );
}
