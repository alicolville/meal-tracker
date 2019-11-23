jQuery( document ).ready(function ($) {

    /**
     * Take an entry in JSON format and render into UI
     * @param entry
     */
    function yk_mt_render_entry( entry ) {

        if ( typeof entry !== 'object' ) {
            return;
        }

       // yk_mt_loading_start();

        // // Render meal rows under each meal type
        // $.each( entry.meals, function( meal_type_id, meals ) {
        //     yk_mt_render_meal_rows( meal_type_id, meals, entry.counts[ meal_type_id ]);
        // });

        yk_mt_chart_data_set( entry[ 'calories_allowed' ],
            entry[ 'calories_remaining' ],
            entry[ 'calories_used' ],
            entry[ 'percentage_used' ],
            entry[ 'chart_title' ]
        );

        yk_mt_chart_render();

      //  yk_mt_loading_stop();
    }

    // Are we on a shortcode page and have initial data to load?
    if ( yk_mt_sc_meal_tracker[ 'load-entry' ] ) {
        yk_mt_render_entry( yk_mt_sc_meal_tracker [ 'todays-entry' ] );
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
});
