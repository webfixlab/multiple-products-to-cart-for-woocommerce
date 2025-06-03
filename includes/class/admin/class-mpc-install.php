<?php
/**
 * Installation related functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * MPC Installation Class.
 */
class MPC_Inatall {
    /**
     * Static initialization function
     */
    public static function init(){
        add_filter( 'plugin_action_links_' . plugin_basename( MPC ), array( __CLASS__, 'plugin_action_links' ) );
        add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
    }

    /**
     * Plugin activation function. Fires once when activating.
     */
    public static function activate(){
        // runs only once, when activating the plugin.
        self::init_fields();
        flush_rewrite_rules();
    }

    

    /**
     * Initialize options with default value
     */
    public static function init_fields() {
        $init_fields = array(
            'mpc_show_title_dopdown'      => 'on',
            'wmc_show_pagination_text'    => 'on',
            'wmc_show_products_filter'    => 'on',
            'wmc_show_all_select'         => 'on',
            'wmca_show_reset_btn'         => 'on',
            'wmca_show_header'            => 'on',
            'wmc_redirect'                => 'ajax',
            'mpc_show_total_price'        => 'on',
            'mpc_show_add_to_cart_button' => 'on',
            'mpc_add_to_cart_checkbox'    => 'on',
            'mpc_protitle_font_size'      => 16,
        );

        foreach ( $init_fields as $key => $def ) {
            if ( get_option( $key ) ) {
                update_option( $key, $def );
            } else {
                add_option( $key, $def );
            }
        }
    }

    /**
     * Display action links on the plugin screen
     *
     * @param array $links Plugin action links.
     *
     * @return array
     */
    public static function plugin_action_links( $links ){
        global $mpc__;

        $action_links             = array();
        $action_links['settings'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=mpc-settings' ) ),
            __( 'Settings', 'multiple-products-to-cart-for-woocommerce' )
        );

        if ( ! in_array( 'activated', explode( ' ', $mpc__['prostate'] ), true ) ) {
            $action_links['premium'] = sprintf(
                '<a href="%s" style="color: #FF8C00;font-weight: bold;text-transform: uppercase;">%s</a>',
                esc_url( $mpc__['prolink'] ),
                __( 'Get PRO', 'multiple-products-to-cart-for-woocommerce' )
            );
        }

        return array_merge( $action_links, $links );
    }

    /**
     * Display row meta on plugin screen.
     *
     * @param mixed $links Plugin row meta.
     * @param mixed $file  Plugin base file.
     *
     * @return array
     */
    public static function plugin_row_meta( $links, $file ){
        global $mpc__;

        // if it's not mpc plugin, return.
        if ( plugin_basename( MPC ) !== $file ) {
            return $links;
        }

        $row_meta            = array();
        $row_meta['apidocs'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url( $mpc__['plugin']['request_quote'] ),
            __( 'Support', 'multiple-products-to-cart-for-woocommerce' )
        );

        return array_merge( $links, $row_meta );
    }
}

MPC_Inatall::init();
