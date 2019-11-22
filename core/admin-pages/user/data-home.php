<?php

defined('ABSPATH') or die('Naw ya dinnie!');

/**
 * Selector to decide which user page to display
 */
function yk_mt_admin_page_data_home() {

    // TODO: Add role permission check here

	// Call relevant page function
	switch ( yk_mt_querystring_value( 'mode', 'dashboard' ) ) {
        case 'user':
            yk_mt_admin_page_user_summary();
            break;
        case 'entry':
            yk_mt_admin_page_entry_view();
            break;
        case 'settings':
            // Call settings page
            break;
		default:
            // Dashboard
			break;
	}
}
