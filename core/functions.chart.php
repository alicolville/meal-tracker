<?php

defined('ABSPATH') or die("Jog on!");

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