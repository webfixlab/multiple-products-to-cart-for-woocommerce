<?php
/**
 * Frontend loader functions.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

// Global frontend data.
global $mpc_frontend__;

/**
 * Frontend view loader class
 */
class MPC_Frontend_Loader {
    private static $products;

    /**
     * Initialize hooks
     */
    public static function init() {
        add_shortcode( 'woo-multi-cart', array( __CLASS__, 'mpc_frontend_loader' ) );

        add_action( 'wp_ajax_mpc_ajax_table_loader', array( __CLASS__, 'mpc_frontend_ajax_loader' ) );
        add_action( 'wp_ajax_nopriv_mpc_ajax_table_loader', array( __CLASS__, 'mpc_frontend_ajax_loader' ) );
    }



    /**
     * Product table shortcode loader
     *
     * @param array $atts Shortcode attributes.
     */
    public static function mpc_frontend_loader( $atts ) {
        MPC_Frontend_Helper::check_pro();
        
        $products = self::get_products( $atts );
        if( empty( $products ) ) return;

        ob_start();
        self::template_loader( 'table' );
        $content = ob_get_contents();
        ob_get_clean();

        return do_shortcode( $content );
    }

    /**
     * Ajax product table loader
     */
    public static function mpc_frontend_ajax_loader() {
        MPC_Frontend_Helper::check_pro();

        $message = '';
        if ( ! isset( $_POST['table_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['table_nonce'] ) ), 'table_nonce_ref' ) ) {
            $message = __( 'Nonce verification failed.', 'multiple-products-to-cart-for-woocommerce' );
        }

        if ( ! isset( $_POST ) || ( ! isset( $_POST['page'] ) ) ) {
            $message = __( 'POST - page or atts variable not found.', 'multiple-products-to-cart-for-woocommerce' );
        }

        $atts = array(); // shortcode attribute data.
        foreach(wp_unslash($_POST['atts']) as $key => $val){
            $atts[$key] = !is_array($val) ? sanitize_text_field($val) : array_map('sanitize_text_field', $val);
        }

        $products = self::get_products( $atts );
        if( empty( $products ) ){
            wp_send_json(
                array(
                    'status'        => 'error',
                    'msg'           => __( 'No posts found!', 'multiple-products-to-cart-for-woocommerce' ),
                    'mpc_fragments' => array(
                        array(
                            'key' => 'table.mpc-wrap',
                            'val' => sprintf(
                                '<table class="mpc-wrap"><tr class="mpc-search-empty"><td><span class="woocommerce-error">%s</span></td></tr></table>',
                                esc_html__( 'Sorry! No products found!', 'multiple-products-to-cart-for-woocommerce' )
                            ),
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

        if ( ! empty( $message ) ) return wp_send_json( array(
            'status' => 'error', 'message' => $message
        ) );

        ob_start();

        MPC_Table_Template::display_table();

        $response[] = array(
            'key' => 'table.mpc-wrap',
            'val' => ob_get_clean(),
        );

        ob_start();
        MPC_Table_Template::pagination();
        $response[] = array(
            'key'         => '.mpc-pagenumbers',
            'parent'      => '.mpc-inner-pagination',
            'adding_type' => 'prepend',
            'val'         => ob_get_clean(),
        );

        ob_start();
        MPC_Table_Template::pagination_info();
        $response[] = array(
            'key'         => '.mpc-product-range',
            'val'         => ob_get_clean(),
        );

        wp_send_json(
            array(
                'mpc_fragments' => $response,
            )
        );
    }



    public static function template_loader( $template ){
        switch ( $template ) {
            case 'table':
                include apply_filters( 'mpc_template_loader', MPC_PATH . 'templates/listing-list.php' );
                break;
            
            default:
                break;
        }
    }

    public static function get_products( $atts ) {
        global $mpc_frontend__;

        $atts = MPC_Frontend_Helper::process_atts( $atts );
        $args = MPC_Frontend_Helper::get_query_args( $atts );

        // Remove hooks for nuiscense.
        remove_all_filters( 'pre_get_posts' );
        remove_all_filters( 'posts_orderby' );

        $query = new WP_Query( $args );
        wp_reset_postdata();

        $mpc_frontend__['products']      = $query->posts;
        $mpc_frontend__['found_posts']   = $query->found_posts;
        $mpc_frontend__['max_num_pages'] = $query->max_num_pages;
        $mpc_frontend__['query_args']    = $args;

        if( !$query->have_posts() ) return array();
        
        return $query->posts;
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

MPC_Frontend_Loader::init();
