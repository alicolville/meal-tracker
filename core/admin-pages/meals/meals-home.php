<?php

defined('ABSPATH') or die('Naw ya dinnie!');

/**
 * Selector to decide which meals page to display
 */
function yk_mt_admin_page_meals_home() {

    yk_mt_admin_permission_check();

  	// Call relevant page function
	switch ( yk_mt_querystring_value( 'mode', 'dashboard' ) ) {
        case 'meal':
            yk_mt_admin_page_meals_add_edit();
            break;
		case 'import':
			yk_mt_admin_page_meals_import();
			break;
		default:
            yk_mt_admin_page_meals_dashboard();
			break;
	}
}
