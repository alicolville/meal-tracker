<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 * Enqueue front end scripts
	 */
	function yk_mt_enqueue_front_end_dependencies() {

		$minified = yk_mt_use_minified();

        wp_enqueue_script( 'meal-tracker', plugins_url( 'assets/js/core' . $minified . '.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION, true );
		wp_enqueue_style( 'meal-tracker', plugins_url( 'assets/css/frontend' . $minified . '.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );

        wp_localize_script( 'meal-tracker', 'yk_mt', yk_mt_ajax_config() );

    }
	add_action( 'wp_enqueue_scripts', 'yk_mt_enqueue_front_end_dependencies' );
