<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_user_summary() {

    // TODO: Add role permission check here

    $user_id = yk_mt_get_user_id_from_qs();

    // Ensure this WP user ID exists!
    yk_mt_exist_check( $user_id );

    $todays_entry   = yk_mt_db_entry_get();
    $entries        = yk_mt_db_entries_summary( [ 'user-id' => $user_id ] );

    //TODO: Handle no data?
    ?>
    <div class="wrap ws-ls-user-data ws-ls-admin-page">
    <div id="poststuff">
        <?php yk_mt_user_header( $user_id ); ?>
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <?php
                        if ( false === YK_MT_IS_PREMIUM ) {
                            yk_mt_display_pro_upgrade_notice();
                        }
                    ?>
                    <div class="postbox">
                        <h2 class="hndle"><span><?php echo __( 'Chart', YK_MT_SLUG ); ?></span></h2>
                        <div class="inside">
                            <?php

                                if ( false === empty( $entries ) ) {
                                    yk_mt_chart_line_allowed_versus_used( [
                                        'entries'   => $entries,
                                        'max'       => 15,
                                        'title'     => __( 'Latest 15 entries', YK_MT_SLUG )
                                    ]);
                                } else {
                                    printf ( '<p><em>%s</em></p>', __( 'No results', YK_MT_SLUG ) );
                                }
                            ?>
                        </div>
                    </div>
                    <div class="postbox">
                        <h2 class="hndle"><span><?php echo __('Entries for this user', YK_MT_SLUG ); ?></span></h2>
                        <div class="inside">
                            <?php
                                yk_mt_table_user_entries( [ 'entries'   => $entries ] );
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <?php yk_mt_user_side_bar( $user_id, $todays_entry ); ?>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
    <?php
}
