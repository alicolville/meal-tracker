<?php

defined('ABSPATH') or die('Naw ya dinnie!');

/**
 * Depending on the admin page being viewed, embed some data in JS
 */
function yk_mt_admin_localise() {

    // Do we have an entry ID in the URL?
    $entry = yk_mt_querystring_value( 'entry-id', false );

    if ( false === $entry ) {


        // Try and get user ID from QS
        $user_id = yk_mt_querystring_value( 'user-id', false );

        if ( false !== $user_id ) {

            $entry_id_for_today = yk_mt_db_entry_get_id_for_today( $user_id );

            if ( false === empty( $entry_id_for_today ) ) {
                $entry = yk_mt_entry( $entry );
            }
        }

    } else {
        $entry = yk_mt_entry( $entry );
    }

    wp_localize_script( 'yk-mt-admin', 'yk_mt_sc_meal_tracker', [
        'localise'          => yk_mt_localised_strings(),
        'todays-entry'      => $entry,
        'load-entry'        => ! empty( $entry ),
        'is-admin'          => true
    ]);
}

/**
 * Blur string if incorrect license
 *
 * @param $text
 */
function yk_mt_blur_text( $text, $number_format = true ) {

    if ( true === YK_MT_IS_PREMIUM ) {

        return ( true === $number_format ) ?
                yk_mt_format_number( $text ) :
                        $text;
    }

    $text = str_repeat( '0', strlen( $text ) + 1 );

    return $text;
}
/**
 * Return base URL for user data
 * @return string
 */
function yk_mt_link_user_data() {
    return admin_url( 'admin.php?page=yk-mt-user' );
}

/**
 * Display all user's entries in a data table
 */
function yk_mt_table_user_entries( $args ) {

    $args = wp_parse_args( $args, [
        'user-id'       => get_current_user_id(),
        'entries'       => NULL,
        'show-username' => false
    ]);

    // Fetch entries if non specified
    if ( NULL === $args[ 'entries' ] ) {
        $args[ 'entries' ] = yk_mt_db_entries_summary( [ 'user-id' =>  $args[ 'user-id' ] ] );
    }

    ?>
    <table class="yk-mt-footable yk-mt-footable-basic widefat" data-paging="true" data-sorting="true" data-state="true">
        <thead>
            <tr>
                <th data-type="date" data-format-string="D/M/Y"><?php echo __( 'Date', YK_MT_SLUG ); ?></th>
                <th data-type="text" data-breakpoints="sm"  data-visible="<?php echo ( true == $args[ 'show-username' ] ) ? 'true' : 'false'; ?>">
                    <?php echo __( 'User', YK_MT_SLUG ); ?>
                </th>
                <th data-breakpoints="xs" data-type="number"><?php echo __( 'Calories Allowed', YK_MT_SLUG ); ?></th>
                <th data-breakpoints="sm" data-type="number"><?php echo __( 'Calories Used', YK_MT_SLUG ); ?></th>
                <th data-breakpoints="xs" data-type="number"><?php echo __( 'Calories Remaining', YK_MT_SLUG ); ?></th>
                <th data-breakpoints="xs" data-sortable="false" width="20"><?php echo __( 'Percentage Used', YK_MT_SLUG ); ?></th>
                <th></th>
            </tr>
        </thead>
            <?php
                foreach ( $args[ 'entries' ] as $entry ) {

                    $class = ( $entry[ 'calories_used' ] > $entry[ 'calories_allowed' ] ) ? 'yk-mt-error' : 'yk-mt-ok';

                    printf ( '    <tr class="%6$s">
                                                <td>%1$s</td>
                                                <td>%8$s</td>
                                                <td class="yk-mt-blur">%2$s</td>
                                                <td class="yk-mt-blur">%3$s</td>
                                                <td class="yk-mt-blur">%4$s</td>
                                                <td class="yk-mt-blur">%5$s</td>
                                                <td><a href="%7$s" class="btn btn-default footable-edit"><i class="fa fa-eye"></i></a></td>
                                            </tr>',
                        yk_mt_date_format( $entry['date' ] ),
                        $entry[ 'calories_allowed' ],
                        $entry[ 'calories_used' ],
                        $entry[ 'calories_remaining' ],
                        $entry[ 'percentage_used' ] . '%',
                        $class,
                        yk_mt_link_admin_page_entry( $entry[ 'id' ] ),
                        yk_mt_link_profile_display_name_link( $entry[ 'user_id' ] )
                    );
                }
            ?>
        </tbody>
    </table>
<?php
}

/**
 * Helper function to disable admin page if the user doesn't have the correct user role.
 */
function yk_mt_admin_permission_check() {

    $permission_level = yk_mt_admin_permission_check_setting();

    if ( ! current_user_can( $permission_level ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' , YK_MT_SLUG ) );
    }
}

/**
 * Fetch the current permission level needed to view user data
 * @return mixed
 */
function yk_mt_admin_permission_check_setting() {

    if ( false === YK_MT_IS_PREMIUM ) {
        return 'manage_options';
    }

    return get_option( 'yk-mt-edit-permissions', 'manage_options' );
}

/**
 * If a new admin allowance is specified then update user's settings
 * @param null $user_id
 */
function yk_mt_admin_process_post_updates($user_id = NULL ) {

    if ( false === YK_MT_IS_PREMIUM ) {
        return;
    }

    $user_id = ( null === $user_id ) ? yk_mt_get_user_id_from_qs() : $user_id;

    if ( true === empty( $user_id ) ) {
        return;
    }

    // New user allowance specified?
    $calorie_source = yk_mt_post_value( 'yk-mt-calorie-source', false );

    if ( false === empty( $calorie_source ) ) {
        yk_mt_settings_set( 'calorie-source', $calorie_source,  $user_id );
    }

    // New user allowance specified?
    $admin_allowance = yk_mt_post_value( 'yk-mt-allowed-calories-admin', false );

    if ( false === empty( $admin_allowance ) ) {
        yk_mt_settings_set( 'allowed-calories-admin', $admin_allowance,  $user_id );
        yk_mt_settings_set( 'calorie-source', 'admin',  $user_id );
    }

    do_action( 'yk_mt_settings_admin_sidebar_saved' );
}

/**
 * Fetch a user's First name / Last name from WP. IF not available, use display_name.
 * @param $user_id
 * @return string
 */
function yk_mt_user_display_name( $user_id ) {

    if ( true === empty( $user_id ) ) {
        return '-';
    }

    $name = sprintf( '%s %s', get_user_meta( $user_id, 'first_name' , true ), get_user_meta( $user_id, 'last_name' , true ) );

    return ( true === empty( $name ) || ' ' === $name ) ?
        get_user_meta( $user_id, 'nickname' , true ) :
        $name;
}