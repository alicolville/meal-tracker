<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_dashboard() {

    // TODO: Add role permission check here

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
                        <h2 class="hndle"><span><?php echo __('Latest 100 entries', YK_MT_SLUG ); ?></span></h2>
                        <div class="inside">
                            <?php
                                $entries  = yk_mt_db_entries_summary( [ 'limit' => 100 ] );

                                yk_mt_table_user_entries( [ 'entries'   => $entries, 'show-username' => true ] );
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
