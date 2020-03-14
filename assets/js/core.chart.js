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
 * Extend Chart.JS to render text within doughnut
 * Based on: https://stackoverflow.com/questions/20966817/how-to-add-text-inside-the-doughnut-chart-using-chart-js
 */
Chart.pluginService.register({
    beforeDraw: function (chart) {
        if (chart.config.options.elements.center) {
            //Get ctx from string
            var ctx = chart.chart.ctx;

            //Get options from the center object in options
            var centerConfig = chart.config.options.elements.center;
            var fontStyle = centerConfig.fontStyle || 'Arial';
            var txt = centerConfig.text;
            var color = centerConfig.color || '#000';
            var sidePadding = centerConfig.sidePadding || 20;
            var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)

            let window_width = jQuery( window ).width();

            let font_size = 30;

            if ( window_width < 460 ) {
                font_size = 15;
            } else if ( window_width < 540 ) {
                font_size = 20;
            }

            ctx.font = font_size + "px " + fontStyle;

            //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
            var stringWidth = ctx.measureText(txt).width;
            var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

            // Find out how much the font can grow in width.
            var widthRatio = elementWidth / stringWidth;
            var newFontSize = Math.floor(30 * widthRatio);
            var elementHeight = (chart.innerRadius * 2);

            // Pick a new font size so it will not be larger than the height of label.
            var fontSizeToUse = Math.min(newFontSize, elementHeight);

            //Set font settings to draw it correctly.
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
            var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
            ctx.font = fontSizeToUse+"px " + fontStyle;
            ctx.fillStyle = color;

            //Draw text in center
            ctx.fillText(txt, centerX, centerY);
        }
    }
});

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

function yk_mt_chart_data() {
    return {
        datasets: [{
            data: [ yk_mt_chart_config[ 'calories_used' ],  yk_mt_chart_config[ 'calories_remaining' ] ],
            backgroundColor: [ yk_mt_chart_color, "#e5e5e5" ],
            borderWidth: 0
        }],
        labels: [
            yk_mt_chart_config[ 'calories_used' ] + ' ' + yk_mt_sc_meal_tracker[ 'localise' ][ 'chart-label-used' ],
            yk_mt_chart_config[ 'calories_remaining' ] + ' ' + yk_mt_sc_meal_tracker[ 'localise' ][ 'chart-label-remaining' ]
        ]
    };
}

/**
 * Return options for Chart.js doughnut
 * @returns object
 */
function yk_mt_chart_options() {

    let options = {
        cutoutPercentage: 88,
        title: {
            display: ! yk_mt_chart_is_admin,
            fontFamily: yk_mt_chart_font,
            fontSize: 16,
            fontStyle: 'normal',
            padding: 20,
            text: yk_mt_chart_config[ 'chart_title' ],
            fontColor: '#000000'
        },
        legend: {
            display: ! yk_mt_chart_is_admin,
            position: 'right',
            labels: {
                fontFamily: yk_mt_chart_font,
                fontSize: 16,
                boxWidth: 16,
                fontColor: '#000000'
            }
        }
    };

    if ( false === yk_mt_chart_is_admin ) {
        options[ 'elements' ] = {
            center: {
                text: yk_mt_chart_config[ 'percentage_used' ] + '%',
                color: yk_mt_chart_color,
                fontStyle: yk_mt_chart_font,
                sidePadding: 125,
            }
        }
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

