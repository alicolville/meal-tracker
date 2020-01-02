<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_dashboard() {

    yk_mt_admin_permission_check();

    ?>
    <div class="wrap ws-ls-user-data ws-ls-admin-page">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <?php
                        if ( false === YK_MT_IS_PREMIUM ) {
                            yk_mt_display_pro_upgrade_notice();
                        }
                    ?>
                   <div class="postbox">
                        <h2 class="hndle"><span><?php echo __('Summary', YK_MT_SLUG ); ?></span></h2>
                        <div class="inside">
                            <?php

                                yk_mt_admin_option_links_clicked( 'summary-fetch' );

                                $option_links = [
                                        'today'      => __( 'Today \'s entries', YK_MT_SLUG ),
                                        'week'       => __( 'Last 7 days', YK_MT_SLUG ),
                                        'latest-100' => __( 'Latest 100', YK_MT_SLUG ),
                                        'latest-500' => __( 'Latest 500', YK_MT_SLUG )

                                ];

                                $db_args = [ 'sort-order' => 'desc', 'caching-notice' => 5 ];

                                switch  ( yk_mt_site_options( 'summary-fetch', 'today' ) ) {
                                    case 'latest-100':
                                        $db_args[ 'limit' ] = 100;
                                        break;
                                    case 'latest-500':
                                        $db_args[ 'limit' ] = 500;
                                        break;
                                    case 'week':
                                        $db_args[ 'last-x-days' ] = 7;
                                        break;
                                    default:
                                        $db_args[ 'last-x-days' ] = 1;
                                        break;
                                }

                                $entries  = yk_mt_db_entries_summary( $db_args );

                                yk_mt_table_user_entries( [ 'entries'   => $entries, 'show-username' => true ] );

                                yk_mt_admin_option_links( 'summary-fetch', 'today', $option_links, 5 );

                                ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <?php yk_mt_dashboard_side_bar(); ?>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
    <?php
}
