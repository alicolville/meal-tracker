<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 * Generate HTML output for the shorcode [meal-tracker]
	 *
	 * @return string
	 */
	function yk_mt_shortcode_meal_tracker() {

	    // TODO: Check here to ensure the shortcode has only been placed once!

		if ( true === is_admin() ) {
			return '';
		}

		$shortcode_mode = yk_mt_shortcode_get_mode();

		$html = '<!-- Meal Tracker Start -->';

		// Is the user logged in?
		if ( false === is_user_logged_in() ) {
			return yk_mt_shortcode_log_in_prompt();
		}

		$html .= '<div id="yk-mt-shortcode-meal-tracker" class="yk-mt-shortcode-meal-tracker">';

        // This is used to create an empty entry if one doesn't already exist for this user / day
        yk_mt_entry_get_id_or_create();

        yk_mt_shortcode_meal_tracker_localise( $shortcode_mode );

		// Load settings?
		if ( 'settings' === $shortcode_mode ) {

			$html .= yk_mt_shortcode_meal_tracker_settings();

		} else {

			$html .= yk_mt_shortcode_meal_tracker_summary();

			$html .= yk_mt_shortcode_meal_tracker_meal_types();

			if ( true === yk_mt_is_pro() ) {
				$html .= sprintf( '<br /><button href="%s" class="yk-mt-button-small yk-mt-button-secondary yk-mt-clickable">%s</button>', yk_mt_shortcode_get_current_url( 'settings' ), __( 'Settings', YK_MT_SLUG ) );
			}

			// Embed hidden form / dialog required for adding a meal
			$html .= yk_mt_shortcode_meal_tracker_add_meal_dialog();

		}

		$html .= '</div>';

		return $html;
	}
	add_shortcode( 'meal-tracker', 'yk_mt_shortcode_meal_tracker' );

	/**
	 * Get current URL with mode argument added
	 * @param $mode
	 *
	 * @return mixed
	 */
	function yk_mt_shortcode_get_current_url( $mode = '' ) {
		return add_query_arg( 'yk-mt-mode', $mode, get_permalink() );
	}

	/**
	 * Get current mode
	 * @return mixed|null
	 */
	function yk_mt_shortcode_get_mode() {
		$mode = ( false === empty( $_GET[ 'yk-mt-mode' ] ) ) ?
									$_GET[ 'yk-mt-mode' ] :
										'default';

		if ( 'default' !== $mode && true !== yk_mt_is_pro() ) {
			$mode = 'default';
		}

		return $mode;
	}

    /**
     * Display chart JS and summary data
     */
	function yk_mt_shortcode_meal_tracker_summary() {

		return '<div class="yk-mt-t yk-mt-summary-table">
			                <div class="yk-mt-r" >
			                        <div class="yk-mt-c yk-mt-summary-chart-slot">
			                            <canvas id="yk-mt-chart" class="yk-mt-chart"></canvas>
			                        </div>
			                        
			                </div>
	                    </div>';
    }

	/**
	 * Render Settings tab
	 *
	 * @return string
	 */
    function yk_mt_shortcode_meal_tracker_settings() {

	    $html = yk_mt_html_accordion_open( 'yk-mt-settings' );

	    $html .= '<form id="yk-mt-settings-form" class="yk-mt-settings-form" >';

	    if ( true === yk_mt_site_options( 'allow-calorie-override' ) ) {

		    /**
		     * Calories Tab
		     */
		    $calories_html = yk_mt_form_number( __( 'Specify your own allowed calorie intake for each day. Current: ', YK_MT_SLUG ) .
		                                            yk_mt_user_calories_target() . __( 'kcal', YK_MT_SLUG ),
			    'allowed-calories',
			    yk_mt_settings_get( 'allowed-calories' ),
		        '',
		        1,
		        1,
		        20000
		    );

		    $html .= yk_mt_html_accordion_section( [    'id' => 1,
		                                                'title' => __( 'Calorie Intake', YK_MT_SLUG ),
		                                                'content' => $calories_html,
		                                                'is-active' => true
		    ]);
	    }

	    $html .= yk_mt_html_accordion_close();

	    $html .= sprintf( '<br /><button id="yk-mt-button-save-settings" class="yk-mt-button-secondary">%1$s</button>
                                    &nbsp;<button href="%2$s" class="yk-mt-button-cancel yk-mt-clickable">%3$s</button>
                                    </form></div>',
            __( 'Save Settings', YK_MT_SLUG ),
            yk_mt_shortcode_get_current_url(),
            __( 'Cancel', YK_MT_SLUG )
	    );

	    return $html;
    }

	/**
	 * Render HTML for Meal Types
	 *
	 * @return string
	 */
	function yk_mt_shortcode_meal_tracker_meal_types() {

		$html = '<!-- Start Meal Types -->';

		// Fetch all meal types that haven't been deleted
		$meal_types = yk_mt_db_meal_types_all();

		if ( false === empty( $meal_types ) ) {

			$html = yk_mt_html_accordion_open( 'yk-mt-meal-types' );

			$active_tab = true;

			// For each meal type, display an accordion and relevant meal data
			foreach ( $meal_types as $meal_type ) {

                // Add the "Add Meal" prompt now
                $meal_type_html = sprintf( '<p>%s</p>', yk_mt_shortcode_meal_tracker_add_meal_button( __( 'Add Meal', YK_MT_SLUG ), $meal_type['id'] ) );

                $meal_list_class = apply_filters( 'yk_mt_shortcode_meal_tracker_meal_list', 'yk-mt-t yk-mt-list-of-meals' );

                $localised_strings = yk_mt_localised_strings();

                $meal_type_html .= sprintf( '<div id="meals-table-%d" class="%s">%s.</div>',
                                                    $meal_type['id'],
                                                    esc_attr( $meal_list_class ),
                                                    $localised_strings[ 'no-data' ]
                );

				$html .= yk_mt_html_accordion_section( [    'id' => $meal_type['id'],
															'title' => $meal_type['name'],
															'content' => $meal_type_html,
															'is-active' => $active_tab
				]);

				$active_tab = false;
			}

			$html .= yk_mt_html_accordion_close();
		}

		return $html;

	}

	$yk_mt_add_meal_button_id = 0;

	/**
	 * Return the HTML required to trigger the Add Meal dialog box.
	 *
	 * @param $button_text
	 * @param null $meal_type_id
	 *
	 * @return string
	 */
	function yk_mt_shortcode_meal_tracker_add_meal_button( $button_text, $meal_type_id = NULL, $default_css_class = 'yk-mt-button-small' ) {

		global $yk_mt_add_meal_button_id;

		$yk_mt_add_meal_button_id++;

		$css_class = apply_filters( 'yk_mt_shortcode_button_meal_add_css', $default_css_class );
		$button_text = apply_filters( 'yk_mt_shortcode_button_meal_add_text', $button_text );

		return sprintf( '<button href="#yk-mt-add-meal-dialog" class="%1$s yk-mt-add-meal-prompt" id="%3$d" data-meal-type="%2$d">%4$s</button>',
						esc_attr( $css_class ),
						(int) $meal_type_id,
						(int) $yk_mt_add_meal_button_id,
						esc_html( $button_text )
			       );
	}

	/**
	 * Render HTML required for "Add Meal" dialog.
	 *
	 * This HTML remains hidden until the relevant HTML prompt is clicked (has to have a class "yk-mt-add-meal-prompt")
	 *
	 * @return string
	 */
	function yk_mt_shortcode_meal_tracker_add_meal_dialog() {

		$top = apply_filters( 'yk_mt_shortcode_dialog_top', 30 );

        $html = sprintf( '<div id="yk-mt-add-meal-dialog" style="%1$dpx" data-meal-type="0" class="yk-mt-hide">
                             <div class="yk-mt-modal-header">
                                <h3 class="yk-mt-hide-if-editing">%3$s</h3>
                                <h3 class="yk-mt-hide-if-adding">%4$s</h3>
                                <button id="btn-close-modal" class="close-yk-mt-add-meal-dialog yk-mt-button-silent">
                                    %2$s
                                </button>
                             </div>   
                			 <div class="yk-mt-modal-content">
                			    <div class="yk-mt-hide-if-editing">
                			    <form id="yk-mt-form-add-meal-to-entry">
				                ',
				            $top,
                            __( 'Close', YK_MT_SLUG ),
                            __( 'Log a meal', YK_MT_SLUG ),
                            __( 'Edit meal', YK_MT_SLUG )
        );

        // Build HTML for "Add Meal" tab
        $add_form = yk_mt_shortcode_meal_tracker_add_meal_select( 'yk-mt-meal-id' );

        // Do we have any existing meals for this user?
        if ( false === empty( $add_form ) ) {

            $html .= sprintf('  <h5>%1$s</h5>', __( 'Quick Search', YK_MT_SLUG ) );

	        $html .= $add_form;

            $html .= sprintf('  <div class="yk-mt-quantity-row"><label>%1$s:</label>', __( 'Add', YK_MT_SLUG ) );

            for ( $i = 1; $i <= 10; $i++ ) {
                 $html .= sprintf( '<button id="yk-mt-button-add-meal-%1$d" data-quantity="%1$d" class="yk-mt-meal-button-add yk-mt-button-silent">%1$d</button>', $i );
            }
        }

        $html .= sprintf('    </div>
                                  </form>
                                  <div class="yk-mt-auto-close">
                                        <input type="checkbox" id="%1$s" checked="checked" />
                                        <label for="%1$s">%2$s</label>
                                  </div>
                                </div>',
                                'yk-mt-button-add-meal-close',
                                __( 'Close screen after adding meal(s)', YK_MT_SLUG )
        );

        $html .= yk_mt_shortcode_meal_tracker_add_new_meal_form();

        $html .= '</div></div>
                <a id="yk-mt-open-dialog-edit" class="yk-mt-meal-button-edit yk-mt-add-meal-prompt yk-mt-hide"></a>               
        ';

		return $html;
	}

	/**
	 * Render Add new meal form
	 * @return string
	 */
	function yk_mt_shortcode_meal_tracker_add_new_meal_form() {

		$html = sprintf( '  <div class="yk-mt-add-new-meal-form">
                                        <form id="yk-mt-form-add-new-meal">
								<h5 id="yk-mt-header-meal-add" class="yk-mt-hide-if-editing">%s</h5>', __( 'Add new meal', YK_MT_SLUG ) );

		$html .= yk_mt_form_text( __( 'Name', YK_MT_SLUG ),	'add-meal-name' );

		$html .= yk_mt_form_text( __( 'Description', YK_MT_SLUG ), 'add-meal-description', '', 200, false );

		$html .= yk_mt_form_number( __( 'Calories', YK_MT_SLUG ), 'add-meal-calories' );

        $html .= sprintf( ' <p class="yk-mt-info yk-mt-hide-if-adding">%1$s</p>',
            __( 'Calorie counts will automatically be adjusted for today\'s entry if a meal\'s calrie value is changed. Other entries will only be updated if manually amended.', YK_MT_SLUG )
        );

		$html .= yk_mt_form_select( __( 'Unit', YK_MT_SLUG ), 'add-meal-unit', '', yk_mt_units() );

		$html .= yk_mt_form_number( __( 'Quantity', YK_MT_SLUG ), 'add-meal-quantity', '', '', 1, 1, 99999, true, false );

		$html .= sprintf( ' <button id="yk-mt-button-meal-add" class="yk-mt-button-add-new-meal yk-mt-button-secondary yk-mt-hide-if-editing">%1$s</button>',
			__( 'Add a new meal', YK_MT_SLUG )
		);

        $html .= sprintf( ' <button id="yk-mt-button-meal-edit" class="yk-mt-button-secondary yk-mt-hide-if-adding">%1$s</button>',
            __( 'Edit meal', YK_MT_SLUG )
        );

		$html .= '</form></div>';

		return $html;
	}

    /**
     * Render <select> for meals
     *
     * @param $select_name
     * @param null $user_id
     *
     * @return string
     */
	function yk_mt_shortcode_meal_tracker_add_meal_select( $select_name, $user_id = NULL ) {

        return sprintf(   '<select id="%1$s" name="%1$s" class="yk-mt-select-meal" placeholder="%2$s..." required ></select>',
	                        esc_attr( $select_name ),
		                    __( 'Search for an existing meal', YK_MT_SLUG )
	    );

    }

	/**
	 * Return HTML for opening an accordion
	 *
	 * @return string
	 */
	function yk_mt_html_accordion_open( $id = '' ) {

		$accordion_class = apply_filters( 'yk_mt_shortcode_meal_tracker_accordion', 'yk-mt-accordion' );

		return sprintf( '<div class="%s" %s>', esc_attr( $accordion_class ), ( false === empty( $id ) ? ' id="' . esc_attr( $id ) . '" ' : '' ) );
	}

	$yk_mt_accordion_id = 0;

	/**
	 * @param bool $is_active - determines if this tab should be opened on apen on page load
	 *
	 * @return string
	 */
	function yk_mt_html_accordion_section( $options = [] ) {

		global $yk_mt_accordion_id;

		$yk_mt_accordion_id++;

		$options = wp_parse_args( $options, [
			'id' => $yk_mt_accordion_id,
			'title' => '',
			'content' => '',
			'is-active' => false
		]);

		$accordion_section_class = apply_filters( 'yk_mt_shortcode_meal_tracker_accordion_section', '' );

		$html = sprintf( '  <div class="yk-mt-accordion-section%2$s" id="%1$d">
									<a class="yk-mt-accordion-section-title%3$s" href="#yk-mt-acc-%1$d">%4$s</a>
									<div id="yk-mt-acc-%1$d" class="yk-mt-accordion-section-content">
										%5$s
									</div>
								</div>',
			(int) $options['id'],
			esc_attr( $accordion_section_class ),
			( true === $options['is-active'] ) ? ' initial-active' : '',
			esc_html( $options['title'] ),
			$options['content']
		);

		return $html;
	}

	/**
	 * HTML to close an accordion
	 *
	 * @return string
	 */
	function yk_mt_html_accordion_close() {
		return '</div>';
	}

	$yk_mt_shortcode_meal_tracker_modal_enqueued = false;

	/**
	 * Enqueue CSS / JS for this shortcode
	 */
	function yk_mt_shortcode_meal_tracker_enqueue_scripts() {

        // Don't include JS / CSS in admin.
        if ( true === is_admin() ) {
            return;
        }

        global $post;

        if( ! ( ( is_page() || is_single() )
                && has_shortcode( $post->post_content, 'meal-tracker') ) ) {
            return;
        }

		global $yk_mt_shortcode_meal_tracker_modal_enqueued;

		// Only enqueue dialog etc once.
		if ( true === $yk_mt_shortcode_meal_tracker_modal_enqueued ) {
			return;
		}

        $minified = yk_mt_use_minified();

       	wp_enqueue_style( 'mt-meal-tracker-normalize', plugins_url( 'assets/css/normalize.min.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );
        wp_enqueue_style( 'mt-animate', plugins_url( 'assets/css/animate.min.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );
        wp_enqueue_style( 'mt-selectize', 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.default.min.css', [], YK_MT_PLUGIN_VERSION );
        wp_enqueue_style( 'meal-tracker', plugins_url( 'assets/css/frontend' . $minified . '.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );
        wp_enqueue_style( 'mt-forced', plugins_url( 'assets/css/forced' . $minified . '.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );

       //todo  yk_mt_enqueue_front_end_dependencies();

		wp_enqueue_script( 'mt-modal', plugins_url( 'assets/js/animatedModal.min.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION, true );
        wp_enqueue_script( 'mt-selectize', 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js', [], YK_MT_PLUGIN_VERSION, true );
        wp_enqueue_script( 'mt-loading-overlay', plugins_url( 'assets/js/loadingoverlay.min.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION, true );
        wp_enqueue_script( 'mt-chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js', [ 'jquery' ], YK_MT_PLUGIN_VERSION );
        wp_enqueue_script( 'mt-notify', plugins_url( 'assets/js/notify.min.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION, true );

        wp_enqueue_script( 'meal-tracker', plugins_url( 'assets/js/core' . $minified . '.js', __DIR__ ),
                        [ 'mt-modal', 'mt-selectize', 'mt-loading-overlay', 'mt-chart-js', 'mt-notify' ], YK_MT_PLUGIN_VERSION, true );

        wp_localize_script( 'meal-tracker', 'yk_mt', yk_mt_ajax_config() );

        $yk_mt_shortcode_meal_tracker_modal_enqueued = true;
	}
    add_action( 'wp_enqueue_scripts', 'yk_mt_shortcode_meal_tracker_enqueue_scripts');

	/**
	 * Add relevant data into JS object
	 */
	function yk_mt_shortcode_meal_tracker_localise( $shortcode_mode = '' ) {

		$dialog_options = [
			'color'         => '#FFFFFF',
			'zIndexIn'      => '999999',
			'opacityIn'     => '1'
		];

		$dialog_options = apply_filters( 'yk_mt_shortcode_meal_tracker_dialog_options', $dialog_options );

		wp_localize_script( 'meal-tracker', 'yk_mt_sc_meal_tracker', [
			'mode'              => $shortcode_mode,
			'dialog-options'    => json_encode( $dialog_options ),
            'localise'          => yk_mt_localised_strings(),
            'todays-entry'      => yk_mt_entry(),
            'load-entry'        => true
		] );
	}