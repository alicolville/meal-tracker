<?php

defined('ABSPATH') or die('Jog on!');

/**
 * Display admin notice for notification from yeken.uk
 */
function yk_mt_get_marketing_message() {

	if ( $cache = get_transient( '_yeken_meal_tracker_update' ) ) {
		return $cache;
	}

	$response = wp_remote_get( YK_MT_YEKEN_UPDATES_URL );

	// All ok?
	if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

		$body = wp_remote_retrieve_body( $response );

		if ( false === empty( $body ) ) {

			$body = json_decode( $body, true );
			
			set_transient( '_yeken_meal_tracker_update', $body, HOUR_IN_SECONDS );

			return $body;
		}
	}

	return NULL;
}

/**
 * Get/Set key of notice last dismissed.
 */
function yk_mt_marketing_update_key_last_dismissed( $key = NULL ) {

	if ( NULL !== $key ) {
		set_transient( '_yeken_meal_tracker_update_key_last_dismissed', $key );
	}
	
	return get_transient( '_yeken_meal_tracker_update_key_last_dismissed' ) ;

}

/**
 * Display HTML for admin notice
 */
function yk_mt_updates_display_notice( $json ) {

	if ( false === is_array( $json ) ) {
		return;
	}

	wp_enqueue_script( 'mt-dismiss-notices', YK_MT_BASE_URL . 'assets/js/dissmiss-notices.js', [ 'jquery' ], YK_MT_PLUGIN_VERSION );

	$button = '';

	if ( !empty( $json[ 'url'] ) && !empty( $json[ 'url-title' ] ) ) {
		$button = sprintf( '<p>
								<a href="%1$s" class="button button-primary" target="_blank" rel="noopener">%2$s</a>
							</p>',
							esc_url( $json[ 'url' ] ),
							yk_mt_wp_kses( $json[ 'url-title' ] )
		);
	}
				

    printf('<div class="updated notice is-dismissible yk-mt-update-notice" data-update-key="%4$s" data-nonce="%5$s">
                        <p><strong>%1$s</strong>: %2$s.</p>
                       	%3$s
                    </div>',
                    'Meal Tracker',
                    !empty( $json[ 'message' ] ) ? esc_html( $json[ 'message' ] ) : '',
                    $button,
					esc_html( $json[ '_update_key' ] ),
					esc_attr( wp_create_nonce( 'yk-mt-nonce' ) )
    );
}

 /**
  * display and admin notice if one exists and hasn't been dismissed already.
  */
function yk_mt_updates_admin_notice() {
   
	$json = yk_mt_get_marketing_message();

	if ( $json[ '_update_key' ] <> yk_mt_marketing_update_key_last_dismissed() ) {
	
		yk_mt_updates_display_notice( $json );
	}
}
add_action( 'admin_notices', 'yk_mt_updates_admin_notice' );

 /**
  * Ajax handler to dismiss setup wizard
  */
 function yk_mt_updates_ajax_dismiss() {
 
	check_ajax_referer( 'yk-mt-nonce', 'security' );
 
	$update_key = sanitize_text_field( yk_mt_post_value( 'update_key' ) );

	if ( false === empty( $update_key ) ) {
		yk_mt_marketing_update_key_last_dismissed( $update_key );
	}
 }
 add_action( 'wp_ajax_yk_mt_dismiss_notice', 'yk_mt_updates_ajax_dismiss' );

/**
 * Render a list of features
 * @param array features
 * @param bool $echo
 * @param string $format 'table' or 'ul' or 'markdown'
 */
function yk_mt_display_features( $features, $echo = true, $format = 'table'  ) {

	if ( true === empty( $features ) ) {
		return;
	}

	switch( $format ) {
		case 'table':
			$html = '';
			break;
		case 'ul':
			$html = '';
			break;
		default:
			$html = '';
	}

	$html = 'table' === $format ? '<table class="form-table yk-mt-features-table">' : '<ul>';

	$class = '';

	foreach ( $features as $feature ) {

		$class 	= ('alternate' == $class) ? '' : 'alternate';

		switch( $format ) {
			case 'table':
				$html_template = '<tr class="%1$s">
										<td>
											&middot; <strong>%2$s</strong> - %3$s
										</td>
									</tr>';
				break;
			case 'ul':
				$html_template = '<li><strong>%2$s</strong> - %3$s</li>';
				break;
			default:	
				$html_template = '* **%2$s** - %3$s' . PHP_EOL;
		}

		$row 	= sprintf( 	$html_template,
							$class,
							$feature[ 'title' ],
							$feature[ 'description' ] );

		$html .= $row;
	}	

	switch( $format ) {
		case 'table':
			$html .= '</table>';
			break;
		case 'ul':
			$html .= '</ul>';
			break;
		default:
			$html .= '';
	}

	if ( false === $echo ) {
		return $html;
	}

	yk_mt_echo_wp_kses( $html );	
}

/**
 * Render a list of features
 * @param array features
 */
function yk_mt_shortcode_display_features() {
	//return yk_mt_display_features( yk_mt_feature_list_pro(), false, 'ul' );
}
add_shortcode( 'wt-features-table', 'yk_mt_shortcode_display_features' );

/**
 * Render a list of pro features
 * @param array features
 */
function yk_mt_shortcode_display_pro_features() {
	//return yk_mt_display_features( yk_mt_feature_list_pro_plus(), false, 'ul' );
}
add_shortcode( 'wt-pro-features-table', 'yk_mt_shortcode_display_pro_features' );

/**
 * Display WP Version
 * @return text
 */
function yk_mt_shortcode_version() {
	return esc_html( YK_MT_PLUGIN_VERSION );
}
add_shortcode( 'wt-version', 'yk_mt_shortcode_version' );

// add_action( 'init', function() {
// 	yk_mt_display_features(  yk_mt_feature_list_pro(), true, $format = 'markdown'  );
// 	yk_mt_display_features(  yk_mt_feature_list_pro_plus(), true, $format = 'markdown'  );
// 	die;
// });