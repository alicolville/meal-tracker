<?php

    defined('ABSPATH') or die('Jog on!');

    function yk_mt_advertise_pro() {

        $site_hash = yk_mt_generate_site_hash();

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', YK_MT_SLUG ) );
        }

        // Remove existing license?
        if ( false === empty( $_GET['remove-license'] ) ) {
            yk_mt_license_remove();
        }

        ?>

        <div class="wrap ws-ls-admin-page">
            <?php
                if ( false === empty( $_POST['license-key'] ) ){

                    // First try validating and applying a new subscription license
                    $valid_license = yk_mt_license_apply( $_POST['license-key'] );

                    if ( $valid_license ) {
                        yk_mt_message_display( __('Your license has been applied!', YK_MT_SLUG ) );
                    } else {
                        yk_mt_message_display(__('There was an error applying your license. ', YK_MT_SLUG ), true);
                    }
                }

                $existing_license = ( true === yk_mt_license_is_premium() ) ? yk_mt_license() : NULL;

                if ( false === empty( $existing_license ) ) {
                    $license_decoded = yk_mt_license_decode( $existing_license );
                }

            ?>
            <div id="icon-options-general" class="icon32"></div>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">

                        <div class="meta-box-sortables ui-sortable">
                            <div class="postbox">
                                <h3 class="hndle"><span>Upgrade / your License</span></h3>
                                <div class="inside">
                                    <center>
                                        <h3>In case you need, your <strong>Site Hash</strong>
                                            is: <?php echo esc_html( $site_hash ) ; ?></h3>

                                        <?php

                                            if ( true === empty( $existing_license ) ) :

                                                yk_mt_upgrade_button();
                                        ?>
                                                <br />
												<br />
                                                <hr />
                                                <h3>Premium Features</h3>
                                                <p>Upgrade to the Premium version of Meal Tracker and get the additional features:</p>

                                                <br />
                                        	<?php

												yk_mt_features_button();

												echo yk_mt_features_display();

                                            endif;
                                        ?>
                                    </center>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="postbox-container-1" class="postbox-container">

                        <div class="meta-box-sortables">

                            <div class="postbox">

                                <h3 class="hndle"><span>Add or Update License</span></h3>

                                <div class="inside">

                                    <form action="<?php echo admin_url( 'admin.php?page=yk-mt-license&add-license=true' ); ?>"
                                          method="post">
                                        <p>Copy and paste the license given to you by YeKen into this box and click "Apply License".</p>
                                        <textarea rows="5" style="width:100%" name="license-key"></textarea>
                                        <br/><br/>
                                        <input type="submit" class="button-secondary large-text" value="Apply License"/>
                                    </form>
                                </div>
                            </div>
                            <div class="postbox">
                                <h3 class="hndle"><span>Your License Information</span></h3>
                                <div class="inside">
                                    <table class="ws-ls-sidebar-stats">
                                        <tr>
                                            <th>Site Hash</th>
                                            <td><?php echo esc_html( yk_mt_generate_site_hash() ); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Expires</th>
                                            <td>
                                                <?php

                                                    if( false === empty( $license_decoded['type'] ) &&
															'mt-premium' === $license_decoded['type'] ) {

                                                        $time = strtotime( $license_decoded['expiry-date'] );
                                                        $formatted = date( 'd/m/Y', $time );

                                                        echo esc_html( $formatted );
                                                    } else {
                                                        echo __('No active license', YK_MT_SLUG );
                                                    }

                                                ?>
                                            </td>
                                        </tr>

                                        <?php if ( false === empty( $existing_license ) ): ?>
                                            <tr class="last">
                                                <th colspan="2"><?php echo __('Your Existing License', YK_MT_SLUG ); ?></th>
                                            </tr>
                                            <tr class="last">
                                                <td colspan="2"><textarea rows="5" style="width:100%"><?php echo esc_textarea( $existing_license ); ?></textarea></td>
                                            </tr>
                                            <tr class="last">
                                                <td colspan="2"><a href="<?php echo admin_url('admin.php?page=yk-mt-license&remove-license=true'); ?>" class="button-secondary delete-license">Remove License</a></td>
                                            </tr>

                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                      </div>
                    </div>
                    <div id="post-body" class="metabox-holder columns-3">
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">

                            </div>
                        </div>
                        <br class="clear">
                    </div>
                </div>

            <?php
        }
?>
