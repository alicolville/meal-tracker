<?php

defined('ABSPATH') or die("Jog on!");

include_once YK_MT_ABSPATH . 'core/external-sources/base.php';
include_once YK_MT_ABSPATH . 'core/external-sources/fat-secret.php';

function yk_mt_ext_source_create_instance() {

	global $external_source;

	/**
	 * Notes:
	 *
	 * Fat Secret.
	 *
	 * 		Requires Client ID and Secret for Bearer token
	 * 		!! IP address of server must be whitelisted here: https://platform.fatsecret.com/api/Default.aspx?screen=mykd&id=16104
	 */

	$fat_secret_credentials = [	'client_id' => '7e823ae975674e68975177a282aa9516', 'client_secret' => 'c69255d42e294e969d48c13a7482ceb8' ];

	$external_source = new YK_MT_EXT_FAT_SECRET( $fat_secret_credentials );

	return $external_source;
}


/**
 * Test the specified end point is working and display an error if not.
 * @return bool|string
 */
function yk_mt_ext_source_test() {

	$external_source = yk_mt_ext_source_create_instance();

	// An errors?
	if ( $external_source->has_error() ) {
		return sprintf( '%s: %s', __( 'Error', YK_MT_SLUG ), $external_source->get_error() );
	}

	// Perform a test search for something obvious. We should get results!
	$external_source->search('apple' );

	if ( $external_source->has_error() ) {
		return sprintf( '%s: %s', __( 'Error', YK_MT_SLUG ), $external_source->get_error() );
	}

var_dump($external_source);
	return true;
}


function test() {

	var_dump( yk_mt_ext_source_test() );
die;
}
add_action( 'init', 'test' );

