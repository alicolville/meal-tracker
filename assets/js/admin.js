jQuery( document ).ready(function ($) {

    if ( $.fn.zozoTabs ) {
        $( '.yk-mt-tabs' ).zozoTabs({
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
