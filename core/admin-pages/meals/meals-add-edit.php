<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_meals_add_edit() {

	yk_mt_admin_permission_check();

	?>
	<div class="wrap ws-ls-user-meals ws-ls-admin-page">
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<?php
					if ( false === YK_MT_IS_PREMIUM ) {
						yk_mt_display_pro_upgrade_notice();
					}
					?>
					<div class="postbox">
						<h2 class="hndle"><span><?php echo __( 'Add / edit a meal', YK_MT_SLUG ); ?></span></h2>
						<div class="inside">
							<p>
							<?php

								// Editing an entry?
								$meal_id 		= yk_mt_querystring_value( 'edit' );
								$existing_meal 	= ( false === empty( $meal_id ) ) ? yk_mt_db_meal_get( $meal_id, get_current_user_id() ) : NULL;

								echo yk_mt_shortcode_meal_tracker_manual_meal_entry_form( $existing_meal );

							?>
						</div>
					</div>
				</div>
			</div>
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<?php yk_mt_dashboard_meals_side_bar(); ?>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
	<?php
}