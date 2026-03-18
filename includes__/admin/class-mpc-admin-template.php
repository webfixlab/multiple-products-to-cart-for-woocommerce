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
        /**
         * Show admin notices
         */
        public static function saved_settings_notice(){
			?>
			<div class="mpc-notice mpcdp_settings_toggle mpcdp_container">
				<div class="mpcdp_settings_option visible">
					<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
						<span class="theme_customizer_icon dashicons dashicons-saved"></span>
						<div class="mpcdp_settings_option_description">
							<div class="mpcdp_option_label"><?php echo __( 'Settings Saved', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php
        }

        /**
		 * Display shortcode table item
		 *
		 * @param int    $id    table id.
		 * @param string $title table title.
		 * @param string $desc  table description.
		 */
		public static function display_shortcode( $id, $title, $desc ) {
			?>
			<div class="mpcdp_settings_toggle mpcdp_container mpc-shortcode">
				<div class="mpcdp_settings_option visible">
					<div class="mpcdp_row">
						<?php self::display_shortcode_title( $title, $desc ); ?>
					</div>
					<div class="mpcdp_row">
						<?php self::display_shortcode_details( $id ); ?>
					</div>
				</div>
			</div>
			<?php
		}
        private static function display_shortcode_title( $title, $desc ){
            ?>
            <div class="mpcdp_settings_option_description col-md-12">
                <div class="mpcdp_option_label"><?php echo esc_html( $title ); ?></div><div class="mpcdp_option_description">
                    <?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
                </div>
            </div>
            <?php
        }
        private static function display_shortcode_details( $id ){
            $edit   = admin_url( 'admin.php?page=mpc-shortcode' );
			$delete = admin_url( 'admin.php?page=mpc-shortcodes' );
			$nonce  = wp_create_nonce( 'mpc_option_tab' );
            ?>
            <div class="mpcdp_settings_option_description col-md-12">
                <textarea class="mpc-opt-sc" readonly >[woo-multi-cart table="<?php echo esc_attr( $id ); ?>"]</textarea>
            </div>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-4 mpc-sc-btns">
                <span class="mpc-opt-sc-btn copy">
                    <span class="dashicons dashicons-admin-page"></span>
                    <span class="mpc-sc-label"><?php echo __( 'Copy', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
                </span>
                <a class="mpc-opt-sc-btn edit" href="<?php echo esc_url( $edit . '&tab=all-tables&mpctable=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">
                    <span class="dashicons dashicons-welcome-write-blog"></span>
                    <span class="mpc-sc-label"><?php echo __( 'Edit', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
                </a>
                <a class="mpc-opt-sc-btn delete" href="<?php echo esc_url( $delete . '&tab=all-tables&mpcscdlt=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">
                    <span class="dashicons dashicons-trash"></span>
                    <span class="mpc-sc-label"><?php echo __( 'Delete', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
                </a>
            </div>
            <?php
        }
        public static function no_shortcode_notices(){
            $link = '<a href="' . esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ) . '">' . __( 'here', 'multiple-products-to-cart-for-woocommerce' ) . '</a>';
			?>
			<div class="mpcdp_settings_toggle mpcdp_container" style="margin-top: 30px;">
				<div class="mpcdp_settings_option visible">
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-6">
							<div class="mpcdp_option_label"><?php echo esc_html__( 'No shortcodes found.', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
							<div class="mpcdp_option_description"><?php printf(
                                // translators: %s: new product table crate link.
                                __( 'Create a product table shortcode %s.', 'multiple-products-to-cart-for-woocommerce' ),
                                wp_kses_post( $link )
                            );?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
        }

        public static function sidebar( $plugin_data ){
            ?>
            <div class="sidebar_top">
                <h2><?php echo esc_html__( 'Boost your tables to the next level', 'multiple-products-to-cart-for-woocommerce' ); ?></h2>
                <div class="tagline_side">
                    <?php
                        echo wp_kses_post(
                            sprintf(
                                // translators: %1$s: line brake, %2$s: line brake.
                                __( 'Popular PRO features: Product category dropdown, AJAX-powered search, 4 additional columns, and subscription product support. Upgrade to the PRO version to unlock the full power of the plugin!', 'multiple-products-to-cart-for-woocommerce' )
                            )
                        );
                        ?>
                </div>
                <div><a href="<?php echo esc_url( $plugin_data['pro_plugin_url'] ); ?>" target="_blank"><?php echo esc_html__( 'Get PRO license now', 'multiple-products-to-cart-for-woocommerce' ); ?></a></div>
            </div>
            <div class="site-intro">
                <h2><?php echo esc_html__( 'Missing any features? No worries!', 'multiple-products-to-cart-for-woocommerce' ); ?></h2>
                <a href="https://webfixlab.com/wordpress-offer/" target="_blank"><?php echo esc_html__( 'Customize for $99 only', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
            </div>
            <div class="mpca_sidebar">
                <h2><?php echo esc_html__( 'Dedicated Support Team', 'multiple-products-to-cart-for-woocommerce' ); ?></h2>
                <div class="tagline_side">
                    <?php echo esc_html__( 'Our support is what makes us No.1. We are available round the clock for any support.', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </div>
                <div>
                    <a href="<?php echo esc_url( $plugin_data['contact_us_url'] ); ?>" target="_blank">
                        <?php echo esc_html__( 'Contact us', 'multiple-products-to-cart-for-woocommerce' ); ?>
                    </a>
                </div>
            </div>
            <?php
        }
        public static function popup( $pro_plugin_url ){
            ?>
            <span class="mpcpop-close dashicons dashicons-no"></span>
            <div class="mpc-pro-tag">PRO</div>
            <div class="mpc-focus">
                <?php echo sprintf(
                    // translators: %s: HTML skeleton to inquired feature.
                    __( 'Please upgrade to get %s and other advanced features.', 'multiple-products-to-cart-for-woocommerce' ),
                    '<span></span>'
                ); ?>
            </div>
            <div class="mpcex-features">
                <p><?php echo esc_html__( 'Unlock advanced features like custom columns for different tables, support for more product types, and an \'Add to cart\' button with the PRO version. These tools are designed to streamline your workflow, enhance your experience, and boost your sales. We\'re committed to delivering the best solutions for you, 24/7.', 'multiple-products-to-cart-for-woocommerce' ); ?> <a href="<?php echo esc_url( $pro_plugin_url ); ?>" target="_blank"><?php echo esc_html__( 'Read more', 'multiple-products-to-cart-for-woocommerce' ); ?></a></p>
            </div>
            <a class="mpc-get-pro" href="<?php echo esc_url( $pro_plugin_url ); ?>" target="_blank"><?php echo esc_html__( 'Upgrade Now', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
            <?php
        }
	}
}
