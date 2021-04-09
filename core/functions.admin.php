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
        'localise'          			=> yk_mt_localised_strings(),
		'ajax-security-nonce'   		=> wp_create_nonce( 'yk-mt-nonce' ),
		'ajax-admin-security-nonce'   	=> wp_create_nonce( 'yk-mt-admin-nonce' ),
        'todays-entry'      			=> $entry,
        'load-entry'        			=> ! empty( $entry ),
        'is-admin'          			=> true,
		'mode'							=> yk_mt_querystring_value( 'mode' ),
		'units-hide-quantity'   		=> yk_mt_units_where( 'drop-quantity', true, true ),
		'meta-fields'					=> yk_mt_meta_js_config(),
		'previous-url'					=> yk_mt_link_previous_url()
    ]);
}

/**
 * Blur string if incorrect license
 *
 * @param $text
 * @param bool $number_format
 * @return string
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
    return yk_mt_link_admin_page( 'yk-mt-user' );
}

/**
 * Build Admin URL link
 * @param $slug
 *
 * @return string|void
 */
function yk_mt_link_admin_page( $slug ) {

	$url = sprintf( 'admin.php?page=%s', $slug );

	return admin_url( $url );
}

/**
 * Display all user's entries in a data table
 */
function yk_mt_table_user_entries( $args ) {

    $args = wp_parse_args( $args, [
        'user-id'       => get_current_user_id(),
        'entries'       => NULL,
        'show-username' => false,
        'use-cache'     => true
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
 * Display all user's entries in a data table
 */
function yk_mt_table_meals( $args ) {

	$args = wp_parse_args( $args, [
		'user-id'       	=> get_current_user_id(),
		'added_by_admin'	=> false,					// Show ony admin meals
		'meals'       		=> NULL,
		'show-username' 	=> false,
		'use-cache'     	=> true,
		'user-id-for-link'	=> ''
	]);

	// Fetch meals if non specified
	if ( NULL === $args[ 'meals' ] ) {
		$args[ 'meals' ] = yk_mt_db_meal_for_user( $args[ 'user-id' ], $args );
	}

	$meta_fields = ( true === yk_mt_meta_is_enabled() ) ?
						yk_mt_meta_fields_where( 'visible_user', true ) :
							NULL;
	?>
	<table class="yk-mt-footable yk-mt-footable-basic widefat" data-paging="true" data-sorting="true" data-state="true" data-filtering="true">
		<thead>
		<tr>
			<th data-breakpoints="xs" data-type="text"><?php echo __( 'Name', YK_MT_SLUG ); ?></th>
			<th data-breakpoints="sm" data-type="text"><?php echo __( 'Calories', YK_MT_SLUG ); ?></th>
			<th data-breakpoints="sm" data-type="text"><?php echo __( 'Portion Size', YK_MT_SLUG ); ?></th>
			<th data-breakpoints="xs" data-type="string"><?php echo __( 'Source', YK_MT_SLUG ); ?></th>
			<?php

			if ( false === empty( $meta_fields ) ) {
				foreach ( $meta_fields as $field ) {
					printf( '<th data-breakpoints="sm" data-type="text">%s</th>', esc_html( $field[ 'title' ] ) );
				}
			}

			?>
			<th></th>
		</tr>
		</thead>
		<?php
			if ( false === empty( $args[ 'meals' ] ) ) {

				$base_url 	= admin_url( 'admin.php?page=yk-mt-meals' );

				if ( false === empty( $args[ 'user-id-for-link' ] ) ) {
					$base_url = add_query_arg( 'user-id', (int) $args[ 'user-id-for-link' ], $base_url );
				}

				foreach ( $args[ 'meals' ] as $meal ) {

					$edit_link = add_query_arg( [ 'edit' => $meal[ 'id' ], 'mode' => 'meal' ], $base_url );
					$edit_link = yk_mt_link_add_back_link( $edit_link );

					printf ( '    <tr>
													<td><a href="%5$s">%1$s</a></td>
													<td class="yk-mt-blur">%2$s</td>
													<td class="yk-mt-blur">%3$s</td>
													<td class="yk-mt-blur">%4$s</td>',
						esc_html( $meal[ 'name' ] ),
						sprintf( '%s%s', number_format( $meal[ 'calories'] ), __( 'kcal', YK_MT_SLUG ) ),
						yk_mt_get_unit_string( $meal ),
						yk_mt_ext_source_as_string( $meal[ 'source' ] ),
						esc_url( $edit_link )
					);

					if ( false === empty( $meta_fields ) ) {
						foreach ( $meta_fields as $field ) {
							printf('<td class="yk-mt-blur">%s</td>', esc_html( $meal[ $field[ 'db_col' ] ] ) );
						}
					}

					printf( '	<td>
									<a href="%1$s" class="btn btn-default footable-delete"><i class="fa fa-trash"></i></a>
									<a href="%2$s" class="btn btn-default footable-edit"><i class="fa fa-edit"></i></a>
								</td>
							</tr>',
							esc_url( $base_url . '&delete=' . (int) $meal[ 'id' ] ),
							$edit_link
					);
				}
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

	yk_mt_cache_user_delete( $user_id );

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

/**
 * Handle click of option link
 * @param $key
 */
function yk_mt_admin_option_links_clicked( $key ) {

    // Has a link been clicked? If so, update option
    $clicked_value = yk_mt_querystring_value( $key );

    if ( false === empty( $clicked_value ) ) {
        update_option( $key, $clicked_value );
    }
}

/**
 * Render out links for options
 *
 * @param $key
 * @param $default
 * @param $options
 * @param null $cache_notice
 * @param null $prepend
 * @param string $page_slug
 */
function yk_mt_admin_option_links( $key, $default,  $options, $cache_notice = NULL, $prepend = NULL, $page_slug = 'yk-mt-user' ) {

    if ( false === is_array( $options ) ||
            true === empty( $options ) ) {
        return;
    }

    $current_selected = yk_mt_site_options( $key, $default );

    $url = yk_mt_link_admin_page( $page_slug );

    echo '<div class="yk-mt-link-group">';

    if ( false === empty( $prepend ) ) {
    	echo esc_html( $prepend );
	}

    foreach ( $options as $option_key => $option_name ) {
        printf(     '<a href="%1$s" class="%2$s">%3$s</a> &middot; ',
                        esc_url( add_query_arg( $key , $option_key, $url ) ),
                            ( $current_selected === $option_key ) ? 'yk-mt-selected' : '',
                                esc_html( $option_name )
        );
    }

    if ( false === empty ( $cache_notice ) &&
        true === yk_mt_site_options_as_bool('caching-enabled' ) ) {

            printf( '<small>%1$s %2$d %3$s.</small>', __('The above table updates every', YK_MT_SLUG ), $cache_notice, __('minutes', YK_MT_SLUG ) );
    }

    echo '</div>';
}
