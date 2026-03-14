<?php
/**
 * Plugin admin loader
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Loader' ) ) {

	/**
	 * Plugin admin loader class
	 */
	class MPC_Admin_Loader {

        /**
         * Plugin core data
         * @var array
         */
        private static $plugin_data;

		/**
		 * Plugin installation handler
         *
         * @param string $pro_state Pro plugin status.
		 */
		public static function init( $pro_state ) {
            self::$pro_state = $pro_state;

			// add extra links right under plug.
			add_filter( 'plugin_action_links_' . plugin_basename( MPC ), array( __CLASS__, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'desc_meta' ), 10, 2 );
		}

		/**
		 * Add plugin action links on all plugins page
		 *
		 * @param array $links current plugin action links.
		 */
		public static function action_links( $links ) {
			$action_links = array();

			$action_links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=mpc-settings' ),
				esc_html__( 'Settings', 'multiple-products-to-cart-for-woocommerce' )
			);

            if( ! empty( self::$pro_state ) ){
                return array_merge( $action_links, $links );
            }

			$action_links['premium'] = sprintf(
				'<a href="%s" style="font-weight: bold;background: linear-gradient(94deg, #0090F7, #BA62FC, #F2416B, #F55600);background-clip: text;color: transparent;">%s</a>',
				esc_url( self::$plugin_data[ 'pro_plugin_url' ] ),
				__( 'Get PRO Plugin', 'multiple-products-to-cart-for-woocommerce' )
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Add plugin description meta data on all plugins page
		 *
		 * @param array  $links all meta data.
		 * @param string $file  plugin base file name.
		 */
		public static function desc_meta( $links, $file ) {
			// if it's not Role Based Product plugin, return.
			if ( plugin_basename( MPC ) !== $file ) {
				return $links;
			}

			$row_meta = array();

			$row_meta['apidocs'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( self::$plugin_data[ 'contact_us_url' ] ),
				esc_html__( 'Support', 'multiple-products-to-cart-for-woocommerce' )
			);

			return array_merge( $links, $row_meta );
		}
	}
}
