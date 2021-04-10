jQuery( document ).ready( function ( $ ) {

  $( '.yk-mt-table-entries' ).footable( {
                                            "paging": {
                                                        "enabled" : true,
                                                        "size"    : 10
                                            },
                                            "sorting": {
                                                        "enabled": true
                                            }
    });

});
