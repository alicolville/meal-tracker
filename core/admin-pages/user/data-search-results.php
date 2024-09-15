<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_search_results() {

    yk_mt_admin_permission_check();

    ?>
    <div class="wrap">
    <h2><span><?php echo esc_html__( 'Search Results', 'meal-tracker' ) ?></span></h2>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <div class="inside">
                        <?php

                            $search_term = yk_mt_querystring_value( 'search' );

                            if( false === YK_MT_IS_PREMIUM ) {

                                yk_mt_display_pro_upgrade_notice();

                            } else if (  false === empty( $search_term ) ) {

                                yk_mt_user_search_form();

                                $user_query     = new WP_User_Query([ 'search' => sprintf( '*%s*', $search_term ) ]);
                                $count          = $user_query->total_users;

                                if( 0 !== $count ) {
                                    printf('<p>%1$d %2$s: <em>"%3$s"</em></p>',
                                                    esc_html( $count ),
                                                    esc_html__( 'results were found for', 'meal-tracker' ),
                                                    esc_html( $search_term )
                                    );

                                    ?>
                                    <table class="widefat yk-mt-footable yk-mt-footable-basic">
                                        <thead>
                                            <tr>
                                                <th class="row-title"><?php echo esc_html__( 'Username', 'meal-tracker' ) ?></th>
                                                <th data-breakpoints="xs"><?php echo esc_html__( 'Email', 'meal-tracker' ) ?></th>
                                                <th data-breakpoints="xs"><?php echo esc_html__( 'Latest Entry', 'meal-tracker' ) ?></th>
                                                <th data-breakpoints="xs"><?php echo esc_html__( 'Number Entries', 'meal-tracker' ) ?></th>
                                                <th data-breakpoints="xs"><?php echo esc_html__( 'Number Meals', 'meal-tracker' ) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            foreach ( $user_query->get_results() as $user ) {
                                                yk_mt_search_row( $user );
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                <?php

                                } else {
                                    echo sprintf('<p>%1$s: <strong>"%2$s"</strong>.</p>',
                                        esc_html__( 'No users were found for the given search criteria', 'meal-tracker' ),
                                        esc_html( $search_term )
                                    );
                                }
                            } else {
                                echo esc_html__( 'No search terms were specified', 'meal-tracker' );
                            }
                        ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
    <?php
}

/**
 * Render a search result
 * @param $user
 * @param string $class
 */
function yk_mt_search_row( $user, $class = '') {

    if( false === empty( $user ) ) {

        $stats = yk_mt_user_stats( $user->data->ID );

        printf('<tr valign="top" class="%1$s">
                            <td><a href="%2$s">%3$s</a></td>
                            <td><a href="mailto:%4$s">%4$s</a></td>
                            <td>%5$s</td>
                            <td><a href="%2$s">%6$s</a></td>
                            <td>%7$s</td>
                        </tr>',
            esc_attr( $class ),
            yk_mt_link_admin_page_user_render( $user->data->ID ),
            esc_html( $user->data->display_name ),
            esc_attr( $user->data->user_email ),
            yk_mt_date_format( $stats[ 'date-last' ] ),
            $stats[ 'count-entries' ],
            $stats[ 'count-meals' ]
        );
    }
}