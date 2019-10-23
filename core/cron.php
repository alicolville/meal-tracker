<?php

    defined('ABSPATH') or die("Jog on!");

    // Fetch the existing license from WP Options and run it through validation again.
    function yk_mt_cron_licence_check() {

        $existing_license = yk_mt_license();

        yk_mt_license_apply( $existing_license );
    }
    add_action( 'daily', 'yk_mt_cron_licence_check' );
