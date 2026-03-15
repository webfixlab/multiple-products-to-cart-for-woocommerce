<?php
/**
 * Plugin loading class.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Loader' ) ) {

	/**
	 * Simple variation swatch loader class
	 */
	class MPC_Loader {

		/**
		 * Initialize plugin loader
		 */
		public static function init_plugin() {
			include MPC_PATH . 'includes__/class-mpc-core-data.php';
			include MPC_PATH . 'includes__/class-mpc-installer.php';

			if ( ! MPC_Installer::install() ) {
				return;
			}

			add_action( 'init', array( __CLASS__, 'init' ) );
			add_action( 'before_woocommerce_init', array( __CLASS__, 'wc_init' ) );
		}

		/**
		 * Plugin activation process
		 */
		public static function init() {
			load_plugin_textdomain( 'multiple-products-to-cart-for-woocommerce', false, plugin_basename( dirname( MPC ) ) . '/languages' );

			// include required files.
			self::includes();

			// check if pro plugin exists.
			$pro_state = apply_filters( 'mpca_change_pro_state', '' );

			// load admin navigations and pages.
			MPC_Admin_Loader::init( $pro_state );

			// load plugin assets.
			MPC_Asset_Loader::init( $pro_state );
		}

		/**
		 * Include necessary plugin files.
		 */
		public static function includes() {
			// admin functions.
			include MPC_PATH . 'includes__/admin/class-mpc-admin-field.php';
			include MPC_PATH . 'includes__/admin/class-mpc-admin-template.php';
			include MPC_PATH . 'includes__/admin/class-mpc-admin-page.php';
			include MPC_PATH . 'includes__/admin/class-mpc-admin-loader.php';

			// asset files handler.
			include MPC_PATH . 'includes__/class-mpc-asset-loader.php';
		}

		/**
		 * WooCommerce High-Performance Order Storage (HPOS) compatibility enable
		 */
		public static function wc_init() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MPC, true );
			}
		}
	}
}

MPC_Loader::init_plugin();
