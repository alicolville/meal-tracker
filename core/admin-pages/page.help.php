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
