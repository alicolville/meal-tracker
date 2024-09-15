jQuery( document ).ready(function ($) {
    $( '.yk-mt-update-notice' ).on('click', '.notice-dismiss', function ( event ) {

        event.preventDefault();
    
        if( false == $( this ).parent().hasClass( 'yk-mt-update-notice' ) ){
        return;
        }
    
        $.post( ajaxurl, {
            action: 'yk_mt_dismiss_notice',
            url: ajaxurl,
            security: $( this ).parent().data( 'nonce' ),
            update_key: $( this ).parent().data('update-key')
        });
    });
});