<?php
/**
 * Plugin data structure.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Installer' ) ) {

	/**
	 * Simple variation swatch loader class
	 */
	class MPC_Installer {

        /**
         * Plugin core data
         * @var array
         */
        private static $plugin_data;

		/**
		 * Plugin installation handler
		 */
		public static function install() {
            self::setup();

			if ( ! self::has_wc() ) {
				add_action( 'admin_notices', array( __CLASS__, 'missing_wc' ) );
				return false;
			}

			// register plugin activation hooks.
			register_activation_hook( MPC, array( __CLASS__, 'activate' ) );
			register_deactivation_hook( MPC, array( __CLASS__, 'deactivate' ) );

			// add extra links right under plug.
			add_filter( 'plugin_action_links_' . plugin_basename( MPC ), array( __CLASS__, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'desc_meta' ), 10, 2 );

			return true;
		}

        /**
         * Setup plugin core data
         */
        private static function setup(){
            self::$plugin_data = MPC_Core_Data::get_plugin();
        }

		/**
		 * Checks if base plugin is active or not
		 *
		 * @return bool
		 */
		public static function has_wc() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

            $base   = 'woocommerce';
            $plugin = 'multiple-products-to-cart-for-woocommerce';

			// if base plugin is active but woocommer is not, skip.
			if ( ! is_plugin_active( "{$base}/{$base}.php" ) && is_plugin_active( "{$plugin}/{$plugin}.php" ) ) {
				deactivate_plugins( $plugin );
				return false;
			}

			return true;
		}

		/**
		 * Notice for base plugin missing
		 */
		public static function missing_wc() {
			?>
			<div class="error">
				<p>
					<?php self::display_missing_wc_notice(); ?>
				</p>
			</div>
			<?php
		}

        private static function display_missing_wc_notice(){
            $plugin = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( self::$plugin_data[ 'plugin_url' ] ),
				__( 'Multiple Products to Cart – WooCommerce Product Table', 'multiple-products-to-cart-for-woocommerce' )
			);
			$base   = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( self::$plugin_data[ 'wc_plugin_url' ] ),
				esc_html__( 'WooCommerce', 'multiple-products-to-cart-for-woocommerce' )
			);
            printf(
                // translators: %1$s: plugin name with url, %2$s: base plugin with url.
                esc_html__( 'Plugin deactivated! Please activate %1$s to activate plugin %2$s', 'multiple-products-to-cart-for-woocommerce' ),
                wp_kses_post( $base ),
                wp_kses_post( $plugin )
            );
        }

		/**
		 * Activate plugin functionality
		 */
		public static function activate() {
			flush_rewrite_rules();
		}

		/**
		 * Deactivate plugin functionlity
		 */
		public static function deactivate() {
			flush_rewrite_rules();
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

            if ( ! in_array( 'activated', explode( ' ', self::$plugin_data[ 'pro_state' ] ), true ) ) {
				$action_links['premium'] = sprintf(
					'<a href="%s" style="font-weight: bold;background: linear-gradient(94deg, #0090F7, #BA62FC, #F2416B, #F55600);background-clip: text;color: transparent;">%s</a>',
					esc_url( self::$plugin_data[ 'pro_plugin_url' ] ),
					__( 'Get PRO Plugin', 'multiple-products-to-cart-for-woocommerce' )
				);
			}

			return array_merge( $action_links, $links );
		}

		/**
		 * Add plugin description meta data on all plugins page
		 *
		 * @param array  $links all meta data.
		 * @param string $file  plugin base file name.
		 */
		public static function desc_meta( $links, $file ) {
			global $svsw__;

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
