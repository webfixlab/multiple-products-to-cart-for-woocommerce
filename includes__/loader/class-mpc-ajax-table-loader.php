<?php
/**
 * Table add to cart functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Ajax_Table_Loader' ) ) {

	/**
	 * Table add to cart class
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
		public function product_table_ajax() {
			check_ajax_referer( 'table_nonce_ref', 'table_nonce' );

			// Switch to the new locale | Multilingual | PolyLang plugin support.
			$locale = isset( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : '';
			if ( ! empty( $locale ) ) {
				switch_to_locale( $locale );
			}

			$atts = array(); // shortcode attribute data.
			if ( isset( $_POST['atts'] ) ) {
				$atts = array_map( 'sanitize_text_field', wp_unslash( $_POST['atts'] ) );
			}

			MPC_Product_Data::get_table( $atts );

			if ( ! isset( $mpctable__['products'] ) || empty( $mpctable__['products'] ) ) {
				wp_send_json(
					array(
						'status'        => 'error',
						'msg'           => __( 'No posts found!', 'multiple-products-to-cart-for-woocommerce' ),
						'mpc_fragments' => array(
							array(
								'key' => 'table.mpc-wrap',
								'val' => sprintf(
									'<table class="mpc-wrap"><tr class="mpc-search-empty"><td><span>%s</span></td></tr></table>',
									esc_html__( 'Sorry! No products found!', 'multiple-products-to-cart-for-woocommerce' )
								),
							),
							array(
								'key'         => '.mpc-product-range',
								'parent'      => '.mpc-button', // if key element not found add to parent.
								'adding_type' => 'prepend',
								'val'         => '',
							),
							array(
								'key'         => '.mpc-pagenumbers',
								'parent'      => '.mpc-inner-pagination',
								'adding_type' => 'prepend',
								'val'         => '',
							),
						),
					)
				);
			}

			if ( 'error' === $response['status'] ) {
				wp_send_json( $response );
			}

			ob_start();

			// display table body content.
			mpc_display_table();

			$response[] = array(
				'key' => 'table.mpc-wrap',
				'val' => ob_get_clean(),
			);

			ob_start();
			$mpc_template__->display_table_pagination_range();
			$response[] = array(
				'key'         => '.mpc-product-range',
				'parent'      => '.mpc-button', // if key element not found add to parent.
				'adding_type' => 'prepend',
				'val'         => ob_get_clean(),
			);

			ob_start();
			$mpc_template__->numbered_pagination();
			$response[] = array(
				'key'         => '.mpc-pagenumbers',
				'parent'      => '.mpc-inner-pagination',
				'adding_type' => 'prepend',
				'val'         => ob_get_clean(),
			);

			restore_previous_locale();

			wp_send_json(
				array(
					'mpc_fragments' => $response,
				)
			);
		}
	}
}
