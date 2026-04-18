<?php
/**
 * Table add to cart functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Add_To_Cart' ) ) {

	/**
	 * Table add to cart class
	 */
	class MPC_Add_To_Cart {

        /**
         * Current admin state notice
         * @var array
         */
        // private static $notice;

		/**
		 * Class initialization function
		 */
		public static function init() {
            add_action( 'wp_loaded', array( __CLASS__, 'table_form_submit' ), 15 );

			add_action( 'wp_ajax_mpc_ajax_add_to_cart', array( __CLASS__, 'add_to_cart_ajax' ) );
			add_action( 'wp_ajax_nopriv_mpc_ajax_add_to_cart', array( __CLASS__, 'add_to_cart_ajax' ) );
		}

        /**
		 * Add to cart handler
		 */
		public static function table_form_submit() {
            if ( ! isset( $_REQUEST['mpc_cart_data'] ) || ! class_exists( 'WC_Form_Handler' ) || ! isset( $_POST['cart_nonce'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cart_nonce'] ) ), 'cart_nonce_ref' ) ) {
				return;
			}

			remove_action( 'wp_loaded', array( WC_Form_Handler::class, 'add_to_cart_action' ), 20 );

			$cart_data = sanitize_text_field( wp_unslash( $_REQUEST['mpc_cart_data'] ) );
			$cart_data = json_decode( $cart_data, true );

			$added = self::add_products_to_cart( $cart_data );

            wc_add_to_cart_message( $added, true, false );

            $url = 'cart' === get_option( 'wmc_redirect' ) ? wc_get_cart_url() : '';
            $url = apply_filters( 'mpc_add_to_cart_redirect_url', $url );
            if( ! empty( $url ) ){
                wp_safe_redirect( $url );
                exit;
            }
		}
 
        /**
		 * Add to cart process
		 *
		 * @param array  $data product data for adding then to cart.
		 */
		private static function add_products_to_cart( $data ) {
			if ( empty( $data ) ) {
				return;
			}

			$added = array(); // array of product id => quantity.
			foreach ( $data as $product_id => $product ) {
				$key = '';

				if ( 'grouped' === $product['type'] ) {
					continue;
				}

				$quantity     = isset( $product['quantity'] ) && ! empty( $product['quantity'] ) ? (int) $product['quantity'] : 1;
				$variation_id = isset( $product['variation_id'] ) && ! empty( $product['variation_id'] ) ? (int) $product['variation_id'] : 0;
				$variation    = $product['attributes'] ?? array();

				$cart_data = array( 'mpc_data' => $product );

				$key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_data );
				if ( false !== $key ) {
					do_action( 'woocommerce_ajax_added_to_cart', $product_id );
					$added[ $product_id ] = $quantity;
				}

				do_action( 'mpc_after_add_to_cart', $product_id, $key );
			}

            return $added;
		}

		/**
		 * Ajax add to cart handler
		 */
		public static function add_to_cart_ajax() {
			if ( ! isset( $_POST['cart_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cart_nonce'] ) ), 'cart_nonce_ref' ) ) {
				return;
			}

			// check ajax add to cart data.
			if ( ! isset( $_POST['mpca_cart_data'] ) ) {
				return;
			}

            $cart_data = wp_unslash( $_POST['mpca_cart_data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			// unslash and sanitize array data.
			$added = self::add_products_to_cart( $cart_data );
            
            $resonse = self::cart_refreshed_fragments();
            $resonse['req']          = $cart_data;
            $resonse['cart_message'] = wc_add_to_cart_message( $added, true, true );
            if ( count( $added ) !== count( array_keys( $cart_data ) ) ) { // check for any errors.
                $resonse['error_message'] = self::cart_format_error();
            }

            wp_send_json( $resonse );
		}

		/**
		 * WooCommerce frontend mini cart html data
		 */
		private static function cart_refreshed_fragments() {
			ob_start();
			woocommerce_mini_cart();
			$mini_cart = ob_get_clean();

			return array(
				'fragments' => apply_filters(
					'woocommerce_add_to_cart_fragments',
					array(
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
					)
				),
				'cart_hash' => WC()->cart->get_cart_hash(),
			);
		}

		/**
		 * Process add to cart errors
		 */
		private static function cart_format_error() {
			$notices = wc_get_notices( 'error' );
			if ( empty( $notices ) || ! is_array( $notices ) ) {
				$notices = array( __( 'There was an error adding to the cart. Please try again.', 'multiple-products-to-cart-for-woocommerce' ) );
			}

            wc_clear_notices();
			$error_fmt = apply_filters( 'wc_product_table_cart_error_format', '<span class="cart-error">%s</span>' );
            
			$result = '';
			foreach ( $notices as $notice ) {
				$result .= isset( $notice['notice'] ) ? sprintf( $error_fmt, $notice['notice'] ) : $notice; 
			}
            
			return $result;
		}
	}
}
