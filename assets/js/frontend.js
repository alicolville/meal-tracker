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
     * If "Add a meal" button is pressed, set the meal ID on the dialog
     */
    $( '.yk-mt-add-meal-prompt' ).click( function( e ) {

        var meal_id = $( e.target ).data( 'meal-id' );
        $( 'body' ).data( 'yk-mt-meal-id', meal_id );
    });

    /**
     * Initialise dialog
     */
    var dialog_options = yk_mt[ 'dialog-options' ];

    dialog_options = JSON.parse( dialog_options );

    dialog_options.beforeOpen = function() { yk_mt_dialog_open() };
    dialog_options.beforeClose = function() { yk_mt_dialog_close() };

    $(".yk-mt-add-meal-prompt").animatedModal( dialog_options );

    /**
     * Initialise opened dialog
     */
    function yk_mt_dialog_open() {

        var meal_id = yk_mt_dialog_meal_id_get();


    }

    /**
     * Tidy up after dialog closed
     */
    function yk_mt_dialog_close() {
        //yk_mt_dialog_meal_id_reset();
    }

    /**
     * Reset the meal ID required for dialog
     */
    function yk_mt_dialog_meal_id_reset() {
        $( 'body' ).data( 'yk-mt-meal-id', false );
    }

    /**
     * Fetch the meal ID required for dialog
     */
    function yk_mt_dialog_meal_id_get() {
        var meal_id = $( 'body' ).data( 'yk-mt-meal-id' );
        return ( undefined === meal_id ) ? false : meal_id;
    }

    // Init meal-id data attribute on <body> tag
    yk_mt_dialog_meal_id_reset();

    /**
     * ---------------------------------------------------------------------------------------
     * Add a meal form
     * ---------------------------------------------------------------------------------------
     */

    $('.yk-mt-select-meal').selectize();

    $( '.yk-mt-meal-button-add' ).click( function( e ) {

       alert('here');

        e.preventDefault();
    });

});