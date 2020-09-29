<?php

defined('ABSPATH') or die("Jog on!");

// ------------------------------------------------------------------------------
// User Side Bar
// ------------------------------------------------------------------------------

/**
 * @param $user_id
 * @param $entry
 */
function yk_mt_user_side_bar( $user_id, $entry = NULL ) {

    if( true === empty( $user_id ) )  {
        return;
    }

    $stats = yk_mt_user_stats( $user_id );

    $current_url = yk_mt_link_current_url();

    if ( NULL !== $entry ): ?>
        <div class="postbox">
            <h2 class="hndle"><?php echo ( false === empty( $_GET[ 'mode' ] ) && 'entry' === $_GET[ 'mode' ] ) ? __( 'Entry summary', YK_MT_SLUG ) : __( 'Today\'s entry', YK_MT_SLUG ) ; ?></h2>
            <div class="inside">
                <div class="yk-mt__table--summary-chart-slot">
                    <canvas id="yk-mt-chart" class="yk-mt-chart"></canvas>
                </div>
                <table class="yk-mt-sidebar-stats">
                    <tr>
                        <th><?php echo __( 'Date', YK_MT_SLUG ); ?></th>
                        <td><?php echo yk_mt_date_format( $entry[ 'date' ] ); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __( 'Calories Allowed', YK_MT_SLUG ); ?></th>
                        <td>
                            <form class="yk-mt-admin-form" id="yk-mt-admin-calories-allowed" method="post" action="<?php echo esc_url( $current_url ); ?>">
                                <input type="hidden" name="yk-mt-update-allowance" value="<?php echo (int) $entry[ 'id' ]; ?>" />
                               <?php

                                    echo yk_mt_form_number( __( 'Calories allowed: ', YK_MT_SLUG ),
                                        'calories_allowed',
                                        (int) $entry[ 'calories_allowed' ],
                                        '',
                                        1,
                                        1,
                                        20000
                                    );

                                ?>
                             <input type="submit" class="button" value="<?php echo __( 'Save', YK_MT_SLUG ); ?>" />
                            </form>

                        </td>
                    </tr>
                    <tr>
                        <th><?php echo __( 'Calories Used', YK_MT_SLUG ); ?></th>
                        <td class="yk-mt-blur"><?php echo yk_mt_blur_text( $entry[ 'calories_used' ] ); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __( 'Calories Remaining', YK_MT_SLUG ); ?></th>
                        <td class="yk-mt-blur"><?php echo yk_mt_blur_text( $entry[ 'calories_remaining' ] ); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __( 'Percentage used', YK_MT_SLUG ); ?></th>
                        <td class="yk-mt-blur"><?php echo yk_mt_format_number( $entry[ 'percentage_used' ], 1 ); ?>%</td>
                    </tr>
                    <tr>
                        <th><?php echo __( 'Meals', YK_MT_SLUG ); ?></th>
                        <td class="yk-mt-blur"><?php echo yk_mt_blur_text( $entry[ 'counts' ][ 'total-meals' ] ); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="postbox yk-mt-user-data">
        <h2 class="hndle"><span><?php echo __( 'User Information', YK_MT_SLUG ); ?></span></h2>
        <div class="inside">
            <table class="yk-mt-sidebar-stats">
               <tr>
                    <th><?php echo __( 'Latest Entry', YK_MT_SLUG ); ?></th>
                    <td><?php echo yk_mt_date_format( $stats[ 'date-last' ] ); ?></td>
                </tr>
                <tr>
                    <th><?php echo __( 'Oldest Entry', YK_MT_SLUG ); ?></th>
                    <td class="yk-mt-blur"><?php echo yk_mt_date_format( $stats[ 'date-first' ] ); ?></td>
                </tr>
                <tr>
                    <th><?php echo __( 'Number of Entries', YK_MT_SLUG ); ?></th>
                    <td class="yk-mt-blur"><?php echo yk_mt_blur_text( $stats[ 'count-entries' ] ); ?></td>
                </tr>
                <tr>
                    <th><?php echo __( 'Number of Meals', YK_MT_SLUG ); ?></th>
                    <td class="yk-mt-blur"><?php echo yk_mt_blur_text( $stats[ 'count-meals' ] ); ?>
                    <?php
						printf( ' ( <a href="%s">%s</a> ) ',
							esc_url( admin_url( 'admin.php?page=yk-mt-meals&user-id=' . (int) $user_id ) ),
							__( 'view', YK_MT_SLUG )
						);
					?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

     <div class="postbox yk-mt-user-data">
        <h2 class="hndle"><span><?php echo __( 'Allowed calories source', YK_MT_SLUG ); ?></span></h2>
        <div class="inside">
            <p><?php echo __( 'When a new entry is created for this user, their allowed calories will be set in the following way', YK_MT_SLUG ); ?>:</p>
            <?php
                $selected_source = yk_mt_user_calories_target( $user_id, true );

                ?>
                    <table class="yk-mt-sidebar-stats">
                        <tr>
                            <th colspan="2"><?php echo __( 'Source', YK_MT_SLUG ); ?></th>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?php
                                    $sources = yk_mt_user_calories_sources();

                                    if ( false === empty( $sources ) ) {

                                        printf( ' <form class="yk-mt-admin-form" method="post" action="%1$s">
                                                            <select name="%2$s" id="%2$s">', yk_mt_link_current_url(), 'yk-mt-calorie-source' );

                                        foreach ( yk_mt_user_calories_sources() as $key => $source ) {

                                            printf( '<option value="%1$s" %3$s >%2$s</option>',
                                                            esc_attr( $key ),
                                                            esc_html( $source[ 'admin-message' ] ),
                                                            selected( $key, $selected_source[ 'key' ] )
                                            );

                                        }

                                        printf( '</select>
                                               <input type="submit" class="button" value="%1$s" />
                                               </form>',
                                               __( 'Save', YK_MT_SLUG )
                                        );

                                    } else {
                                        printf( '<p class="yk-mt-error">%s</p>', __( 'You must specify one or more calorie sources in settings.', YK_MT_SLUG ) );
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo __( 'Current allowance', YK_MT_SLUG ); ?></th>
                            <td><?php echo yk_mt_format_calories( $selected_source[ 'value' ] ); ?></td>
                        </tr>
                    </table>
                <?php
                ?>
                <?php if ( true === YK_MT_IS_PREMIUM &&
                        true === yk_mt_site_options_as_bool( 'allow-calorie-override-admin' ) ): ?>
                    <form class="yk-mt-admin-form yk-mt-side-bar-admin-allowance<?php echo ( 'admin' !== $selected_source[ 'key' ] ) ? ' yk-mt-hide' : ''; ?>" id="yk-mt-admin-allowance" method="post" action="<?php echo esc_url( $current_url ); ?>">
                        <p><strong><?php echo __( 'Specify admin allowance for the user', YK_MT_SLUG ); ?></strong></p>
                        <p class="small"><?php echo __( 'Please be aware that the user can override this value if other calories sources have been enabled within the plugin\'s settings.', YK_MT_SLUG ); ?></p>
                        <?php

                            echo yk_mt_form_number( __( 'Set Target: ', YK_MT_SLUG ),
                                'allowed-calories-admin',
                                yk_mt_user_calories_target_admin_specified( $user_id ),
                                '',
                                1,
                                1,
                                20000
                            );

                        ?>
                        <input type="submit" class="button" value="<?php echo __( 'Save', YK_MT_SLUG ); ?>" />
                    </form>
                <?php endif; ?>
        </div>
    </div>

    <div class="postbox">
        <h2 class="hndle"><?php echo __( 'User Search', YK_MT_SLUG ); ?></h2>
        <div class="inside">
            <?php yk_mt_user_search_form(); ?>
        </div>
    </div>

    <div class="postbox">
        <h2 class="hndle"><?php echo __( 'Delete Data', YK_MT_SLUG ); ?></h2>
        <div class="inside">
            <a href="<?php echo esc_url( yk_mt_link_admin_page_user($user_id, 'user', [ 'delete-entries' => 'y' ] ) );?>" class="button-secondary yk-mt-button-confirm"><?php echo __( 'All Entries', YK_MT_SLUG ); ?></span></a>
            <a href="<?php echo esc_url( yk_mt_link_admin_page_user($user_id, 'user', [ 'delete-meals' => 'y' ] ) );?>"
                data-content="<?php echo __( 'All of the user\'s meals will be marked as deleted. They will still reside in the system, yet can only be viewed against old entries. They can no longer be added to entries', YK_MT_SLUG ); ?>"
                class="button-secondary yk-mt-button-confirm"><?php echo __( 'All Meals', YK_MT_SLUG ); ?></span></a>
        </div>
    </div>
    <?php
}

/**
 * Display sidebar for dashboard
 */
function yk_mt_dashboard_side_bar() {

     $stats = yk_mt_stats();

     if ( 'yk-mt-meals' === yk_mt_querystring_value( 'page' )
     			&& 'meal' !== yk_mt_querystring_value( 'mode' ) ) :
    ?>
     <div class="postbox">
        <h2 class="hndle"><?php echo __( 'Options', YK_MT_SLUG ); ?></h2>
        <div class="inside">
        	<center>
        		<a href="<?php echo esc_url( admin_url( 'admin.php?page=yk-mt-meals&mode=meal' ) ); ?>" class="button-primary"><?php echo __( 'Add a new meal', YK_MT_SLUG ); ?></span></a>
			</center>
        </div>
     </div>
     <?php else: ?>
     <div class="postbox">
        <h2 class="hndle"><?php echo __( 'User Search', YK_MT_SLUG ); ?></h2>
        <div class="inside">
            <?php yk_mt_user_search_form(); ?>
        </div>
    </div>
     <?php endif; ?>
    <div class="postbox">
        <h2 class="hndle"><?php echo __( 'Summary Counts', YK_MT_SLUG ); ?></h2>
        <div class="inside">
             <table class="yk-mt-sidebar-stats">
                 <tr>
                     <th><?php echo __( 'Entries', YK_MT_SLUG ); ?></th>
                     <td><?php echo yk_mt_format_number( $stats[ 'yk_mt_entry' ] ); ?></td>
                 </tr>
                 <tr>
                     <th><?php echo __( 'Meals added by users', YK_MT_SLUG ); ?></th>
                     <td class="yk-mt-blur"><?php echo yk_mt_format_number( $stats[ 'meals-user' ] ); ?></td>
                 </tr>
                 <tr>
                     <th><?php echo __( 'Meal Collection', YK_MT_SLUG ); ?></th>
                     <td class="yk-mt-blur"><?php echo yk_mt_link_render( esc_url( admin_url( 'admin.php?page=yk-mt-meals' ) ), yk_mt_format_number( $stats[ 'meals-admin' ] ) ); ?></td>
                 </tr>
                 <tr>
                     <th><?php echo __( 'Meals added to entries', YK_MT_SLUG ); ?></th>
                     <td class="yk-mt-blur"><?php echo yk_mt_format_number( $stats[ 'yk_mt_entry_meals' ] ); ?></td>
                 </tr>
                 <tr>
                     <th><?php echo __( 'WordPress users', YK_MT_SLUG ); ?></th>
                     <td class="yk-mt-blur"><?php echo yk_mt_format_number( $stats[ 'wp-users' ] ); ?></td>
                 </tr>
                 <tr>
                     <th><?php echo __( 'Users with an entry', YK_MT_SLUG ); ?></th>
                     <td class="yk-mt-blur"><?php echo yk_mt_format_number( $stats[ 'unique-users' ] ); ?></td>
                 </tr>
                 <tr>
                     <th><?php echo __( 'Entries on target', YK_MT_SLUG ); ?></th>
                     <td class="yk-mt-blur"><?php echo yk_mt_format_number( $stats[ 'successful-entries' ] ); ?></td>
                 </tr>
                 <tr>
                     <th><?php echo __( 'Entries over target', YK_MT_SLUG ); ?></th>
                     <td class="yk-mt-blur"><?php echo yk_mt_format_number( $stats[ 'failed-entries' ] ); ?></td>
                 </tr>
                  <tr>
                     <td colspan="2" class="small"><?php printf( '%s %s', __( 'last updated at ', YK_MT_SLUG ), $stats[ 'last-updated' ] ); ?></td>
                 </tr>
             </table>
    </div>

    <?php
}

/**
 * Displays a navigational header at top of user data page
 *
 * @param $user_id
 */
function yk_mt_user_header( $user_id ) {

    if( false === is_numeric( $user_id )) {
        return;
    }

    $user_data = get_userdata( $user_id );

    if( false === $user_data ) {
        return;
    }

    $previous_url = yk_mt_link_previous_url( yk_mt_link_admin_page_user_dashboard() );

    $additional_links = apply_filters( 'yk_mt_user_profile_header_links', '', $user_id );

    printf('
        <h3>%s %s</h3>
        <div class="postbox yk-mt-user-data">
            <div class="inside">
                <a href="%s" class="button-secondary"><i class="fa fa-arrow-left"></i><span> %s</span></a>
                <a href="%s" class="button-secondary"><i class="fa fa-wordpress"></i> <span>%s</span></a>
                <a href="%s" class="button-secondary"><i class="fa fa-pie-chart"></i> <span>%s</span></a>
                %s
            </div>
        </div>',
        yk_mt_user_display_name( $user_id ) ,
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
* @param null $additional_qs
* @return string
*/
function yk_mt_link_admin_page_user( $user_id, $mode = 'user', $additional_qs = NULL ) {

    if ( false === is_numeric( $user_id ) ) {
        return '#';
    }

    $url = sprintf( 'admin.php?page=yk-mt-user&mode=%1$s&user-id=%2$d', $mode, $user_id );

    $url = admin_url( $url );

    if ( false === empty( $additional_qs ) &&
        true === is_array( $additional_qs ) ) {
        $url = add_query_arg( $additional_qs, $url );
    }

    return $url;
}
/**
 * Get a link to an admin entry page
 * @param $user_id
 * @param bool $add_back_link
 * @return string
 */
function yk_mt_link_admin_page_entry( $entry_id, $add_back_link = true ) {

    if ( false === is_numeric( $entry_id ) ) {
        return '#';
    }

    $url = admin_url( 'admin.php?page=yk-mt-user&mode=entry&entry-id=' . (int) $entry_id );

    if ( true === $add_back_link ) {
        $url = yk_mt_link_add_back_link( $url );
    }

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
    $profile_url = yk_mt_link_add_back_link( $profile_url );

    return ( false === empty( $display_text ) ) ?
        yk_mt_link_render( $profile_url, $display_text ) :
        $profile_url;
}

/**
 * Get link for editing meal
*
* @param $meal_id
* @param string $text
 *
* @param bool $add_back_link
*
* @return string
*/
function yk_mt_link_admin_page_meal_edit( $meal_id, $text = '', $add_back_link = true ) {

	if ( true === empty( $text ) ) {
		$text = __( 'Edit Meal', YK_MT_SLUG );
	}

	$base_url = admin_url( 'admin.php?page=yk-mt-meals&mode=meal&edit=' . (int) $meal_id );

	if ( true === $add_back_link ) {
		$base_url = yk_mt_link_add_back_link( $base_url );
	}

	return yk_mt_link_render( $base_url, $text );
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

/**
 * Given a user ID, generate a <a> / URL to their profile
 * @param $user_id
 * @return string
 */
function yk_mt_link_profile_display_name_link( $user_id ) {


    if ( true === empty( $user_id ) ) {
        return '-';
    }

    $label = yk_mt_user_display_name( $user_id );

    return yk_mt_link_admin_page_user_render( $user_id, $label );
}

/**
 * Add a back link to the given URL
 * @param $link
 * @return mixed
 */
function yk_mt_link_add_back_link( $link ) {

    if ( true === empty( $link ) ) {
        return $link;
    }

    $current_url = yk_mt_link_current_url();

	// Remove &delete= from QS
	$current_url = remove_query_arg( 'delete', $current_url );

    $current_url = base64_encode( $current_url );

    return add_query_arg( 'yk-mt-prev-url', $current_url, $link );
}

/**
 * Current URL
 * @return string
 */
function yk_mt_link_current_url() {

    $protocol = (
        ( isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ||
        ( isset($_SERVER['SERVER_PORT'] ) && 443 == $_SERVER['SERVER_PORT'] )
    ) ? 'https://' : 'http://';

    $base_url = $protocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    return esc_url_raw( $base_url );
}

/**
 * Look in querystring for a previous link
*
* @param string $default
*
* @return bool
*/
function yk_mt_link_previous_url( $default = '#' ) {

    $previous_url = yk_mt_querystring_value( 'yk-mt-prev-url', false );

    if ( false !== $previous_url ) {
        $previous_url = base64_decode( $previous_url );
        return esc_url( $previous_url );
    }

    return $default;
}

/**
 * Build some summary stats for the given suser
 * @param $user_id
 * @return array
 */
function yk_mt_user_stats( $user_id ) {

    $user_id            = ( NULL === $user_id ) ? get_current_user_id() : $user_id;
    $entries            = yk_mt_db_entry_get_ids_and_dates( $user_id );
    $number_of_entries  = count( $entries );
    $entry_dates        = array_values( $entries );
    $meal_count         = yk_mt_db_meal_for_user( $user_id, [ 'count-only' => true ] );

    return [
                'user-id'       => $user_id,
                'count-meals'   => ( false === empty( $meal_count ) ) ? $meal_count : 0,
                'count-entries' => $number_of_entries,
                'date-first'    => ( false === empty( $entry_dates[ 0 ] ) ) ? $entry_dates[ 0 ] : NULL,
                'date-last'     => ( false === empty( $entry_dates[ $number_of_entries - 1 ] ) ) ? $entry_dates[ $number_of_entries - 1 ] : NULL
    ];
}

/**
 * Return some stats about the plugin on this site
 */
function yk_mt_stats() {

    if ( $cache = yk_mt_cache_temp_get( 'dashboard-stats' ) ) {
       return $cache;
    }

    $stats = [
        YK_WT_DB_MEALS          => yk_mt_db_mysql_count_table( YK_WT_DB_MEALS, false ),
        YK_WT_DB_ENTRY          => yk_mt_db_mysql_count_table( YK_WT_DB_ENTRY, false ),
        YK_WT_DB_ENTRY_MEAL     => yk_mt_db_mysql_count_table( YK_WT_DB_ENTRY_MEAL, false ),
        'meals-user'			=> yk_mt_db_mysql_count( 'meals-user', false ),
        'meals-admin'			=> yk_mt_db_mysql_count( 'meals-admin', false ),
        'wp-users'              => yk_mt_db_mysql_count_table( 'users', false ),
        'unique-users'          => yk_mt_db_mysql_count( 'unique-users', false ),
        'successful-entries'    => yk_mt_db_mysql_count( 'successful-entries', false ),
        'failed-entries'        => yk_mt_db_mysql_count( 'failed-entries', false ),
        'last-updated'          => date('g:ia' ),
        'last-updated-iso'      => date("Y-m-d H:i:s")
    ];

    yk_mt_cache_temp_set( 'dashboard-stats', $stats );

    return $stats;
}

// ------------------------------------------------------------------------------
// User search Search box
// ------------------------------------------------------------------------------

/**
 * Render HTML for user search form
 */
function yk_mt_user_search_form() {

    ?>	<p><?php echo __( 'Enter a user\'s email, display name or username then click "Search".', YK_MT_SLUG ); ?></p>
    <form id="yk-mt-user-search" class="yk-mt-user-search-ajax" >
        <input type="text" name="search" placeholder="" id="yk-mt-search-field" />
        <input type="hidden" name="page" value="yk-mt-user"  />
        <input type="hidden" name="mode" value="search-results"  />
        <input type="submit" class="button" value="Search" id="yk-mt-search-button" />
    </form>
    <?php
}
