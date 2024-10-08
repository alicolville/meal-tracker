<?php

defined('ABSPATH') or die('Jog on!');

function yk_mt_help_page() {

	?>

    <div class="wrap ws-ls-admin-page">

	<div id="icon-options-general" class="icon32"></div>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-3">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">

                    <div class="postbox">
                        <h3 class="hndle"><span><?php echo esc_html__( 'Custom modifications / web development', 'meal-tracker' ); ?> </span></h3>
                        <div style="padding: 0px 15px 0px 15px">
	                        <?php yk_mt_custom_notification_html(); ?>
                        </div>
                    </div>
					<div class="postbox">
						<h3 class="hndle"><span><?php echo esc_html__( 'Documentation and Release notes', 'meal-tracker' ); ?> </span></h3>
						<div style="padding: 0px 15px 0px 15px">
							<p><?php echo esc_html__( 'You can find detailed documentation for this plugin at our site:', 'meal-tracker' ); ?></p>
							<p>
								<a href="https://mealtracker.yeken.uk" rel="noopener noreferrer"  class="button"  target="_blank"><?php echo esc_html__( 'View Documentation', 'meal-tracker' ); ?></a>
								<a href="https://github.com/alicolville/meal-tracker/releases"  class="button"  rel="noopener noreferrer" target="_blank"><?php echo esc_html__( 'Release Notes', 'meal-tracker' ); ?></a>
							</p>
						</div>
					</div>
					<?php if ( false === function_exists( 'wl_ls_setup_wizard_meal_tracker_html' ) ): ?>
						<div class="postbox">
							<h3 class="hndle"><span><?php echo esc_html__( 'Weight Tracker', 'meal-tracker' ); ?> </span></h3>
							<div style="padding: 0px 15px 0px 15px">
								<?php yk_mt_setup_wizard_meal_tracker_html(); ?>
							</div>
						</div>
					<?php endif; ?>
                    <div class="postbox">
                        <h3 class="hndle"><span><?php echo esc_html__( 'Admin Tools', 'meal-tracker' ); ?> </span></h3>
                        <div class="ws-ls-help-admin" style="padding: 0px 15px 0px 15px">
                            <p>
                                <?php

                                if ( false === yk_mt_setup_wizard_show_notice() ) {

                                    printf('<a class="button" href="%1$s" >%2$s</a>',
                                        esc_url( admin_url( 'admin.php?page=yk-mt-help&show-setup-wizard-links=y') ),
                                        esc_html__('Show Setup Wizard link', 'meal-tracker' )
                                    );
                                }

								printf( '<a href="%1$s?page=yk-mt-settings&amp;recreate-tables=y" class="button">%2$s</a>', get_permalink(), esc_html__( 'Run MySQL Tool', 'meal-tracker' ) );

                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle"><span><?php echo esc_html__( 'Contact', 'meal-tracker' ); ?> </span></h3>
                        <div style="padding: 0px 15px 0px 15px">
                            <p>If you have any questions or bugs to report, then please contact us at <a href="mailto:email@yeken.uk">email@yeken.uk</a>.</p>
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

/**
 * HTML for mention of meal tracker
 */
function yk_mt_setup_wizard_meal_tracker_html() {
	?>
	<p><img src="<?php yk_mt_echo( plugins_url( 'admin-pages/assets/images/wt-logo.png', __DIR__ ) ); ?>" width="100" height="100" style="margin-right:20px" align="left" />
		<?php echo esc_html__( 'Why not check out our sister plugin Weight Tracker. Allow your user\'s to track weight and much more!', 'meal-tracker' ); ?></p>
	<p><strong><?php echo esc_html__( 'Get Weight Tracker Now', 'meal-tracker' ); ?>.</strong></p>
	<p style="font-weight: bold; font-size: 18px;"><?php echo esc_html__( '20% off coupon', 'meal-tracker' ); ?>: 20-OFF-WEIGHT-TRACKER</p>
	<p><a href="https://docs.yeken.uk" rel="noopener noreferrer" target="_blank"><?php echo esc_html__( 'Documentation site', 'meal-tracker' ); ?></a> /
		<a href="https://shop.yeken.uk/product-category/weight-tracker/" rel="noopener noreferrer" target="_blank"><?php echo esc_html__( 'Upgrade to Premium', 'meal-tracker' ); ?></a> /
		<a href="mailto:email@yeken.uk" ><?php echo esc_html__( 'Any questions, email@yeken.uk', 'meal-tracker' ); ?></a></p>
	<br clear="both"/>

	<?php
}
