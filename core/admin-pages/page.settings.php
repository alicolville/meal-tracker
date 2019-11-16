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
                        <h3 class="hndle">
                            <span>
                                <?php echo __( YK_MT_PLUGIN_NAME . ' Settings', YK_MT_SLUG); ?>
                            </span>
                        </h3>
                        <div class="inside">
                            <form method="post" action="options.php">
                                <?php

                                settings_fields( 'yk-mt-options-group' );
                                do_settings_sections( 'yk-mt-options-group' );

                                ?>
                                <div id="yk-mt-tabs">
                                    <ul>
                                        <li><a><?php echo __( 'General', YK_MT_SLUG ); ?><span><?php echo __( 'General settings', YK_MT_SLUG ); ?></span></a></li>
                                        <li><a><?php echo __( 'Calorie Allowance', YK_MT_SLUG ); ?><span><?php echo __( 'Specify the sources for determining a user\'s calorie allowance', YK_MT_SLUG ); ?></span></a></li>
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
                                                            <option value="yes" <?php selected( get_option('yk-mt-allow-user-preferences'), 'yes' ); ?>><?php echo __('Yes', YK_MT_SLUG )?></option>
                                                            <option value="no" <?php selected( get_option('yk-mt-allow-user-preferences'), 'no' ); ?>><?php echo __('No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __('Allow your users to select their own data units, complete their "About You" fields and remove all their data.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div>
                                            <p><?php echo __('Specify the methods in which a user\'s daily allowance can be determined:', YK_MT_SLUG )?></p>
                                            <table class="form-table">
                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo __( 'User specified' , YK_MT_SLUG ); ?></th>
                                                    <td>
                                                        <?php
                                                            $allow_calorie = yk_mt_site_options_as_bool('allow-calorie-override' );
                                                        ?>
                                                        <select id="allow-calorie-override" name="allow-calorie-override">
                                                            <option value="true" <?php selected( $allow_calorie, true ); ?>><?php echo __('Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $allow_calorie, false ); ?>><?php echo __('No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __('Allow a user to specify their own daily calorie intake.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo __( 'Admin specified' , YK_MT_SLUG ); ?></th>
                                                    <td>
                                                        <?php
                                                        $allow_calorie = yk_mt_site_options_as_bool('allow-calorie-override-admin' );
                                                        ?>
                                                        <select id="allow-calorie-override-admin" name="allow-calorie-override-admin">
                                                            <option value="true" <?php selected( $allow_calorie, true ); ?>><?php echo __('Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $allow_calorie, false ); ?>><?php echo __('No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __('Admins can specify a user\'s daily calorie intake.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row">
                                                        <a href="https://weight.yeken.uk" target="_blank" rel="noopener">
                                                            <?php echo __( 'YeKen: Weight Tracker' , YK_MT_SLUG ); ?>
                                                        </a>
                                                    </th>
                                                    <td>
                                                        <?php
                                                        $allow_calorie = yk_mt_site_options_as_bool('allow-calorie-external-wlt' );
                                                        ?>
                                                        <select id="allow-calorie-external-wlt" name="allow-calorie-external-wlt">
                                                            <option value="true" <?php selected( $allow_calorie, true ); ?>><?php echo __('Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $allow_calorie, false ); ?>><?php echo __('No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __('If enabled and Weight Tracker activated, a user\'s calorie intake can be taken calculated automatically from YeKen\'s Weight Tracker', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
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
                                                        <select id="accordion-enabled" name="accordion-enabled">
                                                            <option value="true" <?php selected( $accordion_enabled, true ); ?>><?php echo __( 'Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $accordion_enabled, false ); ?>><?php echo __( 'No', YK_MT_SLUG )?></option>
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
                    </div>
                 </div>
            </div>
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
        register_setting( 'yk-mt-options-group', 'allow-calorie-override-admin' );
        register_setting( 'yk-mt-options-group', 'allow-calorie-override' );
        register_setting( 'yk-mt-options-group', 'allow-calorie-external-wlt' );
    }
}
add_action( 'admin_init', 'yk_mt_register_settings' );
