<?php

	defined('ABSPATH') or die("Jog on!");

	define('WE_LS_CRON_NAME', 'weight_loss_tracker_hourly');
	define('WE_LS_CRON_NAME_YEKEN_COMMS', 'weight_loss_tracker_yeken_comms');
	define('WE_LS_CRON_SCHEDULE_WEEKLY', 'weight_loss_tracker_weekly');

	function ws_ls_schedules( $schedules ) {

		$schedules[ WE_LS_CRON_SCHEDULE_WEEKLY ] = array(
			'interval' => 604800,					// Every Week
			'display' => 'Weight Tracker Weekly'
		);

		return $schedules;
	}
	add_action('cron_schedules', 'ws_ls_schedules');

	// ------------------------------------------------------------------------------------------------------------
	// Cron job to expire old licenses
	// ------------------------------------------------------------------------------------------------------------

	// Fetch the existing license from WP Options and run it through validation again.
	function ws_ls_licences_cron() {

		$existing_license = ws_ls_license();

		ws_ls_license_apply( $existing_license );
	}
	add_action( WE_LS_CRON_NAME, 'ws_ls_licences_cron' );

    // ------------------------------------------------------------------------------------------------------------
    // Cron job to delete old error logs
    // ------------------------------------------------------------------------------------------------------------

    function ws_ls_logs_delete_cron() {

        ws_ls_log_delete_old();

    }
    add_action( WE_LS_CRON_NAME, 'ws_ls_logs_delete_cron' );

