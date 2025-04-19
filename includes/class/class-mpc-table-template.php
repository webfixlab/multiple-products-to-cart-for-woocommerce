<?php
/**
 * Frontend table template functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Frontend table template class
 */
class MPC_Table_Template {

    /**
     * Initialize hooks
     */
    public static function init() {
        add_action( 'mpc_table_header', array( __CLASS__, 'table_orderby' ), 10 );
        add_action( 'mpc_table_header', array( __CLASS__, 'table_all_check' ), 30);
        
        add_action( 'mpc_table_body', array( __CLASS__, 'table_body' ), 10 );

        add_action( 'mpc_table_title_columns', array( __CLASS__, 'table_columns' ), 10 );

        add_action( 'mpc_table_column_image', array( __CLASS__, 'row_image' ), 10 );
        add_action( 'mpc_table_column_product', array( __CLASS__, 'row_product' ), 10 );
        add_action( 'mpc_table_column_price', array( __CLASS__, 'row_price' ), 10 );
        add_action( 'mpc_table_column_variation', array( __CLASS__, 'row_variation' ), 10 );
        add_action( 'mpc_table_column_quantity', array( __CLASS__, 'row_quantity' ), 10 );
        add_action( 'mpc_table_column_buy', array( __CLASS__, 'row_buy' ), 10 );

        add_action( 'mpc_table_footer', array( __CLASS__, 'table_total' ), 10 );
        add_action( 'mpc_table_footer', array( __CLASS__, 'pagination_info' ), 15 );
        add_action( 'mpc_table_footer', array( __CLASS__, 'reset_button' ), 20 );
        add_action( 'mpc_table_footer', array( __CLASS__, 'add_to_cart_button' ), 25 );
        add_action( 'mpc_table_footer', array( __CLASS__, 'pagination' ), 30 );
        add_action( 'mpc_table_footer', array( __CLASS__, 'table_data' ), 99 );

        add_action( 'wp_footer', array( __CLASS__, 'image_popup' ) );
    }



    public static function display_table(){
        ?>
        <table class="mpc-wrap" cellspacing="0">
            <?php do_action( 'mpc_table_title_columns' ); ?>
            <tbody>
                <?php do_action( 'mpc_table_body' ); ?>
            </tbody>
        </table>
        <?php
    }



    public static function table_orderby(){
        global $mpc_frontend__;

        $show_orderby = get_option( 'wmc_show_products_filter' ) ?? '';
        if( !empty( $show_orderby ) && 'on' !== $show_orderby ) return;

        $title_filter = get_option( 'mpc_show_title_dopdown' ) ?? '';
        $title_filter = empty( $title_filter ) || 'on' === $title_filter ? true : false;

        // Pro feature.
        $sku_filter = get_option( 'wmca_allow_sku_sort' ) ?? '';
        $sku_filter = empty( $sku_filter ) || 'on' === $sku_filter ? true : false;

        $select = $mpc_frontend__['atts']['orderby'] ?? 'menu_order';

        $options = array(
            'menu_order' => array(
                'key'   => 'mpc_sddt_default',
                'label' => __( 'Default sorting', 'multiple-products-to-cart-for-woocommerce' )
            ),
            'price-ASC'  => array(
                'key'   => 'mpc_sddt_price_asc',
                'label' => __( 'Price: Low to High', 'multiple-products-to-cart-for-woocommerce' )
            ),
            'price-DESC' => array(
                'key'   => 'mpc_sddt_price_desc',
                'label' => __( 'Price: High to Low', 'multiple-products-to-cart-for-woocommerce' )
            )
        );
        if( $title_filter ){
            $options['title-ASC'] = array(
                'key'   => 'mpc_sddt_title_asc',
                'label' => __( 'Title: A to Z', 'multiple-products-to-cart-for-woocommerce' )
            );
            $options['title-DESC'] = array(
                'key'   => 'mpc_sddt_title_desc',
                'label' => __( 'Title: Z to A', 'multiple-products-to-cart-for-woocommerce' )
            );
        }
        if( $mpc_frontend__['has_pro'] && $sku_filter ){
            $options['_sku-ASC'] = array(
                'key'   => 'mpc_sddt_sku_asc',
                'label' => __( 'SKU: A to Z', 'multiple-products-to-cart-for-woocommerce-pro' )
            );
            $options['_sku-DESC'] = array(
                'key'   => 'mpc_sddt_sku_desc',
                'label' => __( 'SKU: Z to A', 'multiple-products-to-cart-for-woocommerce-pro' )
            );
        }

        ?>
        <div class="mpc-sort">
            <select name="mpc_orderby" class="mpc-orderby" title="<?php echo esc_html__( 'Table order by', 'multiple-products-to-cart-for-woocommerce' ); ?>">
                <?php self::orderby_options( $options, $select ); ?>
            </select>
            <input type="hidden" name="paged" value="1" />
        </div>
        <?php
    }
    private static function orderby_options( $options, $select ){
        foreach( $options as $slug => $option ){
            $selected = $slug === $select ? 'selected' : '';
            $label    = get_option( $option['key'] ) ?? $option['label'];
            ?>
            <option value="<?php echo esc_attr( $slug ); ?>" <?php echo esc_attr( $selected ); ?>>
                <?php echo esc_html( $label ); ?>
            </option>
            <?php
        }
    }

    public static function table_all_check(){
        // Don't show if not enabled.
        $show_all_check = get_option( 'wmc_show_all_select' ) ?? '';
        if( !empty( $show_all_check ) && 'on' !== $show_all_check ) return;

        // Don't show if checkbox is hidden in the table.
        $has_checkbox = get_option( 'mpc_add_to_cart_checkbox' ) ?? '';
        if( !empty( $has_checkbox ) && 'on' !== $has_checkbox ) return;

        // Don't show if buy column isn't in one of the table columns.
        $cols = MPC_Frontend_Helper::get_table_columns();
        $cols = !empty( $cols ) ? array_keys( $cols ) : [];
        if( !empty( $cols ) && !in_array( 'wmc_ct_buy', $cols, true ) ) return;

        $label = get_option( 'wmc_select_all_text' ) ?? __( 'Select All', 'multiple-products-to-cart-for-woocommerce' );
        ?>
        <div class="mpc-all-select">
            <label><?php echo esc_html( $label ); ?></label>
            <input type="checkbox" class="mpc-check-all">
        </div>
        <?php
    }



    public static function table_columns() {
        $cols = MPC_Frontend_Helper::get_table_columns();
        if( empty( $cols ) ) return;
        ?>
        <thead>
            <tr>
                <?php foreach ( $cols as $col => $label ) : ?>
                    <th for="<?php echo esc_attr( $col ); ?>" class="mpc-product-<?php echo esc_attr( str_replace( 'wmc_ct_', '', $col ) ); ?>">
                        <?php echo esc_html( $label ); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <?php
    }
    public static function table_body() {
        global $mpc_frontend__;

        if( !$mpc_frontend__['products'] ) return;

        foreach ( $mpc_frontend__['products'] as $id ) {
            self::display_row( $id );
        }
    }



    public static function display_row( $id ) {
        global $mpc_frontend__;

        // Setup product data.
        $data  = MPC_Frontend_Helper::get_product_data( $id );
        $price = $data['price_'] ?? '';

        $mpc_frontend__['row_data'] = $data;
        $mpc_frontend__['row_id']   = $id;
        ?>
        <tr class="cart_item <?php echo esc_attr( $data['type'] ); ?>" data-varaition_id="0" data-type="<?php echo esc_attr( $data['type'] ); ?>" data-id="<?php echo esc_attr( $id ); ?>" stock="<?php echo esc_attr( $data['stock'] ); ?>" data-price="<?php echo esc_attr( $price ); ?>">
            <?php self::display_all_columns(); ?>
        </tr>
        <?php
        do_action( 'mpc_table_row' );
    }
    public static function display_all_columns(){
        global $mpc_frontend__;

        foreach ( $mpc_frontend__['cols'] as $col ) {
            do_action( 'mpc_table_column_' . str_replace( 'wmc_ct_', '', $col ) );
        }
    }


    public static function row_image() {
        global $mpc_frontend__;

        $data = $mpc_frontend__['row_data'];
        $img  = MPC_Frontend_Helper::get_product_image( $data );
        ?>
        <td for="image" class="mpc-product-image" data-pimg-thumb="<?php echo esc_url( $img['thumb'] ); ?>" data-pimg-full="<?php echo esc_url( $img['full'] ); ?>">
            <div class="mpcpi-wrap">
                <?php if ( $img['show_sale'] ) : ?>
                    <span class="wfl-sale">
                        <?php echo esc_html__( 'sale', 'multiple-products-to-cart-for-woocommerce' ); ?>
                    </span>
                <?php endif; ?>
                <img src="<?php echo esc_url( $img['thumb'] ); ?>" class="mpc-product-image attachment-thumbnail size-thumbnail" alt="" data-fullimage="<?php echo esc_url( $img['full'] ); ?>">
            </div>
            <?php do_action( 'init_mpc_gallery' ); ?>
        </td>
        <?php
    }
    
    public static function row_product() {
        global $mpc_frontend__;

        $data = $mpc_frontend__['row_data'];
        $atts = $mpc_frontend__['atts'];
        ?>
        <td for="title" class="mpc-product-name">
            <div class="mpc-product-title">
                <?php self::product_parent( $data ); ?>
                <?php self::product_title( $data, $atts ); ?>
                <?php self::product_desc( $data, $atts ); ?>
            </div>
        </td>
        <?php
    }
    private static function product_parent( $data ){
        if( ! isset( $data['parent'] ) || empty( $data['parent'] ) ){
            return;
        }
        ?>
        <div class="product-parent">
            <a href="<?php echo esc_url( $data['parent']['url'] ); ?>">
                <?php echo esc_html( $data['parent']['title'] ); ?>
            </a>
        </div>
        <?php
    }
    private static function product_title( $data, $atts ){
        if( !$atts['link'] ){
            echo esc_html( $data['title'] );
            return;
        }
        ?>
        <a href="<?php echo esc_url( $data['url'] ); ?>">
            <?php echo esc_html( $data['title'] ); ?>
        </a>
        <?php
    }
    private static function product_desc( $data, $atts ){
        if( !$atts['description'] ) {
            return;
        }
        ?>
        <div class="woocommerce-product-details__short-description">
            <p>
                <?php echo wp_kses_post( $data['desc'] ); ?>
            </p>
        </div>
        <?php
    }

    public static function row_price() {
        global $mpc_frontend__;

        $data = $mpc_frontend__['row_data'];
        ?>
        <td for="price" class="mpc-product-price">
            <div class="mpc-single-price" style="display:none;">
                <?php self::price_range( $data ); ?>
            </div>
            <div class="mpc-range">
                <?php self::product_price( $data ); ?>
            </div>
        </td>
        <?php
    }
    private static function product_price( $data ){
        if( empty( $data['price'] ) ) return;
        wp_kses(
            $data['price'],
            array(
                'span' => array(
                    'class' => array(),
                ),
                'bdi'  => array(),
                'del'  => array(),
                'ins'  => array(),
            )
        );
    }
    private static function price_range( $data ){
        if( 'variable' !== $data['type'] ) return;
        ?>
        <span class="woocommerce-Price-amount amount">
            <bdi>
                <span class="total-price">0</span>
                <span class="woocommerce-Price-currencySymbol"><?php echo wp_kses_post( get_woocommerce_currency_symbol() ); ?></span>
            </bdi>
        </span>
        <?php
    }

    public static function row_variation() {
        global $mpc_frontend__;
        $data = $mpc_frontend__['row_data'];
        ?>
        <td for="variation" class="mpc-product-variation">
            <?php do_action( 'mpcp_custom_variation_html' ); ?>
            <?php self::no_variation( $data ); ?>
            <?php self::json_variation_data( $data ); ?>
            <?php self::variation_attributes( $data ); ?>
        </td>
        <?php
    }
    private static function variation_attributes( $data ){
        if( !isset( $data['atts'] ) || empty( $data['atts'] ) ) return;
        $choose = get_option( 'wmc_option_text' ); // Choose attribute label.

        foreach( $data['atts'] as $attribute => $options ){
            $name = sanitize_title( $attribute ); // Sanitized attribute name.
            ?>
            <div class="variation-group">
                <select class="<?php echo esc_attr( $name ); ?>" name="attribute_<?php echo esc_attr( $name . $data['row_id'] ); ?>" data-attribute_name="attribute_<?php echo esc_attr( $name ); ?>">
                    <option value="">
                        <?php echo empty( $choose ) ? '' : esc_html( $choose ) . '&nbsp;'; ?>
                        <?php echo wc_attribute_label( $attribute ); ?>
                    </option>
                    <?php self::attribute_options( $attribute, $options, $data ); ?>
                </select>
            </div>
            <?php
        }

        self::clear_variation( $data );
    }
    private static function attribute_options( $attribute, $options, $data ){
        if( empty( $options ) ) return;

        $terms = taxonomy_exists( $attribute ) ? wc_get_product_terms( $data['row_id'], $attribute, array( 'fields' => 'all' ) ) : [];

        if( !empty( $terms ) ){
            foreach( $terms as $term ){
                if( !in_array( $term->slug, $options, true ) ) continue;
                self::option_item( $term->slug, $term->name, $data );
            }
        }else{
            foreach( $options as $option ){
                $s_option = sanitize_title( $option ); // Sanitized option name.
                self::option_item( $s_option, $option, $data );
            }
        }
    }
    private static function option_item( $slug, $name, $data ){
        ?>
        <option
            value="<?php echo esc_attr( $slug ); ?>"
            <?php self::select_option( $slug ); ?>>
            <?php echo esc_html( $name ); ?>
        </option>
        <?php
    }
    private static function select_option( $slug ){
        $slug   = sanitize_title( $slug );
        $select = $data['default_atts'] ?? [];
        if( empty( $select ) ) return;

        echo selected( $select, $slug ) || selected( $select, 'attribute_' . $slug );
    }
    private static function json_variation_data( $data ){
        if( !isset( $data['children'] ) || empty( $data['children'] ) ) return;
        ?>
        <div class="row-variation-data" data-variation_data="<?php echo wc_esc_json( wp_json_encode( $data['children'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></div>
        <?php
    }
    private static function clear_variation( $data ){
        $total   = isset( $data['atts'] ) && !empty( $data['atts'] ) ? count( $data['atts'] ) : 0;
        $default = isset( $data['default_atts'] ) && !empty( $data['default_atts'] ) ? count( $data['default_atts'] ) : 0;
        ?>
        <div class="clear-button">
            <?php if( $total === $default && $total > 0 ) : ?>
                <a href="#">
                    <?php echo esc_html__( 'Clear', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </a>
            <?php endif; ?>
        </div>
        ?>
        <?php
    }
    private static function no_variation( $data ){
        if( isset( $data['atts'] ) && !empty( $data['atts'] ) ) return;

        $label = get_option( 'wmc_empty_value_text' );
        ?>
        <span>
            <?php echo !empty( $label ) ? esc_html( $label ) : esc_html__( 'N/A', 'multiple-products-to-cart-for-woocommerce' ); ?>
        </span>
        <?php
    }

    public static function row_quantity(){
        global $mpc_frontend__;
        $data = $mpc_frontend__['row_data'];
        ?>
        <td for="quantity" class="mpc-product-quantity">
            <?php self::quantity_field( $data ); ?>
        </td>
        <?php
    }
    private static function quantity_field( $data ){
        if( 'grouped' === $data['type'] ) return;

        $id          = $data['row_id'];
        $stock       = $data['stock'];
        $stock       = empty( $stock ) ? '' : (int) $stock;
        $stock       = $data['sold_individually'] ? 1 : $stock;

        $default_qty = get_option( 'wmca_default_quantity' ) ?? 0;
        $default_qty = (int) $default_qty;

        $min         = 1;
        $max         = empty( $stock ) ? '' : $stock;
        $default_qty = !empty( $stock ) && $default_qty > $stock ? $stock : $default_qty;

        ?>
        <div class="quantity">
            <input
                type="number"
                name="quantity<?php echo esc_attr( $id ); ?>"
                value="<?php echo esc_attr( $default_qty ); ?>"
                class="input-text qty text"
                step="1"
                min="<?php echo esc_attr( $min ); ?>"
                max="<?php echo esc_attr( $max ); ?>"
                title="<?php echo esc_html__( 'Quantity', 'multiple-products-to-cart-for-woocommerce' ); ?>"
                size="4"
                inputmode="numeric"
                data-default="<?php echo esc_attr( $default_qty ); ?>"
                data-current_stock="<?php echo esc_attr( $stock ); ?>">
        </div>
        <?php
    }

    public static function row_buy() {
        global $mpc_frontend__;

        $data     = $mpc_frontend__['row_data'];
        $label    = get_option( 'wmc_ct_buy' ) ?? __( 'Buy', 'multiple-products-to-cart-for-woocommerce' );
        $selected = $mpc_frontend__['atts']['selected'] ?? [];
        $checked  = !empty( $selected ) && in_array( $data['row_id'], $selected, true ) ? 'checked' : '';
        ?>
        <td for="buy" class="mpc-product-select">
            <span class="mpc-mobile-only">
                <?php echo esc_html( $label ); ?>
            </span>
            <input
                type="checkbox"
                name="product_ids[]"
                value="<?php echo esc_attr( $data['row_id'] ); ?>"
                data-price="<?php echo isset( $data['price_'] ) ? esc_attr( $data['price_'] ) : ''; ?>"
                <?php echo esc_attr( $checked ); ?>>
        </td>
        <?php
    }



    public static function table_total(){
        $label = get_option( 'wmc_total_button_text' ) ?? __( 'Total', 'multiple-products-to-cart-for-woocommerce' );
        ?>
        <div class="total-row">
            <span class="total-label"><?php echo esc_html( $label ); ?></span>
            <span class="mpc-total">
                <span class="woocommerce-Price-amount amount">
                    <bdi>
                        <span class="total-price"><?php echo esc_attr( 0 ); ?></span>
                        <span class="woocommerce-Price-currencySymbol"><?php echo wp_kses_post( get_woocommerce_currency_symbol() ); ?></span>
                    </bdi>
                </span>
            </span>
        </div>
        <?php
    }
    public static function pagination_info(){
        global $mpc_frontend__;

        // Settings: hide pagination info.
        $show_info = get_option( 'wmc_show_pagination_text' ) ?? '';
        if( !empty( $show_info ) && 'on' !== $show_info ) return;

        // Table attribute: hidden pagination info.
        $atts = $mpc_frontend__['atts'] ?? [];
        if( isset( $atts['pagination'] ) && !$atts['pagination'] ) return;

        // Found less products than posts per page.
        if( $mpc_frontend__['found_posts'] <= $atts['limit'] ) return;

        $label = get_option( 'wmc_pagination_text' ) ?? __( 'Showing', 'multiple-products-to-cart-for-woocommerce' );

        $page  = MPC_Frontend_Helper::get_current_page();
        $limit = $atts['limit'];
        $total = $mpc_frontend__['found_posts'];

        $start = ( $page - 1 ) * $limit + 1; // Example: pa 1, li 10, st is 1.
        $end   = min( $page * $limit, $total ); // Take whichever comes first, end or total.
        $range = "{$start} - {$end}";
        ?>
        <div class="mpc-product-range" data-page_limit="<?php echo esc_attr( $limit ); ?>">
            <p>
                <?php printf(
                    // translators: %1$s label, %2$s current range, %3$s total products.
                    __( '%1$s %2$s of %3$s products', '' ),
                    esc_html( $label ),
                    esc_attr( $range ),
                    esc_attr( $total )
                ); ?>
            </p>
        </div>
        <?php
    }
    public static function reset_button(){
        $show_button = get_option( 'wmca_show_reset_btn' ) ?? '';
        if( !empty( $show_button ) && 'on' !== $show_button ) return;

        $label = get_option( 'wmc_reset_button_text' ) ?? __( 'Reset', 'multiple-products-to-cart-for-woocommerce' );
        ?>
        <input type="reset" class="mpc-reset" value="<?php echo esc_html( $label ); ?>">
        <?php
    }
    public static function add_to_cart_button(){
        $label = get_option( 'wmc_button_text' ) ?? __( 'Add to Cart', 'multiple-products-to-cart-for-woocommerce' );
        ?>
        <input
            type="submit"
            class="mpc-add-to-cart single_add_to_cart_button button alt wc-forward"
            name="proceed"
            value="<?php echo esc_html( $label ); ?>" />
        <?php
    }
    public static function pagination(){
        global $mpc_frontend__;

        $current_page = MPC_Frontend_Helper::get_current_page();
        $max_page     = $mpc_frontend__['max_num_page'];

        $pages = self::get_pagination_pages( $current_page, $max_page );
        if ( empty( $pages ) ) {
            return;
        }
        ?>
        <div class="mpc-pagenumbers" data-max_page="<?php echo esc_attr( $max_page ); ?>">
            <?php foreach ( $pages as $i => $page ) : ?>
                <?php if( $i > 0 && ( $page - $pages[$i - 1]) > 1 ) : ?>
                    <span class="mpc-divider">...</span>
                <?php endif; ?>
                <span <?php echo $page === $current_page ? 'class="current"' : ''; ?>>
                    <?php echo esc_html( $page ); ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php
    }
    /**
     * Get a list of pagination numbers to display.
     *
     * @param int $current  Current page.
     * @param int $total    Total number of pages.
     * @param int $surround Number of pages to show before and after current page.
     * @return array
     */
    private static function get_pagination_pages( $current, $total, $surround = 1 ) {
        if ( $total <= 1 ) return array();

        $pages = array_unique( // Find unique numbers.
            array_merge( // Merge all page numbers.
                [ 1, $total ],
                range( // Basically it's $current, -1 and +1.
                    max( 1, $current - $surround ),
                    min( $total, $current + $surround )
                )
            )
        );

        sort( $pages );

        return $pages;
    }

    public static function table_data(){
        global $mpc_frontend__;

        $args = $mpc_frontend__['args'] ?? [];
        $atts = $mpc_frontend__['atts'] ?? [];
        ?>
        <input type="hidden" name="add_more_to_cart" value="1">
        <div
            class="mpc-table-query"
            data-query="<?php echo wc_esc_json( wp_json_encode( $args ) ); ?>"
            data-atts="<?php echo wc_esc_json( wp_json_encode( $atts ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></div>
        <?php
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

MPC_Table_Template::init();
