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
                        <h3 class="hndle"><span><?php echo __( 'Custom modifications / web development', WE_LS_SLUG); ?> </span></h3>
                        <div style="padding: 0px 15px 0px 15px">
	                        <?php yk_mt_custom_notification_html(); ?>
                        </div>
                    </div>
                    <div class="postbox">
                        <h3 class="hndle"><span><?php echo __( 'Admin Tools', WE_LS_SLUG); ?> </span></h3>
                        <div class="ws-ls-help-admin" style="padding: 0px 15px 0px 15px">
                            <p>
                                <?php

                                if ( false === yk_mt_setup_wizard_show_notice() ) {

                                    printf('<a class="button" href="%1$s" >%2$s</a>',
                                        esc_url( admin_url( 'admin.php?page=yk-mt-help&show-setup-wizard-links=y') ),
                                        __('Show Setup Wizard link', WE_LS_SLUG)
                                    );
                                }

                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle"><span><?php echo __( 'Contact', WE_LS_SLUG); ?> </span></h3>
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

    ws_ls_create_dialog_jquery_code(__('Are you sure?', WE_LS_SLUG),
        __('Are you sure you wish to remove all issued awards?', WE_LS_SLUG) . '<br /><br />',
        'awards-confirm');

    ws_ls_create_dialog_jquery_code(__('Are you sure?', WE_LS_SLUG),
        __('Are you sure you wish to clear all log entries?', WE_LS_SLUG) . '<br /><br />',
        'logs-confirm');

}



?>
