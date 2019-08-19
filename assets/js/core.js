
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

    dialog_options.beforeOpen = function() { yk_mt_dialog_open() };
   // dialog_options.beforeClose = function() { yk_mt_dialog_close() };

    var meal_tracker_dialog = $(".yk-mt-add-meal-prompt").animatedModal( dialog_options );

    /**
     * Initialise opened dialog
     */
    function yk_mt_dialog_open() {
        // todo
        // var meal_id = yk_mt_dialog_meal_type_get();
    }

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

    /**
     * Fetch the meal ID required for dialog
     */
    // function yk_mt_dialog_meal_type_get() {
    //     return ( undefined === yk_mt_selected_meal_type ) ? false : yk_mt_selected_meal_type;
    // }

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

        jQuery.post( yk_mt[ 'ajax-url' ], data, function( response ) {
            callback( data, response );
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
        if ( true === response ) {
            $( 'body' ).trigger( 'meal-tracker-refresh' );
        } else {
            $( 'body' ).trigger( 'meal-tracker-save-error' );
        }
    }

    /**
     * Data has been refreshed, reload as needed!
     */
    $( 'body' ).on( 'meal-tracker-refresh', function( event ) {
        alert( 'Data refresh!' );
    });

    /**
     * There was an error saving the data
     */
    $( 'body' ).on( 'meal-tracker-save-error', function( event ) {
        alert( 'There was an error saving your entry!' ); //TODO: Either make this pretty / translate it
    });
});