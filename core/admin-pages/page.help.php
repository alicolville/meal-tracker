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
                        <h3 class="hndle"><span><?php echo __( 'Custom modifications / web development', YK_MT_SLUG ); ?> </span></h3>
                        <div style="padding: 0px 15px 0px 15px">
	                        <?php yk_mt_custom_notification_html(); ?>
                        </div>
                    </div>
					<div class="postbox">
						<h3 class="hndle"><span><?php echo __( 'Documentation and Release notes', YK_MT_SLUG); ?> </span></h3>
						<div style="padding: 0px 15px 0px 15px">
							<p><?php echo __( 'You can find detailed documentation for this plugin at our site:', YK_MT_SLUG ); ?></p>
							<p>
								<a href="https://mealtracker.yeken.uk" rel="noopener noreferrer"  class="button"  target="_blank"><?php echo __( 'View Documentation', YK_MT_SLUG ); ?></a>
								<a href="https://github.com/alicolville/meal-tracker/releases"  class="button"  rel="noopener noreferrer" target="_blank"><?php echo __( 'Release Notes', YK_MT_SLUG ); ?></a>
							</p>
						</div>
					</div>
					<?php if ( false === function_exists( 'wl_ls_setup_wizard_meal_tracker_html' ) ): ?>
						<div class="postbox">
							<h3 class="hndle"><span><?php echo __( 'Weight Tracker', YK_MT_SLUG); ?> </span></h3>
							<div style="padding: 0px 15px 0px 15px">
								<?php yk_mt_setup_wizard_meal_tracker_html(); ?>
							</div>
						</div>
					<?php endif; ?>
                    <div class="postbox">
                        <h3 class="hndle"><span><?php echo __( 'Admin Tools', YK_MT_SLUG ); ?> </span></h3>
                        <div class="ws-ls-help-admin" style="padding: 0px 15px 0px 15px">
                            <p>
                                <?php

                                if ( false === yk_mt_setup_wizard_show_notice() ) {

                                    printf('<a class="button" href="%1$s" >%2$s</a>',
                                        esc_url( admin_url( 'admin.php?page=yk-mt-help&show-setup-wizard-links=y') ),
                                        __('Show Setup Wizard link', YK_MT_SLUG )
                                    );
                                }

								printf( '<a href="%1$s?page=yk-mt-settings&amp;recreate-tables=y" class="button">%2$s</a>', get_permalink(), __( 'Run MySQL Tool', YK_MT_SLUG ) );

                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle"><span><?php echo __( 'Contact', YK_MT_SLUG ); ?> </span></h3>
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
	<p><img src="<?php echo plugins_url( 'admin-pages/assets/images/wt-logo.png', __DIR__ ); ?>" width="100" height="100" style="margin-right:20px" align="left" />
		<?php echo __( 'Why not check out our sister plugin Weight Tracker. Allow your user\'s to track weight and much more!', YK_MT_SLUG ); ?></p>
	<p><strong><?php echo __( 'Get Weight Tracker Now', YK_MT_SLUG); ?>.</strong></p>
	<p style="font-weight: bold; font-size: 18px;"><?php echo __( '20% off coupon', YK_MT_SLUG); ?>: 20-OFF-WEIGHT-TRACKER</p>
	<p><a href="https://docs.yeken.uk" rel="noopener noreferrer" target="_blank"><?php echo __( 'Documentation site', YK_MT_SLUG); ?></a> /
		<a href="https://shop.yeken.uk/product-category/weight-tracker/" rel="noopener noreferrer" target="_blank"><?php echo __( 'Upgrade to Premium', YK_MT_SLUG); ?></a> /
		<a href="mailto:email@yeken.uk" ><?php echo __( 'Any questions, email@yeken.uk', YK_MT_SLUG); ?></a></p>
	<br clear="both"/>

	<?php
}
