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
		$output 	= yk_mt_import_csv_meal_collection( (int) $_POST[ 'attachment-id' ], $dry_run );
	}

    ?>
    <div class="wrap ws-ls-user-meals ws-ls-admin-page">
	<h2><?php echo esc_html__( 'Import CSV', 'meal-tracker' ); ?></h2>
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
					   	<div class="inside">
                        	<?php if ( false === $importing ): ?>
								<div class="yk-mt-form-row">
									<p>
										<?php echo esc_html__( 'Please select a CSV file to import one or meals into your collection.', 'meal-tracker' ); ?>
										<a href="https://mealtracker.yeken.uk/csv-import.html" rel="noopener noreferrer" target="_blank"><?php echo esc_html__( 'Read more about CSV imports and the required format', 'meal-tracker' ); ?>.</a>
									</p>
									<input id="select_csv" type="button" class="button" value="<?php echo esc_html__( 'Select CSV file', 'meal-tracker' ); ?>" />
									<br />
								</div>
								<div class="yk-mt-hide" id="selected-form" >
									<form action="<?php echo esc_url( admin_url( 'admin.php?page=yk-mt-meals&mode=import') ); ?>" method="post">
										<div class="yk-mt-form-row">
											<label for="attachment-path"><?php echo esc_html__( 'Selected file:', 'meal-tracker' ); ?></label>
											<input type='text' name='attachment-path' id='attachment-path' value='' class="widefat" disabled="disabled" />
											<input type='hidden' name='attachment-id' id='attachment-id' value='' />
										</div>
										<div class="yk-mt-form-row">
											<input type='checkbox' name='dry-run' id='dry-run' value='yes' />
											<label for="dry-run"><?php echo esc_html__( 'Dry run mode. This will do basic tests on the file without performing an import.', 'meal-tracker' ); ?></label>
										</div>
										<div class="yk-mt-form-row">
											<input type="submit" class="button button-primary" value="<?php echo esc_html__( 'Import CSV', 'meal-tracker' ); ?>" <?php if ( false === YK_MT_IS_PREMIUM ) { echo 'disabled="disabled"'; } ?> />
										</div>
									</form>
								</div>
							<?php else: ?>
								<p><strong><?php echo esc_html__( 'Output:', 'meal-tracker' ); ?></strong></p>
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
