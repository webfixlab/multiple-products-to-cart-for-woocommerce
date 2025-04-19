<?php
/**
 * Admin helper class
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * MPC Admin Helper Class.
 */
class MPC_Admin_Helper {
    /**
     * Plugin core data
     * @var array
     */
    private static $data;

    /**
     * Set plugin core data
     */
    public static function set(){
        global $mpc__;
        self::$data = $mpc__;
    }



    /**
     * Check if current screen is one of the allowed one
     */
    public static function in_screen(){
        global $mpc__;

        $screen = get_current_screen();

        return in_array( $screen->id, $mpc__['plugin']['screen'], true );
    }

    /**
     * Pre-process settings field data
     *
     * @param array $field settings field data.
     */
    public static function pre_save_field( $field ) {
        if ( ! isset( $_POST['mpc_admin_settings'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_admin_settings'] ) ), 'mpc_admin_settings_save' ) ) {
            return;
        }
        
        if ( ! isset( $_POST ) || empty( $_POST ) ) {
            return;
        }
        
        self::set();

        // don't save pro field data without existing pro version.
        if ( isset( $field['pro'] ) && true === $field['pro'] && false === self::$data['has_pro'] ) {
            return;
        }
        self::save_field( $field );
        
        // handle followup also.
        if ( ! isset( $field['followup'] ) || empty( $field['followup'] ) ) {
            return;
        }
        
        foreach ( $field['followup'] as $followup_field ) {
            self::save_field( $followup_field );
        }
    }

    /**
     * Save settings field data
     *
     * @param array $field settings field data.
     */
    public static function save_field( $field ) {
        $name = $field['key'];

        // without a name/key to find or save data, skip.
        if ( ! isset( $name ) || empty( $name ) ) {
            return;
        }

        if ( ! isset( $_POST['mpc_admin_settings'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_admin_settings'] ) ), 'mpc_admin_settings_save' ) ) {
            return;
        }

        // only checkbox field and no data? save it as unchecked (no).
        if ( 'checkbox' === $field['type'] && ! isset( $_POST[ $name ] ) ) {
            update_option( $name, 'no' );
            return;
        }

        // without post data, skip.
        if ( ! isset( $_POST[ $name ] ) || empty( $_POST[ $name ] ) ) {
            delete_option( $name );
            return;
        }

        update_option( $name, sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) );
    }

    /**
     * Save global table columns settings
     */
    public static function save_sorted_columns() {
        if ( ! isset( $_POST['mpc_col_sort'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_col_sort'] ) ), 'mpc_col_sort_save' ) ) {
            return;
        }

        if ( ! isset( $_POST ) || empty( $_POST ) ) {
            return;
        }

        if ( ! isset( $_POST['wmc_sorted_columns'] ) || empty( $_POST['wmc_sorted_columns'] ) ) {
            return;
        }

        update_option( 'wmc_sorted_columns', sanitize_text_field( wp_unslash( $_POST['wmc_sorted_columns'] ) ) );
    }


    
    /**
     * Display active or inactive columns in a list
     *
     * @param string  $value     saved columns.
     * @param boolean $is_active wheather to show saved or active columns.
     */
    public static function column_list( $value, $is_active ) {
        self::set();

        $labels = array(
            'image'     => __( 'Image', 'multiple-products-to-cart-for-woocommerce' ),
            'product'   => __( 'Product', 'multiple-products-to-cart-for-woocommerce' ),
            'price'     => __( 'Price', 'multiple-products-to-cart-for-woocommerce' ),
            'variation' => __( 'Variation', 'multiple-products-to-cart-for-woocommerce' ),
            'quantity'  => __( 'Quantity', 'multiple-products-to-cart-for-woocommerce' ),
            'buy'       => __( 'Buy', 'multiple-products-to-cart-for-woocommerce' ),
            'category'  => __( 'Category', 'multiple-products-to-cart-for-woocommerce' ),
            'stock'     => __( 'Stock', 'multiple-products-to-cart-for-woocommerce' ),
            'tag'       => __( 'Tag', 'multiple-products-to-cart-for-woocommerce' ),
            'sku'       => __( 'SKU', 'multiple-products-to-cart-for-woocommerce' ),
            'rating'    => __( 'Rating', 'multiple-products-to-cart-for-woocommerce' ),
        );

        $cols = self::sorted_columns( $value );
        if ( ! $is_active ) {
            $cols['columns'] = array_diff( array_keys( $labels ), $cols['columns'] );
        }

        foreach ( $cols['columns'] as $col ) {
            $label = get_option( 'wmc_ct_' . esc_attr( $col ) );
            if ( empty( $label ) ) {
                $label = $labels[ $col ];
            }

            $stone_cls = in_array( $col, $cols['stones'], true ) ? 'mpc-stone-col' : 'ui-state-default';

            printf(
                '<li class="ui-sortable-handle %s" data-meta_key="wmc_ct_%s">%s</li>',
                esc_attr( $stone_cls ),
                esc_attr( $col ),
                esc_html( $label )
            );
        }
    }

    /**
     * Get table columns settings, either active or inactive
     *
     * @param string $value sorted columns in comma separated way.
     */
    protected static function sorted_columns( $value ) {
        $stones = array();

        // columns only available in pro plugin.
        $pro_cols = array( 'category', 'stock', 'tag', 'sku', 'rating' );

        // get saved columns.
        $cols = array( 'image', 'product', 'price', 'variation', 'quantity', 'buy' );
        if ( ! empty( $value ) && ! is_array( $value ) ) {
            $cols = explode( ',', str_replace( array( ' ', 'wmc_ct_' ), '', $value ) ); // check if array str_replace works or not?
        }

        // remove pro columns if pro does not exist.
        if ( ! self::$data['has_pro'] ) {
            $cols   = array_diff( $cols, $pro_cols );
            $stones = $pro_cols;
        }

        array_push( $stones, 'variation' );

        // variation is a stone column, it shouldn't be removed.
        if ( ! in_array( 'variaion', $cols, true ) ) {
            array_push( $stones, 'variation' );
        }

        return array(
            'columns' => $cols,
            'stones'  => $stones,
        );
    }


    
    /**
     * Check if notice interval is passed given interval
     *
     * @param string $key             Notice type option name.
     * @param int    $notice_interval Notice interval in days.
     * @param string $skip_           Whether this notice purpose is complete.
     */
    public static function date_difference( $key, $notice_interval, $skip_ = '' ) {
        $value = get_option( $key );

        if ( empty( $value ) || '' === $value ) {
            // if skip value is meta value - return false.
            if ( '' !== $skip_ && $skip_ === $value ) {
                return false;
            } else {
                $c   = date_create( gmdate( 'Y-m-d' ) );
                $d   = date_create( $value );
                $dif = date_diff( $c, $d );
                $b   = (int) $dif->format( '%d' );

                // if days difference meets minimum given interval days - return true.
                if ( $b >= $notice_interval ) {
                    return true;
                }
            }
        } else {
            add_option( $key, gmdate( 'Y-m-d' ) );
        }

        return false;
    }
}
