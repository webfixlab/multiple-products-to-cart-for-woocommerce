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
            self::$plugin_data = MPC_Core_Data::get_plugin();

			if ( ! self::has_wc() ) {
				add_action( 'admin_notices', array( __CLASS__, 'missing_wc' ) );
				return false;
			}

			// register plugin activation hooks.
			register_activation_hook( MPC, array( __CLASS__, 'activate' ) );
			register_deactivation_hook( MPC, array( __CLASS__, 'deactivate' ) );

			return true;
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
	}
}
