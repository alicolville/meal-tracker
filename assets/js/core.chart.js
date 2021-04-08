/*global $, jQuery, yk_mt_chart*/

// Chart localized vars
let yk_mt_chart_font  = yk_mt_chart.chartFont;
let yk_mt_chart_color = yk_mt_chart.chartColor;

var yk_mt_chart_config      = false;
var yk_mt_ctx               = false;
var yk_mt_chart             = false;
var yk_mt_chart_is_admin    = ( 'undefined' !== typeof( yk_mt_sc_meal_tracker ) && '1' === yk_mt_sc_meal_tracker[ 'is-admin' ] );

/**
 * ------ ---------------------------------------------------------------------------------
 * Charting
 * ---------------------------------------------------------------------------------------
 */

/**
 * TODO: Re-looking at this function, needs some refactoring. Do we need to assign to a variable again?
 *
 * Set chart data
 * @param calories_allowed
 * @param calories_remaining
 * @param calories_used
 * @param percentage_used
 */
function yk_mt_chart_data_set( calories_allowed, calories_remaining, calories_used, percentage_used, chart_title ) {

    yk_mt_chart_config = {
        calories_allowed:   calories_allowed,
        calories_remaining: calories_remaining,
        calories_used:      calories_used,
        percentage_used:    percentage_used,
        chart_title:        chart_title
    };
}

/**
 * Render Chart
 */
function yk_mt_chart_render() {

    // If the chart is already rendered, then just trigger a refresh. If not, we need to render chart.
    if ( yk_mt_ctx && yk_mt_chart ) {

        yk_mt_chart.data    = yk_mt_chart_data();
        yk_mt_chart.options = yk_mt_chart_options();
        yk_mt_chart.update();

    } else {

        yk_mt_ctx   = jQuery('#yk-mt-chart');

        yk_mt_chart = new Chart( yk_mt_ctx, {
            type:       'doughnut',
            data:       yk_mt_chart_data(),
            options:    yk_mt_chart_options()
        });
    }
}

/**
 * Fetch chart data
 * @returns {{datasets: [{backgroundColor: [*, string], data: [*, *], borderWidth: number}], labels: string[]}}
 */
function yk_mt_chart_data() {
    return {
        datasets: [{
            data: [ yk_mt_chart_config[ 'calories_used' ],  yk_mt_chart_config[ 'calories_remaining' ] ],
            backgroundColor: [ yk_mt_chart_color, "#e5e5e5" ],
            borderWidth: 0
        }],
        labels: [
            yk_mt_chart_config[ 'calories_used' ] + 'kcal ' + yk_mt_sc_meal_tracker[ 'localise' ][ 'chart-label-used' ],
            yk_mt_chart_config[ 'calories_remaining' ] + 'kcal ' + yk_mt_sc_meal_tracker[ 'localise' ][ 'chart-label-remaining' ]
        ]
    };
}

/**
 * Return options for Chart.js doughnut
 * @returns object
 */
function yk_mt_chart_options() {

  let responsive = ( '1' === yk_mt_ctx.attr('data-responsive' ) ) ? true : false;

  let options = {
        cutout: '80%',
        plugins : {
          title: {
            display: ! yk_mt_chart_is_admin,
            font : {
              color: '#000000',
              family: yk_mt_chart_font,
              size: 16,
              weight: 'normal',
            },
            padding: 20,
            text: yk_mt_chart_config[ 'chart_title' ],
          },
          legend: {
            display: ! yk_mt_chart_is_admin,
            position: 'right',
            labels: {
              font : {
                color: '#000000',
                family: yk_mt_chart_font,
                size: 14,
                weight: 'normal',
              },
            }
          }
        }
    };

    if ( true === responsive ) {
      options[ 'elements' ]             = true;
      options[ 'maintainAspectRatio' ]  = false;
    }

  return options;
}

jQuery( document ).ready( function ( $ ) {

    /**
     * Render a line chart
     */
    $( '.yk-mt-line-chart' ).each( function( index ) {

        let id          =  $( this ).attr( 'id' );
        let yk_mt_ctx   = $( '#' + id );
        let chart_data  = window[ id + '_data' ];

        let yk_mt_chart = new Chart( yk_mt_ctx, {
            type:       chart_data[ 'type' ],
            data:       { labels: chart_data[ 'labels' ], datasets: chart_data[ 'data' ] },
            options:    chart_data[ 'options' ]
        });
    });
});

