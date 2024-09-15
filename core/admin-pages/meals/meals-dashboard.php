<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_meals_dashboard() {

    yk_mt_admin_permission_check();

	if ( false === YK_MT_IS_PREMIUM ) {
		yk_mt_display_pro_upgrade_notice();
	}

	$user_id = yk_mt_querystring_value( 'user-id' );
	$options = [ 'sort-order' => 'asc', 'use-cache' => false ];

	if ( true === empty( $user_id ) ) {
		$options[ 'admin-meals-only' ] = true;
	}

    ?>
    <div class="wrap ws-ls-user-meals ws-ls-admin-page">
	<h2>
		<span>
			<?php
				if  ( false === empty( $user_id ) ) {
					printf( '%s: <em>%s</em>', esc_html__( 'Meals added by', 'meal-tracker' ), yk_mt_user_display_name( $user_id ) );
				} else {
					printf( ' %s', esc_html__( 'Meal collection', 'meal-tracker' ) );
				}
			?>
		</span>
	</h2>
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
							<?php

							$delete_id = yk_mt_querystring_value( 'delete' );

							if ( false === empty( $delete_id ) &&
									true === YK_MT_IS_PREMIUM ) {
								if ( true === yk_mt_meal_update_delete( $delete_id ) ) {
									printf( '<p><strong>%s</strong></p>', esc_html__( 'The meal has been successfully deleted.', 'meal-tracker'  ) );
								}
							}

							if ( true === empty( $user_id ) ) {

								printf( '<p>%s</p>', esc_html__( 'The following meals can be searched by your users and added to their daily entries.', 'meal-tracker' ) );

								yk_mt_admin_option_links_clicked( 'search-admin-meals' );

								if ( false === yk_mt_site_options_as_bool('search-admin-meals', false )
									 || false === YK_MT_IS_PREMIUM ) {
									printf( '<p class="yk-mt-error-red"><strong>%s</strong>. %s. <a href="%s">%s</a>.</p>',
										esc_html__( 'Admin Collection not searchable', 'meal-tracker' ),
										esc_html__( 'As an administrator, you can add, edit and delete meals. However, the setting "Admin\'s meal collection" under "Searching meals" has been disabled which means your users can not search this collection', 'meal-tracker' ),
										esc_url( admin_url( 'admin.php?page=yk-mt-meals&search-admin-meals=true' ) ),
										esc_html__( 'Enable now', 'meal-tracker' )
									);
								}
							}

							$meals = yk_mt_db_meal_for_user( $user_id, $options );

							yk_mt_table_meals( [ 'meals' => $meals, 'show-username' => true, 'user-id-for-link' => $user_id ] );

							?>
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
