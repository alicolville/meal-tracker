<?php

defined('ABSPATH') or die('Jog on!');

function yk_mt_settings_page_generic() {

    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' , YK_MT_SLUG ) );
    }

    $is_premium = yk_mt_license_is_premium();

    $disable_if_not_premium_class = ( YK_MT_IS_PREMIUM ) ? '' : 'yk-mt-disabled';

	if ( true === isset( $_GET[ 'settings-updated' ] ) ) {
		do_action( 'yk_mt_settings_saved' );
	}

	// Rebuild mysql tables?
	if ( false === empty( $_GET[ 'recreate-tables' ] ) ) {
		yk_mt_missing_database_table_fix();

		printf( '<div class="notice"><p>%1$s.</p></div>', esc_html__( 'All database tables have been rebuilt', YK_MT_SLUG ) );
	}

	$mysql_table_check = yk_mt_missing_database_table_any_issues();

	if ( false !== $mysql_table_check ) {

		printf(
			'<div class="error">
						<p>%1$s</p>
						<p><a href="%2$s?page=yk-mt-settings&amp;recreate-tables=y">%3$s</a></p>
					</div>',
			esc_html__( 'One or more database tables are missing for this plugin. They must be rebuilt if you wish to use the plugin.', YK_MT_SLUG ),
			get_permalink(),
			esc_html__( 'Rebuild them now', YK_MT_SLUG )

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
                                <?php echo esc_html__( YK_MT_PLUGIN_NAME . ' Settings', YK_MT_SLUG); ?>
                            </span>
                        </h3>
                        <div class="inside">
							<?php if ( false === empty( $_GET[ 'test-search' ] ) ): ?>
								<a name="test-performed" />
								<h3><?php echo esc_html__( 'API Test Results' , YK_MT_SLUG); ?></h3>
								<table class="form-table">
									<tr>
										<th scope="row"><?php echo esc_html__( 'Test Results' , YK_MT_SLUG); ?></th>
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
                                        <li id="general"><a><?php echo esc_html__( 'General', YK_MT_SLUG ); ?><span><?php echo esc_html__( 'General settings', YK_MT_SLUG ); ?></span></a></li>
                                        <li id="calorie-allowance"><a><?php echo esc_html__( 'Calorie Allowance', YK_MT_SLUG ); ?><span><?php echo esc_html__( 'Specify the sources for determining a user\'s calorie allowance', YK_MT_SLUG ); ?></span></a></li>
										<li id="external-sources"><a><?php echo esc_html__( 'External Sources', YK_MT_SLUG ); ?><span><?php echo esc_html__( 'Specify an external source to allow your user\'s to search for meals', YK_MT_SLUG ); ?></span></a></li>
                                        <li id="display"><a><?php echo esc_html__( 'Display', YK_MT_SLUG ); ?><span><?php echo esc_html__( 'Specify how the plugin looks', YK_MT_SLUG ); ?></span></a></li>
                                    </ul>
                                    <div>
                                        <div>
                                            <?php
                                                if ( false === $is_premium ) {
                                                    yk_mt_display_pro_upgrade_notice();
                                                }
                                            ?>
                                            <h3><?php echo esc_html__( 'Caching' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                                <tr>
                                                    <th scope="row"><?php echo esc_html__( 'Enable Caching?' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <?php
                                                            $cache_enabled = yk_mt_site_options_as_bool('caching-enabled' );
                                                        ?>
                                                        <select id="caching-enabled" name="caching-enabled">
                                                            <option value="true" <?php selected( $cache_enabled, true ); ?>><?php echo esc_html__('Yes', YK_MT_SLUG); ?></option>
                                                            <option value="false" <?php selected( $cache_enabled, false ); ?>><?php echo esc_html__('No', YK_MT_SLUG); ?></option>
                                                        </select>
                                                        <p><?php echo esc_html__('If enabled, additional caching will be performed to reduce database queries and calls to external APIs. It is highly recommended that this remains enabled and only disabled for testing or to enable other caching mechanisms.', YK_MT_SLUG); ?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                            <h3><?php echo esc_html__( 'Security' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                                <tr  class="<?php echo $disable_if_not_premium_class; ?>" >
                                                    <th scope="row"><?php echo esc_html__( 'Who can view and modify user data?' , YK_MT_SLUG ); ?></th>
                                                    <td>
                                                        <?php
                                                            $permission_level = yk_mt_admin_permission_check_setting();
                                                        ?>
                                                        <select id="yk-mt-edit-permissions" name="yk-mt-edit-permissions">
                                                            <option value="manage_options" <?php selected( $permission_level, 'manage_options' ); ?>><?php echo esc_html__( 'Administrators Only', YK_MT_SLUG )?></option>
                                                            <option value="read_private_posts" <?php selected( $permission_level, 'read_private_posts' ); ?>><?php echo esc_html__( 'Editors and above', YK_MT_SLUG )?></option>
                                                            <option value="publish_posts" <?php selected( $permission_level, 'publish_posts' ); ?>><?php echo esc_html__( 'Authors and above', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo esc_html__('Specify the minimum level of user role that maybe view or edit user data', YK_MT_SLUG ); ?>.</p>
                                                    </td>
                                                </tr>
                                            </table>
                                            <h3><?php echo esc_html__( 'Searching for meals' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo esc_html__( 'Other user\'s meals' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <?php
                                                        $search_others = yk_mt_site_options_as_bool('search-others-meals', false );
                                                        ?>
                                                        <select id="search-others-meals" name="search-others-meals">
                                                            <option value="false" <?php selected( $search_others, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
                                                            <option value="true" <?php selected( $search_others, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo esc_html__( 'If set to "Yes", users are not restricted to searching their own meal collections. Instead, a search will be performed across every user\'s meal collection. Please note, this may cause search to slow across large meal collections.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
												<tr class="<?php echo $disable_if_not_premium_class; ?>">
													<th scope="row"><?php echo esc_html__( 'Admin\'s meal collection' , YK_MT_SLUG); ?></th>
													<td>
														<?php
														$search_admin_meals = yk_mt_site_options_as_bool('search-admin-meals', false );
														?>
														<select id="search-admin-meals" name="search-admin-meals">
															<option value="false" <?php selected( $search_admin_meals, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
															<option value="true" <?php selected( $search_admin_meals, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo esc_html__( 'If set to "Yes", users are allowed to search meals with the admin meal collection.', YK_MT_SLUG )?></p>
													</td>
												</tr>
                                            </table>
											<h3><?php echo esc_html__( 'Macronutrients' , YK_MT_SLUG); ?></h3>
											<table class="form-table">
												<tr class="<?php echo $disable_if_not_premium_class; ?>">
													<th scope="row"><?php echo esc_html__( 'Enable Macronutrients?' , YK_MT_SLUG); ?></th>
													<td>
														<?php
														$value = yk_mt_site_options_as_bool('macronutrients-enabled', false );
														?>
														<select id="macronutrients-enabled" name="macronutrients-enabled">
															<option value="false" <?php selected( $value, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
															<option value="true" <?php selected( $value, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo esc_html__( 'Please note, there is only basic support for Macronutrient fields at the moment. The framework has been build for specifying and storing these values against meals. Future releases will further integrate the values into the user interface.', YK_MT_SLUG )?></p>
													</td>
												</tr>
												<tr class="<?php echo $disable_if_not_premium_class; ?>">
													<th scope="row"><?php echo esc_html__( 'Required?' , YK_MT_SLUG); ?></th>
													<td>
														<?php
														$value = yk_mt_site_options_as_bool('macronutrients-required', false );
														?>
														<select id="macronutrients-required" name="macronutrients-required">
															<option value="false" <?php selected( $value, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
															<option value="true" <?php selected( $value, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo esc_html__( 'Should users be forced to enter values for Macronutrients?', YK_MT_SLUG )?></p>
													</td>
												</tr>
											</table>
                                            <h3><?php echo esc_html__( 'New Entries' , YK_MT_SLUG); ?></h3>
											<table class="form-table">
												<tr class="<?php echo $disable_if_not_premium_class; ?>">
													<th scope="row"><?php echo esc_html__( 'Allow new entries in the past' , YK_MT_SLUG); ?></th>
													<td>
														<?php
														$value = yk_mt_site_options_as_bool('new-entries-past' );
														?>
														<select id="new-entries-past" name="new-entries-past">
															<option value="true" <?php selected( $value, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
															<option value="false" <?php selected( $value, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo esc_html__( 'If set to "Yes", users can create entries for dates in the past.', YK_MT_SLUG )?></p>
													</td>
												</tr>
												<tr class="<?php echo $disable_if_not_premium_class; ?>">
													<th scope="row"><?php echo esc_html__( 'Allow new entries in the future' , YK_MT_SLUG); ?></th>
													<td>
														<?php
														$value = yk_mt_site_options_as_bool('new-entries-future' );
														?>
														<select id="new-entries-future" name="new-entries-future">
															<option value="true" <?php selected( $value, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
															<option value="false" <?php selected( $value, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo esc_html__( 'If set to "Yes", users can create entries for dates in the future.', YK_MT_SLUG )?></p>
													</td>
												</tr>
											</table>
											<h3><?php echo esc_html__( 'Allow fractions of meals when adding to an entry?' , YK_MT_SLUG); ?></h3>
											<table class="form-table">
												<tr class="<?php echo $disable_if_not_premium_class; ?>">
													<th scope="row"><?php echo esc_html__( 'Enabled' , YK_MT_SLUG); ?></th>
													<td>
														<?php
														$allow_fractions = yk_mt_site_options_as_bool('allow-fractions', false );
														?>
														<select id="allow-fractions" name="allow-fractions">
															<option value="false" <?php selected( $allow_fractions, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
															<option value="true" <?php selected( $allow_fractions, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo esc_html__( 'If enabled, rather than selecting only multiples of 1 for meal quantities, additional options of 1/4, 1/2 and 3/4 will be added. Please note: When a user selects a fraction, the meal is cloned and the relevant values divided.', YK_MT_SLUG )?></p>
													</td>
												</tr>
											</table>
                                        </div>
                                        <div>
                                            <p><?php echo esc_html__('Specify the methods in which a user\'s daily allowance can be determined', YK_MT_SLUG )?>.
                                                <strong><?php echo esc_html__('Ensure you specify at least one option or your users will not be able to use Meal Tracker as no allowance will be set.', YK_MT_SLUG )?>.</strong></p>
                                            <table class="form-table">

                                                <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row">1. <?php echo esc_html__( 'Admin specified' , YK_MT_SLUG ); ?></th>
                                                    <td>
                                                        <?php
                                                        $allow_calorie = yk_mt_site_options_as_bool('allow-calorie-override-admin' );
                                                        ?>
                                                        <select id="allow-calorie-override-admin" name="allow-calorie-override-admin">
                                                            <option value="true" <?php selected( $allow_calorie, true ); ?>><?php echo esc_html__('Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $allow_calorie, false ); ?>><?php echo esc_html__('No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo esc_html__('Admins can specify a user\'s daily calorie intake.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">2. <?php echo esc_html__( 'User specified' , YK_MT_SLUG ); ?></th>
                                                    <td>
                                                        <?php
                                                        $allow_calorie = yk_mt_site_options_as_bool('allow-calorie-override' );
                                                        ?>
                                                        <select id="allow-calorie-override" name="allow-calorie-override">
                                                            <option value="true" <?php selected( $allow_calorie, true ); ?>><?php echo esc_html__('Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $allow_calorie, false ); ?>><?php echo esc_html__('No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo esc_html__('Allow a user to specify their own daily calorie intake.', YK_MT_SLUG )?></p>
                                                    </td>
                                                </tr>
                                                <tr class="<?php echo $disable_if_not_premium_class; ?><?php if ( false === yk_mt_wlt_pro_plus_enabled() ) { echo ' yk-mt-plugin-disabled'; } ?>">
                                                    <th scope="row">3.
                                                        <a href="https://weight.yeken.uk" target="_blank" rel="noopener">
                                                            <?php echo esc_html__( 'YeKen: Weight Tracker' , YK_MT_SLUG ); ?>
                                                        </a>
                                                    </th>
                                                    <td>
                                                        <?php
                                                            $allow_calorie = yk_mt_wlt_enabled_for_mt();
                                                        ?>
                                                        <select id="allow-calorie-external-wlt" name="allow-calorie-external-wlt">
                                                            <option value="true" <?php selected( $allow_calorie, true ); ?>><?php echo esc_html__('Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $allow_calorie, false ); ?>><?php echo esc_html__('No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo esc_html__('If enabled and Weight Tracker activated, a user\'s calorie intake can be taken calculated automatically from YeKen\'s Weight Tracker', YK_MT_SLUG )?></p>
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
												<?php echo esc_html__( 'Specify settings for your preferred external service. Meal Tracker will then allow your user\'s to search the external collection, select meals and copy the data to the user\'s meal collection' , YK_MT_SLUG); ?>.
												<strong><?php echo esc_html__( 'Only one service can be used' , YK_MT_SLUG); ?>. <?php echo esc_html__( 'Meal Tracker will choose just one of the services if more than one has been enabled' , YK_MT_SLUG); ?>.</strong>
											</p>
											<h3><?php echo esc_html__( 'Enabled' , YK_MT_SLUG ); ?></h3>
											<table class="form-table">
												<tr class="<?php echo $disable_if_not_premium_class; ?>">
													<th scope="row"><?php echo esc_html__( 'External sources enabled?' , YK_MT_SLUG ); ?></th>
													<td>
														<?php
															$external_source_enabled = yk_mt_site_options_as_bool('external-enabled', false );
														?>
														<select id="external-enabled" name="external-enabled">
															<option value="false" <?php selected( $external_source_enabled, false ); ?>><?php echo esc_html__('No', YK_MT_SLUG )?></option>
															<option value="true" <?php selected( $external_source_enabled, true ); ?>><?php echo esc_html__('Yes', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo esc_html__('Should users be allowed to search external databases for meals?', YK_MT_SLUG )?></p>
													</td>
												</tr>
											</table>
											<?php

												$current_source 		= yk_mt_ext_source_credentials();
												$current_source_text 	= ( false === empty( $current_source[ 'source' ] ) ) ? $current_source[ 'source' ] : '';

											?>
											<h3><?php echo esc_html__( 'Active Source' , YK_MT_SLUG ); ?></h3>
											<table class="form-table">
												<tr>
													<th scope="row"><?php echo esc_html__( 'Source' , YK_MT_SLUG); ?></th>
													<td>
														<?php echo esc_html( ( false === empty( $current_source ) ? print_r( $current_source, true ) : esc_html__( 'API credentials missing for all APIs' , YK_MT_SLUG ) ) ); ?>
													</td>
												</tr>
												<?php if ( false !== $current_source ): ?>
													<tr>
														<th scope="row"><?php echo esc_html__( 'Test API' , YK_MT_SLUG); ?></th>
														<td>
															<a href="<?php echo esc_url( admin_url('admin.php?page=yk-mt-settings&test-search=true#test-performed') ); ?>" class="button"><?php echo esc_html__( 'Perform a test search for "Apples"' , YK_MT_SLUG); ?></a>
														</td>
													</tr>
												<?php endif; ?>
											</table>
											<?php
												$wprm_enabled = yk_mt_ext_source_wprm_enabled();
											?>
											<h3><?php echo esc_html__( 'WP Recipe Maker' , YK_MT_SLUG ); ?></h3>
											<p><?php echo esc_html__( 'If enabled, allow your users to search recipes stored within the WP plugin' , YK_MT_SLUG); ?> <a href="https://en-gb.wordpress.org/plugins/wp-recipe-maker/" target="_blank">WP Recipe Maker</a>.</p>
											<?php if ( false === $wprm_enabled ) {
												printf( '<p class="yk-mt-error-red">%s</p>',esc_html__( 'WP Recipe Maker is not installed and/or activated.' , YK_MT_SLUG ) );
											} ?>
											<?php
												if ( 'wp-recipe-maker' === $current_source_text ) {
													printf( '<p class="yk-mt-active-ext-source">%s</p>', esc_html__( 'Active external source.' , YK_MT_SLUG ) );
												}
											?>
											<table class="form-table">
												<tr>
													<th scope="row"><?php echo esc_html__( 'Enabled' , YK_MT_SLUG); ?></th>
													<td>
														<?php

															$external_source_wprm_enabled = yk_mt_site_options_as_bool('external-wprm-enabled', false );
														?>
														<select id="external-wprm-enabled" name="external-wprm-enabled" <?php if ( false === $wprm_enabled ) { echo ' disabled="disabled"'; } ?>>
															<option value="false" <?php selected( $external_source_wprm_enabled, false ); ?>><?php echo esc_html__('No', YK_MT_SLUG )?></option>
															<option value="true" <?php selected( $external_source_wprm_enabled, true ); ?>><?php echo esc_html__('Yes', YK_MT_SLUG )?></option>
														</select>
													</td>
												</tr>
											</table>
											<h3><?php echo esc_html__( 'FatSecret API' , YK_MT_SLUG ); ?></h3>
											<p><?php echo esc_html__( 'You are able to create the required REST API OAuth 2.0 Credentials a the following page:' , YK_MT_SLUG); ?> <a href="https://platform.fatsecret.com/api/Default.aspx?screen=myk" target="_blank">https://platform.fatsecret.com/api/Default.aspx?screen=myk</a></p>
											<p>
												<strong><?php echo esc_html__( 'Important' , YK_MT_SLUG); ?></strong>:
												<?php echo esc_html__( 'Please ensure you have whitelisted your server\'s IP address with FatSecret. This can be done by selecting your application (using the above link) and completing the "Allowed IP Addresses" section. It looks like your server IP may be:' , YK_MT_SLUG); ?>
												<strong><?php echo yk_mt_server_ip(); ?></strong>
											</p>
											<?php
											if ( $current_source_text === 'fat-secret' ) {
												printf( '<p class="yk-mt-active-ext-source">%s</p>', esc_html__( 'Active external source.' , YK_MT_SLUG ) );
											}
											?>
											<table class="form-table">
												<tr>
													<th scope="row"><?php echo esc_html__( 'Client ID' , YK_MT_SLUG); ?></th>
													<td>
														<input type="text" name="external-fatsecret-id" id="external-fatsecret-id" value="<?php echo esc_attr( yk_mt_site_options( 'external-fatsecret-id', '' ) ); ?>" class="large-text" maxlength="40" />
													</td>
												</tr>
												<tr>
													<th scope="row"><?php echo esc_html__( 'Client Secret' , YK_MT_SLUG); ?></th>
													<td>
														<input type="password" name="external-fatsecret-secret" id="external-fatsecret-secret" value="<?php echo esc_attr( yk_mt_site_options( 'external-fatsecret-secret', '' ) ); ?>" class="large-text" maxlength="40" />
													</td>
												</tr>
												<tr>
													<th scope="row"><?php echo esc_html__( 'Which API?' , YK_MT_SLUG ); ?></th>
													<td>
														<?php
														$food_api = yk_mt_site_options('external-fatsecret-food-api', 'recipes' );
														?>
														<select id="external-fatsecret-food-api" name="external-fatsecret-food-api">
															<option value="recipes" <?php selected( $food_api, 'recipes' ); ?>><?php echo esc_html__( 'Recipes API', YK_MT_SLUG ); ?></option>
															<option value="foods" <?php selected( $food_api, 'foods' ); ?>><?php echo esc_html__( 'Foods API', YK_MT_SLUG ); ?></option>
														</select>
														<?php printf( '<p>%1$s <a href="https://platform.fatsecret.com/api/Default.aspx?screen=rapiref2&method=recipes.search" target="_blank" rel="noopener">%2$s</a> %3$s
																						<a href="https://platform.fatsecret.com/api/Default.aspx?screen=rapiref2&method=foods.search" target="_blank" rel="noopener">%4$s</a>.</p>',
																				esc_html__( 'Which FatSecrets API would you like to use? Their ', YK_MT_SLUG ),
																				esc_html__( 'Recipes API', YK_MT_SLUG ),
																				esc_html__( 'or', YK_MT_SLUG ),
																				esc_html__( 'Foods API', YK_MT_SLUG ) );
															?>
													</td>
												</tr>
											</table>
                                            <h3><?php echo esc_html__( 'Another Meal Tracker instance' , YK_MT_SLUG ); ?></h3>
                                            <p><?php echo esc_html__( 'Connect to another site that has Meal Tracker installed and search the meal collection (added by admin) there. For this to work, you must have an additional plugin installed on the other site called "Meal Tracker API". Please email us for further information: ' , YK_MT_SLUG); ?> <a href="mailto:email@yeken.uk" target="_blank">email@yeken.uk</a></p>
											<?php

											if ( $current_source_text === 'meal-tracker'  ) {
												printf( '<p class="yk-mt-active-ext-source">%s</p>', esc_html__( 'Active external source.' , YK_MT_SLUG ) );
											}
											?>
                                            <table class="form-table">
                                                <tr>
                                                    <th scope="row"><?php echo esc_html__( 'API Endpoint' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <input type="text" name="external-meal-tracker-endpoint" id="external-meal-tracker-endpoint" placeholder="http://somewhere.com/wp-json/" value="<?php echo esc_attr( yk_mt_site_options( 'external-meal-tracker-endpoint', '' ) ); ?>" class="large-text" maxlength="40" />
                                                        <p><?php echo esc_html__( 'Specify the path for the other site\'s WP JSON end point e.g. change "somewhere.com" to the relevant URL: http://somewhere.com/wp-json/ ' , YK_MT_SLUG); ?></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row"><?php echo esc_html__( 'Bearer Token' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <input type="password" name="external-meal-tracker-bearer-token" id="external-meal-tracker-bearer-token" value="<?php echo esc_attr( yk_mt_site_options( 'external-meal-tracker-bearer-token', '' ) ); ?>" class="large-text" maxlength="40" />
                                                        <p><?php echo esc_html__( 'To communicate with the API endpoint, you must specify a Bearer Token that is created in the "Meal Tracker API" installed on the other website.', YK_MT_SLUG); ?></p>
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
											<h3><?php echo esc_html__( 'CSS Theme' , YK_MT_SLUG); ?></h3>
											<table class="form-table">
												<tr>
													<th scope="row"><?php echo esc_html__( 'Enabled' , YK_MT_SLUG); ?></th>
													<td>
														<?php
															$css_enabled = yk_mt_site_options_as_bool('css-theme-enabled' );
														?>
														<select id="css-theme-enabled" name="css-theme-enabled">
															<option value="true" <?php selected( $css_enabled, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
															<option value="false" <?php selected( $css_enabled, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
														</select>
														<p><?php echo esc_html__( 'If set to "Yes", the additional theme CSS shall be included. If you wish to add more of your own styling, you may wish to disable the bundled theme.', YK_MT_SLUG )?></p>
													</td>
												</tr>
											</table>
											<h3><?php echo esc_html__( 'Chart' , YK_MT_SLUG); ?></h3>
											<table class="form-table">
												<tr class="<?php echo $disable_if_not_premium_class; ?>">
													<th scope="row"><?php echo esc_html__( 'Calories Allowed colour', YK_MT_SLUG ); ?></th>
													<td>
														<input id="ws-ls-calories-allowed-colour" name="ws-ls-calories-allowed-colour" type="color" value="<?php echo esc_attr( get_option( 'ws-ls-calories-allowed-colour', '#fb8e2e' ) ); ?>">
														<p><?php echo esc_html__('Specify a HEX colour code to use for the Calories Allowed section of the pie chart.', YK_MT_SLUG); ?></p>
													</td>
												</tr>
											</table>
                                            <h3><?php echo esc_html__( 'Meal Tracker Shortcode' , YK_MT_SLUG); ?></h3>
                                            <table class="form-table">
                                               <tr class="<?php echo $disable_if_not_premium_class; ?>">
                                                    <th scope="row"><?php echo esc_html__( 'Accordion Enabled' , YK_MT_SLUG); ?></th>
                                                    <td>
                                                        <?php
                                                            $accordion_enabled = yk_mt_site_options_as_bool('accordion-enabled' );
                                                        ?>
                                                        <select id="accordion-enabled" name="accordion-enabled">
                                                            <option value="true" <?php selected( $accordion_enabled, true ); ?>><?php echo esc_html__( 'Yes', YK_MT_SLUG )?></option>
                                                            <option value="false" <?php selected( $accordion_enabled, false ); ?>><?php echo esc_html__( 'No', YK_MT_SLUG )?></option>
                                                        </select>
                                                        <p><?php echo esc_html__( 'If set to "Yes", the main meal tracker will use accordions to display meal data.', YK_MT_SLUG )?></p>
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
	register_setting( 'yk-mt-options-group', 'allow-calorie-override' );

	// Pro only open
    if( true ===  yk_mt_license_is_premium() ){

        register_setting( 'yk-mt-options-group', 'accordion-enabled' );
        register_setting( 'yk-mt-options-group', 'allow-calorie-override-admin' );
        register_setting( 'yk-mt-options-group', 'allow-calorie-external-wlt' );
        register_setting( 'yk-mt-options-group', 'yk-mt-edit-permissions' );
        register_setting( 'yk-mt-options-group', 'search-others-meals' );
		register_setting( 'yk-mt-options-group', 'search-admin-meals' );
        register_setting( 'yk-mt-options-group', 'new-entries-past' );
        register_setting( 'yk-mt-options-group', 'new-entries-future' );
		register_setting( 'yk-mt-options-group', 'allow-fractions' );
		register_setting( 'yk-mt-options-group', 'macronutrients-enabled' );
		register_setting( 'yk-mt-options-group', 'macronutrients-required' );

		register_setting( 'yk-mt-options-group', 'external-enabled' );
		register_setting( 'yk-mt-options-group', 'external-wprm-enabled' );
		register_setting( 'yk-mt-options-group', 'external-fatsecret-id' );
		register_setting( 'yk-mt-options-group', 'external-fatsecret-secret' );
		register_setting( 'yk-mt-options-group', 'external-fatsecret-food-api' );
		register_setting( 'yk-mt-options-group', 'external-meal-tracker-endpoint' );
		register_setting( 'yk-mt-options-group', 'external-meal-tracker-bearer-token' );
		register_setting( 'yk-mt-options-group', 'ws-ls-calories-allowed-colour' );
    }
}
add_action( 'admin_init', 'yk_mt_register_settings' );
