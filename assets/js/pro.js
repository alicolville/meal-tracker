jQuery( document ).ready( function( $ ) {

    $('.mt-datepicker').Zebra_DatePicker( {
        show_icon: false,
        onSelect: function( format, date_iso, date_obj ) {

            let redirect_to = yk_mt[ 'page-url' ] + '&entry-id=' + date_iso;

            window.location.replace( redirect_to );
        }
    });
});
