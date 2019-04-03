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

			$todays_meals = yk_mt_db_entry_get();

			$html = yk_mt_html_accordion_open();

			$active_tab = true;
//TODO
// print_r($meal_types);
// print_r($todays_meals);
			// For each meal type, display an accordian and relevant meal data
			foreach ( $meal_types as $meal_type ) {

				$meal_type_html = sprintf( '<p class="yk-mt-no-meals">%1$s, <a href="%2$s">%3$s</a>.</p>',
										__( 'No data for today', YK_MT_SLUG ),
										'#',
										yk_mt_shortcode_meal_tracker_add_meal_button( __( 'Add Meal', YK_MT_SLUG ), $meal_type['id'], '' )
				);

				if ( false === empty( $todays_meals['meals'][ $meal_type['id'] ] ) ) {
					$meal_type_html = yk_mt_html_meals_list( $meal_type['id'], $todays_meals['meals'][ $meal_type['id'] ] );
				}

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

		return sprintf( '<a href="#yk-mt-add-meal-dialog" class="%1$s yk-mt-add-meal-prompt" id="%2$d" data-meal-id="%3$d">%4$s</a>',
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

		?>

		<!--DEMO01-->
		<div id="yk-mt-add-meal-dialog">
			<!--THIS IS IMPORTANT! to close the modal, the class name has to match the name given on the ID -->
			<div  id="btn-close-modal" class="close-yk-mt-add-meal-dialog">
				CLOSE MODAL
			</div>

			<div class="modal-content">

				<!--Your modal content goes here-->
			</div>
		</div>

		<?php

		return '';
	}

	/**
	 * Return HTML for opening an accordion
	 *
	 * @return string
	 */
	function yk_mt_html_accordion_open() {

		$accordion_class = apply_filters( 'yk_mt_shortcode_meal_tracker_accordion', 'yk-mt-accordion' );

		return sprintf( '<div class="%s">', esc_attr( $accordion_class ) );
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

		$html = sprintf( '  <div class="yk-mt-accordion-section %2$s" id="%1$d">
									<a class="yk-mt-accordion-section-title%3$s" href="#yk-mt-acc-%1$d">%4$s</a>
									<div id="yk-mt-acc-%1$d" class="yk-mt-accordion-section-content">
										%5$s
									</div>
								</div>',
			(int) $options['id'],
			esc_attr( $accordion_section_class ),
			( true === $options['is-active'] ) ? ' initial-active' : '',
			esc_html( $options['title'] ),
			wp_kses_post( $options['content'] )
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

		wp_add_inline_script( 'meal-tracker-modal', ' jQuery(".yk-mt-add-meal-prompt").animatedModal(); ' );

		$yk_mt_shortcode_meal_tracker_modal_enqueued = true;
	}

	/**
	 * Add relevant data into JS object
	 */
	function yk_mt_shortcode_meal_tracker_localise() {

		wp_localize_script( 'meal-tracker-js', 'yk_mt', [
			'html-meal-row' => yk_mt_html_template_row()
		] );
	}

	/**
	 * Render a meals object into a <div> table
	 *
	 * @param $meals
	 *
	 * @return string
	 */
	function yk_mt_html_meals_list( $meal_type_id, $meals ) {

		if ( true === empty( $meals ) ) {
			return '';
		}

		$meal_list_class = apply_filters( 'yk_mt_shortcode_meal_tracker_meal_list', 'yk-mt-t' );

		$html = sprintf( '<div class="%s">', esc_attr( $meal_list_class ) );

		// Add the "Add Meal" prompt now
		$html .= sprintf( '<p>%s</p>', yk_mt_shortcode_meal_tracker_add_meal_button( __( 'Add Meal', YK_MT_SLUG ), $meal_type_id ) );

		$total = 0;

		foreach ( $meals as $meal ) {

			$meal = apply_filters( 'yk_mt_shortcode_meal_tracker_meal_data', $meal );

			$html .= sprintf(   yk_mt_html_template_row(),
								(int) $meal['id'],
								esc_html( $meal['name'] ),
								__( 'Remove', YK_MT_SLUG ),
								esc_html( $meal['calories'] ),
								esc_html( $meal['quantity'] ),
								$meal_type_id
			);

			$total += $meal['calories'];
		}

		$html .= sprintf( ' <div class="yk-mt-r" >
									<div class="yk-mt-c">
									</div>
									<div class="yk-mt-c yk-mt-cq">
										%1$skcal
									</div>	
									<div class="yk-mt-c yk-mt-o">
									</div>
							</div>',
							$total
		);

		$html .= '</div>';

		return $html;
	}

	/**
	 * Return HTML for Meal row
	 *
	 * @return string
	 */
	function yk_mt_html_template_row() {

		$html = '<div class="yk-mt-r" data-mt="%6$d">
					<div class="yk-mt-c">
						%2$s
					</div>
					<div class="yk-mt-c yk-mt-cq">
						%4$skcal / %5$sg
					</div>	
					<div class="yk-mt-c yk-mt-o">
						<a href="#" data-id="%1$d" class="yk-mt-act-r">%3$s</a>
					</div>
				</div>';

		// Remove white spaces, tabs, etc
		return preg_replace( '/\s+/S', ' ', $html );
	}