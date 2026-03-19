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

            MPC_Admin_Save_Settings::init( $tab, $pro_state );

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

        /**
         * Render all admin settings page menues
         */
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

        /**
         * Display menu item
         */
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

        /**
         * Display settings page main content
         */
        private static function main_content(){
            ?>
            <div class="mpcdp_settings_content">
                <div id="<?php esc_attr( self::$settings_tab ); ?>" class="hidden mpcdp_settings_tab active" data-tab="<?php esc_attr( self::$settings_tab ); ?>" style="display: block;">
                    <?php self::navigate_settings(); ?>
                </div>
            </div>
            <?php
        }

        /**
         * Navigate settings based on given tab
         */
        private static function navigate_settings(){
            $tab = self::$settings_tab;

            // load settings template for these tabs.
            if( 'all-tables' === $tab ){
                self::display_all_tables();
                return;
            }elseif( 'new-table' === $tab ){
                self::display_new_table();
                return;
            }elseif( 'column-sorting' === $tab ){
                MPC_Admin_Template::column_sorting( self::$pro_state );
                return;
            }elseif( 'export' === $tab || 'import' === $tab ){
                MPC_Admin_Migration_Template::render_template( $tab, self::$pro_state );
                return;
            }

            $fields = array(); // get settings input fields.
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

			foreach ( $fields as $section ) {
                ?>
                <div class="mpcdp_settings_section">
                    <?php printf( '<div class="mpcdp_settings_section_title">%s</div>', esc_html( $section['section'] ) ); ?>
                    <?php self::display_settings_section( $section ); ?>
                </div>
                <?php
			}
        }

        private static function display_all_tables(){
            $has_shortcode = false; // has any shortcodes saved;
            ?>
            <div class="mpcdp_settings_section">
                <div class="mpcdp_settings_section_title">
                    <?php echo __( 'All Product Tables', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </div>
                <?php $has_shortcode = $has_shortcode || self::get_all_cpt_tables(); ?>
                <?php $has_shortcode = $has_shortcode || self::get_legacy_tables(); ?>
            </div>
            <?php
            if( ! $has_shortcode ){
                MPC_Admin_Template::no_shortcode_notices();
            }
        }
        private static function get_all_cpt_tables(){
            $args = array(
				'post_type'      => 'mpc_product_table',
				'posts_per_page' => -1,
			);

			// remove hooks for nuiscense.
			remove_all_filters( 'pre_get_posts' );
			remove_all_filters( 'posts_orderby' );

			// get products from query.
			$tables = new WP_Query( $args );
			wp_reset_postdata();

            if( !isset( $tables->posts ) || empty( $tables->posts ) ){
                return false;
            }

            foreach ( $tables->posts as $post ) {
                // $id = get_post_meta( $post->ID, 'table_id', true );
                MPC_Admin_Template::display_shortcode( $post->ID, $post->post_title, $post->post_content );
            }

            return true;
        }
        private static function get_legacy_tables(){
            global $wpdb;
            $res = $wpdb->get_results( "SELECT `option_name`, `option_value` FROM {$wpdb->options} WHERE `option_name` LIKE 'mpcasc_cod%'" );

            if( ! $res ){
                return false;
            }

            foreach( $res as $row ){
                $table_id = (int) str_replace( 'mpcasc_code', '', $row->option_name );
                // $value = maybe_unserialize( $row->option_value );
                MPC_Admin_Template::display_shortcode( $table_id, '', '' );
            }

            return true;
        }
        private static function display_new_table(){
            ?>
            <div class="mpcdp_settings_section">
                <?php MPC_Admin_New_Shortcode::init_new_table( self::$pro_state ); ?>
            </div>
            <div class="mpca-content new-table">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ); ?>" class="mpcasc-reset">
                    <span class="button-secondary">
                        <?php echo esc_html__( 'Reset', 'multiple-products-to-cart-for-woocommerce' ); ?>
                    </span>
                </a>
            </div>
            <?php
            wp_nonce_field( 'mpc_opt_sc_save', 'mpc_opt_sc' );
        }

        /**
         * Display admin settings section
         *
         * @param array $section All input fields in this section.
         */
        private static function display_settings_section( $section ){
            foreach ( $section['fields'] as $field ) {
                MPC_Admin_Field::init( $field, self::$pro_state );
            }
        }
	}
}
