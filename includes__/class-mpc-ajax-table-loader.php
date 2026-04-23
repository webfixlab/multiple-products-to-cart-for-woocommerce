<?php
/**
 * Table ajax loader functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Ajax_Table_Loader' ) ) {

	/**
	 * Table ajax loader class
	 */
	class MPC_Ajax_Table_Loader {

		/**
		 * Class initialization function
		 */
		public static function init() {
            add_action( 'wp_ajax_mpc_ajax_table_loader', array( __CLASS__, 'product_table_ajax' ) );
			add_action( 'wp_ajax_nopriv_mpc_ajax_table_loader', array( __CLASS__, 'product_table_ajax' ) );
		}

		/**
		 * Ajax product table loader
		 */
		public static function product_table_ajax() {
			check_ajax_referer( 'table_nonce_ref', 'table_nonce' );

			$locale = isset( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : '';
			$paged  = isset( $_POST['page'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1;

			$atts   = sanitize_text_field( wp_unslash( $_POST['atts'] ) );
			$atts   = json_decode( $atts, true );
			if( empty( $atts ) ){
				wp_send_json_error( array( 'msg' => __( 'Sorry! There was an error.', 'multiple-products-to-cart-for-woocommerce' ) ) );
			}

			$data = MPC_Product_Data::get_products( $atts, $paged );
			if( empty( $data ) || empty( $data['products'] ) ){
				wp_send_json( array(
					'status'        => 'error',
					'msg'           => __( 'No posts found!', 'multiple-products-to-cart-for-woocommerce' ),
					'mpc_fragments' => self::empty_products_error_response()
				) );
			}

			MPC_Front_Data::setup_frontend_data( $atts, $data );
			
			wp_send_json( array( 'mpc_fragments' => self::get_table_fragments( $atts, $locale ) ) );
		}

		/**
		 * No products found - ajax response.
		 * @return array<array{adding_type: string, key: string, parent: string, val: string|array{key: string, val: string}>}
		 */
		private static function empty_products_error_response(){
			return array(
				array(
					'key' => 'table.mpc-wrap',
					'val' => sprintf(
						'<table class="mpc-wrap"><tr class="mpc-search-empty"><td><span>%s</span></td></tr></table>',
						esc_html__( 'Sorry! No products found!', 'multiple-products-to-cart-for-woocommerce' )
					),
				),
				array(
					'key' => '.mpc-product-range',
					'val' => '',
				),
				array(
					'key' => '.mpc-pagenumbers',
					'val' => '',
				),
			);
		}

		/**
		 * Get table html fragments
		 *
		 * @param array  $atts   Shortcode attributes.
		 * @param string $locale Translation locale.
		 * @return array
		 */
		private static function get_table_fragments( array $atts, string $locale ){
			$response = array();

			// Switch to the new locale | Multilingual | PolyLang plugin support.
			switch_to_locale( $locale );

			ob_start();
			mpc_display_table(); // display table body content.
			$response[] = array(
				'key' => 'table.mpc-wrap',
				'val' => ob_get_clean(),
			);

			ob_start();
			MPC_Table_Template::display_pagination_numbers();
			$response[] = array(
				'key' => '.mpc-pagenumbers',
				'val' => '<div class="mpc-pagenumbers">' . ob_get_clean() . '</div>',
			);

			ob_start();
			MPC_Table_Template::display_pagination_range();
			$response[] = array(
				'key' => '.mpc-product-range',
				'val' => '<div class="mpc-product-range">' . ob_get_clean() . '</div>',
			);

			restore_previous_locale(); // switch back to default admin locale.

			return $response;
		}
	}
}
