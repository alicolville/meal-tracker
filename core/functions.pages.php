<?php

defined('ABSPATH') or die("Jog on!");

// ------------------------------------------------------------------------------
// User Side Bar
// ------------------------------------------------------------------------------

/**
 * @param $user_id
 */
function yk_mt_user_side_bar( $user_id ) {

    if( true === empty( $user_id ) )  {
        return;
    }

    ?>
    <div class="postbox">
        <h2 class="hndle"><?php echo __( 'User Search', YK_MT_SLUG ); ?></h2>
        <div class="inside">
            <?php // yk_mt_box_user_search_form(); ?>
        </div>
    </div>

    <div class="postbox ws-ls-user-data">
        <h2 class="hndle"><span><?php echo __( 'User Information', YK_MT_SLUG ); ?></span></h2>
        <div class="inside">
            <table class="ws-ls-sidebar-stats">

                <tr>
                    <th><?php echo __( 'No. of Entries', YK_MT_SLUG ); ?></th>
                    <td>123</td>
                </tr>

            </table>
        </div>
    </div>
    <div class="postbox ws-ls-user-data">
        <h2 class="hndle"><span><?php echo __( 'Settings', YK_MT_SLUG ); ?></span></h2>
        <div class="inside">
            <a class="button-secondary" href="#">
                <i class="fa fa-cog"></i>
                <?php echo __( 'Preferences', YK_MT_SLUG ); ?>
            </a>
            <a href="<?php echo esc_url( get_edit_user_link( $user_id ) ); ?>" class="button-secondary"><i class="fa fa-wordpress"></i> WordPress Record</a>
        </div>
    </div>
    <?php

}

/**
 * Displays a navigational header at top of user data page
 *
 * @param $user_id
 * @param bool $previous_url
 */
function yk_mt_user_header( $user_id, $previous_url = false ) {

    if( false === is_numeric($user_id)) {
        return;
    }

    $user_data = get_userdata( $user_id );

    if( false === $user_data ) {
        return;
    }

    $previous_url = ( true === empty( $previous_url ) ) ? yk_mt_link_admin_page_user_dashboard() : $previous_url;

    $additional_links = apply_filters( 'yk_mt_user_profile_header_links', '' );

    printf('
        <h3>%s %s</h3>
        <div class="postbox ws-ls-user-data">
            <div class="inside">
                <a href="%s" class="button-secondary"><i class="fa fa-arrow-left"></i> %s</a>
                <a href="%s" class="button-secondary"><i class="fa fa-wordpress"></i> %s</a>
                <a href="%s" class="button-secondary"><i class="fa fa-pie-chart"></i> %s</a>
                %s
            </div>
        </div>',
        $user_data->user_nicename,
        yk_mt_link_email_for_user( $user_id, true ),
        esc_url( $previous_url ),
        __( 'Back', YK_MT_SLUG ),
        esc_url( get_edit_user_link( $user_id ) ),
        __( 'WordPress Record', YK_MT_SLUG ),
        yk_mt_link_admin_page_user_render( $user_id ),
        __( 'Meal Tracker Record', YK_MT_SLUG ),
        wp_kses_post( $additional_links )
    );
}

/**
 * Simple function to render a user's email address
 *
 * @param $user_id
 * @param bool $include_brackets
 * @return string
 */
function yk_mt_link_email_for_user( $user_id = NULL, $include_brackets = false ) {

    $user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;

    $user_data = get_userdata( $user_id );

    if ( true === empty($user_data->user_email) ) {
        return '';
    }

    return sprintf('  %1$s<a href="mailto:%2$s">%2$s</a>%3$s',
                                ( $include_brackets ) ? '(' : '',
                                esc_attr( $user_data->user_email ),
                                ( $include_brackets ) ? ')' : ''
    );
}

/**
 * Return base URL for user data
 * @return string
 */
function yk_mt_link_admin_page_user_dashboard() {
    return yk_mt_link_admin_page_user( 0, $mode = 'dashboard' );
}
/**
 * Get a link to an admin User page
 * @param $user_id
 * @param string $mode
 * @return string
 */
function yk_mt_link_admin_page_user( $user_id, $mode = 'user' ) {

    if ( false === is_numeric( $user_id ) ) {
        return '#';
    }

    $url = sprintf( 'admin.php?page=yk-mt-user&mode=%1$s&user-id=%2$d', $mode, $user_id );

    $url = admin_url( $url );

    return esc_url( $url );
}

/**
 * Given a user ID, return a link to the user's profile
 * @param int $user_id User ID
 * @param null $display_text
 * @return string
 */
function yk_mt_link_admin_page_user_render( $user_id, $display_text = NULL ) {

    $profile_url = yk_mt_link_admin_page_user( $user_id );

    return ( false === empty( $display_text ) ) ?
        yk_mt_link_render( $profile_url, $display_text ) :
        $profile_url;
}

/**
 * @param $link
 * @param $label
 *
 * @return string
 */
function yk_mt_link_render( $link, $label ) {
    return sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $label ) );
}