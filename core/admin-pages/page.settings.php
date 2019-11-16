<?php

defined('ABSPATH') or die('Jog on!');

function yk_mt_settings_page_generic() {

    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' , YK_MT_SLUG) );
    }

    $is_premium = yk_mt_license_is_premium();

    $disable_if_not_premium_class = ( $is_premium ) ? '' : 'yk-mt-disabled';

    ?>
    <div id="icon-options-general" class="icon32"></div>

    <div id="poststuff">

        <div id="post-body" class="metabox-holder columns-3 yk-mt-settings">

            <!-- main content -->
            <div id="post-body-content">

                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox">
                        <h3 class="hndle"><span><?php echo __( YK_MT_PLUGIN_NAME . ' Settings', YK_MT_SLUG); ?></span></h3>

                        <div class="inside">

                            <form method="post" action="options.php">
                                <?php

                                settings_fields( 'yk-mt-options-group' );
                                do_settings_sections( 'yk-mt-options-group' );

                                ?>
                                <div class="yk-mt-tabs">
                                    <ul>
                                        <li><a><?php echo __( 'General', YK_MT_SLUG ); ?><span><?php echo __( 'General settings', YK_MT_SLUG ); ?></span></a></li>
                                        <li><a><?php echo __( 'Test', YK_MT_SLUG ); ?><span><?php echo __( 'To Do', YK_MT_SLUG ); ?></span></a></li>
                                        <li><a><?php echo __( 'Display', YK_MT_SLUG ); ?><span><?php echo __( 'Specify how the plugin looks', YK_MT_SLUG ); ?></span></a></li>
                                    </ul>
                                    <div>
                                        <div>
                                            <?php
                                                if ( false === $is_premium ) {
                                                    yk_mt_display_pro_upgrade_notice();
                                                }
                                            ?>
                                            <h3><?php echo __( 'Caching' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                                <tr>
                                                    <th scope="row"><?php echo __( 'Enable Caching?' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <?php
                                                            $cache_enabled = yk_mt_site_options_as_bool('caching-enabled' );
                                                        ?>
                                                        <select id="caching-enabled" name="caching-enabled">
                                                            <option value="true" <?php selected( $cache_enabled, true ); ?>><?php echo __('Yes', YK_MT_SLUG); ?></option>
                                                            <option value="false" <?php selected( $cache_enabled, false ); ?>><?php echo __('No', YK_MT_SLUG); ?></option>
                                                        </select>
                                                        <p><?php echo __('If enabled, additional caching will be performed to reduce database queries. It is highly recommended that this remains enabled and only disabled for testing or to enable other caching mechanisms.', YK_MT_SLUG); ?></p>
                                                    </td>
                                                </tr>
                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo __( 'Allow user settings' , WE_LS_SLUG); ?></th>
                                                    <td>
                                                        <select id="yk-mt-allow-user-preferences" name="yk-mt-allow-user-preferences">
                                                            <option value="yes" <?php selected( get_option('yk-mt-allow-user-preferences'), 'yes' ); ?>><?php echo __('Yes', WE_LS_SLUG)?></option>
                                                            <option value="no" <?php selected( get_option('yk-mt-allow-user-preferences'), 'no' ); ?>><?php echo __('No', WE_LS_SLUG)?></option>
                                                        </select>
                                                        <p><?php echo __('Allow your users to select their own data units, complete their "About You" fields and remove all their data.', WE_LS_SLUG)?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div>
                                        <div>
                                            Test
                                        </div>
                                    </div>
                                    <div>
                                        <div>
                                            <?php
                                            if ( false === $is_premium ) {
                                                yk_mt_display_pro_upgrade_notice();
                                            }
                                            ?>
                                            <h3><?php echo __( 'Meal Tracker Shortcode' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                               <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo __( 'Accordion Enabled' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <?php
                                                            $accordion_enabled = yk_mt_site_options_as_bool('accordion-enabled' );
                                                        ?>
                                                        <select id="yk-mt-accordion-enabled" name="yk-mt-accordion-enabled">
                                                            <option value="true" <?php selected( $accordion_enabled, true ); ?>><?php echo __( 'Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" ><?php echo __( 'No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __( 'If set to "Yes", the main meal tracker will use accordions to display meal data.', YK_MT_SLU )?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php submit_button(); ?>
                            </form>
                        </div>
                        <!-- .inside -->

                    </div>
                    <!-- .postbox -->

                    <div class="postbox">
                    </div>
                    <!-- .meta-box-sortables .ui-sortable -->

                </div>
                <!-- post-body-content -->

            </div>
            <!-- #post-body .metabox-holder .columns-2 -->

            <br class="clear">
        </div>
        <!-- #poststuff -->

    </div> <!-- .wrap -->

    <?php

}

/**
 * Register fields to save
 */
function yk_mt_register_settings(){

    register_setting( 'yk-mt-options-group', 'caching-enabled' );

    // Pro only open
    if( true ===  yk_mt_license_is_premium() ){
        register_setting( 'yk-mt-options-group', 'accordion-enabled' );
    }
}
add_action( 'admin_init', 'yk_mt_register_settings' );
