<?php

defined('ABSPATH') or die('Naw ya dinnie!');

function yk_mt_admin_page_user_summary() {

    yk_mt_admin_permission_check();

    $use_cache = true;

    $user_id = yk_mt_get_user_id_from_qs();

    // Ensure this WP user ID exists!
    yk_mt_exist_check( $user_id );

    yk_mt_admin_process_post_updates( $user_id );

	// Delete cache for the user?
	if ( 'y' === yk_mt_querystring_value( 'delete-cache', false ) ) {
		yk_mt_cache_user_delete( $user_id );
	}

    // Delete entries for the user?
    if ( 'y' === yk_mt_querystring_value( 'delete-entries', false ) ) {
        yk_mt_entry_delete_all_for_user( $user_id );
		yk_mt_cache_user_delete( $user_id );
        $use_cache = false;
    }

    // Delete meals for the user?
    if ( 'y' === yk_mt_querystring_value( 'delete-meals', false ) ) {
        yk_mt_meal_soft_delete_all_for_user( $user_id );
		yk_mt_cache_user_delete( $user_id );
    }

    $entries            = yk_mt_db_entries_summary( [ 'user-id' => $user_id, 'use-cache' => $use_cache ] );
    $todays_entry_id    = yk_mt_db_entry_get_id_for_today( $user_id );
    $todays_entry       = ( false === empty( $todays_entry_id ) ) ? yk_mt_db_entry_get( $todays_entry_id ) : NULL;

    ?>
    <div class="wrap ws-ls-user-data ws-ls-admin-page">
    <div id="poststuff">
        <?php yk_mt_user_header( $user_id ); ?>
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable" id="yk-mt-user-data-one">
                    <?php
                        if ( false === YK_MT_IS_PREMIUM ) {
                            yk_mt_display_pro_upgrade_notice();
                        }

                        $order = get_option( 'yk-mt-postbox-order-yk-mt-user-data-one', [ 'chart', 'table' ] );

						foreach ( $order as $postbox ) {

							if ( 'chart' === $postbox ) {
								yk_mt_postbox_user_chart( $entries );
							} elseif ( 'table' === $postbox ) {
								yk_mt_postbox_user_table( $entries );
							}
						}
                    ?>
                </div>
            </div>
            <?php yk_mt_user_side_bar( $user_id, $todays_entry ); ?>
        </div>
        <br class="clear">
    </div>
    <?php
}

/**
* Postbox for user chart
*
* @param $entries
*/
function yk_mt_postbox_user_chart( $entries ) {
?>
	<div class="postbox <?php yk_mt_postbox_classes( 'chart' ); ?>" id="chart">
		<?php yk_mt_postbox_header( [ 'title' => __( 'Chart', YK_MT_SLUG ), 'postbox-id' => 'chart', 'postbox-col' => 'yk-mt-user-data-one' ] ); ?>
		<div class="inside">
			<?php

				if ( false === empty( $entries ) ) {
					echo yk_mt_chart_line_allowed_versus_used( [
						'entries'   => $entries,
						'max'       => 15,
						'title'     => __( 'Latest 15 entries', YK_MT_SLUG )
					]);
				} else {
					printf ( '<p><em>%s</em></p>', __( 'No results', YK_MT_SLUG ) );
				}
			?>
		</div>
	</div>
<?php
}

/**
* Postbox for user table
*
* @param $entries
*/
function yk_mt_postbox_user_table( $entries ) {
?>
	<div class="postbox <?php yk_mt_postbox_classes( 'table' ); ?>" id="table">
		<?php yk_mt_postbox_header( [ 'title' => __( 'Entries for this user', YK_MT_SLUG ), 'postbox-id' => 'table', 'postbox-col' => 'yk-mt-user-data-one' ] ); ?>
		<div class="inside">
			<?php
				yk_mt_table_user_entries( [ 'entries'   => $entries ] );
			?>
		</div>
	</div>
<?php
}

