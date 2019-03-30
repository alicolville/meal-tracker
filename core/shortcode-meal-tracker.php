<?php

	defined('ABSPATH') or die("Jog on!");

	/**
	 * Generate HTML output for the shorcode [meal-tracker]
	 *
	 * @return string
	 */
	function yk_mt_shortcode_meal_tracker() {

		$html = '<!-- Meal Tracker Start -->';

		$html .= yk_mt_shortcode_meal_tracker_meal_types();

		return $html;
	}
	add_shortcode( 'meal-tracker', 'yk_mt_shortcode_meal_tracker' );

	function yk_mt_shortcode_meal_tracker_meal_types() {

		$html = '<!-- Start Meal Types -->';

		// Fetch all meal types that haven't been deleted
		$meal_types = yk_mt_db_meal_types_all();

		if ( false === empty( $meal_types ) ) {

			yk_mt_shortcode_meal_tracker_localise();

			//TODO: Change entry ID to lokup for today
			$todays_meals = yk_mt_db_entry_get();

			$html = yk_mt_html_accordion_open();

			$active_tab = true;
print_r($meal_types);
print_r($todays_meals);
			// For each meal type, display an accordian and relevant meal data
			foreach ( $meal_types as $meal_type ) {

				$meal_type_html = sprintf( '<p class="yk-mt-no-meals">%1$s, <a href="%2$s">%3$s</a>.</p>',
										__( 'No data', YK_MT_SLUG ),
										'#',
										__( 'add a meal now', YK_MT_SLUG )
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