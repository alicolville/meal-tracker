<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 * Generate HTML output for the shorcode [meal-tracker]
	 *
	 * @return string
	 */
	function yk_mt_shortcode_meal_tracker() {

		$html = '<!-- Meal Tracker Start -->';

		// Is the user logged in?
		if ( false === is_user_logged_in() ) {
			return yk_mt_shortcode_log_in_prompt();
		}

		$html .= yk_mt_shortcode_meal_tracker_meal_types();

		// Embed hidden form / dialog required for adding a meal
		$html .= yk_mt_shortcode_meal_tracker_add_meal_dialog();

		return $html;
	}
	add_shortcode( 'meal-tracker', 'yk_mt_shortcode_meal_tracker' );

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

			yk_mt_shortcode_meal_tracker_localise();

			$html = yk_mt_html_accordion_open( 'yk-mt-meal-types' );

			$active_tab = true;

			// For each meal type, display an accordion and relevant meal data
			foreach ( $meal_types as $meal_type ) {

                // Add the "Add Meal" prompt now
                $meal_type_html = sprintf( '<p>%s</p>', yk_mt_shortcode_meal_tracker_add_meal_button( __( 'Add Meal', YK_MT_SLUG ), $meal_type['id'] ) );

                $meal_list_class = apply_filters( 'yk_mt_shortcode_meal_tracker_meal_list', 'yk-mt-t' );

                $meal_type_html .= sprintf( '<div id="meals-table-%d" class="%s">%s.</div>',
                                                    $meal_type['id'],
                                                    esc_attr( $meal_list_class ),
                                                    __( 'No data has been entered', YK_MT_SLUG )
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
	function yk_mt_shortcode_meal_tracker_add_meal_button( $button_text, $meal_type_id = NULL, $default_css_class = 'button' ) {

		global $yk_mt_add_meal_button_id;

		$yk_mt_add_meal_button_id++;

		$css_class = apply_filters( 'yk_mt_shortcode_button_meal_add_css', $default_css_class );
		$button_text = apply_filters( 'yk_mt_shortcode_button_meal_add_text', $button_text );

		return sprintf( '<a href="#yk-mt-add-meal-dialog" class="%1$s yk-mt-add-meal-prompt btn button" id="%3$d" data-meal-type="%2$d">%4$s</a>',
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

		yk_mt_shortcode_meal_tracker_enqueue_scripts();

	    $top = apply_filters( 'yk_mt_shortcode_dialog_top', 30 );

        $html = sprintf( '<div id="yk-mt-add-meal-dialog" style="%1$dpx" data-meal-type="0" >
                            <div id="btn-close-modal" class="close-yk-mt-add-meal-dialog">
                                %2$s
                            </div>
                			<div class="modal-content">
				                <h3>%3$s</h3>',
				            $top,
                            __( 'CLOSE MODAL', YK_MT_SLUG ),
                            __( 'Search for a meal', YK_MT_SLUG )
        );

        // Build HTML for "Add Meal" tab
        $add_form = yk_mt_shortcode_meal_tracker_add_meal_select( 'yk-mt-meal-id' );

        // Do we have any existing meals for this user?
        if ( false === empty( $add_form ) ) {

            $html .= '<input type="number" id="yk-mt-quantity" value="1" min="1" max="50" step="1" />';

            $html .= sprintf( '<button class="yk-mt-meal-button-add btn button">%s</button>', __( 'Add', YK_MT_SLUG ) );

            $html .= sprintf( '<button class="yk-mt-meal-button-add btn button close-yk-mt-add-meal-dialog">%s</button>', __( 'Add & Close', YK_MT_SLUG ) );

            $html .= $add_form;
        }

        $html .= '<h4>Add new meal</h4>';


        $html .= '</div></div>';

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

		$meals = yk_mt_db_meal_for_user( $user_id );

		if ( true === empty( $meals ) ) {
		    return '';
        }

        $html = sprintf(   '<select id="%1$s" name="%1$s" class="yk-mt-select-meal" placeholder="%2$s...">',
	                        esc_attr( $select_name ),
		                    __( 'Find a meal', YK_MT_SLUG )
	    );

		foreach ( $meals as $meal ) {
			$html .= sprintf( '<option value="%1$s">%2$s ( %3$s %4$s / %5$sg )</option>',
                                esc_attr( $meal['id'] ),
                                esc_html( $meal['name'] ),
				                esc_html( $meal['calories'] ),
				                __( 'kcal', YK_MT_SLUG ),
				                esc_html( $meal['quantity'] )
			);
        }

		$html .= '</select>';

	    return $html;
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
			$options['content'] // TODO: Add some sort of sanitiser
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

		global $yk_mt_shortcode_meal_tracker_modal_enqueued;

		// Only enqueue dialog etc once.
		if ( true === $yk_mt_shortcode_meal_tracker_modal_enqueued ) {
			return;
		}

		wp_enqueue_script( 'meal-tracker-modal', plugins_url( 'assets/js/animatedModal.min.js', __DIR__ ), [ 'jquery' ], YK_MT_PLUGIN_VERSION, true );
		wp_enqueue_style( 'meal-tracker-normalize', plugins_url( 'assets/css/normalize.min.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );
		wp_enqueue_style( 'meal-tracker-animate', plugins_url( 'assets/css/animate.min.css', __DIR__ ), [], YK_MT_PLUGIN_VERSION );

		// Bring in jQuery library for fancy drop down box
		wp_enqueue_style( 'selectize', 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.default.min.css', [], YK_MT_PLUGIN_VERSION );
		wp_enqueue_script( 'selectize', 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js', [], YK_MT_PLUGIN_VERSION, true );

		$yk_mt_shortcode_meal_tracker_modal_enqueued = true;
	}

	/**
	 * Add relevant data into JS object
	 */
	function yk_mt_shortcode_meal_tracker_localise() {

		$dialog_options = [
			'color' => '#FFFFFF',
			'top' => '30px',
		];

		$dialog_options = apply_filters( 'yk_mt_shortcode_meal_tracker_dialog_options', $dialog_options );

		wp_localize_script( 'meal-tracker', 'yk_mt_sc_meal_tracker', [
			'dialog-options'    => json_encode( $dialog_options ),
            'localise'          => yk_mt_localised_strings(),
            'todays-entry'      => yk_mt_entry(),
            'load-entry'        => true
		] );
	}