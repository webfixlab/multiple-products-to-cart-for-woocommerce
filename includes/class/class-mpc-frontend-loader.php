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
        add_shortcode( 'woo-multi-cart', array( __CLASS__, 'lazy_loader' ) );

        add_action('wp_ajax_mpc_lazy_loader', array( __CLASS__, 'mpc_lazy_loader') );
        add_action('wp_ajax_nopriv_mpc_lazy_loader', array( __CLASS__, 'mpc_lazy_loader') );

        add_action( 'wp_footer', array( __CLASS__, 'image_popup' ) );
    }



    public static function lazy_loader( $atts ) {
        ob_start();
        ?>
        <div class="woocommerce-page woocommerce mpc-container-loading" data-atts="<?php echo wc_esc_json(wp_json_encode($atts)); ?>">
            <div class="mpc-skeleton">Loading...</div>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        return do_shortcode( $content );
    }
    public static function mpc_lazy_loader() {
        check_ajax_referer('table_nonce_ref', 'nonce');

        $atts = sanitize_text_field( wp_unslash( $_POST['atts'] ) );
        $atts = json_decode( stripslashes($atts), true );
        $atts = MPC_Frontend_Helper::process_atts( $atts );
        $args = MPC_Frontend_Helper::get_query_args( $atts );

        // Remove hooks for nuiscense.
        remove_all_filters( 'pre_get_posts' );
        remove_all_filters( 'posts_orderby' );

        $query = new WP_Query( $args );
        wp_reset_postdata();
        
        $product_data = [];
        foreach ( $query->posts as $id ) {
            $product_data[$id] = MPC_Frontend_Helper::get_product_data( $id );
        }

        wp_send_json(array(
            'atts'          => $atts,
            'query_args'    => $args,
            'found_posts'   => $query->found_posts,
            'max_num_pages' => $query->max_num_pages,
            'products'      => $product_data,
        ));
    }

    public static function image_popup(){
        ?>
        <div id="mpcpop" class="mpc-popup">
            <div class="image-wrap">
                <span class="dashicons dashicons-dismiss mpcpop-close"></span>
                <img src="">
            </div>
        </div>
        <?php
    }


    
    public static function init_fields() {
        global $mpctable__;

        $mpctable__ = array(
            'image_sizes'     => array(
                'thumb' => 'thumbnail',
                'full'  => 'large', // or we should use full?
            ),
            'quantity'        => array(
                'min' => 0,
                'max' => '', // leave it blank for undefined.
            ),
            'orderby_options' => array(
                'menu_order' => __( 'Default sorting', 'multiple-products-to-cart-for-woocommerce' ),
                'price-ASC'  => __( 'Price: Low to High', 'multiple-products-to-cart-for-woocommerce' ),
                'price-DESC' => __( 'Price: High to Low', 'multiple-products-to-cart-for-woocommerce' ),
            ),
            'labels'          => array(
                'wmc_ct_image'                  => __( 'Image', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_product'                => __( 'Product', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_price'                  => __( 'Price', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_variation'              => __( 'Variation', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_quantity'               => __( 'Quantity', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_buy'                    => __( 'Buy', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_category'               => __( 'Category', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_stock'                  => __( 'Stock', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_tag'                    => __( 'Tag', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_sku'                    => __( 'SKU', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_rating'                 => __( 'Rating', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_button_text'               => __( 'Add to Cart', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_reset_button_text'         => __( 'Reset', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_total_button_text'         => __( 'Total', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_pagination_text'           => __( 'Showing Products', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_select_all_text'           => __( 'Select All', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_option_text'               => '',
                'wmc_empty_form_text'           => __( 'Please check one or more products', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_thead_back_color'          => '', // get option value.
                'wmc_button_color'              => '', // same get option value.
                'wmc_empty_value_text'          => __( 'N/A', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_missed_variation_text'     => __( 'Please select all options', 'multiple-products-to-cart-for-woocommerce' ),
                'wmca_default_quantity'         => 0,
                'wmc_redirect'                  => '',
                'mpce_single_order_button_text' => __( 'Add', 'multiple-products-to-cart-for-woocommerce' ),
                'mpcp_empty_result_text'        => __( 'Sorry! No products found.', 'multiple-products-to-cart-for-woocommerce' ),
            ),
            'options'         => array(
                // checkboxes here.
                'wmc_show_pagination_text'    => '',
                'wmc_show_products_filter'    => '',
                'wmc_show_all_select'         => '',
                'wmca_show_reset_btn'         => '',
                'wmca_single_cart'            => '',
                'wmca_inline_dropdown'        => '',
                'wmca_allow_sku_sort'         => '',
                'wmca_show_header'            => '',
                'mpc_show_title_dopdown'      => '',
                'mpc_show_new_quantity_box'   => '',
                'mpc_show_ajax_search'        => '',
                'mpc_show_ajax_cat_filter'    => '',
                'mpc_show_ajax_tag_filter'    => '',
                'mpc_show_stock_out'          => '',
                'mpc_show_total_price'        => '',
                'mpc_show_add_to_cart_button' => '',
                'mpc_add_to_cart_checkbox'    => '',
                'mpc_show_variation_desc'     => '',
                'mpc_show_product_gallery'    => '',
                'mpc_show_cat_counter'        => '',
                'mpc_show_category_subheader' => '',
                'mpc_show_on_sale'            => '',
            ),
            'woocommerce'     => array(
                'decimal_point' => get_option( 'woocommerce_price_num_decimals', 2 ),
            ),
            'product_types'   => array( 'simple', 'variable' ),
            'default_imgs'    => array(
                'thumb' => wc_placeholder_img_src(),
                'full'  => wc_placeholder_img_src( 'full' ),
            ),
        );

        // populate frontend data structure.
        foreach ( $mpctable__['labels'] as $key => $label ) {
            $data = get_option( $key );
            if ( ! empty( $data ) && '' !== $data ) {
                $mpctable__['labels'][ $key ] = $data;
            }
        }

        // option data( specially checkboxs ).
        foreach ( $mpctable__['options'] as $key => $label ) {
            $value = get_option( $key );
            if ( ! empty( $value ) && '' !== $value ) {
                if ( 'on' === $value ) {
                    $mpctable__['options'][ $key ] = true;
                } else {
                    $mpctable__['options'][ $key ] = false;
                }
            }
        }

        // default quantity.
        if ( get_option( 'wmca_default_quantity' ) ) {
            $mpctable__['labels']['wmca_default_quantity'] = get_option( 'wmca_default_quantity' );
        }

        // change orderby texts.
        $a = get_option( 'mpc_sddt_default' );
        if ( ! empty( $a ) && '' !== $a ) {
            $mpctable__['orderby_options']['menu_order'] = $a;
        }

        $a = get_option( 'mpc_sddt_price_asc' );
        if ( ! empty( $a ) && '' !== $a ) {
            $mpctable__['orderby_options']['_price-ASC'] = $a;
        }

        $a = get_option( 'mpc_sddt_price_desc' );
        if ( ! empty( $a ) && '' !== $a ) {
            $mpctable__['orderby_options']['_price-DESC'] = $a;
        }

        // check if has pro.
        $mpctable__['has_pro'] = false;

        // Hook to modify frontend core data.
        do_action( 'mpc_frontend_core_data' );
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
