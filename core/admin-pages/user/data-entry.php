<?php

defined('ABSPATH') or die('Naw ya dinnie!');

/**
 * Admin page for viewing an entry
 */
function yk_mt_admin_page_entry_view() {

    yk_mt_admin_permission_check();

    $entry_id = yk_mt_querystring_value( 'entry-id', false );

    yk_mt_admin_process_post_updates();

    $entry = yk_mt_db_entry_get( $entry_id );

    if( true === empty( $entry ) ) {
        return;
    }

    ?>
    <div class="wrap ws-ls-user-data ws-ls-admin-page">
    <div id="poststuff">
        <?php yk_mt_user_header( $entry[ 'user_id' ] ); ?>
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <?php
                    if ( false === YK_MT_IS_PREMIUM ) {
                        yk_mt_display_pro_upgrade_notice();
                    }
                    ?>
                    <div class="postbox">
                        <h2 class="hndle"><span><?php echo esc_html__('Entry for', 'meal-tracker' ); ?> <?php yk_mt_echo( yk_mt_date_format( $entry[ 'date' ] ) ); ?></span></h2>
                        <div class="inside">
                            <table class="yk-mt-footable yk-mt-footable-basic yk-mt-data-entry" data-state="true">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html__( 'Meal', 'meal-tracker' ); ?></th>
                                        <th data-breakpoints="xs"><?php echo esc_html__( 'Description', 'meal-tracker' ); ?></th>
                                        <th data-breakpoints="xs"><?php echo esc_html__( 'Detail', 'meal-tracker' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ( yk_mt_db_meal_types_all() as $meal_type ) {

                                            $total_calories = yk_mt_blur_text( $entry[ 'counts' ][ $meal_type[ 'id' ] ] );

											$meta_counts = ( false === empty( $entry[ 'meta_counts' ][ $meal_type[ 'id' ] ][ 'summary' ] ) ) ?
																yk_mt_blur_text( $entry[ 'meta_counts' ][ $meal_type[ 'id' ] ][ 'summary' ], false ) : '';

                                            printf( '<tr class="yk-mt-entry-table-group footable-disabled">
                                                                <td colspan="2">%1$s</td>
                                                                <td class="yk-mt-blur">%2$skcal%3$s</td>
                                                             </tr>',
                                                            yk_mt_wp_kses( $meal_type[ 'name' ] ),
                                                            yk_mt_wp_kses( $total_calories ),
															yk_mt_wp_kses( $meta_counts )
                                            );

                                            if ( true === empty( $entry[ 'meals' ][ $meal_type[ 'id' ] ] ) ) {
                                                printf( '<tr class="yk-mt-entry-table-no-meals footable-disabled"><td colspan="3">%s</td></tr>',esc_html__('No meals', 'meal-tracker' ) );
                                            } else {

                                                $i = 0;

                                                // Print all meals out
                                                foreach(  $entry[ 'meals' ][ $meal_type[ 'id' ] ] as $meal ) {

                                                    $meal[ 'd' ] = yk_mt_blur_text( $meal[ 'd' ], false );

                                                    printf ( '<tr>
                                                                    <td class="%1$s">%2$s</td>
                                                                    <td class="%1$s">%3$s</td>
                                                                    <td data-breakpoints="xs" class="yk-mt-blur">%4$s</td>
                                                                </tr>',
                                                        ( $i < 2 ) ? '' : 'yk-mt-blur',
														yk_mt_link_admin_page_meal_edit( $meal[ 'id' ], esc_html( $meal[ 'name' ] ) ),
                                                        esc_html( $meal[ 'description' ] ),
														wp_kses_post( $meal[ 'd' ] )
                                                    );

                                                    $i++;
                                                }
                                            }
                                        }
                                    ?>
                                  </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
			<?php yk_mt_user_side_bar(  $entry[ 'user_id' ], $entry ); ?>
        </div>
        <br class="clear">
    </div>
    <?php
}
