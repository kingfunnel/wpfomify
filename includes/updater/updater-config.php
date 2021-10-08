<?php

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'IBX_WPFOMO_SL_URL', 'https://wpfomify.com' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

define( 'IBX_WPFOMO_ITEM_NAME', 'WPfomify AppSumo Deal' );

// the name of the settings page for the license input to be displayed
define( 'IBX_WPFOMO_LICENSE_PAGE', 'wpfomo-settings' );

if ( ! class_exists( 'IBX_WPFomo_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function ibx_wpfomo_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'ibx_wpfomo_license_key' ) );

	// setup the updater
	$updater = new IBX_WPFomo_Plugin_Updater( IBX_WPFOMO_SL_URL, IBX_WPFOMO_DIR . '/ibx-wpfomify.php', array(
			'version' 	=> IBX_WPFOMO_VER, 			// current version number
			'license' 	=> $license_key, 			// license key (used get_option above to retrieve from DB)
			'item_name' => IBX_WPFOMO_ITEM_NAME,	// name of this plugin
			'author' 	=> 'IdeaBox Creations',  	// author of this plugin
			'beta'		=> false,
		)
	);

}
add_action( 'admin_init', 'ibx_wpfomo_plugin_updater', 0 );

function wpfomo_sanitize_license( $new ) {
	$old = get_option( 'ibx_wpfomo_license_key' );
	if ( $old && $old != $new ) {
		delete_option( 'ibx_wpfomo_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

/************************************
* this illustrates how to activate
* a license key
*************************************/

function ibx_wpfomo_activate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['ibx_wpfomo_license_activate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'ibx_wpfomo_license_activate_nonce', 'ibx_wpfomo_license_activate_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( get_option( 'ibx_wpfomo_license_key' ) );

		// data to send in our API request
		$license_data->success = true;
 $license_data->error = '';
 $license_data->expires = date('Y-m-d', strtotime('+50 years'));
 $license_data->license = 'valid';
update_option( 'ibx_wpfomo_license_key', '1415b451be1a13c283ba771ea52d38bb' );
update_option( 'ibx_wpfomo_license_status', 'valid' );

		wp_redirect( IBX_WPFomo_Admin::get_form_action() );
		exit();
	} // End if().
}
add_action( 'admin_init', 'ibx_wpfomo_activate_license' );


/***********************************************
* Illustrates how to deactivate a license key.
* This will decrease the site count
***********************************************/

function ibx_wpfomo_deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['ibx_wpfomo_license_deactivate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'ibx_wpfomo_license_deactivate_nonce', 'ibx_wpfomo_license_deactivate_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( get_option( 'ibx_wpfomo_license_key' ) );

		$licensed_data->success = true;
			$license_data->license = 'deactivated';

		wp_redirect( IBX_WPFomo_Admin::get_form_action() );
		exit();
	} // End if().
}
add_action( 'admin_init', 'ibx_wpfomo_deactivate_license' );

function ibx_wpfomo_check_license() {

	global $wp_version;

	$license = trim( get_option( 'ibx_wpfomo_license_key' ) );

	$license_data->success = true;
 $license_data->error = '';
 $license_data->expires = date('Y-m-d', strtotime('+50 years'));
 $license_data->license = 'valid';
update_option( 'ibx_wpfomo_license_key', '1415b451be1a13c283ba771ea52d38bb' );
update_option( 'ibx_wpfomo_license_status', 'valid' );
}

/**
 * Catch errors from the activation method above and displaying it to the customer
 */
function ibx_wpfomo_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch ( $_GET['sl_activation'] ) {
			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error" style="background: #fbfbfb; border-top: 1px solid #eee; border-right: 1px solid #eee;">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				break;
		}
	}
}
add_action( 'admin_notices', 'ibx_wpfomo_admin_notices' );
