jQuery( document ).ready( function( $ ) {


    /**
     * Accordion ( based on https://inspirationalpixels.com/accordion-html-css-jquery/#css )
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

});