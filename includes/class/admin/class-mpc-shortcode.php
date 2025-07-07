<?php
/**
 * Admin shortcode related functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * MPC Admin Shortcode Class.
 */
class MPC_Shortcode {
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

    public static function init(){
        add_action( 'wp_ajax_mpc_admin_search_box', array( __CLASS__, 'ajax_itembox_search' ) );
    }



    /**
     * Get all product table shortcodes
     *
     * @return array
     */
    public static function get_all_shortcodes(){
        global $wpdb;

        $shortcodes = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pm.meta_value AS ID, p.post_title, p.post_content
                        FROM {$wpdb->prefix}posts AS p
                        LEFT JOIN {$wpdb->prefix}postmeta AS pm
                        ON pm.post_id = p.ID
                        WHERE p.post_type=%s AND pm.meta_key=%s",
            'mpc_product_table', 'table_id'
            ),
            ARRAY_A
        );

        return array_merge( $shortcodes, self::get_legacy_tables() );
    }

    /**
     * Get shortcode from the table id based on given context
     *
     * @param int    $table_id Shortcode table id.
     * @param string $context  Return type context.
     */
    public static function get_shortcode( $table_id, $context ) {
        $cpt_id = self::get_cpt_post_id( $table_id );
        if ( $cpt_id ) {
            $code = get_post_meta( $cpt_id, 'shortcode', true );
        } else {
            /**
             * Depricated !!!
             * Get old shortcode.
             */
            $code = get_option( 'mpcasc_code' . $table_id );
        }

        $code = wp_unslash( $code );
        if( 'full' === $context ){
            return "[woo-multi-cart {$code}]";
        }elseif( 'only_atts' === $context ){
            return $code;
        }
    }
    public static function get_cpt_post_id( $table_id ){
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id from {$wpdb->prefix}postmeta WHERE meta_key=%s AND meta_value=%d",
            'table_id',
            $table_id
        ));
    }
    public static function get_cpt_post( $table_id ){
        if ( empty( $table_id ) ) return '';
        
        $post_id = self::get_cpt_post_id( $table_id );
        if( !$post_id ) return '';

        $post = get_post( $post_id );
        if(empty($post)) return '';

        $title = $post->post_title;
        $desc  = $post->post_content;

        $title = $title ?? __( 'Product table', 'multiple-products-to-cart-for-woocommerce' );
        $desc  = $desc ?? __( 'Product table shortcode details.', 'multiple-products-to-cart-for-woocommerce' );

        return array( 'ID' => $post_id, 'post_title' => $title, 'post_content' => $desc );
    }

    /**
     * Ajax search items for dorpdown combo-box
     */
    public static function ajax_itembox_search() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'search_box_nonce' ) ) {
            return '';
        }

        $search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
        $type   = isset( $_POST['type_name'] ) ? sanitize_text_field( wp_unslash( $_POST['type_name'] ) ) : '';
        $limit  = 50; // Limit the number of items.

        if ( 'cats' === $type ) {
            $args = array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,  // Set this to true if you only want categories with products.
                'name__like' => $search,  // Search by category name.
                'number'     => $limit,  // Limit the number of categories.
            );

            $product_categories = get_terms( $args );
            $categories         = array();

            if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                foreach ( $product_categories as $category ) {
                    $categories[] = array(
                        'id'   => $category->term_id,
                        'name' => $category->name,
                    );
                }
            }

            wp_send_json( $categories );
        } else {
            $args = array(
                's'              => $search,
                'post_type'      => 'product',
                'posts_per_page' => $limit,
            );

            $query    = new WP_Query( $args );
            $products = array();

            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $products[] = array(
                        'id'   => get_the_ID(),
                        'name' => get_the_title(),
                    );
                }
            }

            wp_reset_postdata();
            wp_send_json( $products );
        }

        wp_send_json( array() );
    }



    public static function update_shortcode(){
        if( !is_admin() ) return;

        $update_notice = self::save_shortcode();
        $delete_notice = self::delete_shortcode();

        if( empty( $update_notice ) ) return $delete_notice;
        return $update_notice;
    }

    /**
     * Save shortcode table item
     */
    public static function save_shortcode() {
        if ( ! isset( $_POST ) || empty( $_POST ) ) {
            return array();
        }

        // verify nonce.
        if ( ! isset( $_POST['mpc_opt_sc'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_opt_sc'] ) ), 'mpc_opt_sc_save' ) ) {
            return array();
        }

        self::set();

        $update_notice = array();
        $shortcode     = self::prepare_shortcode();
        $title         = '';
        $desc          = '';
        if ( isset( $_POST['shortcode_title'] ) && ! empty( $_POST['shortcode_title'] ) ) {
            $title = sanitize_text_field( wp_unslash( $_POST['shortcode_title'] ) );
        }

        if ( isset( $_POST['shortcode_desc'] ) && ! empty( $_POST['shortcode_desc'] ) ) {
            $desc = sanitize_text_field( wp_unslash( $_POST['shortcode_desc'] ) );
        }

        $post_id  = '';
        $table_id = self::get_table_id_from_url();
        $flag     = '';
        if ( empty( $table_id ) ) {
            $flag = 'add';
        } else {
            $post_id = self::get_cpt_post_id( $table_id );
            if ( empty( $post_id ) ) {
                $flag = 'add';
            }
        }

        // Legacy delete code.
        if ( ! empty( $table_id ) && 'add' === $flag ) {
            delete_option( 'mpcasc_code' . $table_id );
        }

        if ( 'add' === $flag ) {
            $post_id = wp_insert_post(
                array(
                    'post_type'      => 'mpc_product_table',
                    'post_title'     => ! empty( $title ) ? $title : __( 'Product Table', 'multiple-products-to-cart-for-woocommerce' ),
                    'post_content'   => $desc,
                    'post_status'    => 'publish',
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                )
            );

            if ( empty( $title ) ) {
                $title = ! empty( $title ) ? $title : __( 'Product Table', 'multiple-products-to-cart-for-woocommerce' );

                $a = wp_update_post(
                    array(
                        'ID'         => $post_id,
                        'post_title' => $title . ' #' . $post_id,
                    )
                );
            }
        } else {
            // update shortcode.
            $a = wp_update_post(
                array(
                    'ID'           => $post_id,
                    'post_title'   => ! empty( $title ) ? $title : __( 'Product Table #', 'multiple-products-to-cart-for-woocommerce' ) . $post_id,
                    'post_content' => $desc,
                )
            );

            $update_notice = array(
                'status'  => 'updated',
                'message' => __( 'Shortcode updated.', 'multiple-products-to-cart-for-woocommerce' ),
            );
        }

        if ( ! empty( $post_id ) ) {
            if ( empty( $table_id ) ) {
                $table_id = $post_id;
            }

            if ( 'add' === $flag ) {
                add_post_meta( $post_id, 'shortcode', $shortcode );
                add_post_meta( $post_id, 'table_id', $table_id );
            } else {
                update_post_meta( $post_id, 'shortcode', $shortcode );
                update_post_meta( $post_id, 'table_id', $table_id );
            }
        }

        if ( 'add' === $flag ) {
            // redirect to url.
            $page  = admin_url( 'admin.php?page=mpc-shortcode' );
            $nonce = wp_create_nonce( 'mpc_option_tab' );
            $url   = $page . '&tab=all-tables&mpctable=' . esc_attr( $table_id ) . '&nonce=' . esc_attr( $nonce ) . '&created=yes';

            header( 'Location: ' . $url );
            exit();
        }

        self::log( 'shortcode updated' );
        return $update_notice;
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

    /**
     * Delete shortcode table item
     */
    public static function delete_shortcode() {
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
            return array();
        }

        // get table index.
        $table_id = self::get_table_id_from_url();
        if ( empty( $table_id ) || ! isset( $_GET['mpcscdlt'] ) ) {
            return array();
        }

        $delete_notice = array();
        $cpt_id        = self::get_cpt_post_id( $table_id );
        if ( ! empty( $cpt_id ) ) {
            wp_delete_post( $cpt_id, true );

            $delete_notice = array(
                'status'  => 'deleted',
                'message' => __( 'Shortcode deleted.', 'multiple-products-to-cart-for-woocommerce' ),
            );
        }

        self::legacy_delete( $table_id );

        return $delete_notice;
    }



    /**
     * Prepare shortcode post data
     */
    public static function prepare_shortcode(){
        // vefiry nonce again.
        if ( ! isset( $_POST['mpc_opt_sc'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_opt_sc'] ) ), 'mpc_opt_sc_save' ) ) {
            return '';
        }

        $code = '';
        foreach ( self::$data['fields']['new_table'] as $section ) {
            foreach ( $section['fields'] as $fld ) {
                if ( in_array( $fld['key'], array( 'shortcode_title', 'shortcode_desc' ), true ) ) continue;

                $key  = $fld['key'];
                $item = '';

                if ( isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ) {
                    $val = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );

                    // add variation column as it's a must have column.
                    if ( 'columns' === $fld['key'] && strpos( $val, 'variation' ) === false ) {
                        $val .= ! empty( $val ) ? ', variation' : 'variation';
                    }

                    // order attribute miss-match recovery.
                    if ( 'order' === $key ) {
                        if ( ! in_array( $val, array( 'desc', 'asc', 'custom' ), true ) ) {
                            $val = 'desc';
                        }
                    }

                    $item = $key . '="' . $val . '"';
                }

                if ( 'checkbox' === $fld['type'] ) {
                    $item = ! isset( $_POST[ $key ] ) ? 'false' : 'true';
                    $item = $key . '="' . $item . '"';
                }

                if ( empty( $item ) ) continue;

                if ( strlen( $code ) > 0 ) {
                    $code .= ' ' . $item;
                } else {
                    $code = $item;
                }
            }
        }

        return "[woo-multi-cart {$code}]";
    }

    /**
     * Get table id from the url
     */
    public static function get_table_id_from_url(){
        $table_id = '';

        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
            return '';
        }

        if ( isset( $_GET['mpctable'] ) ) {
            $table_id = (int) sanitize_key( wp_unslash( $_GET['mpctable'] ) );
        } elseif ( isset( $_GET['mpcscdlt'] ) ) {
            $table_id = (int) sanitize_key( wp_unslash( $_GET['mpcscdlt'] ) );
        }

        return $table_id;
    }

    public static function extract_shortcode_atts( $atts ){
        if( empty( $atts ) ) return array();

        $atts = self::parse_shortcode_atts( $atts );
        if(empty( $atts )) return array();

        return self::parse_boolean( $atts );
    }
    public static function parse_shortcode_atts( $code ){
        $code = str_replace( '[', '', $code );
        $code = str_replace( ']', '', $code );
        $code = str_replace( 'woo-multi-cart', '', $code );
        if ( empty( $code ) && strlen( $code ) < 10 ) {
            return array();
        }

        return shortcode_parse_atts( $code );
    }
    public static function parse_boolean( $atts ){
        $atts_ = array();
        foreach ( $atts as $name => $value ) {
            if ( false !== strpos( $value, 'true' ) ) {
                $atts_[ $name ] = true;
            } elseif ( false !== strpos( $value, 'false' ) ) {
                $atts_[ $name ] = false;
            } elseif ( is_numeric( $value ) ) {
                $atts_[ $name ] = (int) $value;
            } else {
                $atts_[ $name ] = $value;
            }
        }

        return $atts_;
    }



    /**
     * DEPRICATED !!!
     * Get old table shortcodes list
     */
    public static function get_legacy_tables() {
        $index = self::legacy_index( 'final_index' );
        if ( empty( $index ) || '' === $index ) {
            return array();
        }

        $shortcodes = array();

        $index = (int) $index;
        for ( $i = $index; $i > 0; $i-- ) {
            $code = get_option( 'mpcasc_code' . $i );
            if ( empty( $code ) || '' === $code ) continue;

            $shortcodes[] = array( 'ID' => $i, 'post_title' => '', 'post_content' => '' );
        }

        return $shortcodes;
    }

    /**
     * DEPRICATED !!!
     * Get old shortcode table id.
     *
     * @param string $return_type wheather return in-between empty table id or find a new one.
     */
    public static function legacy_index( $return_type ) {
        $index = (int) get_option( 'mpcasc_counter' );

        if ( ! empty( $index ) ) {
            $i          = 1;
            $_index     = 1;
            $empty_slot = 1;

            // at any given non-empty index, check 10 step ahead for safely finding index.
            while ( $i < ( $_index + 9 ) ) {
                // check if shortcode exists.
                $shortcode = get_option( 'mpcasc_code' . $i );

                if ( ! empty( $shortcode ) ) {
                    $_index = $i + 1;
                } elseif ( 1 === $empty_slot ) {
                    $empty_slot = $i;
                }

                ++$i;

                // fail save everything | as no one should have more than 250 saved shortcodes.
                if ( 250 === $i ) {
                    break;
                }
            }

            update_option( 'mpcasc_counter', $_index );

            if ( 'empty_slot' === $return_type ) {
                return $empty_slot;
            } elseif ( 'final_index' === $return_type ) {
                return $_index;
            }
        } else {
            update_option( 'mpcasc_counter', 1 );
            return 1;
        }
    }

    /**
     * DEPRICATED !!!
     * Delete old shortcode table
     *
     * @param int $table_id old shortcode table id.
     */
    public static function legacy_delete( $table_id ) {
        delete_option( 'mpcasc_code' . $table_id );
    }
}

MPC_Shortcode::init();
