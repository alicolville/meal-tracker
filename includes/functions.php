<?php

	/**
	 * Helper function to ensure all fields have expected keys
	 *
	 * @param $data
	 * @param $expected_fields
	 * @return bool
	 */
	function yk_mt_array_check_fields($data, $expected_fields ) {

		foreach ( $expected_fields as $field ) {
			if ( false === isset( $data[ $field ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate an ISO date
	 *
	 * @param $iso
	 *
	 * @return bool
	 */
	function yk_mt_date_is_valid_iso( $iso ) {

		if ( true === empty( $iso ) ) {
			return false;
		}

		$iso = explode( '-', $iso );

		if ( 3 !== count( $iso ) ) {
			return false;
		}

		return checkdate ( $iso[ 1 ], $iso[ 2 ], $iso[ 0 ] );
	}

	/**
	 * Get today's date in ISO
	 *
	 * @return string
	 */
	function yk_mt_date_iso_today() {
		return date( 'Y-m-d' );
	}