<?php
/**
 * Plugin loading class.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
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
			self::include_loader();

			if ( ! MPC_Installer::install() ) {
				return;
			}

			MPC_Admin_Ajax::init();
			
			self::includes();
			
			MPC_Add_To_Cart::init();
			MPC_Ajax_Table_Loader::init();

			add_action( 'init', array( __CLASS__, 'init' ) );
			add_action( 'before_woocommerce_init', array( __CLASS__, 'wc_init' ) );
		}

		/**
		 * Include plugin loader files
		 */
		private static function include_loader(){
			include MPC_PATH . 'includes__/loader/class-mpc-core-data.php';
			include MPC_PATH . 'includes__/loader/class-mpc-installer.php';

			include MPC_PATH . 'includes__/loader/class-mpc-admin-ajax.php';
			include MPC_PATH . 'includes__/loader/class-mpc-asset-loader.php';
			include MPC_PATH . 'includes__/loader/class-mpc-admin-loader.php';
		}

		/**
		 * Include necessary plugin files.
		 */
		public static function includes() {
			// admin files.
			include MPC_PATH . 'includes__/admin/class-mpc-admin-save-settings.php';
			include MPC_PATH . 'includes__/admin/class-mpc-admin-field.php';

			include MPC_PATH . 'includes__/admin/class-mpc-admin-template.php';
			include MPC_PATH . 'includes__/admin/class-mpc-admin-migration-template.php';
			include MPC_PATH . 'includes__/admin/class-mpc-admin-new-shortcode.php';

			include MPC_PATH . 'includes__/admin/class-mpc-admin-page.php';

			// frontend files.
			include MPC_PATH . 'includes__/class-mpc-add-to-cart.php';

			include MPC_PATH . 'includes__/class-mpc-product-data.php';
			include MPC_PATH . 'includes__/class-mpc-table-template.php';
			include MPC_PATH . 'includes__/class-mpc-shortcode.php';
		}

		/**
		 * Plugin activation process
		 */
		public static function init() {
			load_plugin_textdomain( 'multiple-products-to-cart-for-woocommerce', false, plugin_basename( dirname( MPC ) ) . '/languages' );

			// check if pro plugin exists.
			$pro_state = apply_filters( 'mpca_change_pro_state', '' );

			// load admin navigations and pages.
			MPC_Admin_Loader::init( $pro_state );

			// load plugin assets.
			MPC_Asset_Loader::init( $pro_state );

			MPC_Shortcode::init();
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
