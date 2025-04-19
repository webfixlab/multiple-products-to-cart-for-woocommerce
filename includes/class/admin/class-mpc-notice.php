<?php
/**
 * Plugin notice handler class
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * MPC Admin Notice Handler Class.
 */
class MPC_Notice {
    public static function init(){
        add_action( 'init', array( __CLASS__, 'admin_notice' ) );
        add_action( 'admin_head', array( __CLASS__, 'remove_admin_notice' ) );
    }

    /**
     * Add plugin related notices.
     */
    public static function admin_notice() {
        global $mpc__;

        if ( isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_rating_nonce' ) ) {
            if ( isset( $_GET['mpca_rate_us'] ) ) {
                $task = sanitize_key( wp_unslash( $_GET['mpca_rate_us'] ) );

                if ( 'done' === $task ) {
                    // never show this notice again.
                    update_option( 'mpca_rate_us', 'done' );
                } elseif ( 'cancel' === $task ) {
                    // show this notice in a week again.
                    update_option( 'mpca_rate_us', gmdate( 'Y-m-d' ) );
                }
            }
        } elseif ( isset( $_GET['pinonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['pinonce'] ) ), 'mpc_pro_info_nonce' ) ) {
            if ( isset( $_GET['mpca_notify_pro'] ) ) {
                if ( 'cancel' === sanitize_key( wp_unslash( $_GET['mpca_notify_pro'] ) ) ) {
                    update_option( 'mpca_notify_pro', gmdate( 'Y-m-d' ) );
                }
            }
        } else {
            if ( MPC_Admin_Helper::date_difference( 'mpca_rate_us', $mpc__['plugin']['notice_interval'], 'done' ) ) {
                // show notice to rate us after 15 days interval.
                add_action( 'admin_notices', array( __CLASS__, 'ask_feedback_notice' ) );
            }

            $proinfo = get_option( 'mpca_notify_pro' );
            if ( empty( $proinfo ) || '' === $proinfo ) {
                add_action( 'admin_notices', array( __CLASS__, 'pro_notice' ) );
            } elseif ( MPC_Admin_Helper::date_difference( 'mpca_notify_pro', $mpc__['plugin']['notice_interval'], '' ) ) {
                // show notice to inform about pro version after 15 days interval.
                add_action( 'admin_notices', array( __CLASS__, 'pro_notice' ) );
            }
        }
    }

    public static function remove_admin_notice(){
        global $mpc__;
    
        if( ! MPC_Admin_Helper::in_screen() ) return;
    
        // Buffer only the notices.
        ob_start();
    
        do_action( 'admin_notices' );
    
        $content = ob_get_contents();
        ob_get_clean();
    
        // Keep the notices in global $mpc__.
        array_push( $mpc__['notice'], $content );
    
        // Remove all admin notices as we don't need to display in it's place.
        remove_all_actions( 'admin_notices' );
    }


    /**
     * WooCommerce not active notice. WooCommerce MUST be active before activating this plugin.
     */
    public static function wc_missing_notice() {
        global $mpc__;

        $plugin = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url( $mpc__['plugin']['free_mpc_url'] ),
            __( 'Multiple Products to Cart – WooCommerce Product Table', 'multiple-products-to-cart-for-woocommerce' )
        );

        $wc = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url( $mpc__['plugin']['woo_url'] ),
            __( 'WooCommerce', 'multiple-products-to-cart-for-woocommerce' )
        );

        ?>
        <div class="error">
            <p>
                <?php
                    echo wp_kses_post(
                        sprintf(
                            // translators: %1$s mpc plugin with link, %2$s: woocommerce with link.
                            __( '%1$s plugin can not be active. Please activate the following plugin first - %2$s', 'multiple-products-to-cart-for-woocommerce' ),
                            wp_kses_post( $plugin ),
                            wp_kses_post( $wc )
                        )
                    );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Notice for asking user feedback
     */
    public static function ask_feedback_notice() {
        global $mpc__;

        // get current page.
        $page = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        // dynamic extra parameter adding beore adding new url parameters.
        $page .= strpos( $page, '?' ) !== false ? '&' : '?';

        $nonce = wp_create_nonce( 'mpc_rating_nonce' );

        $plugin = sprintf(
            '<strong><a href="%s">%s</a></strong>',
            esc_url( $mpc__['plugin']['review_link'] ),
            __( 'Multiple Products to Cart for WooCommerce', 'multiple-products-to-cart-for-woocommerce' )
        );

        $review = sprintf(
            '<strong><a href="%s">%s</a></strong>',
            esc_url( $mpc__['plugin']['review_link'] ),
            __( 'WordPress.org', 'multiple-products-to-cart-for-woocommerce' )
        );

        ?>
        <div class="notice notice-info is-dismissible">
            <h3><?php echo esc_html__( 'Multiple Products to Cart for WooCommerce', 'multiple-products-to-cart-for-woocommerce' ); ?></h3>
            <p>
                <?php
                    echo wp_kses_post(
                        sprintf(
                            // translators: %1$s: plugin name with url, %2$s: WordPress and review url.
                            __( 'Excellent! You\'ve been using %1$s for a while. We\'d appreciate if you kindly rate us on %2$s', 'multiple-products-to-cart-for-woocommerce' ),
                            wp_kses_post( $plugin ),
                            wp_kses_post( $review )
                        )
                    );
                ?>
            </p>
            <p>
                <a href="<?php echo esc_url( $mpc__['plugin']['review_link'] ); ?>" class="button-primary">
                    <?php echo esc_html__( 'Rate it', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </a> <a href="<?php echo esc_url( $page ); ?>mpca_rate_us=done&nonce=<?php echo esc_attr( $nonce ); ?>" class="button">
                    <?php echo esc_html__( 'Already Did', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </a> <a href="<?php echo esc_url( $page ); ?>mpca_rate_us=cancel&nonce=<?php echo esc_attr( $nonce ); ?>" class="button">
                    <?php echo esc_html__( 'Cancel', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * PRO plugin advertising notice
     */
    public static function pro_notice() {
        global $mpc__;

        // get current page.
        $page = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        // dynamic extra parameter adding beore adding new url parameters.
        $page .= strpos( $page, '?' ) !== false ? '&' : '?';

        $pro_feature = sprintf(
            '<strong>%s</strong>',
            __( '10+ PRO features available!', 'multiple-products-to-cart-for-woocommerce' )
        );

        $pro_link = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url( $mpc__['prolink'] ),
            __( 'PRO features here', 'multiple-products-to-cart-for-woocommerce' )
        );

        ?>
        <div class="notice notice-warning is-dismissible">
            <h3><?php echo esc_html__( 'Multiple Products to Cart for WooCommerce PRO', 'multiple-products-to-cart-for-woocommerce' ); ?></h3>
            <p>
                <?php
                    echo wp_kses_post(
                        sprintf(
                            // translators: %1$s: pro features number, %2$s: pro feature list url.
                            __( '%1$s Supercharge Your WooCommerce Stores with our light, fast and feature-rich version. See all %2$s', 'multiple-products-to-cart-for-woocommerce' ),
                            wp_kses_post( $pro_feature ),
                            wp_kses_post( $pro_link )
                        )
                    );
                ?>
            </p>
            <p>
                <a href="<?php echo esc_url( $mpc__['prolink'] ); ?>" class="button-primary">
                    <?php echo esc_html__( 'Get PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </a> <a href="<?php echo esc_url( $page ); ?>mpca_notify_pro=cancel&pinonce=<?php echo esc_attr( wp_create_nonce( 'mpc_pro_info_nonce' ) ); ?>" class="button">
                    <?php echo esc_html__( 'Cancel', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </a>
            </p>
        </div>
        <?php
    }
}

MPC_Notice::init();
