<?php
/**
 * Frontend add to cart related functions.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Frontend add to cart class
 */
class MPC_Add_To_Cart {

    /**
     * Initialize hooks
     */
    public static function init() {
        add_action( 'wp_loaded', array( __CLASS__, 'add_to_cart' ), 15 );

        add_action( 'wp_ajax_mpc_ajax_add_to_cart', array( __CLASS__, 'add_to_cart_ajax' ) );
        add_action( 'wp_ajax_nopriv_mpc_ajax_add_to_cart', array( __CLASS__, 'add_to_cart_ajax' ) );
    }



    /**
     * Add to cart handler
     */
    public static function add_to_cart() {
        if ( ! class_exists( 'WC_Form_Handler' ) ) {
            return;
        }

        if ( ! isset( $_POST['cart_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cart_nonce'] ) ), 'cart_nonce_ref' ) ) {
            return;
        }

        // only for mpc plugin add to cart event.
        if ( ! isset( $_REQUEST['mpc_cart_data'] ) ) {
            return;
        }

        remove_action( 'wp_loaded', array( WC_Form_Handler::class, 'add_to_cart_action' ), 20 );

        $d = sanitize_text_field( wp_unslash( $_REQUEST['mpc_cart_data'] ) );
        $d = json_decode( $d, true );

        self::do_add_to_cart( $d, 'submission' );
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

        // unslash and sanitize array data.
        self::do_add_to_cart( wp_unslash( $_POST['mpca_cart_data'] ), 'ajax' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    }

    /**
     * Add to cart process
     *
     * @param array  $data   product data for adding then to cart.
     * @param string $method add to cart method, ajax or not.
     */
    public static function do_add_to_cart( $data, $method ) {
        if( empty( $data ) ) return;

        $all_products = array(); // all successfully products added to cart.
        foreach( $data as $product_id => $product ) {
            if( 'grouped' === $product['type'] ) continue;

            $id           = (int) $product_id;
            $qty          = (int) $product['quantity'];
            $variation_id = isset( $product['variation_id'] ) && !empty( $product['variation_id'] ) ? (int) $product['variation_id'] : 0;
            $attributes   = isset( $product['attributes'] ) && !empty( $product['attributes'] ) ? $product['attributes'] : [];

            $flag = WC()->cart->add_to_cart( $id, $qty, $variation_id, $attributes );
            if( false !== $flag ) {
                do_action( 'woocommerce_ajax_added_to_cart', $id );
                $all_products[ $id ] = $product['quantity'];
            }

            do_action( 'mpc_after_add_to_cart', $id, $flag );
        }

        if( 'ajax' === $method ) {
            $resonse                 = self::cart_refreshed_fragments();
            $resonse['req']          = $data;
            $resonse['cart_message'] = !empty( $all_products ) ? wc_add_to_cart_message( $all_products, true, true ) : '';

            if( empty( $all_products ) || count( $data ) !== count( $all_products ) ) $resonse['error_message'] = self::cart_format_error();

            wp_send_json( $resonse );
        } else {
            wc_add_to_cart_message( $all_products, true, false );
            self::cart_redirect();
        }
    }



    /**
     * Redirect to URL after successful add to cart
     *
     * @param string $url URL to redirect after add to cart.
     */
    public static function cart_redirect( $url = '' ) {
        // if admin option set to cart.
        if( 'cart' === get_option( 'wmc_redirect', 'ajax' ) ) $url = wc_get_cart_url();

        // filter - modify given url.
        $url = apply_filters( 'mpc_add_to_cart_redirect_url', $url );
        if( empty( $url ) ) return;

        wp_safe_redirect( $url );
        exit;
    }

    /**
     * WooCommerce frontend mini cart html data
     */
    public static function cart_refreshed_fragments() {
        ob_start();

        woocommerce_mini_cart();

        $mini_cart = ob_get_clean();

        $data = array(
            'fragments' => apply_filters(
                'woocommerce_add_to_cart_fragments',
                array(
                    'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
                )
            ),
            'cart_hash' => WC()->cart->get_cart_hash(),
        );

        return $data;
    }

    /**
     * Process add to cart errors
     */
    public static function cart_format_error() {
        $notices = wc_get_notices( 'error' );

        if ( empty( $notices ) || ! is_array( $notices ) ) {
            $notices = array( __( 'There was an error adding to the cart. Please try again.', 'multiple-products-to-cart-for-woocommerce' ) );
        }

        $result    = '';
        $error_fmt = apply_filters( 'wc_product_table_cart_error_format', '<span class="cart-error">%s</span>' );

        foreach ( $notices as $notice ) {
            $notice_text = isset( $notice['notice'] ) ? $notice['notice'] : $notice;
            $result     .= sprintf( $error_fmt, $notice_text );
        }

        wc_clear_notices();
        return $result;
    }



    private static function log( $data ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $data ) || is_object( $data ) ) {
                error_log( print_r( $data, true ) );
            } else {
                error_log( $data );
            }
        }
    }
}

MPC_Add_To_Cart::init();
