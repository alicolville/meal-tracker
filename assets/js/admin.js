var is_premium = false;

jQuery( document ).ready(function ($) {

    if ( 'undefined' !== typeof yk_mt_settings &&
        yk_mt_settings[ 'premium' ] ) {
        is_premium = true;
    }

    /**
     * Take an entry in JSON format and render into UI
     * @param entry
     */
    function yk_mt_render_entry( entry ) {

        if ( typeof entry !== 'object' ) {
            return;
        }

        yk_mt_chart_data_set( entry[ 'calories_allowed' ],
            entry[ 'calories_remaining' ],
            entry[ 'calories_used' ],
            entry[ 'percentage_used' ],
            entry[ 'chart_title' ]
        );

        yk_mt_chart_render();

    }

    // Are we on a shortcode page and have initial data to load?
    if ( 'undefined' !== typeof yk_mt_sc_meal_tracker &&
            yk_mt_sc_meal_tracker[ 'load-entry' ] ) {
        yk_mt_render_entry( yk_mt_sc_meal_tracker [ 'todays-entry' ] );
    }

    /**
     * Foo table
     */
    if ( 'object' === typeof FooTable ) {
        $( '.yk-mt-footable-basic' ).footable();
    }

    /**
     * Zozo Tabs
     */
    if ( $.fn.zozoTabs ) {
        $( '#yk-mt-tabs' ).zozoTabs({
            rounded: false,
            multiline: true,
            theme: "silver",
            size: "medium",
            responsive: true,
            animation: {
                effects: "slideH",
                easing: "easeInOutCirc",
                type: "jquery"
            }
        });
    }

    // Show / Hide Admin allowance form.
    if ( true === is_premium ) {

        $( '#yk-mt-calorie-source' ).change( function()  {

            yk_mt_calorie_source_form_update( $( '#yk-mt-calorie-source' ).val() );
        });
    }

    /**
     * Toggle admin allowance form in admin ui
     * @param source
     */
    function yk_mt_calorie_source_form_update( source ) {

        if ( 'admin' === source ) {
            $( '#yk-mt-admin-allowance' ).removeClass( 'yk-mt-hide' );
        } else {
            $( '#yk-mt-admin-allowance' ).addClass( 'yk-mt-hide' );
        }

    }

    /**
     * Confirmation dialog
     */
    $( '.yk-mt-button-confirm' ).click( function( e ) {

        e.preventDefault();

        var title   = $( this ).data( 'title' );
        var content = $( this ).data( 'content' );
        var url     = $( this ).attr( 'href' );

        if ( 'undefined' === typeof title ) {
            title = yk_mt_sc_meal_tracker[ 'localise' ][ 'confirm-title' ];
        }

        if ( 'undefined' === typeof content ) {
            content = yk_mt_sc_meal_tracker[ 'localise' ][ 'confirm-content' ];
        }

        if ( 'url' === typeof content ) {
            url = '#';
        }

        $.confirm({
            title: title,
            content: content,
            type: 'blue',
            boxWidth: '30%',
            useBootstrap: false,
            buttons: {
                confirm: function () {
                   location.href = url;
                },
                cancel: function () {

                }
            }
        });
    });
});
