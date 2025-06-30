<?php
/**
 * Frontend helper functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Frontend view loader class
 */
class MPC_Frontend_Helper {
    /**
     * Process shortcode to array
     *
     * @param array $atts table shortcode.
     */
    public static function process_atts( $atts ) {
        global $mpc_frontend__;

        $atts = self::get_atts_from_cpt( $atts );
        $atts = self::parse_atts( $atts );

        $mpc_frontend__['atts'] = $atts;
        return $atts;
    }
    public static function get_atts_from_cpt( $atts ){
        if ( ! isset( $atts['table'] ) || empty( $atts['table'] ) ) {
            return $atts;
        }

        $table_id = (int) $atts['table'];
        $code     = MPC_Shortcode::get_shortcode( $table_id, 'full' );
        if ( empty( $code ) ) {
            return $atts;
        }

        $code = str_replace( '[', '', $code );
        $code = str_replace( ']', '', $code );
        $code = str_replace( 'woo-multi-cart', '', $code );

        return shortcode_parse_atts( $code );
    }

    /**
     * Validate and see all attributes are within scopes
     *
     * @param array $atts shortcode attributes.
     */
    public static function parse_atts( $atts ) {
        $ref = apply_filters( 'mpc_filter_attributes', array(
            'table'         => '',
            'limit'         => 10,
            'orderby'       => '',
            'order'         => 'DESC',
            'ids'           => '',
            'skip_products' => '',
            'cats'          => '',
            'type'          => 'all',
            'link'          => '',
            'description'   => '',
            'selected'      => '',
            'pagination'    => '',
            'columns'       => '',
        ) );

        return self::sanitize_atts(
            shortcode_atts( $ref, $atts, 'woo-multi-cart' )
        );
    }

    /**
     * Convert possible boolean values if it's not
     *
     * @param array $atts shortcode attributes.
     */
    public static function sanitize_atts( $atts ) {
        if( !is_array( $atts ) ) return $atts;

        $boolean_types = [ 'link', 'description', 'pagination' ];
        $number_types  = [ 'limit', 'table' ];
        $array_types   = [ 'selected', 'ids', 'skip_products', 'cats', 'tags', 'type', 'columns' ];
        foreach( $atts as $key => $value ){
            if( empty( $value ) ) continue;

            if( in_array( $key, $boolean_types, true ) ){
                $atts[ $key ] = 'false' === $value || false === $value ? false : true;
            }
            elseif( in_array( $key, $number_types, true ) ){
                $atts[ $key ] = 'int' === gettype( $value ) ? $value : (int) $value;
            }
            elseif( in_array( $key, $array_types, true ) ){
                if( 'all' === $value || is_array( $value ) ) continue;
                $atts[ $key ] = array_unique( explode( ',', str_replace( ' ', '', $value ) ) );
            }
        }
        return $atts;
    }


    
    public static function get_query_args( $atts ) {
        // Paged: current page.
        $paged = self::get_current_page();

        // Posts per page.
        $limit = isset( $atts['limit'] ) && ! empty( $atts['limit'] ) ? (int) $atts['limit'] : 10;
        $limit = isset( $atts['pagination'] ) && false === $atts['pagination'] ? 100 : $limit;
        
        // Orderb By and Order.
        $order_by = $atts['orderby'] ?? 'date';
        $order_by = !in_array( $order_by, array( 'title', 'date', 'price' ), true ) ? 'date' : $order_by;
        $order    = isset( $atts['order'] ) && !empty( $atts['order'] ) ? strtoupper( $atts['order'] ) : 'DESC';

        // special ordering support.
        $special_support = apply_filters( 'mpc_get_orderby_list', array( 'price' ) );

        $meta_key = '';
        if( in_array( $order_by, $special_support, true ) ){
            $meta_key = 'price' === $order_by ? '_price' : $order_by;
            $order_by = 'meta_value_num';
        }
        
        // Fail-safe for enhanced security.
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $limit > 100 ? 100 : $limit,
            'paged'          => $paged,
            'fields'         => 'ids',
            'orderby'        => $order_by,
            'order'          => $order
        );
        if( !empty( $meta_key ) ) $args['meta_key'] = $meta_key; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key

        $args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            array(
                'key'      => '_stock_status',
                'value'    => 'instock',
                'complare' => '=',
            ),
            array(
                'key'     => '_price',
                'compare' => 'EXISTS',
            ),
        );

        if( isset( $atts['ids'] ) && '' !== $atts['ids'] ) $args['post__in'] = $atts['ids'];

        // exclude posts if skip_products attribute is given.
        if( isset( $atts['skip_products'] ) && ! empty( $atts['skip_products'] ) ) $args['post__not_in'] = $atts['skip_products'];

        // product type(s).
        $att_types = $atts['type'] ? (is_array($atts['type']) ? $atts['type'] : explode(',', $atts['type'])) : ['simple', 'variable'];
        $supported = apply_filters( 'mpc_change_product_types', array( 'simple', 'variable' ) );

        // sanitize types.
        $types = array_map(function ( $val ) {
            return sanitize_title( $val );
        }, $att_types );
        $supported = array_map(
            function ( $val ) {
                return sanitize_title( $val );
            },
            $supported
        );

        // filter appropriate types.
        $types = in_array( 'all', $types, true ) ? $supported : array_intersect( $types, $supported );
        if( empty( $types ) ) $types = array( 'simple', 'variable' );

        $args['tax_query'] = array( 'relation' => 'AND' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

        $args['tax_query'][] = array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => $types,
        );

        $term_ids = self::get_term_ids( $atts['cats'], 'product_cat' );
        if( false !== $term_ids ) $args['tax_query'][] = $term_ids;

        return apply_filters( 'mpc_modify_query', $args, $atts );
    }
    public static function get_term_ids( $atts, $taxonomy ) {
        if( ! isset( $atts ) || '' === $atts ) {
            return false;
        }

        if( ! is_array( $atts ) ) {
            $atts = explode( ',', str_replace( ' ', '', $atts ) );
        }

        if( ! is_array( $atts ) || empty( $atts ) ) {
            return false;
        }

        // terms can either be id or slug.
        $extracted_term_ids = array();
        foreach ( $atts as $term_id ) {
            $term = get_term_by( 'id', $term_id, $taxonomy );

            if( ! empty( $term ) && isset( $term->term_id ) ) {
                $extracted_term_ids[] = $term->term_id;
            } else {
                $term = get_term_by( 'slug', $term_id, $taxonomy );
                if( ! empty( $term ) && isset( $term->term_id ) ) {
                    $extracted_term_ids[] = $term->term_id;
                }
            }
        }

        if( empty( $extracted_term_ids ) ) {
            return false;
        }

        return array(
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => $extracted_term_ids,
        );
    }



    public static function check_pro(){
        global $mpc_frontend__;

        $mpc_frontend__['has_pro'] = false;

        // Hook to modify frontend core data.
        do_action( 'mpc_frontend_core_data' );
    }



    public static function get_table_columns(){
        global $mpc_frontend__;
        
        $cols = $mpc_frontend__['atts']['columns'] ?? get_option( 'wmc_sorted_columns' );
        if( empty( $cols ) ) return array();

        $cols = !is_array($cols) ? explode( ',', str_replace( ' ', '', $cols ) ) : $cols;

        // Add correct extension to column names.
        $cols = array_map(function( $col ){
            return false === strpos( $col, 'wmc_ct_' ) ? 'wmc_ct_' . $col : $col;
        }, $cols );

        // Remove Pro columns if it's free.
        if( !$mpc_frontend__['has_pro'] ){
            $cols = array_diff( $cols, array( 'wmc_ct_category', 'wmc_ct_stock', 'wmc_ct_tag', 'wmc_ct_sku', 'wmc_ct_rating' ) );
        }

        $mpc_frontend__['cols'] = $cols;
        return $cols;
    }



    public static function get_product_data( $id ){
        $product = wc_get_product( $id );

        $data                      = self::get_common_data( $product );
        $data['id']                = $id;
        $data['stock']             = $product->get_stock_quantity();
        $data['on_sale']           = $product->is_on_sale();
        $data['backorder']         = $product->get_backorders();
        $data['sold_individually'] = $product->is_sold_individually();

        if('variable' === $data['type']){
            $data['atts']         = $product->get_variation_attributes();
            $data['default_atts'] = $product->get_default_attributes(); // Pre-selected.

            $data['children'] = self::get_all_children_data( $product, [] );
        }elseif( 'grouped' === $data['type'] ){
            $data['children'] = self::get_all_children_data( $product, array( 'title' => $data['title'], 'url' => $data['url'] ) );
        }elseif( 'grouped' !== $data['type'] ){
            $data['img_id'] = $product->get_image_id();
        }

        $data = self::process_product_data( $data );
        return apply_filters( 'mpcp_modify_product_data', $data, $product );
    }
    public static function process_product_data( $data ){
        // Description.
        $data['desc'] = wp_strip_all_tags( do_shortcode( $data['desc'] ) );
        $data['desc'] = strlen( $data['desc'] ) > 100 ? substr( $data['desc'], 0, 100 ) . '...' : $data['desc'];

        // Backorders.
        $data['stock'] = 'yes' === $data['backorder'] || 'notify' === $data['backorder'] ? '' : $data['stock'];
        $data['stock'] = $data['stock'] > 0 ? $data['stock'] : 0;

        // Price without HTML.
        $data['price_'] = 'simple' === $data['type'] ? self::extract_price( $data['price'] ) : '';

        return $data;
    }

    public static function get_all_children_data( $product, $parent ){
        $data      = array();
        $childrens = $product->get_children();

        if( empty( $childrens ) ) return array();

        foreach( $childrens as $child ){
            $data[$child] = self::get_child_data( $child, $parent );
        }

        return $data;
    }
    public static function get_child_data( $id, $parent ){
        $product = wc_get_product( $id );

        $data           = self::get_common_data( $product );
        $data['id']     = $id;
        $data['img_id'] = $product->get_image_id();
        $data['ss_fee'] = get_post_meta( $id, '_subscription_sign_up_fee', true ); // Subscription product's sign up fee.

        if( 'variation' === $data['type'] ){
            $data['atts'] = $product->get_attributes();
        }else{
            $data['on_sale']   = $product->is_on_sale();
            $data['backorder'] = $product->get_backorders();
            $data['parent']    = array(
                'title' => $parent['title'],
                'url'   => $parent['url']
            );
        }

        return self::process_child_data( $data );
    }
    public static function process_child_data( $data ){
        $image = self::get_product_image( $data );
        $data['image'] = array(
            'thumbnail' => $image['thumb'],
            'full'      => $image['full']
        );

        $data['price'] = self::extract_price( $data['price'] );
        $data['stock'] = self::prepare_stock( $data );
        return $data;
    }
    public static function get_common_data( $product ){ // properties common for parent and child product.
        $common = array(
            'type'         => $product->get_type(),
            'title'        => $product->get_title(),
            'url'          => $product->get_permalink(),
            'desc'         => $product->get_description(),
            'price'        => $product->get_price_html(),
            'sku'          => $product->get_sku(),
            'stock'        => $product->get_stock_quantity(),
            'stock_status' => $product->get_stock_status(),
        );

        $common['desc'] = empty( $common['desc'] ) ? $product->get_description() : $common['desc'];
        $common['desc'] = strlen( $common['desc'] ) > 150 ? substr( $common['desc'], 0, 150 ) . '...' : $common['desc'];
        if( strlen( $common['desc'] ) > 150 ) $common['desc'] = substr( $common['desc'], 0, 150 ) . '...';

        return $common;
    }

    public static function extract_price( $price_html ) {
        $cs = get_woocommerce_currency_symbol();

        $text = wp_strip_all_tags( $price_html );
        $text = html_entity_decode( $text );
        $text = str_replace( $cs, '', $text );

        $decimal_sep  = get_option( 'woocommerce_price_decimal_sep', '.' );
        $thousand_sep = get_option( 'woocommerce_price_thousand_sep', ',' );

        $ds = preg_quote( $decimal_sep, '/' );
        $ts = preg_quote( $thousand_sep, '/' );

        $pattern = "/\d{1,}(?:{$ts}\d{3})*(?:{$ds}\d+)?/";
        preg_match_all( $pattern, $text, $matches );
        if( empty( $matches[0] ) ) return 0.0;

        $prices = array_map(
            function ( $price ) use ( $thousand_sep, $decimal_sep ) {
                $price = str_replace( $thousand_sep, '', $price );
                $price = str_replace( $decimal_sep, '.', $price );
                return (float) $price;
            },
            $matches[0]
        );

        return end( $prices );
    }
    public static function prepare_stock( $data ){
        $status = $data['stock_status'] ?? '';
        $stock  = $data['stock'] ?? '';
        
        switch ($status) {
            case 'instock':
                $stock = empty( $stock ) || '' === $stock ? 'In stock' : $stock . ' in stock';
                break;

            case 'outofstock':
                $stock = 'Out of stock';
                break;

            case 'onbackorder':
                $stock = 'On backorder';
                break;
            
            default:
                # code...
                break;
        }

        return $stock;
    }

    
    public static function get_product_image( $data ){
        $image_id  = $data['img_id'] ?? '';
        $show_sale = get_option( 'mpc_show_on_sale' );

        return array(
            'show_sale' => !empty( $show_sale ) && 'on' === $show_sale ? true : false,
            'thumb'     => empty( $image_id ) ? wc_placeholder_img_src() : wp_get_attachment_image_url( $image_id, 'thumbnail' ),
            'full'      => empty( $image_id ) ? wc_placeholder_img_src( 'full' ) : wp_get_attachment_image_url( $image_id, 'full' )
        );
    }



    public static function get_current_page(){
        $paged = 1;
        if ( isset( $_POST['table_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['table_nonce'] ) ), 'table_nonce_ref' ) ) {
            if ( isset( $_POST['page'] ) && ! empty( $_POST['page'] ) ) {
                $paged = (int) sanitize_key( $_POST['page'] );
            }
        }

        return $paged;
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
