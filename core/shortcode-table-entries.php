<?php

defined('ABSPATH') or die("Jog on!");

/**
 * Render shortcode [mt-table-entries]
 */
function yk_mt_shortcode_table_entries( $user_defined_arguments ) {

	if ( false === YK_MT_IS_PREMIUM ) {
		return yk_mt_display_premium_upgrade_notice_for_shortcode();
	}

	$shortcode_arguments = shortcode_atts( [    'link-new-window'   => 'true',
												'url-mealtracker'   => '',
												'user-id'           => get_current_user_id(),
												'sort-direction'    => 'desc',
												'text-no-entries'   => __( 'You currently have no entries.', YK_MT_SLUG ),
												'type'              => 'advanced'                                           // advanced / basic
	], $user_defined_arguments );

	$advanced_mode                              = ( 'advanced' === $shortcode_arguments[ 'type'] );

	$entries                                    = yk_mt_db_entries_summary( [ 'user-id' => $shortcode_arguments[ 'user-id' ], 'sort-order' => $shortcode_arguments[ 'sort-direction' ] ] );

	if ( true === empty( $entries ) ){
		return sprintf( '<p>%s</p>', esc_html( $shortcode_arguments[ 'text-no-entries' ] ) );
	}

	// Include footable library if advanced table
	if ( 'advanced' === $shortcode_arguments[ 'type' ] ) {

		yk_mt_enqueue_scripts_footable();
		wp_enqueue_script( 'mt-footable-table-entries', plugins_url( '/assets/js/shortcodes/mt-table-entries.js', __DIR__ ), [ 'mt-footable' ], YK_MT_PLUGIN_VERSION, true );

	}

	$mt_link_enabled    = ! empty( $shortcode_arguments[ 'url-mealtracker' ] );
	$mt_link_class      = ( false === $mt_link_enabled ) ? ' data-visible="false" style="display: none" ' : '';

	$html = sprintf( '<table class="yk-mt-table-entries">
			            <thead>
				            <tr>
				                <th data-type="date" data-format-string="%1$s">%2$s</th>
								<th data-breakpoints="xs" data-type="number">%3$s</th>
								<th data-breakpoints="sm" data-type="number">%4$s</th>
								<th data-breakpoints="xs" data-type="number">%5$s</th>
								<th data-breakpoints="xs" data-sortable="false">%%</th>
								<th %6$s></th>
							</tr>
						</thead>
						<tbody>',
						'D/M/Y',
						__( 'Date', YK_MT_SLUG ),
						__( 'Allowed', YK_MT_SLUG ),
						__( 'Used', YK_MT_SLUG ),
						__( 'Remaining', YK_MT_SLUG ),
						$mt_link_class
	);

	foreach ( $entries as $entry ) {

		$class  = ( $entry[ 'calories_used' ] > $entry[ 'calories_allowed' ] ) ? 'yk-mt-error' : 'yk-mt-ok';

		$url    = ( true === $mt_link_enabled ) ? add_query_arg( 'entry-id', $entry[ 'date' ], $shortcode_arguments[ 'url-mealtracker' ] ) : '';

		$html .= sprintf ( '  <tr class="%1$s">
		                        <td>%2$s</td>
		                        <td>%3$s</td>
		                        <td>%4$s</td>
		                        <td>%5$s</td>
		                        <td>%6$s</td>
		                        <td %8$s><a href="%7$s" class="btn btn-default footable-edit" %10$s><i class="fa fa-eye">%9$s</i></a></td>
		                    </tr>',
							$class,
							yk_mt_date_format( $entry['date' ] ),
							$entry[ 'calories_allowed' ],
							$entry[ 'calories_used' ],
							$entry[ 'calories_remaining' ],
							$entry[ 'percentage_used' ] . '%',
							esc_url( $url ),
							$mt_link_class,
							! $advanced_mode ? __( 'View', YK_MT_SLUG ) : '',
							yk_mt_to_bool( $shortcode_arguments[ 'link-new-window' ] ) ? ' target="_blank" rel="noopener"' : ''
		);
	}

	$html .= '</tbody></table>';

	return $html;
}
add_shortcode( 'mt-table-entries', 'yk_mt_shortcode_table_entries' );
