<?php

defined('ABSPATH') or die('Jog on!');

function yk_mt_settings_page_generic() {

    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' , YK_MT_SLUG ) );
    }

    $is_premium = yk_mt_license_is_premium();

    $disable_if_not_premium_class = ( YK_MT_IS_PREMIUM ) ? '' : 'yk-mt-disabled';

	if ( true === isset( $_GET[ 'settings-updated' ] ) ) {
		do_action( 'yk_mt_settings_saved' );
	}

	// Rebuild mysql tables?
	if ( false === empty( $_GET[ 'recreate-tables' ] ) ) {
		yk_mt_missing_database_table_fix();
	}

	$mysql_table_check = yk_mt_missing_database_table_any_issues();

	if ( false !== $mysql_table_check ) {

		printf(
			'<div class="error">
						<p>%1$s</p>
						<p><a href="%2$s?page=yk-mt-settings&amp;recreate-tables=y">%3$s</a></p>
					</div>',
			__( 'One or more database tables are missing for this plugin. They must be rebuilt if you wish to use the plugin.', YK_MT_SLUG ),
			get_permalink(),
			__( 'Rebuild them now', YK_MT_SLUG )

		);
	}
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
							<?php if ( false === empty( $_GET[ 'test-search' ] ) ): ?>
								<h3><?php echo __( 'API Test Results' , YK_MT_SLUG); ?></h3>
								<table class="form-table">
									<tr>
										<th scope="row"><?php echo __( 'Test Results' , YK_MT_SLUG); ?></th>
										<td>
											<textarea class="large-text" rows="20"><?php echo esc_html( yk_mt_ext_source_test() ); ?></textarea>
										</td>
									</tr>
								</table>
								<br />
							<?php endif; ?>
                            <form method="post" action="options.php">
                                <?php

                                settings_fields( 'yk-mt-options-group' );
                                do_settings_sections( 'yk-mt-options-group' );

                                ?>
                                <div id="yk-mt-tabs">
                                    <ul>
                                        <li id="general"><a><?php echo __( 'General', YK_MT_SLUG ); ?><span><?php echo __( 'General settings', YK_MT_SLUG ); ?></span></a></li>
                                        <li id="calorie-allowance"><a><?php echo __( 'Calorie Allowance', YK_MT_SLUG ); ?><span><?php echo __( 'Specify the sources for determining a user\'s calorie allowance', YK_MT_SLUG ); ?></span></a></li>
										<li id="external-sources"><a><?php echo __( 'External Sources', YK_MT_SLUG ); ?><span><?php echo __( 'Specify an external source to allow your user\'s to search for meals', YK_MT_SLUG ); ?></span></a></li>
                                        <li id="display"><a><?php echo __( 'Display', YK_MT_SLUG ); ?><span><?php echo __( 'Specify how the plugin looks', YK_MT_SLUG ); ?></span></a></li>
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
                                                        <p><?php echo __('If enabled, additional caching will be performed to reduce database queries and calls to external APIs. It is highly recommended that this remains enabled and only disabled for testing or to enable other caching mechanisms.', YK_MT_SLUG); ?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                            <h3><?php echo __( 'Security' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                                <tr  class="<?php echo $disable_if_not_premium_class; ?>" >
                                                    <th scope="row"><?php echo __( 'Who can view and modify user data?' , YK_MT_SLUG ); ?></th>
                                                    <td>
                                                        <?php
                                                            $permission_level = yk_mt_admin_permission_check_setting();
                                                        ?>
                                                        <select id="yk-mt-edit-permissions" name="yk-mt-edit-permissions">
                                                            <option value="manage_options" <?php selected( $permission_level, 'manage_options' ); ?>><?php echo __( 'Administrators Only', YK_MT_SLUG )?></option>
                                                            <option value="read_private_posts" <?php selected( $permission_level, 'read_private_posts' ); ?>><?php echo __( 'Editors and above', YK_MT_SLUG )?></option>
                                                            <option value="publish_posts" <?php selected( $permission_level, 'publish_posts' ); ?>><?php echo __( 'Authors and above', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __('Specify the minimum level of user role that maybe view or edit user data', YK_MT_SLUG ); ?>.</p>
                                                    </td>
                                                </tr>
                                            </table>
                                            <h3><?php echo __( 'Meals' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo __( 'Search other\'s meals' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <?php
                                                        $search_others = yk_mt_site_options_as_bool('search-others-meals', false );
                                                        ?>
                                                        <select id="search-others-meals" name="search-others-meals">
                                                            <option value="false" <?php selected( $search_others, false ); ?>><?php echo __( 'No', YK_MT_SLUG )?></option>
                                                            <option value="true" <?php selected( $search_others, true ); ?>><?php echo __( 'Yes', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __( 'If set to "Yes", users are not restricted to searching their own meal collections. Instead, a search will be performed across every user\'s meal collection. Please note, this may cause search to slow across large meal collections.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                            <h3><?php echo __( 'New Entries' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo __( 'Allow new entries in the past' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <?php
                                                        $value = yk_mt_site_options_as_bool('new-entries-past' );
                                                        ?>
                                                        <select id="new-entries-past" name="new-entries-past">
                                                            <option value="true" <?php selected( $value, true ); ?>><?php echo __( 'Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $value, false ); ?>><?php echo __( 'No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __( 'If set to "Yes", users can create entries for dates in the past.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo __( 'Allow new entries in the future' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <?php
                                                        $value = yk_mt_site_options_as_bool('new-entries-future' );
                                                        ?>
                                                        <select id="new-entries-future" name="new-entries-future">
                                                            <option value="true" <?php selected( $value, true ); ?>><?php echo __( 'Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $value, false ); ?>><?php echo __( 'No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __( 'If set to "Yes", users can create entries for dates in the future.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div>
                                            <p><?php echo __('Specify the methods in which a user\'s daily allowance can be determined', YK_MT_SLUG )?>.
                                                <strong><?php echo __('Ensure you specify at least one option or your users will not be able to use Meal Tracker as no allowance will be set.', YK_MT_SLUG )?>.</strong></p>
                                            <table class="form-table">

                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row">1. <?php echo __( 'Admin specified' , YK_MT_SLUG ); ?></th>
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
                                                <tr>
                                                    <th scope="row">2. <?php echo __( 'User specified' , YK_MT_SLUG ); ?></th>
                                                    <td>
                                                        <?php
                                                        $allow_calorie = yk_mt_site_options_as_bool('allow-calorie-override' );
                                                        ?>
                                                        <select id="allow-calorie-override" name="allow-calorie-override">
                                                            <option value="true" <?php selected( $allow_calorie, true ); ?>><?php echo __('Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $allow_calorie, false ); ?>><?php echo __('No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo __('Allow a user to specify their own daily calorie intake.', 'WE_LS_SLUG'  )?></p>
                                                    </td>
                                                </tr>
                                                <tr class="<?php echo $disable_if_not_premium_class; ?><?php if ( false === yk_mt_wlt_pro_plus_enabled() ) { echo ' yk-mt-plugin-disabled'; } ?>">
                                                    <th scope="row">3.
                                                        <a href="https://weight.yeken.uk" target="_blank" rel="noopener">
                                                            <?php echo __( 'YeKen: Weight Tracker' , YK_MT_SLUG ); ?>
                                                        </a>
                                                    </th>
                                                    <td>
                                                        <?php
                                                            $allow_calorie = yk_mt_wlt_enabled_for_mt();
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
											<p>
												<?php echo __( 'Specify API credentials for your preferred external service. Meal Tracker will then allow your user\'s to search their database, select meals and copy the data to the user\'s meal collection' , YK_MT_SLUG); ?>.
												<strong><?php echo __( 'Only one API can be used' , YK_MT_SLUG); ?>. <?php echo __( 'If more than one has been specified, then the plugin will pick the one itself.' , YK_MT_SLUG); ?>.</strong>
											</p>
											<?php

												$current_source = yk_mt_ext_source_credentials();

											?>
											<h3><?php echo __( 'Enabled Source' , YK_MT_SLUG ); ?></h3>
											<table class="form-table">
												<tr>
													<th scope="row"><?php echo __( 'Enabled Source' , YK_MT_SLUG); ?></th>
													<td>
														<?php echo esc_html( ( false === empty( $current_source ) ? print_r( $current_source, true ) : __( 'API credentials missing for all APIs' , YK_MT_SLUG ) ) ); ?>
													</td>
												</tr>
												<?php if ( false !== $current_source ): ?>
													<tr>
														<th scope="row"><?php echo __( 'Test API' , YK_MT_SLUG); ?></th>
														<td>
															<a href="<?php echo esc_url( admin_url('admin.php?page=yk-mt-settings&test-search=true#external-sources') ); ?>" class="button"><?php echo __( 'Perform a test search for "Apples"' , YK_MT_SLUG); ?></a>
														</td>
													</tr>
												<?php endif; ?>
											</table>
											<h3><?php echo __( 'FatSecret API' , YK_MT_SLUG ); ?></h3>
											<p><?php echo __( 'You are able to create the required REST API OAuth 2.0 Credentials a the following page:' , YK_MT_SLUG); ?> <a href="https://platform.fatsecret.com/api/Default.aspx?screen=myk" target="_blank">https://platform.fatsecret.com/api/Default.aspx?screen=myk</a></p>
											<table class="form-table">
												<tr>
													<th scope="row"><?php echo __( 'Client ID' , YK_MT_SLUG); ?></th>
													<td>
														<input type="text" name="external-fatsecret-id" id="external-fatsecret-id" value="<?php echo esc_attr( yk_mt_site_options( 'external-fatsecret-id', '' ) ); ?>" class="large-text" maxlength="40" />
													</td>
												</tr>
												<tr>
													<th scope="row"><?php echo __( 'Client Secret' , YK_MT_SLUG); ?></th>
													<td>
														<input type="text" name="external-fatsecret-secret" id="external-fatsecret-secret" value="<?php echo esc_attr( yk_mt_site_options( 'external-fatsecret-secret', '' ) ); ?>" class="large-text" maxlength="40" />
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
											<h3><?php echo __( 'CSS Theme' , YK_MT_SLUG); ?></h3>
											<table class="form-table">
												<tr>
													<th scope="row"><?php echo __( 'Enabled' , YK_MT_SLUG); ?></th>
													<td>
														<?php
															$css_enabled = yk_mt_site_options_as_bool('css-theme-enabled' );
														?>
														<select id="css-theme-enabled" name="css-theme-enabled">
															<option value="true" <?php selected( $css_enabled, true ); ?>><?php echo __( 'Yes', YK_MT_SLUG )?></option>
															<option value="false" <?php selected( $css_enabled, false ); ?>><?php echo __( 'No', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo __( 'If set to "Yes", the additional theme CSS shall be included. If you wish to add more of your own styling, you may wish to disable the bundled theme.', YK_MT_SLUG )?></p>
													</td>
												</tr>
											</table>
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
                                                        <p><?php echo __( 'If set to "Yes", the main meal tracker will use accordions to display meal data.', YK_MT_SLUG )?></p>
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
	register_setting( 'yk-mt-options-group', 'css-theme-enabled' );

    // Pro only open
    if( true ===  yk_mt_license_is_premium() ){

        register_setting( 'yk-mt-options-group', 'accordion-enabled' );
        register_setting( 'yk-mt-options-group', 'allow-calorie-override-admin' );
        register_setting( 'yk-mt-options-group', 'allow-calorie-override' );
        register_setting( 'yk-mt-options-group', 'allow-calorie-external-wlt' );
        register_setting( 'yk-mt-options-group', 'yk-mt-edit-permissions' );
        register_setting( 'yk-mt-options-group', 'search-others-meals' );
        register_setting( 'yk-mt-options-group', 'new-entries-past' );
        register_setting( 'yk-mt-options-group', 'new-entries-future' );
        register_setting( 'yk-mt-options-group', 'macronutrients-enabled' );

		register_setting( 'yk-mt-options-group', 'external-fatsecret-id' );
		register_setting( 'yk-mt-options-group', 'external-fatsecret-secret' );
    }
}
add_action( 'admin_init', 'yk_mt_register_settings' );
