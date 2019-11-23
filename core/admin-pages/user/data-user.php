<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_user_summary() {

    // TODO: Add role permission check here

    $user_id = yk_mt_get_user_id_from_qs();

    // Ensure this WP user ID exists!
    yk_mt_exist_check( $user_id );

    $user_data = get_userdata( $user_id );

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

//                            // Fetch last 25 weight entries
//                            $weight_data = ws_ls_get_weights($user_id, 25, -1, 'desc');
//
//                            // Reverse array so in cron order
//                            if($weight_data) {
//                                $weight_data = array_reverse($weight_data);
//                            }
//
//                            if ( true !== WS_LS_IS_PRO ) {
//
//                                echo sprintf('<p><a href="%s">%s</a> %s.</p>',
//                                    ws_ls_upgrade_link(),
//                                    __('Upgrade to Pro', WE_LS_SLUG),
//                                    __('to view the a chart of the user\'s weight entries.' , WE_LS_SLUG)
//                                );
//
//                            } else {
//                                echo ws_ls_display_chart($weight_data, ['type' => 'line', 'max-points' => 25, 'user-id' => $user_id]);
//                            }

                            ?>
                        </div>
                    </div>
                    <div class="postbox">
                        <h2 class="hndle"><span><?php echo __('Entries for this user', YK_MT_SLUG ); ?></span></h2>
                        <div class="inside">
                            <?php // echo ws_ls_data_table_placeholder($user_id, false, true); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <?php yk_mt_user_side_bar( $user_id ); ?>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
    <?php
}
