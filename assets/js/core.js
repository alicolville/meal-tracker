
var yk_mt_entry_id              = 0;
var yk_mt_selected_meal_type    = false;
var yk_mt_chart_config          = false;
var yk_mt_ctx                   = false;
var yk_mt_chart                 = false;
var yk_mt_meal_tracker_found    = ( 'undefined' !== typeof( yk_mt_sc_meal_tracker ) );
var yk_meal_tracker_dialog      = false;
var yk_mt_meal_selector         = false;

jQuery( document ).ready( function( $ ) {

    /**
     * ---------------------------------------------------------------------------------------
     * Accordion ( based on https://inspirationalpixels.com/accordion-html-css-jquery/#css )
     * ---------------------------------------------------------------------------------------
     */

    // Close all sections (probably none open in the first place)
    yk_mt_accordion_close_sections();

    $( '.yk-mt-accordion-section .initial-active' ).each( function() {
        var currentAttrValue = $( this ).attr( 'href' );
        $( this ).addClass( 'active' );
        $( '.yk-mt-accordion ' + currentAttrValue ).slideDown( 300 ).addClass('open');
    });

    $( '.yk-mt-accordion-section-title' ).click( function( e ) {

        // Grab current anchor value
        var currentAttrValue = $( this ).attr( 'href' );

        if( $( e.target ).is( '.active' ) ) {
            yk_mt_accordion_close_sections();
        } else {
            yk_mt_accordion_close_sections();

            // Add active class to section title
            $( this ).addClass( 'active' );
            // Open up the hidden content panel
            $( '.yk-mt-accordion ' + currentAttrValue ).slideDown( 300 ).addClass('open');
        }

        e.preventDefault();
    });

    function yk_mt_accordion_close_sections() {
        $( '.yk-mt-accordion .yk-mt-accordion-section-title' ).removeClass( 'active' );
        $( '.yk-mt-accordion .yk-mt-accordion-section-content' ).slideUp(300).removeClass( 'open' );
    }

    /**
     * ---------------------------------------------------------------------------------------
     * Add a meal dialog
     * ---------------------------------------------------------------------------------------
     */

    /**
     * If "Add a meal" button is pressed, set the meal type ID on the dialog
     */
    $( '.yk-mt-add-meal-prompt' ).click( function( e ) {

        var meal_type = $( e.target ).data( 'meal-type' );

        yk_mt_selected_meal_type = meal_type;

        $( 'body' ).attr( 'yk-mt-meal-type', meal_type ); // remove this? Or keep above.
    });

    /**
     * Initialise dialog
     */
    var dialog_options = JSON.parse( yk_mt_sc_meal_tracker[ 'dialog-options' ] );

    dialog_options.afterClose = function() { yk_mt_dialog_close() };
    dialog_options.beforeOpen = function() {

        yk_mk_selectize_init()

        $( '#yk-mt-add-meal-dialog' ).removeClass( 'yk-mt-hide' );
    };

    yk_meal_tracker_dialog = $(".yk-mt-add-meal-prompt").animatedModal( dialog_options );

    /**
     * Tidy up after dialog closed
     */
    function yk_mt_dialog_close() {
        yk_mt_dialog_meal_type_reset();
    }

    /**
     * Reset the meal ID required for dialog
     */
    function yk_mt_dialog_meal_type_reset() {
        yk_mt_selected_meal_type = false;
    }

    // Init meal type data attribute
    yk_mt_dialog_meal_type_reset();


    /**
     * ---------------------------------------------------------------------------------------
     * Selectize
     * ---------------------------------------------------------------------------------------
     */

    /**
     * Add an option to list of meals
     * @param option
     */
    function yk_mk_selectize_add_option( option ) {

        if ( false !== yk_mt_meal_selector && option ) {

            var selectize = yk_mt_meal_selector[0].selectize;

            selectize.addOption( option );
            selectize.addItem( option[ 'id' ] );
        }
    }

    /*
        Initialise the Meal picker
     */
    function yk_mk_selectize_init() {

        yk_mt_meal_selector = $( '#yk-mt-meal-id' ).selectize({
            preload: true,
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            options: [],
            load: function( query, callback ) {

                this.clearOptions();

                // if ( query.length > 0 && query.length < 3 ) {
                //     return;
                // } //todo

                $.ajax({
                    url: yk_mt[ 'ajax-url' ],
                    type: 'POST',
                    data: { action: 'meals', security: yk_mt[ 'ajax-security-nonce' ], search: query },
                    error: function() {
                        callback();
                    },
                    success: function(res) {
                        callback( res );
                    }
                });
            }
        });
    }

    /**
     * ---------------------------------------------------------------------------------------
     * Add a meal form
     * ---------------------------------------------------------------------------------------
     */

    /**
     * Add a meal to today's entry
     */
    $( '.yk-mt-meal-button-add' ).click( function( e ) {

        e.preventDefault();

        var quantity = $( this ).data( 'quantity' );

        /**
         * Add meal to today's entry
         */
        yk_mt_post_api_add_meal_to_entry( yk_mt_entry_id,
            $( '#yk-mt-meal-id' ).val(),
            yk_mt_selected_meal_type,
            quantity
        );
    });

    /**
     * Post to AJAX back end
     * @param action
     * @param data
     * @param callback
     */
    function yk_mt_post( action, data, callback ) {

        data[ 'action' ]    = action;
        data[ 'security' ]  = yk_mt[ 'ajax-security-nonce' ];

        $( 'body' ).trigger( 'meal-tracker-ajax-started' );

        jQuery.post( yk_mt[ 'ajax-url' ], data, function( response ) {

            callback( data, response );

            $( 'body' ).trigger( 'meal-tracker-ajax-finished' );
        });
    }

    /**
     * Post back to Ajax handler,
     * @param user_id
     * @param entry_id
     * @param meal_id
     * @param meal_type
     */
    function yk_mt_post_api_add_meal_to_entry( entry_id, meal_id, meal_type, quantity = 1 ) {

        if ( meal_id ) {

            if ( ! quantity ) {
                quantity = 1;
            }

            var data = {
                'entry-id'  : entry_id,
                'meal-id'   : meal_id,
                'meal-type' : meal_type,
                'quantity'  : quantity
            };

            yk_mt_post( 'add_meal_to_entry', data,  yk_mt_post_api_add_meal_to_entry_callback);

        } else {
            yk_mt_warn( yk_mt_sc_meal_tracker[ 'localise' ][ 'meal-entry-missing-meal' ], '#yk-mt-form-add-meal-to-entry .selectize-control' );
        }
    }

    /**
     * Handle the call back to adding a meal to an entry
     * @param data
     * @param response
     */
    function yk_mt_post_api_add_meal_to_entry_callback( data, response ) {
        if ( false === response[ 'error' ] ) {

            yk_mt_render_entry( response[ 'entry' ] );

            $('#yk-mt-form-add-meal-to-entry').trigger("reset");

            yk_mt_success( yk_mt_sc_meal_tracker[ 'localise' ][ 'meal-entry-added-success' ] );

            yk_mt_success( yk_mt_sc_meal_tracker[ 'localise' ][ 'meal-entry-added-short' ], '#yk-mt-button-add-meal-' + data[ 'quantity' ] );

            if ( $( '#yk-mt-button-add-meal-close' ).is( ':checked' ) ) {
                $('#btn-close-modal').click();
            }

            $( 'body' ).trigger( 'meal-tracker-meal-added' );
        } else {

            $('#btn-close-modal').click();

            $( 'body' ).trigger( 'meal-tracker-save-error' );
        }
    }

     /**
     * Delete meal from entry
     * @param meal_entry_id
     */
    function yk_mt_post_api_delete_meal_to_entry( meal_entry_id ) {

        var data = {
            'meal-entry-id'  : meal_entry_id
        };

        yk_mt_post( 'delete_meal_to_entry', data,  yk_mt_post_api_delete_meal_to_entry_callback);
    }

    /**
     * Handle the call back to deleting a meal from an entry
     * @param data
     * @param response
     */
    function yk_mt_post_api_delete_meal_to_entry_callback( data, response ) {

        if ( false === response[ 'error' ] ) {

            yk_mt_render_entry( response[ 'entry' ] );

            yk_mt_success( yk_mt_sc_meal_tracker[ 'localise' ][ 'meal-entry-deleted-success' ] );

            $( 'body' ).trigger( 'meal-tracker-meal-deleted' );
        } else {
            $( 'body' ).trigger( 'meal-tracker-save-error' );
        }
    }

    /**
     * Refresh entry UI
     * @param entry_id
     */
    function yk_mt_refresh_entry( entry_id = false ) {

        var data = {
            'entry-id'  : entry_id
        };

        yk_mt_post( 'get_entry', data,  yk_mt_refresh_entry_callback);
    }

    /**
     * Update UI component with latest entry data
     * @param data
     * @param response
     */
    function yk_mt_refresh_entry_callback( data, response ) {
        yk_mt_render_entry( response );
    }

    /**
     * There was an error saving the data
     */
    $( 'body' ).on( 'meal-tracker-save-error', function( event ) {
        yk_mt_warn( yk_mt_sc_meal_tracker[ 'localise' ][ 'db-error' ] );
    });

    /**
     * Listen for trigger to delete meal from an entry
     */
    $( 'body' ).on( 'meal-tracker-meal-entry-delete', function( event, meal_entry_id ) {
        yk_mt_post_api_delete_meal_to_entry( meal_entry_id );
    });

    /**
     * ------ ---------------------------------------------------------------------------------
     * Add new meal
     * ---------------------------------------------------------------------------------------
     */

    $( '#yk-mt-form-add-new-meal' ).submit(function( event ) {

        event.preventDefault();

        yk_mt_post_api_add_meal(
                $( '#yk-mt-add-meal-name' ).val(),
                $( '#yk-mt-add-meal-description' ).val(),
                $( '#yk-mt-add-meal-calories' ).val(),
                $( '#yk-mt-add-meal-quantity' ).val(),
                $( '#yk-mt-add-meal-unit' ).val()
        );
    });

    /**
     * Add a new meal
     * @param name
     * @param description
     * @param calories
     * @param quantity
     * @param unit
     */
    function yk_mt_post_api_add_meal( name, description, calories, quantity, unit ) {

        var data = {
            'name'          : name,
            'description'   : description,
            'calories'      : calories,
            'quantity'      : quantity,
            'unit'          : unit,
            'entry-id'      : yk_mt_entry_id,
            'meal-type'     : yk_mt_selected_meal_type
        };

        yk_mt_post( 'add_meal', data,  yk_mt_post_api_add_meal_callback);
    }

    function yk_mt_selectize_init() {

    }

    /**
     * Handle the call back to adding a meal
     * @param data
     * @param response
     */
    function yk_mt_post_api_add_meal_callback( data, response ) {

        if ( false === response[ 'error' ] ) {

            yk_mk_selectize_add_option( response[ 'new-meal' ] );

            yk_mt_success( yk_mt_sc_meal_tracker[ 'localise' ][ 'meal-entry-added-short' ], '#yk-mt-button-meal-add' );

            $('#yk-mt-form-add-new-meal').trigger("reset");

            yk_mt_success( yk_mt_sc_meal_tracker[ 'localise' ][ 'meal-entry-added-success' ] );

            yk_mt_refresh_entry();

            $('#btn-close-modal').click();

            $( 'body' ).trigger( 'meal-tracker-new-meal-added' );

        } else {
            $( 'body' ).trigger( 'meal-tracker-save-error' );
        }
    }

    /**
     * ------ ---------------------------------------------------------------------------------
     * HTML Templates and Rendering
     * ---------------------------------------------------------------------------------------
     */

    /**
     * HTML for a Meal row (within data table)
     * @param meal_entry_id
     * @param meal_type
     * @param name
     * @param calories
     * @param quantity
     * @returns {string}
     * @constructor
     */
    const MealRow = ({ meal_entry_id, meal_type, name, calories, quantity, d}) => `
                        <div class="yk-mt-r" data-mt="${meal_type}">
                            <div class="yk-mt-c">
                                 ${name}
                            </div>
                            <div class="yk-mt-c yk-mt-cq">
                                ${d}
                            </div>
                            <div class="yk-mt-c yk-mt-o">
                                <button data-id="${meal_entry_id}" class="yk-mt-act-r" onclick="yk_mt_trigger_meal_entry_delete( ${meal_entry_id} )"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>`;

    /**
     * HTML to provide a total row
     * @param total
     * @param unit
     * @returns {string}
     * @constructor
     */
    const SummaryRow = ({ total, unit }) => `
                        <div class="yk-mt-r" >
                                <div class="yk-mt-c">
                                </div>
                                <div class="yk-mt-c yk-mt-cq">
                                    ${total}${unit}
                                </div>	
                                <div class="yk-mt-c yk-mt-o">
                                </div>
                        </div>`;

    /**
     * Render all meals for a given meal type
     * @param table_id
     * @param meals
     * @param total
     */
    function yk_mt_render_meal_rows( table_id, meals, total ) {

        let html = yk_mt_sc_meal_tracker[ 'localise' ][ 'no-data' ] + '.';

        if ( 0 !== total ) {

            // Get HTML for all meal rows
            html_meals = meals.map( MealRow ).join('');

            total = [ { total: total, unit: yk_mt_sc_meal_tracker[ 'localise' ][ 'calorie-unit' ] } ];

            // Get HTML for total row
            html_total = total.map( SummaryRow ).join('');

            html = html_meals + html_total;
        }

        $( '#meals-table-' + table_id ).html( html );
    }

    /**
     * Take an entry in JSON format and render into UI
     * @param entry
     */
    function yk_mt_render_entry( entry ) {

        if ( typeof entry !== 'object' ) {
            return;
        }

        yk_mt_loading_start();

        // Render meal rows under each meal type
        $.each( entry.meals, function( meal_type_id, meals ) {
            yk_mt_render_meal_rows( meal_type_id, meals, entry.counts[ meal_type_id ]);
        });

        yk_mt_chart_data_set( entry[ 'calories_allowed' ],
            entry[ 'calories_remaining' ],
            entry[ 'calories_used' ],
            entry[ 'percentage_used' ]
        );

        yk_mt_chart_render();

        yk_mt_loading_stop();
    }

    // Are we on a shortcode page and have initial data to load?
    if ( yk_mt_sc_meal_tracker [ 'load-entry' ] ) {
        yk_mt_render_entry( yk_mt_sc_meal_tracker [ 'todays-entry' ] );
    }

    /**
     * ------ ---------------------------------------------------------------------------------
     * Loading Overlay
     * ---------------------------------------------------------------------------------------
     */

    function yk_mt_loading_start() {
        $.LoadingOverlay("show");

        setTimeout(function(){
            yk_mt_loading_stop();
        }, 3000);
    }

    function yk_mt_loading_stop() {
        $.LoadingOverlay("hide");
    }

    $( 'body' ).on( 'meal-tracker-ajax-started', function( event ) {

        // AC: Set time out here? If "loading" for more than x seconds then hide and show error?
        // note: to cause it to fail, just remove an AJAX hook
        yk_mt_loading_start();
    });

    $( 'body' ).on( 'meal-tracker-ajax-finished', function( event ) {
        yk_mt_loading_stop();
    });

    /**
     * ------ ---------------------------------------------------------------------------------
     * Notifications
     * ---------------------------------------------------------------------------------------
     */

    function yk_mt_warn( text, selector = null ) {
        yk_mt_notification( text, 'error', selector );
    }

    function yk_mt_info( text, selector = null ) {
        yk_mt_notification( text, 'info', selector );
    }

    function yk_mt_success( text, selector = null ) {
        yk_mt_notification( text, 'success', selector );
    }

    function yk_mt_notification( text, type = 'warn', selector = null ) {

        let options = { position: 'bottom right', className: type };

        if ( null === selector ) {
            $.notify( text, options );
        } else {
            $( selector ).notify( text, options );
        }
    }

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
                //Start with a base font of 30px
                ctx.font = "30px " + fontStyle;

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
     * Set chart data
     * @param calories_allowed
     * @param calories_remaining
     * @param calories_used
     * @param percentage_used
     */
    function yk_mt_chart_data_set( calories_allowed, calories_remaining, calories_used, percentage_used ) {

        yk_mt_chart_config = {
            calories_allowed:   calories_allowed,
            calories_remaining: calories_remaining,
            calories_used:      calories_used,
            percentage_used:    percentage_used
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

            yk_mt_ctx   = $('#yk-mt-chart');

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
                backgroundColor: [ "rgb(255, 99, 132)", "rgb(228,228,228)" ],
                borderWidth: 1
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

        return {
            cutoutPercentage: 70,
            title: {
                display: false
            },
            legend: {
                display: true,
                position: 'right',
                labels: {
                    fontSize: 17,
                    boxWidth: 20
                }
            },
            elements: {
                center: {
                    text: yk_mt_chart_config[ 'percentage_used' ] + '%',
                    color: 'rgb(255, 99, 132)',
                    fontStyle:  'Helvetica',
                    sidePadding: 125
                }
            }
        };
    }
});

/**
 * This is a wee hack to fire an event for links clicked to remove a meal from an entry
 * @param meal_entry_id
 */
function yk_mt_trigger_meal_entry_delete( meal_entry_id ) {
    jQuery( 'body' ).trigger( 'meal-tracker-meal-entry-delete', [ meal_entry_id ] );
}