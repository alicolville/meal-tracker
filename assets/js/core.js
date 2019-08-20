
var yk_mt_selected_meal_type = false;

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
    var dialog_options = yk_mt_sc_meal_tracker[ 'dialog-options' ];

    dialog_options = JSON.parse( dialog_options );

    //dialog_options.beforeOpen = function() { yk_mt_dialog_open() };

    var meal_tracker_dialog = $(".yk-mt-add-meal-prompt").animatedModal( dialog_options );

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
     * Add a meal form
     * ---------------------------------------------------------------------------------------
     */

    $('.yk-mt-select-meal').selectize();

    /**
     * Add meal today's entry
     */
    $( '.yk-mt-meal-button-add' ).click( function( e ) {

        /**
         * Add meal to today's entry
         */
        yk_mt_post_api_add_meal_to_entry( 0,
            $( '#yk-mt-meal-id' ).val(),
            yk_mt_selected_meal_type,
            $( '#yk-mt-quantity' ).val()
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

        var data = {
            'entry-id'  : entry_id,
            'meal-id'   : meal_id,
            'meal-type' : meal_type,
            'quantity'  : quantity
        };

        yk_mt_post( 'add_meal_to_entry', data,  yk_mt_post_api_add_meal_to_entry_callback);
    }

    /**
     * Handle the call back to adding a meal to an entry
     * @param data
     * @param response
     */
    function yk_mt_post_api_add_meal_to_entry_callback( data, response ) {
        if ( false === response[ 'error' ] ) {
            yk_mt_render_entry( response[ 'entry' ] );

            $( 'body' ).trigger( 'meal-tracker-added' );
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
     * Data has been refreshed, reload as needed!
     */
    $( 'body' ).on( 'meal-tracker-refresh', function( event ) {
        //
        // yk_mt_refresh_entry();
    });

    /**
     * There was an error saving the data
     */
    $( 'body' ).on( 'meal-tracker-save-error', function( event ) {
        alert( 'There was an error saving your entry!' ); //TODO: Either make this pretty / translate it
    });

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
    const MealRow = ({ meal_entry_id, meal_type, name, calories, quantity}) => `
                        <div class="yk-mt-r" data-mt="${meal_type}">
                            <div class="yk-mt-c">
                                 ${name}
                            </div>
                            <div class="yk-mt-c yk-mt-cq">
                                ${calories}${yk_mt_sc_meal_tracker[ 'localise' ][ 'calorie-unit' ]} / ${quantity}g
                            </div>
                            <div class="yk-mt-c yk-mt-o">
                                <a href="#" data-id="${meal_entry_id}" class="yk-mt-act-r">${yk_mt_sc_meal_tracker[ 'localise' ][ 'remove-text' ]}</a>
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

        // Get HTML for all meal rows
        html_meals = meals.map( MealRow ).join('');

        total = [ { total: total, unit: yk_mt_sc_meal_tracker[ 'localise' ][ 'calorie-unit' ] } ]; //todo: localise kcal

        // Get HTML for total row
        html_total = total.map( SummaryRow ).join('');

        $( '#meals-table-' + table_id ).html( html_meals + html_total );
    }

    /**
     * Take an entry in JSON format and render into UI
     * @param entry
     */
    function yk_mt_render_entry( entry ) {

        if ( typeof entry !== 'object' ) {
            return;
        }

        // Render meal rows under each meal type
        $.each( entry.meals, function( meal_type_id, meals ) {
            yk_mt_render_meal_rows( meal_type_id, meals, entry.counts[ meal_type_id ]);
        });
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
    }

    function yk_mt_loading_stop() {
        $.LoadingOverlay("hide");
    }

    $( 'body' ).on( 'meal-tracker-ajax-started', function( event ) {
        yk_mt_loading_start();
    });

    $( 'body' ).on( 'meal-tracker-ajax-finished', function( event ) {
        yk_mt_loading_stop();
    });

    // https://www.chartjs.org/docs/latest/charts/doughnut.html
});