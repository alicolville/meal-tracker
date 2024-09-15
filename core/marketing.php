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
	return yk_mt_display_features( yk_mt_feature_list_premium(), false, 'ul' );
}
add_shortcode( 'mt-features-table', 'yk_mt_shortcode_display_features' );

/**
 * Display WP Version
 * @return text
 */
function yk_mt_shortcode_version() {
	return esc_html( YK_MT_PLUGIN_VERSION );
}
add_shortcode( 'mt-version', 'yk_mt_shortcode_version' );

// add_action( 'init', function() {
// 	yk_mt_display_features(  yk_mt_feature_list_premium(), true, $format = 'markdown'  );
// 	die;
// });


/**
 * Return an array of Pro Plus features.
 * @return array
 */
function yk_mt_feature_list_premium() {
	return [
				[ 	
					'title'			=> esc_html__( 'Additional shortcodes', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Enhance your site with extra shortcodes.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'External APIs', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Allow your users to browse FatSecrets Food and Recipe APIs.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Own Meal collection', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Build your own meal collection for your users to explore.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Edit user\'s meals', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Manage your user\'s meal collections by viewing, editing, and deleting meals.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Create and view entries', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Enable your users to create and view meal entries for any date.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Edit entries', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Give your users the ability to edit their entries for any selected day.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Edit Meals', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Enable your users to modify their saved meals.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Calorie Allowance sources', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Retrieve daily calorie limits from external sources, such as YeKen\'s Weight Tracker.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Compress meal items', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Consolidate multiple meal lines into a single entry line.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Unlimited meals per user', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Users are no longer restricted to a maximum of 40 meals and can now add as many meals as they wish.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Access your user\'s data', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Access all their entries, meals, and calorie intake', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Set calorie allowances', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Assign daily calorie allowances for your users.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Summary Statistics', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Review summary statistics of your Meal Tracker data and analyze its usage by your users.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Fractional meal quantities', 'meal-tracker' ), 
					'description'	=> esc_html__( 'If enabled in the settings, you can use additional quantity options of 1/4, 1/2, and 3/4 when adding meals to an entry.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Admin Search', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Search for users by name or email address.', 'meal-tracker' )
				],
				[ 	
					'title'			=> esc_html__( 'Additional settings', 'meal-tracker' ), 
					'description'	=> esc_html__( 'Additional settings for tailoring your Meal Tracker experience.', 'meal-tracker' )
				],
	];	
}