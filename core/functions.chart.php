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

	wp_enqueue_script( 'mt-chart-js', plugins_url( 'assets/js/Chart.bundle.min.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION );
	wp_enqueue_script( 'mt-chart', plugins_url( 'assets/js/core.chart' . $minified . '.js', __DIR__ ), [ 'jquery', 'mt-chart-js' ], YK_MT_PLUGIN_VERSION, true );

	// Scripts > ChartJS > Localized scripts
	if ( true === yk_mt_site_options_as_bool('css-theme-enabled' ) ) {

		// Styles > Theme > Fonts
		wp_enqueue_style( 'mt-font-nunito', 'https://fonts.googleapis.com/css?family=Nunito:700,800&display=swap', [], YK_MT_PLUGIN_VERSION );

		// Styles > Theme > Vars
		$chart_font  = apply_filters( 'yk-mt-filter-chart-font', '\'Nunito\', \'HelveticaNeue-Light\', \'Helvetica Neue Light\', \'Helvetica Neue\', Helvetica, Arial, sans-serif' );
		$chart_color = apply_filters( 'yk-mt-filter-chart-color', '#fb8e2e' );
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
		'chart-label-used'              => __( 'used', YK_MT_SLUG ),
		'chart-label-remaining'         => __( 'remaining', YK_MT_SLUG ),
		'chart-label-target'            => __( 'Target', YK_MT_SLUG )
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
 */
function yk_mt_chart_placeholder( $args = [] ) {

    $default_options =  [
                    'responsive' => true,
                    'title' => [
                        'display' => true,
                        'text' => __( 'In a chart', YK_MT_SLUG )
                    ]
    ];

    $args = wp_parse_args( $args, [
        'id'        => sprintf( 'yk_mt_chart_%s', uniqid() ),
        'type'      => 'line',
        'height'    => 50,
        'labels'    => [],
        'options'   => $default_options,
        'datasets'  => [],
        'title'     => NULL
    ]);

    if ( NULL !== $args[ 'title' ] ) {
        $args[ 'options' ][ 'title' ][ 'text' ] = $args[ 'title' ];
    }

    yk_mt_enqueue_scripts_chart();

    wp_localize_script( 'mt-chart', $args[ 'id' ] . '_data', $args );

    printf( '<canvas id="%1$s" class="yk-mt-line-chart" height="%2$d" style="height: %2$dpx"></canvas>', esc_attr( $args[ 'id' ] ), $args[ 'height' ] );
}


/**
 * Render <canvas> for Chart
 * @param array $arguments
 *
 * @return string
 */
function yk_mt_chart_progress_canvas( $arguments = [] ) {

	$arguments = wp_parse_args( $arguments, [	'css-class' => 'yk-mt-chart',
	                                             'id'		=> 'yk-mt-chart',
	                                             'height'	=> ''

	]);

	$responsive = false;

	if ( false === empty( $arguments[ 'height' ] ) ) {
		$arguments[ 'height' ] = sprintf( 'style="height:%s"', esc_attr( $arguments[ 'height' ] ) );
		$responsive = true;
	}

	return sprintf( '<div class="yk-mt-chart-container" %3$s>
						<canvas id="%1$s" class="%2$s" data-responsive="%4$s"></canvas>
					</div>',
		esc_attr( $arguments[ 'id'] ),
		esc_attr( $arguments[ 'css-class' ] ),
		$arguments[ 'height' ],
		$responsive ? '1' : 0
	);
}

function yk_mt_chart_progress_enqueue_scriptS() {

}

/**
 * Display a line chart of allowed versus used
 * @param $args
 */
function yk_mt_chart_line_allowed_versus_used( $args ) {

    $args = wp_parse_args( $args, [
                                        'user-id'   => get_current_user_id(),
                                        'entries'   => NULL,
                                        'max'       => NULL,
                                        'title'     => ''
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
        'label'             => __( 'Calories Allowed', YK_MT_SLUG ),
        'fill'              => false,
        'backgroundColor'   => '#62a16c',
        'borderColor'       => '#62a16c',
        'data'              => wp_list_pluck( $args[ 'entries' ], 'calories_allowed' )
    ];

    // Calories Used
    $datasets[] = [
        'label'             => __( 'Calories Used', YK_MT_SLUG ),
        'fill'              => false,
        'showline'          => false,
        'backgroundColor'   => '#ff6384',
        'borderColor'       => '#ff6384',
        'data'  => wp_list_pluck( $args[ 'entries' ], 'calories_used' )
    ];

    // TODO
    // Add Calories Remaining
//    $datasets[] = [
//        'label'             => __( 'Calories Remaining', YK_MT_SLUG ),
//        'fill'              => false,
//        'backgroundColor'   => '#cccccc',
//        'borderColor'       => '#cccccc',
//        'data'              => wp_list_pluck( $args[ 'entries' ], 'calories_remaining' )
//    ];

    $args[ 'labels' ]   = $dates;
    $args[ 'data' ]     = $datasets;

    yk_mt_chart_placeholder( $args );
}
