<?php
/**
 * Table add to cart functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Shortcode' ) ) {

	/**
	 * Table add to cart class
	 */
	class MPC_Shortcode {

		/**
		 * Class initialization function
		 */
		public static function init() {
			add_shortcode( 'woo-multi-cart', array( __CLASS__, 'product_table' ) );
			add_filter( 'mpc_template_loader', array( __CLASS__, 'template_loader' ), 10, 1 );
		}

		/**
		 * Product table shortcode loader
		 *
		 * @param array $atts product table shortcode attributes.
		 */
		public function product_table( array $atts ) {
			global $mpc_table__;
			
			$atts = self::extract_shortcode_atts( $atts );
			if( empty( $atts ) ){
				return;
			}

			$data = MPC_Product_Data::get_products( $atts, 1 );
			if( empty( $data ) || empty( $data['products'] ) ){
				return;
			}

			MPC_Front_Data::setup_frontend_data( $atts, $data );

			ob_start();

			// Load main table template file.
			include apply_filters( 'mpc_template_loader', MPC_PATH . 'templates/listing-list.php' );

			$content = ob_get_contents();
			ob_get_clean();

			return do_shortcode( $content );
		}

		/**
		 * Process shortcode to array
		 *
		 * @param array $atts table shortcode.
		 */
		private function extract_shortcode_atts( array $atts ) {
			if ( ! isset( $atts['table'] ) || empty( $atts['table'] ) ) {
				return $atts;
			}

			$table_id = (int) $atts['table'];
			$shortcode = get_post_meta( $table_id, 'shortcode', true );
			$shortcode = empty( $shortcode ) ? get_option( "mpcasc_code{$table_id}" ) : $shortcode; // legacy option.
			if ( empty( $shortcode ) ) {
				return '';
			}

			$shortcode = str_replace( '[', '', $shortcode );
			$shortcode = str_replace( ']', '', $shortcode );
			$shortcode = str_replace( 'woo-multi-cart', '', $shortcode );

			return empty( $shortcode ) ? '' : shortcode_parse_atts( $shortcode );
		}

		/**
		 * Override default product table template
		 *
		 * @param string $path override if given, else use default template file.
		 */
		public static function template_loader( $path = '' ) {
			// Extract the filename from the path.
			$filename = basename( $path );

			// Construct the path to the file in the theme directory.
			$path_override = get_stylesheet_directory() . '/templates/' . $filename;

			// Check if the file exists in the theme directory.
			if ( file_exists( $path_override ) ) {
				// Return the path to the file in the theme directory.
				return $path_override;
			}

			// If the file doesn't exist in the theme directory, return the original path.
			return $path;
		}
	}
}
