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

			$html = yk_mt_html_accordion_open();

			$active_tab = true;

			foreach ( $meal_types as $meal ) {

				$html .= yk_mt_html_accordion_section( $meal['name'], '', $active_tab );

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
	function yk_mt_html_accordion_section( $title = '', $content = '', $is_active = false ) {

		global $yk_mt_accordion_id;

		$yk_mt_accordion_id++;

		$accordion_section_class = apply_filters( 'yk_mt_shortcode_meal_tracker_accordion_section', '' );

		$html = sprintf( '  <div class="yk-mt-accordion-section %2$s">
									<a class="yk-mt-accordion-section-title%3$s" href="#yk-mt-acc-%1$d">%4$s</a>
									<div id="yk-mt-acc-%1$d" class="yk-mt-accordion-section-content">
										%5$s
									</div>
								</div>',
			$yk_mt_accordion_id,
			esc_attr( $accordion_section_class ),
			( true === $is_active ) ? ' initial-active' : '',
			esc_html( $title ),
			wp_kses_post( $content )
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