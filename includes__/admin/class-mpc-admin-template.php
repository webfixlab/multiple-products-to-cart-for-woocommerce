<?php
/**
 * Plugin admin page template functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Template' ) ) {

	/**
	 * Plugin admin template class
	 */
	class MPC_Admin_Template {

        public static function page_title( $contact_us_url ){
            ?>
            <div id="mpcdp_logo"><?php echo esc_html__( 'Multiple Products to Cart', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
            <div id="mpcdp_customizer_wrapper"></div>
            <div id="mpcdp_toolbar_icons">
                <a class="mpcdp-tippy" target="_blank" href="<?php echo esc_url( $contact_us_url ); ?>" data-tooltip="<?php echo esc_html__( 'Support', 'multiple-products-to-cart-for-woocommerce' ); ?>">
                <span class="tab_icon dashicons dashicons-email"></span>
                </a>
            </div>
            <?php
        }

        /**
         * Display nav item
         *
         * @param array $data Navigation item data.
         */
        public static function navigation_item( $data ){
            ?>
            <a href="<?php echo esc_url( $data['url'] ); ?>">
                <div class="mpcdp_settings_tab_control <?php echo esc_attr( $data['class'] ); ?>" data-tab="<?php echo esc_attr( $data['slug'] ); ?>">
                    <span class="dashicons <?php echo esc_attr( $data['icon'] ); ?>"></span>
                    <span class="label">
                        <?php echo esc_html( $data['tab'] ); ?>
                    </span>
                </div>
            </a>
            <?php
        }

        /**
		 * Display admin settings page save button(s)
		 */
		public static function save_btn( $settings_tab ) {
            if( in_array( $settings_tab, array( 'all-tables', 'export', 'import' ), true ) ) {
                return;
            } 

            $table_id = isset( $_GET['mpctable'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ? sanitize_key( wp_unslash( $_GET['mpctable'] ) ) : '';

            $prefix = !empty( $table_id ) ? __( 'Update Table', 'multiple-products-to-cart-for-woocommerce' ) : ( 'new-table' === $settings_tab ? __( 'Create Table', 'multiple-products-to-cart-for-woocommerce' ) : __( 'Save Changes', 'multiple-products-to-cart-for-woocommerce' ) );
            ?>
            <div class="mpcdp_settings_submit">
                <div class="submit">
                    <button class="mpcdp_submit_button">
                        <div class="save-text">
                            <?php printf(
                                // translators: %s: button prefix.
                                __( '%s Table', '' ),
                                esc_html( $prefix )
                            ); ?>
                        </div>
                        <div class="save-text save-text-mobile"><?php echo esc_html( explode( ' ', $prefix )[0] ); ?></div>
                    </button>
                </div>
            </div>
            <?php
		}
	}
}
