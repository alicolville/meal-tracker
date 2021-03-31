<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Premium license?
 *
 * @return bool
 */
function yk_mt_license_is_premium() {
    return (bool) get_option( 'yk-mt-license-valid', false );
}

/**
 * Return a link to the upgrade page
 *
 * @return string
 */
function yk_mt_license_upgrade_link() {

    $link = admin_url('admin.php?page=yk-mt-license');

    return esc_url( $link );
}

/**
 *	Check an existing license's hash is still valid
 **/
function yk_mt_license_validate( $license ) {

    if ( true === empty( $license ) ) {
        return __( 'License missing', YK_MT_SLUG );
    }

    // Decode license
    $license = yk_mt_license_decode( $license );

    if ( true === empty( $license ) ) {
        return 'Could not decode / verify license';
    }

    // Does site hash in license meet this site's actual hash?
	if ( true === empty( $license['site-hash'] ) ) {
		return 'Invalid license hash';
	}

	// Does site hash in license meet this site's actual hash?
	if ( 'mt-premium' !== $license[ 'type' ] ) {
		return 'Invalid license type';
	}

    // Match this site hash?
    if ( yk_mt_generate_site_hash() !== $license['site-hash'] ) {
        return 'This license doesn\'t appear to be for this site (no match on site hash).';
    }

    // Valid date?
    $today_time = strtotime( date( 'Y-m-d' ) );
    $expire_time = strtotime( $license['expiry-date'] );

    if ( $expire_time < $today_time ) {
        return 'This license has expired.';
    }

    return true;
}

/**
 * Validate and decode a license
 **/
function yk_mt_license_decode( $license ) {

    if( true === empty( $license ) ) {
        return NULL;
    }

    // Base64 and JSON decode
    $license = base64_decode( $license );

    if( false === $license ) {
        return NULL;
    }

    $license = json_decode( $license, true );

    if( true === empty( $license ) ) {
        return NULL;
    }

    // Validate hash!
    $verify_hash = md5( 'yeken.uk' . $license['type'] . $license['expiry-days'] . $license['site-hash'] . $license['expiry-date'] );

    return ( $license['hash'] == $verify_hash && false === empty( $license ) ) ? $license : NULL;
}


/**
 * Validate and apply a license
 **/
function yk_mt_license_apply( $license ) {

    // Validate license
    $license_result = yk_mt_license_validate($license);

    if( true === $license_result ) {

        update_option( 'yk-mt-license', $license );
        update_option( 'yk-mt-license-valid', true );

        return true;
    }

    return false;
}

/**
 * Remove a license
 **/
function yk_mt_license_remove() {

    delete_option( 'yk-mt-license' );
    delete_option( 'yk-mt-license-valid' );
}

/**
 *	Generate a site hash to identify this site.
 **/
function yk_mt_generate_site_hash() {

    $site_hash = get_option( 'yk-mt-hash' );

    // Generate a basic site key from URL and plugin slug
    if( false == $site_hash ) {

        $site_hash = md5( 'yeken-yk-mt-' . site_url() );
        $site_hash = substr( $site_hash, 0, 6 );

        update_option( 'yk-mt-hash', $site_hash );

    }
    return $site_hash;
}

/**
 * Fetch license
 *
 * @return mixed
 */
function yk_mt_license() {
    return get_option( 'yk-mt-license', '' );
}

/**
 * Fetch license price
 *
 * @return float|null
 */
function yk_mt_license_price() {

    $price = yeken_license_price( 'mt-premium' );

    return ( false === empty( $price ) ) ? $price : YK_MT_PREMIUM_PRICE;
}

if ( false === function_exists( 'yeken_license_api_fetch_licenses' ) ) {

    /**
     * Call out to YeKen API for license prices
     */
    function yeken_license_api_fetch_licenses() {

        if ( $cache = get_transient( 'yeken_api_prices' ) ) {
           return $cache;
        }

        $response = wp_remote_get( 'https://shop.yeken.uk/wp-json/yeken/v1/license-prices/' );

        // All ok?
        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

            $body = wp_remote_retrieve_body( $response );

            if ( false === empty( $body ) ) {

                $body = json_decode( $body, true );
                set_transient( 'yeken_api_prices', $body, 216000 ); // Cache for 6 hours

                return $body;
            }
        }

        return NULL;
    }

    /**
     * Fetch a certain product price
     * @param $sku
     * @param string $type
     */
    function yeken_license_price( $sku, $type = 'yearly' ) {

        $licenses = yeken_license_api_fetch_licenses();

        return ( false === empty( $licenses[ $sku ][ $type ] ) ) ? $licenses[ $sku ][ $type ] : NULL;
    }

    /**
     * Render out license prices
     *
     * @param $args
     * @return mixed|string
     */
    function yeken_license_shortcode( $args ) {

        $args = wp_parse_args( $args, [ 'sku' => 'mt-premium', 'type' => 'yearly', 'prefix' => '&pound;' ] );

        $price = yeken_license_price( $args[ 'sku' ], $args[ 'type' ] );

        if ( false === empty( $price ) ) {
            return sprintf( '%s%d', esc_html(  $args[ 'prefix' ] ), $price );
        }

        return '';
    }
    add_shortcode( 'yeken-license-price', 'yeken_license_shortcode' );

}

/**
 * Add a CSS class to the body depending on whether we have a Pro license or not
 * @param $classes
 * @return array
 */
function yk_mt_license_body_class( $classes ) {

    $class = ( true === yk_mt_license_is_premium() ) ? 'pro' : 'not-pro';

    $classes[] = 'yk-mt-' . $class;

    return $classes;
}
add_filter( 'body_class','yk_mt_license_body_class' );
