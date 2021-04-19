<?php

defined('ABSPATH') or die('Jog on!');

function yk_mt_setup_wizard_page() {

    $user_data_link = yk_mt_get_link_to_user_data();

	?>
	<div class="wrap ws-ls-admin-page">

		<div id="icon-options-general" class="icon32"></div>

		<div id="poststuff">

			<div id="post-body" class="metabox-holder columns-3">

				<!-- main content -->
				<div id="post-body-content">

					<div class="meta-box-sortables ui-sortable">

						<div class="postbox">
							<h3 class="hndle"><span><?php echo __( 'Setup Wizard', YK_MT_SLUG ); ?> </span></h3>
							<div style="padding: 15px 15px 0px 15px">
                                <div id="yk-mt-tabs">
                                    <ul>
                                        <li><a>1. <?php echo __( 'Introduction', YK_MT_SLUG ); ?><span><?php echo __( 'Thank you for using Meal Tracker', YK_MT_SLUG ); ?></span></a></li>
                                        <li><a>2. <?php echo __( 'Setup', YK_MT_SLUG ); ?><span><?php echo __( 'How to use Shortcodes and Widgets', YK_MT_SLUG ); ?></span></a></li>
                                        <li><a>3. <?php echo __( 'Admin Interface', YK_MT_SLUG ); ?><span><?php echo __( 'Viewing and interacting with your user\'s data', YK_MT_SLUG ); ?></span></a></li>
                                        <li><a>4. <?php echo __( 'Customisations', YK_MT_SLUG ); ?><span><?php echo __( 'Custom modifications to Meal Tracker', YK_MT_SLUG ); ?></span></a></li>
                                    </ul>
                                    <div>
                                        <div>
                                            <h3>Thank you</h3>
                                            <p>First of all, <strong>thank you</strong> for installing Meal Tracker on your website! Meal Tracker extends your website by giving your users the ability to track their calorie intake and meals for a given day.
												The aim of the plugin is to allow you to extend your site with out-the-box functionality with minimal technical ability. The setup wizard should give you an overview of the plugin and how to set Meal Tracker up on your website.</p>

											<p>For further information, please visit the documentation site:</p>
											<a href="https://mealtracker.yeken.uk" rel="noopener noreferrer"  class="button"  target="_blank"><?php echo __( 'View Documentation', YK_MT_SLUG ); ?></a>
											<h3>Features of Meal Tracker</h3>
											<p>For a full list of Meal Tracker features, please visit our documentation site:</p>
											<a href="https://mealtracker.yeken.uk/features.html" target="_blank" rel="noopener" class="button"><i class="fa fa-link"></i> Meal Tracker Features</a>
                                        </div>
                                        <div>
                                            <p>Out of the box, Meal Tracker does not extend the public facing side of your site with features that allow the users of your site to interact with.
                                                You need to build these by using the Meal Tracker shortcode.

                                            <h3>Shortcodes</h3>
                                            <h4>What are they?</h4>
                                            <p>Shortcodes are a feature of WordPress that allow site administrators to extend the functionality of their site
                                                by simply placing shortcodes in page and post content. When the page or post is published, the shortcode is replaced with the relevant features.
                                                For example, a standard WordPress shortcode for creating a gallery is
                                                [gallery]. If you are unsure about shortcodes and their use, you should consider reading <a href="https://en.support.wordpress.com/shortcodes/" target="_blank" rel="noopener">WordPress's documentation</a>.
                                            </p>
                                            <h4>Meal Tracker shortcode</h4>
                                            <p>
                                                <a rel="nopener" href="<?php echo plugins_url( 'assets/images/setup-wizard-meal-tracker-shortcode.png', __FILE__ ); ?>" target="_blank">
                                                    <img src="<?php echo plugins_url( 'assets/images/setup-wizard-meal-tracker-shortcode-small.png', __FILE__ ); ?>" align="left" class="setup-wizard-image"/>
                                                </a>
												The user interface (as seen) is rendered by placing the shortcode <strong><a href="https://mealtracker.yeken.uk/shortcodes/meal-tracker.html" target="_blank" rel="noopener">[meal-tracker]</a></strong> on a page or post.
                                            </p>
											<br clear="all" />

											<h4>More information</h4>
											<p>For further information on Meal Tracker shortcodes, please refer to the Meal Tracker documentation:</p>
											<a href="https://mealtracker.yeken.uk/shortcodes.html" rel="noopener noreferrer"  class="button"  target="_blank"><?php echo __( 'View Documentation', YK_MT_SLUG ); ?></a>
                                        </div>
                                        <div>
                                            <p>
                                                <a rel="nopener" href="<?php echo plugins_url( 'assets/images/user-data.png', __FILE__ ); ?>" target="_blank">
                                                    <img src="<?php echo plugins_url( 'assets/images/user-data-small.png', __FILE__ ); ?>" align="left" class="setup-wizard-image"/>
                                                </a>
                                                Meal Tracker contains an extensive admin section for viewing your user's data. You can access it from the WordPress admin menu by navigating to Meal Tracker > <a href="<?php echo $user_data_link; ?>">Manage User Data</a>
                                            </p>

                                            <p>
                                                <a href="<?php echo $user_data_link; ?>" class="button"><i class="fa fa-link"></i> View User Data</a>
                                            </p>
											<br clear="all" />
											<h4>More information</h4>
											<p>For further information on the Meal Tracker admin, please refer to the Meal Tracker documentation:</p>
											<a href="https://mealtracker.yeken.uk/admin.html" rel="noopener noreferrer"  class="button"  target="_blank"><?php echo __( 'View Documentation', YK_MT_SLUG ); ?></a>
                                        </div>
                                        <div>
											<?php  yk_mt_setup_wizard_custom_notification_html(); ?>
                                        </div>

                                    </div>
                                </div>
								<br clear="both"/>
                                <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=yk-mt-help&hide-setup-wizard=y') ); ?>" class="button button-primary"><i class="fa fa-check"></i> I've finished - hide the wizard!</a></p>

                            </div>
						</div>
                    </div>
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


?>
