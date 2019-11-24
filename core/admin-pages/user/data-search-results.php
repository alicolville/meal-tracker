<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_search_results() {

    yk_mt_admin_permission_check();

    ?>
    <div class="wrap">
    <h1><?php echo __( 'Search Results', YK_MT_SLUG ); ?></h1>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php echo __( 'Search Results', YK_MT_SLUG ); ?></span></h2>
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
                                                    $count,
                                                    __( 'results were found for', YK_MT_SLUG ),
                                                    esc_html( $search_term )
                                    );

                                    ?>
                                    <table class="widefat yk-mt-footable yk-mt-footable-basic">
                                        <thead>
                                            <tr>
                                                <th class="row-title"><?php echo __( 'Username', YK_MT_SLUG ) ?></th>
                                                <th data-breakpoints="xs"><?php echo __( 'Email', YK_MT_SLUG ) ?></th>
                                                <th data-breakpoints="xs"><?php echo __( 'Latest Entry', YK_MT_SLUG ) ?></th>
                                                <th data-breakpoints="xs"><?php echo __( 'Number Entries', YK_MT_SLUG ) ?></th>
                                                <th data-breakpoints="xs"><?php echo __( 'Number Meals', YK_MT_SLUG ) ?></th>
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
                                        __( 'No users were found for the given search criteria', YK_MT_SLUG ),
                                        esc_html( $search_term )
                                    );
                                }
                            } else {
                                echo __( 'No search terms were specified', YK_MT_SLUG );
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