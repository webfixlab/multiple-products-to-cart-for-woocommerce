<?php
/*
Plugin Name: Multiple Products to Cart for WooCommerce
Plugin URI: https://webfixlab.com/woocommerce-multiple-products-to-cart
Description: A truly lightweight plugin to add multiple products to cart at once.
Author: WebFix Lab
Author URI: https://webfixlab.com/
Version: 2.2.3
Requires at least: 4.4
Tested up to: 5.4
WC requires at least: 3.0
WC tested up to: 4.0.1
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mpc
*/

defined( 'ABSPATH' ) || exit;
/*
Handles Plugin Dependencies
*/
function mpc_is_plugin_active( $plugin ){
  if( ! is_multisite() )
    return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
  // if( is_multisite() )
  //   return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || is_plugin_active_for_network( $plugin );
}
function mpc_deactivate_plugin( $plugin ){
  $excluded = array();
  $all = (array) get_option( 'active_plugins', array() );
  foreach( $all as $plug ){
    if( $plug != $plugin ) array_push( $excluded, $plug );
  }
  update_option( 'active_plugins', $excluded );
}
function mpc_activation_process_handler(){
    $plugin = 'multiple-products-to-cart-for-woocommerce/multiple-products-to-cart-for-woocommerce.php';
    $mpc = array( 'woo' => '', 'mpc' => '' );
    $mpc['woo'] = get_option( "mpc_woo_status", "" );
    $mpc['mpc'] = get_option( "mpc_mpc_status", "" );
    if( $mpc['woo'] == '' && $mpc['mpc'] == '' ){
        if( mpc_is_plugin_active( 'woocommerce/woocommerce.php' ) ){
            add_option( "mpc_mpc_status", "active" );
            add_option( "mpc_woo_status", "active" );
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wmc_settings_link' );
        }else{
            mpc_deactivate_plugin( $plugin );
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
            add_action( 'admin_notices', 'wmc_woo_dependency_error' );
        }
    }else{
        if( $mpc['woo'] == 'active' && ! mpc_is_plugin_active( 'woocommerce/woocommerce.php' ) ){
            deactivate_plugins( $plugin );
            add_action( 'admin_notices', 'wmc_woo_auto_deactivate_error' );
            delete_option( "mpc_mpc_status" );
            delete_option( "mpc_woo_status" );
        }else{
            if ( ! defined( 'WMC_DIR' ) ) {
                define( 'WMC_DIR', dirname( __FILE__ ) );
            }
            include( plugin_dir_path( __FILE__ ) . 'includes/functions.php');
        }
    }
    $mpc['mpc'] = get_option( "mpc_mpc_status", "" );
    if( $mpc['woo'] == 'active' && mpc_is_plugin_active( 'woocommerce/woocommerce.php' ) ){
        include( plugin_dir_path( __FILE__ ) . 'includes/admin-notice.php');
        mpc_client_feedback();
    }
}
add_action( 'init', 'mpc_activation_process_handler' );
function mpc_activation() {
    mpc_activation_process_handler();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mpc_activation' );
function mpc_deactivation() {
    delete_option( "mpc_mpc_status" );
    delete_option( "mpc_woo_status" );
    delete_option( 'mpc_client_feedback' );
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'mpc_deactivation' );
/*
Add Settings to WooCommerce > Settings > Products > WC Multiple Cart
*/
function wmc_settings_link( $links ) {
	if( is_plugin_active( plugin_basename(__FILE__) ) ){
		$links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=wc-settings&tab=products&section=wmc') ) .'">Settings</a>';
		return $links;
	}
}

function wmc_woo_dependency_error() { ?>
  <div class="error"><p><?php echo __( "Please install and activate", "mpc" ); ?> <a href="https://wordpress.org/plugins/woocommerce/" target="_blank"><?php echo __( "WooCommerce", "mpc" ); ?></a> <?php echo __( "plugin first.", "mpc" ); ?></p></div><?php
}
function wmc_woo_auto_deactivate_error() {
    ?><div class="error"><p><a href="https://wordpress.org/plugins/multiple-products-to-cart-for-woocommerce/" target="_blank"><?php echo __( "Multiple Products to Cart – WooCommerce Product Table", "mpc" ); ?></a> <?php echo __( "plugin has been deactivated due to deactivation of", "mpc" ); ?> <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> <?php echo __( "plugin.", "mpc" ); ?></p></div><?php
}
