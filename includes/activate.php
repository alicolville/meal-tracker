<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 *  Run on every version change
	*/
	function yk_wt_upgrade() {

		if( update_option('yk-wt-version-number', YK_MT_PLUGIN_VERSION ) ) {

			// Build DB tables
			yk_wt_mysql_tables_create();
		}
	}
	add_action('admin_init', 'yk_wt_upgrade');
