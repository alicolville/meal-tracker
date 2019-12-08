<?php

defined('ABSPATH') or die('Jog on!');

define( 'YK_MT_SETUP_WIZARD_DIMISS_OPTION', 'yk-mt-setup-wizard-dismiss' );

/**
 * Display HTML for admin notice
 */
function yk_mt_setup_wizard_notice() {

    printf('<div class="updated notice is-dismissible setup-wizard-dismiss">
                        <p>%1$s <strong>%2$s</strong>! %3$s.</p>
                        <p><a href="%4$s" class="button button-primary">Run wizard</a></p>
                    </div>',
                    __( 'Welcome to' , YK_MT_SLUG ),
                YK_MT_PLUGIN_NAME,
                    __( 'You\'re almost there, but a wizard might help you setup the plugin' , YK_MT_SLUG ),
                    esc_url( yk_mt_setup_wizard_get_link() )
    );
}

/**
 * Return URL for setup wizard
 *
 * @return string|void
 */
function yk_mt_setup_wizard_get_link() {
	return admin_url( 'admin.php?page=yk-mt-setup-wizard');
}

/**
 * Show setup wizard
 *
 * @return bool
 */
function yk_mt_setup_wizard_show_notice() {
    return ( false === (bool) get_option( YK_MT_SETUP_WIZARD_DIMISS_OPTION ) );
}

/**
 * Show / hide setup wizard?
 */
function yk_mt_setup_wizard_help_page_show_links_again() {

    if ( true === isset( $_GET[ 'show-setup-wizard-links' ] ) ) {
        yk_mt_setup_wizard_show_notice_links_again();
    } else if ( true === isset( $_GET[ 'hide-setup-wizard' ] ) ) {
		yk_mt_setup_wizard_dismiss_notice();
	}

}
add_action( 'plugins_loaded', 'yk_mt_setup_wizard_help_page_show_links_again' );

/**
 * Show Wizard Links again
 */
function yk_mt_setup_wizard_show_notice_links_again() {
    delete_option( YK_MT_SETUP_WIZARD_DIMISS_OPTION, true );
}

/**
 * Show Admin Notice
 */
function yk_mt_setup_wizard_show_admin_notice() {
    if ( true === yk_mt_setup_wizard_show_notice() ) {
        yk_mt_setup_wizard_notice();
    }
}
add_action( 'admin_notices', 'yk_mt_setup_wizard_show_admin_notice' );

/**
 * Update option on whether to show wizard
 */
function yk_mt_setup_wizard_dismiss_notice() {
    update_option( YK_MT_SETUP_WIZARD_DIMISS_OPTION, true );
}
add_action( 'wp_ajax_yk_mt_setup_wizard_dismiss', 'yk_mt_setup_wizard_dismiss_notice' );

/**
 * HTML for mention of custom work
 */
function yk_mt_setup_wizard_custom_notification_html() {
	?>

		<p><img src="<?php echo plugins_url( 'admin-pages/assets/images/yeken-logo.png', __FILE__ ); ?>" width="100" height="100" style="margin-right:20px" align="left" /><?php echo __( 'If require plugin modifications to Weight Tracker, or need a new plugin built, or perhaps you need a developer to help you with your website then please don\'t hesitiate get in touch!', YK_MT_SLUG ); ?></p>
		<p><strong><?php echo __( 'We provide fixed priced quotes.', YK_MT_SLUG); ?></strong></p>
		<p><a href="https://www.yeken.uk" rel="noopener noreferrer" target="_blank">YeKen.uk</a> /
			<a href="https://profiles.wordpress.org/aliakro" rel="noopener noreferrer" target="_blank">WordPress Profile</a> /
			<a href="mailto:email@yeken.uk" >email@yeken.uk</a></p>
		<br clear="both"/>

	<?php
}

/**
 * Return base URL for user data
 * @return string
 */
function yk_mt_get_link_to_user_data() {
    return esc_url( admin_url( 'admin.php?page=yk-mt-user') );
}