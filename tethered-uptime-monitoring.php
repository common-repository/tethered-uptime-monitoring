<?php
/*
  Plugin Name: Tethered Uptime Monitoring
  Plugin URI: https://tethered.app
  Description: Get unlimited uptime monitoring for free! Monitor just about anything and get notified within minutes of any issues.
  Version: 1.0.3
  Author: CodeCabin
  Author URI: https://www.codecabin.io
  Text Domain: tethered-uptime-monitoring
  Domain Path: /languages
*/


/*
 * 1.0.3 - 2024-07-01
 * Updated Onboarding
 * 
 * 1.0.2 - 2024-06-18
 * Fixed issue where a warning would be thrown on the login/register page
 * Added site removal on account logout 
 * 
 * 1.0.1 - 2024-06-06
 * Improved Onboarding
 * 
 * 1.0.0 - 2024-06-05
 * Launch 
 * 
*/

if(!defined( 'ABSPATH' )){
	exit;
}

define( 'TETHERED_VERSION', '1.0.3' );
define( 'TETHERED_FILE', __FILE__ );
define( 'TETHERED_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TETHERED_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );



/**
 * Activation function
 * 
 * @return void
 */
function tethered_activated(){

}

register_activation_hook( __FILE__, 'tethered_activated' );

/**
 * Alternative to the tethered_activated function
 * 
 * @return void
 */
function tethered_activation_redirect( $plugin ) {
  if( $plugin == plugin_basename( __FILE__ ) ) {
    wp_redirect( admin_url( 'admin.php?page=tethered_monitor' ) );
    exit();
  }
}

add_action( 'activated_plugin', 'tethered_activation_redirect' );

/**
 * Deactivation function
 * 
 * @return void
 */
function tethered_deactivated(){
  
}

register_deactivation_hook( __FILE__, 'tethered_deactivated' );

/**
 * Uninstallation function
 * 
 * @return void
 */
function tethered_uninstalled(){
  delete_option( 'tethered_connected_site_details' );
  delete_option( 'tethered_account_details' );
  delete_option( 'tethered_onboarded' );
}

register_uninstall_hook( __FILE__, 'tethered_uninstalled');

/**
 * Begins execution of the plugin.
 * 
 * @return void
 */
function start_tethered() {
  $tethered = new Tethered();

  add_action( 'admin_menu', array($tethered, 'load_admin_menu' ) );

  add_action( 'rest_api_init', array($tethered, 'register_rest_routes') );
  
  add_action( 'admin_enqueue_scripts', array($tethered, 'load_admin_styles') );
  add_action( 'admin_enqueue_scripts', array($tethered, 'load_admin_scripts'));

}

require TETHERED_PLUGIN_DIR . 'includes/class.tethered.php';
start_tethered();