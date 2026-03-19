<?php
/**
 * Plugin admin migration (import-export) template functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Migration_Template' ) ) {

	/**
	 * Plugin admin template class
	 */
	class MPC_Admin_Migration_Template {

        /**
         * Settings page tab
         * @var string
         */
        private static $settings_tab;

        /**
         * Pro plugin status
         * @var string
         */
        private static $pro_state = '';

        /**
         * Export settings and tables template
         */
        public static function render_template( $tab, $pro_state ){
            self::$tab       = $tab;
            self::$pro_state = $pro_state;

            self::progress_notice();
            ?>
            <div class="mpcdp_settings_section">
                <?php self::page_title(); ?>
                <div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
                    <div class="mpcdp_settings_option visible">
                        <div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
                            <?php self::page_desc(); ?>
                        </div>
                    </div>
                </div>
                <div class="mpcdp_settings_toggle mpcdp_container">
                    <div class="mpcdp_settings_option visible">
                        <div class="mpcdp_row">
                            <?php self::migration_buttons(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            wp_nonce_field( "mpc_{$tab}_nonce", "mpc_{$tab}" );
        }

        /**
         * Display export progress notice
         * 
         * 
         * NOT COMPLETE !!!
         */
        private static function progress_notice(){
            do_action( 'mpc_pro_export' );



            do_action( 'mpc_migration_progress_notice' );
            return;
            ?>
            <div class="mpcdp_settings_toggle mpcdp_container" id="export-success">
                <div class="mpcdp_settings_option visible">
                    <div class="mpcdp_settings_option_field_theme_customizer first_customizer_field mpc-export-notice">
                        <span class="theme_customizer_icon dashicons dashicons-saved"></span>
                        <div class="mpcdp_settings_option_description">
                            <div class="mpcdp_option_label"><?php echo __( 'Please wait while we are getting your file ready for download...', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Display page title
         */
        private static function page_title(){
            ?>
            <div class="mpcdp_settings_section_title">
                <?php echo 'export' === self::$settings_tab ? __( 'Export Settings', 'multiple-products-to-cart-for-woocommerce' ) : __( 'Import Settings', 'multiple-products-to-cart-for-woocommerce' ); ?>
            </div>
            <?php
        }

        /**
         * Display export page desctiption
         */
        private static function page_desc(){
            ?>
            <span class="theme_customizer_icon dashicons dashicons-<?php echo 'export' === self::$settings_tab ? 'download' : 'upload'; ?>"></span>
            <div class="mpcdp_settings_option_description">
                <?php self::pro_ribbon(); ?>
                <div class="mpcdp_option_label"><?php echo wp_kses_post(
                    sprintf(
                        // translators: %s is settings tab.
                        __( '%s MPC Tables and Settings', 'multiple-products-to-cart-for-woocommerce' ),
                        'export' === self::$settings_tab ? __( 'Export', 'multiple-products-to-cart-for-woocommerce' ) : __( 'Import', 'multiple-products-to-cart-for-woocommerce' )
                    )
                ); ?></div>
                <div class="mpcdp_option_description">
                    <?php self::tab_desc(); ?>
                    <?php self::only_for_pro_notice(); ?>
                </div>
            </div>
            <?php
        }

        /**
		 * Settings field PRO ribbon
		 */
		private static function pro_ribbon() {
			if( ! empty( self::$pro_state ) ) {
                return;
            }
			?>
			<div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">
				<?php echo __( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
			</div>
			<?php
		}

        /**
         * Display details of current tab
         */
        private static function tab_desc(){
            if( 'export' === self::$settings_tab ){
                ?>
                <br>
                <?php echo __( 'Click on `Export` to export tables and settings.', 'multiple-products-to-cart-for-woocommerce' ); ?>
                <br><br>
                <?php echo __( 'You will find either a `mpc_export.json` or an enumarated `mpc_export(1).json` file in your `Downloads` folder. You can use this file to import it later or to other websites.', 'multiple-products-to-cart-for-woocommerce' ); ?>
                <br><br>
                <?php
            }else{
                ?>
                <br>
                <?php echo __( 'The file name will be `mpc_export.json` or enumarated `mpc_export(1).json`.', 'multiple-products-to-cart-for-woocommerce' ); ?>
                <br><br>
                <?php echo __( 'Choose the .json file and click on `Import`. This will import `Multiple products to cart for WooCommerce` tables and settings.', 'multiple-products-to-cart-for-woocommerce' ); ?>
                <br><br>
                <?php
            }
        }

        /**
         * Notice to inform the provided key feature is only avialable in Pro plugin
         */
        private static function only_for_pro_notice(){
            if( ! empty( self::$pro_state ) ) {
                return;
            }

            echo wp_kses_post(
                sprintf(
                    // translators: %s is the feature name.
                    __( 'The %s feature is only available for PRO plugin.', 'multiple-products-to-cart-for-woocommerce' ),
                    esc_html( self::$settings_tab )
                )
            );
        }

        /**
         * Display export buttons
         */
        private static function migration_buttons(){
            ?>
            <div class="mpcdp_settings_option_description col-md-6">
                <div class="mpcdp_option_label"><?php echo 'export' === self::$settings_tab ? __( 'Export', 'multiple-products-to-cart-for-woocommerce' ) : __( 'Import', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                <div class="mpcdp_option_description">
                    <?php
                        echo wp_kses_post(
                            sprintf(
                                // translators: %s is settings tab.
                                __( '%s MPC Tables and Settings', 'multiple-products-to-cart-for-woocommerce' ),
                                'export' === self::$settings_tab ? __( 'Export', 'multiple-products-to-cart-for-woocommerce' ) : __( 'Import', 'multiple-products-to-cart-for-woocommerce' )
                            )
                        );
                    ?>
                </div>
            </div>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <div class="mpcdp_settings_submit mpc-file">
                    <div class="submit">
                        <button
                            id="mpc-<?php echo esc_attr( self::$settings_tab ); ?>"
                            class="mpcdp_submit_button <?php echo empty( self::$pro_state ) ? 'mpcex-disabled' : ''; ?>"
                            title="<?php echo 'export' === self::$settings_tab ? __( 'Export', 'multiple-products-to-cart-for-woocommerce' ) : __( 'Import', 'multiple-products-to-cart-for-woocommerce' ); ?>">
                            <div class="save-text"><?php 'export' === self::$settings_tab ? __( 'Export settings', 'multiple-products-to-cart-for-woocommerce' ) : __( 'Import settings', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                            <div class="save-text save-text-mobile"><?php 'export' === self::$settings_tab ? __( 'Export', 'multiple-products-to-cart-for-woocommerce' ) : __( 'Import', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                        </button>
                    </div>
                </div>
            </div>
            <?php
        }
	}
}
