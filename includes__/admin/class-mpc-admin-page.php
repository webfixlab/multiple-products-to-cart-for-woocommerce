<?php
/**
 * Plugin admin page functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Page' ) ) {

	/**
	 * Plugin admin loader class
	 */
	class MPC_Admin_Page {

        /**
         * Plugin core data
         * @var array
         */
        private static $plugin_data;

        /**
         * Pro plugin status
         * @var string
         */
        private static $pro_state;

        /**
         * Settings page tab
         * @var string
         */
        private static $settings_tab;

		/**
		 * Plugin installation handler
         *
         * @param string $tab       Settings tab.
         * @param string $pro_state Pro plugin status.
		 */
		public static function init( $tab, $pro_state ) {
            if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// show error/update messages.
			settings_errors( 'wporg_messages' );

            self::$pro_state    = $pro_state;
            self::$plugin_data  = MPC_Core_Data::get_plugin();

            // get settings tab.
            self::$settings_tab = empty( $tab ) ? self::get_tab() : $tab;

            self::settings_form();
		}

        /**
		 * Get current admin settings tab
		 */
		private static function get_tab(){
            $tab = isset( $_GET['tab'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'new-table';

            return 'new-table' === $tab || 'all-tables' === $tab ? 'general-settings' : $tab;
		}

        /**
         * Display admin settings form
         */
        private static function settings_form(){
            ?>
            <form method="post" action="" id="mpcdp_settings_form" enctype="multipart/form-data">
                <div id="mpcdp_settings" class="mpcdp_container">
                    <?php self::settings_form_content(); ?>
                </div>
                <?php wp_nonce_field( 'mpc_admin_settings_save', 'mpc_admin_settings' ); ?>
            </form>
            <div id="mpcpop" class="mpc-popup">
                <div class="image-wrap">
                    <?php MPC_Admin_Template::popup( self::$plugin_data['pro_plugin_url'] ); ?>
                </div>
            </div>
            <?php
        }

        /**
         * Display settings form content
         */
        private static function settings_form_content(){
            ?>
            <div id="mpcdp_settings_page_header">
                <?php MPC_Admin_Template::page_title( self::$plugin_data['contact_us_url'] ); ?>
            </div>
            <div class="mpcdp_row">
                <div class="col-md-3" id="left-side">
                    <?php self::navigation(); ?>
                </div>
                <div class="col-md-6" id="middle-content">
                    <?php self::main_content(); ?>
                </div>
                <div id="right-side">
                    <div class="mpcdp_settings_promo">
                        <div id="wfl-promo">
                            <?php MPC_Admin_Template::sidebar( self::$plugin_data ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        private static function navigation(){
            ?>
            <div class="mpcdp_settings_sidebar" data-sticky-container="" style="position: relative;">
                <div class="mpcdp_sidebar_tabs">
                    <div class="inner-wrapper-sticky">
                        <?php self::navigation_items(); ?>
                        <?php MPC_Admin_Template::save_btn( self::$settings_tab ); ?>
                    </div>
                </div>
            </div>
            <?php
        }
        private static function navigation_items(){
            $nonce = wp_create_nonce( 'mpc_option_tab' );

			foreach ( MPC_Core_Data::navigation_data() as $slug => $nav ) {
                $page_slug = 'all-tables' === $slug ? 'mpc-shortcodes' : ( 'new-table' === $slug ? 'mpc-shortcode' : 'mpc-settings' );
                
                $page_url  = admin_url( "admin.php?php={$page_slug}" );
                $page_url  = in_array( $slug, array( 'all-tables', 'new-table' ), true ) ? $page_url : "{$page_url}&tab={$slug}&nonce={$nonce}";

                MPC_Admin_Template::navigation_item( array_merge( $nav, array(
                    'slug'  => $slug,
                    'class' => $slug === self::$settings_tab ? 'active' : '',
                    'url'   => $page_url
                ) ) );
			}
        }

        private static function main_content(){
            ?>
            <div class="mpcdp_settings_content">
                <div id="<?php esc_attr( self::$settings_tab ); ?>" class="hidden mpcdp_settings_tab active" data-tab="<?php esc_attr( self::$settings_tab ); ?>" style="display: block;">
                    <?php self::navigate_settings(); ?>
                </div>
            </div>
            <?php
        }
        private static function navigate_settings(){
            $tab = self::$settings_tab;
			if ( in_array( $tab, array( 'new-table', 'all-tables', 'column-sorting', 'import', 'export' ), true ) ) {
				if ( file_exists( MPC_PATH . 'templates/admin/' . esc_attr( $tab ) . '.php' ) ) {
					include MPC_PATH . 'templates/admin/' . esc_attr( $tab ) . '.php';
				}
				return;
			}

            $fields = array();
            if( 'general-settings' === $tab ) {
                $fields = MPC_Core_Data::get_general_settings();
            } elseif( 'labels' === $tab ) {
                $fields = MPC_Core_Data::get_labels();
            } elseif( 'appearence' === $tab ) {
                $fields = MPC_Core_Data::get_labels();
            }

			if ( empty( $fields ) ) {
				return;
			}
            
            // self::process_from_submit( $fields );

			foreach ( $fields as $section ) {
                ?>
                <div class="mpcdp_settings_section">
                    <?php printf( '<div class="mpcdp_settings_section_title">%s</div>', esc_html( $section['section'] ) ); ?>
                    <?php self::display_section( $section ); ?>
                </div>
                <?php
			}
        }

        private static function display_section( $section ){
            foreach ( $section['fields'] as $fld ) {
                // $this->saving_field( $fld );
                // $this->field_settings( $fld );
            }
        }
	}
}
