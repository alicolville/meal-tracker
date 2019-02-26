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