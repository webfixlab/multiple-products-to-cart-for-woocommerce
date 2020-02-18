<?php
/*
Plugin Name: Multiple Products to Cart - WooCommerce Product Table
Plugin URI: https://webfixlab.com/woocommerce-multiple-products-to-cart
Description: A truly lightweight and super FAST WooCommerce product table solution to add multiple products to cart at once.
Author: WebFix Lab
Author URI: https://webfixlab.com/
Version: 2.1
Requires at least: 4.4
Tested up to: 5.3.2
WC requires at least: 3.0
WC tested up to: 3.9.1
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mpc
*/

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WMC_DIR' ) ) {
	define( 'WMC_DIR', dirname( __FILE__ ) );

}
include( plugin_dir_path( __FILE__ ) . 'includes/functions.php');

include( plugin_dir_path( __FILE__ ) . 'includes/admin-notice.php');

/*
Handles Plugin Dependencies
*/

add_action( 'admin_init', 'wmc_plugin_activation_init' );

function wmc_plugin_activation_init() {

    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

        add_action( 'admin_notices', 'wmc_woo_dependency_error' );

        deactivate_plugins( 'woocommerce-multicart/woocommerce-multicart.php' );

        if ( isset( $_GET['activate'] ) ) {

            unset( $_GET['activate'] );

        }

    }
	mpc_client_feedback();

}

function wmc_woo_dependency_error(){

    ?><div class="error"><p>Please install and activate <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> plugin first.</p></div><?php

}


/*

Add Settings to WooCommerce > Settings > Products > WC Multiple Cart

*/

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wmc_settings_link' );

function wmc_settings_link( $links ) {

	if( is_plugin_active( plugin_basename(__FILE__) ) ){

		$links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=wc-settings&tab=products&section=wmc') ) .'">Settings</a>';

		return $links;

	}

}
?>