<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_meals_import() {

    yk_mt_admin_permission_check();

	wp_enqueue_media();

	$importing 	= false;
	$output		= '';

	if ( true === YK_MT_IS_PREMIUM &&
			false === empty( $_POST[ 'attachment-id' ] ) ){

		$importing 	= true;
		$dry_run	= ( false === empty( $_POST[ 'dry-run' ] ) );
		$output 	= yk_mt_import_csv_meal_collection( $_POST[ 'attachment-id' ], $dry_run );
	}

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
					   	<h2 class="hndle"><?php echo __( 'Import CSV', YK_MT_SLUG ); ?></h2>
                        <div class="inside">
                        	<?php if ( false === $importing ): ?>
								<div class="yk-mt-form-row">
									<p>
										<?php echo __( 'Please select a CSV file to import one or meals into your collection.', YK_MT_SLUG ); ?>
										<a href="https://mealtracker.yeken.uk/csv-import.html" rel="noopener noreferrer" target="_blank"><?php echo __( 'Read more about CSV imports and the required format', YK_MT_SLUG ); ?>.</a>
									</p>
									<input id="select_csv" type="button" class="button" value="<?php echo __( 'Select CSV file', YK_MT_SLUG ); ?>" />
									<br />
								</div>
								<div class="yk-mt-hide" id="selected-form" >
									<form action="<?php echo admin_url( 'admin.php?page=yk-mt-meals&mode=import'); ?>" method="post">
										<div class="yk-mt-form-row">
											<label for="attachment-path"><?php echo __( 'Selected file:', YK_MT_SLUG ); ?></label>
											<input type='text' name='attachment-path' id='attachment-path' value='' class="widefat" disabled="disabled" />
											<input type='hidden' name='attachment-id' id='attachment-id' value='' />
										</div>
										<div class="yk-mt-form-row">
											<input type='checkbox' name='dry-run' id='dry-run' value='yes' />
											<label for="dry-run"><?php echo __( 'Dry run mode. This will do basic tests on the file without performing an import.', YK_MT_SLUG ); ?></label>
										</div>
										<div class="yk-mt-form-row">
											<input type="submit" class="button button-primary" value="<?php echo __( 'Import CSV', YK_MT_SLUG ); ?>" <?php if ( false === YK_MT_IS_PREMIUM ) { echo 'disabled="disabled"'; } ?> />
										</div>
									</form>
								</div>
							<?php else: ?>
								<p><strong><?php echo __( 'Output:', YK_MT_SLUG ); ?></strong></p>
								<textarea class="widefat" rows="20" cols="100"><?php echo esc_html( $output ); ?></textarea>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
			<?php yk_mt_dashboard_side_bar(); ?>
        </div>
        <br class="clear">
    </div>
    <?php
}
