<?php
/**
 * Plugin loading class.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Loader' ) ) {
	/**
	 * Plugin loading class.
	 */
	class MPC_Loader {

		/**
		 * Plugin loader hooks
		 */
		public function init_hooks(){
			if ( ! $this->has_woocommerce() ) {
				return;
			}

			register_activation_hook( MPC, array( 'MPC_Install', 'activate' ) );
			register_deactivation_hook( MPC, array( 'MPC_Install', 'deactivate' ) );

			add_action( 'init', array( $this, 'init' ) );
		}
		
		public function init(){
			$this->includes();

			load_plugin_textdomain( 'multiple-products-to-cart-for-woocommerce', false, plugin_basename( dirname( MPC ) ) . '/languages' );

			$this->register_table_cpt();
			
			do_action( 'mpca_change_pro_state' );
			
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_head', array( $this, 'admin_menu_style' ) );
			
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
		}
		
		/**
		 * Include required files
		 */
		public function includes(){
			// Load plugin base data.
			require MPC_PATH . 'includes/core-data.php';
			
			// Load plugin static installation class.
			include MPC_PATH . 'includes/class/admin/class-mpc-install.php';
			include MPC_PATH . 'includes/class/admin/class-mpc-notice.php';

			// Helper includes.
			include MPC_PATH . 'includes/class/admin/class-mpc-admin-helper.php';
			require MPC_PATH . 'includes/class/admin/class-mpc-shortcode.php';
			
			// Include admin settings functions.
			include MPC_PATH . 'includes/class/admin/class-mpc-admin-fields.php';
			require MPC_PATH . 'includes/class/admin/class-mpc-settings-template.php';
			require MPC_PATH . 'includes/class/admin/class-mpc-settings-page.php';
						
			// Include frontend Classes and functions.
			include MPC_PATH . 'includes/class/class-mpc-frontend-helper.php';
			include MPC_PATH . 'includes/class/class-mpc-frontend-loader.php';
			include MPC_PATH . 'includes/class/class-mpc-add-to-cart.php';
			
			include MPC_PATH . 'includes/class/class-mpc-table-template.php';
		}

		/**
		 * Register custom post type for table
		 */
		public function register_table_cpt() {
			register_post_type(
				'mpc_product_table',
				array(
					'labels'              => array(
						'name'               => _x( 'Mpc Product Tables', 'post type general name', 'multiple-products-to-cart-for-woocommerce' ),
						'singular_name'      => _x( 'Product Table', 'post type singular name', 'multiple-products-to-cart-for-woocommerce' ),
						'add_new'            => __( 'Add a New Product Table', 'multiple-products-to-cart-for-woocommerce' ),
						'add_new_item'       => __( 'Add a New Product Table', 'multiple-products-to-cart-for-woocommerce' ),
						'edit_item'          => __( 'Edit Product Table', 'multiple-products-to-cart-for-woocommerce' ),
						'new_item'           => __( 'New Product Table', 'multiple-products-to-cart-for-woocommerce' ),
						'view_item'          => __( 'View Product Table', 'multiple-products-to-cart-for-woocommerce' ),
						'search_items'       => __( 'Search Mpc Product Tables', 'multiple-products-to-cart-for-woocommerce' ),
						'not_found'          => __( 'Nothing Found', 'multiple-products-to-cart-for-woocommerce' ),
						'not_found_in_trash' => __( 'Nothing found in Trash', 'multiple-products-to-cart-for-woocommerce' ),
						'parent_item_colon'  => '',
					),
					'description'         => __( 'Mpc Product Tables', 'multiple-products-to-cart-for-woocommerce' ),
					'public'              => true, // All the relevant settings below inherit from this setting.
					'exclude_from_search' => false, // When a search is conducted through search.php, should it be excluded?
					'publicly_queryable'  => true, // When a parse_request() search is conducted, should it be included?
					'show_ui'             => false, // Should the primary admin menu be displayed?
					'show_in_nav_menus'   => false, // Should it show up in Appearance > Menus?
					'show_in_menu'        => false, // This inherits from show_ui, and determines *where* it should be displayed in the admin.
					'show_in_admin_bar'   => false, // Should it show up in the toolbar when a user is logged in?
					'has_archive'         => 'mpc_product_tables',
					'rewrite'             => array( 'slug' => 'mpc_product_table' ),
				)
			);
		}


		/**
		 * Check if WooCommerce is active. If not deactive the plugin.
		 */
		private function has_woocommerce() {
			if ( !function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$mpc = 'multiple-products-to-cart-for-woocommerce/multiple-products-to-cart-for-woocommerce.php';
			$wc  = 'woocommerce/woocommerce.php';

			$has_woocommerce = is_plugin_active( $wc );
			$has_mpc         = is_plugin_active( $mpc );

			if( !$has_mpc ) return false;

			// Deactive MPC if it's active while WooCommerce isn't.
			if ( $has_mpc && !$has_woocommerce ) {
				deactivate_plugins( $mpc );
				add_action( 'admin_notices', array( 'MPC_Notice', 'wc_missing_notice' ) );
			}

			return $has_mpc && $has_woocommerce;
		}

		/**
		 * Admin menu items.
		 */
		public function admin_menu() {
			global $mpc__;

			// Main menu.
			add_menu_page(
				__( 'Multiple Products to Cart Settings', 'multiple-products-to-cart-for-woocommerce' ),
				__( 'Multiple Products', 'multiple-products-to-cart-for-woocommerce' ),
				'manage_options',
				'mpc-shortcodes',
				array( 'MPC_Settings_Page', 'all_tables_page' ),
				plugin_dir_url( MPC ) . 'assets/images/admin-icon.svg',
				56
			);

			// main menu label change.
			add_submenu_page(
				'mpc-shortcodes',
				__( 'Multiple Products to Cart - All product tables', 'multiple-products-to-cart-for-woocommerce' ),
				__( 'All Product Tables', 'multiple-products-to-cart-for-woocommerce' ),
				'manage_options',
				'mpc-shortcodes'
			);

			// all product tables submenu.
			add_submenu_page(
				'mpc-shortcodes',
				__( 'Multiple Products to Cart - Add product table', 'multiple-products-to-cart-for-woocommerce' ),
				__( 'Add Product Table', 'multiple-products-to-cart-for-woocommerce' ),
				'manage_options',
				'mpc-shortcode',
				array( 'MPC_Settings_Page', 'new_table_page' )
			);

			add_submenu_page(
				'mpc-shortcodes',
				__( 'Multiple Products to Cart - Settings', 'multiple-products-to-cart-for-woocommerce' ),
				__( 'Settings', 'multiple-products-to-cart-for-woocommerce' ),
				'manage_options',
				'mpc-settings',
				array( 'MPC_Settings_Page', 'settings_page' )
			);

			if ( false === $mpc__['has_pro'] ) {
				add_submenu_page(
					'mpc-shortcodes',
					__( 'Multiple Products to Cart - Get PRO', 'multiple-products-to-cart-for-woocommerce' ),
					'<span style="color: #ff8921;">' . __( 'Get PRO', 'multiple-products-to-cart-for-woocommerce' ) . '</span>',
					'manage_options',
					'mpc-get-pro',
					array( 'MPC_Settings_Page', 'pro_page' )
				);
			}
		}

		/**
		 * Admin menu styling
		 */
		public function admin_menu_style() {
			global $mpc__;
			?>
			<style>
				#toplevel_page_mpc-shortcodes img {
					width: 20px;
					opacity:1!important;
				}
				.notice h3{
					margin-top:.5em;
					margin-bottom:0;
				}
			</style>
			<script>
				jQuery( document ).ready(function(){
					jQuery( '#toplevel_page_mpc-shortcodes a' ).each(function(){
						if( jQuery(this).text() == '<?php echo esc_html__( 'Get PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>' ){
							jQuery(this).attr( 'href', '<?php echo esc_url( $mpc__['prolink'] ); ?>' );
							jQuery(this).attr( 'target', '_blank' );
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * Admin styles and scripts
		 */
		public function admin_assets() {
			global $mpc__;

			if( ! MPC_Admin_Helper::in_screen() ) return;

			// enqueue style.
			wp_register_style( 'mpc_admin_style', plugin_dir_url( MPC ) . 'assets/admin/admin.css', array(), MPC_VER );
			wp_enqueue_style( 'mpc_admin_style' );

			// colorpicker style.
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			wp_register_script( 'mpc_admin_script', plugin_dir_url( MPC ) . 'assets/admin/admin.js', array( 'jquery', 'jquery-ui-slider', 'jquery-ui-sortable' ), MPC_VER, true );
			wp_enqueue_script( 'mpc_admin_script' );

			// Choices JS.
			wp_register_style( 'choices-css', plugin_dir_url( MPC ) . 'assets/lib/choices-js/choices.min.css', array(), MPC_VER );
			wp_enqueue_style( 'choices-css' );

			wp_register_script( 'choices-js', plugin_dir_url( MPC ) . 'assets/lib/choices-js/choices.min.js', array( 'jquery' ), MPC_VER, true );
			wp_enqueue_script( 'choices-js' );

			$var = array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'has_pro'      => $mpc__['has_pro'],
				'nonce'        => wp_create_nonce( 'search_box_nonce' ),
				'export_nonce' => wp_create_nonce( 'mpc_export_nonce' ),
				'export_text'  => __( 'Please wait while we are getting your file ready for download...', 'multiple-products-to-cart-for-woocommerce' ),
				'export_ok'    => __( 'Export successful!', 'multiple-products-to-cart-for-woocommerce' ),
			);

			// apply hook for editing localized variables in admin script.
			$var = apply_filters( 'mpca_local_var', $var );

			wp_localize_script( 'mpc_admin_script', 'mpca_obj', $var );
		}

		/**
		 * Frontend styles and scripts
		 */
		public function frontend_assets() {
			global $mpc__;

			// enqueue style.
			wp_enqueue_style( 'mpc-frontend', plugin_dir_url( MPC ) . 'assets/frontend.css', array(), MPC_VER, 'all' );

			// register script.
			wp_register_script( 'mpc-frontend', plugin_dir_url( MPC ) . 'assets/frontend.js', array( 'jquery' ), MPC_VER, true );
			wp_enqueue_script( 'mpc-frontend', plugin_dir_url( MPC ) . 'assets/frontend.js', array( 'jquery' ), MPC_VER, false );

			// handle localized variables.
			$redirect_url = get_option( 'wmc_redirect' );
			if ( '' === $redirect_url ) {
				$redirect_url = 'cart';
			}

			$cart_btn_text = get_option( 'wmc_button_text' );

			// add localized variables.
			$localaized_values = array(
				'locale'         => str_replace( '_', '-', get_locale() ),
				'currency'       => get_woocommerce_currency_symbol(), // currency symbol.
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'redirect_url'   => $redirect_url,
				'page_limit'     => $mpc__['plugin']['page_limit'],
				'missed_option'  => get_option( 'wmc_missed_variation_text' ),
				'blank_submit'   => get_option( 'wmc_empty_form_text' ),
				'outofstock_txt' => '<p class="stock out-of-stock">' . __( 'Out of stock', 'multiple-products-to-cart-for-woocommerce' ) . '</p>',
				'dp'             => get_option( 'woocommerce_price_num_decimals', 2 ),
				'ds'             => wc_get_price_decimal_separator(), // decimal separator.
				'ts'             => wc_get_price_thousand_separator(), // thousand separator.
				'dqty'           => get_option( 'wmca_default_quantity' ),
				'cart_nonce'     => wp_create_nonce( 'cart_nonce_ref' ),
				'table_nonce'    => wp_create_nonce( 'table_nonce_ref' ),
				'reset_var'      => esc_html__( 'Clear', 'multiple-products-to-cart-for-woocommerce' ),
				'has_pro'        => $mpc__['has_pro'],
				'cart_text'      => ! empty( $cart_btn_text ) ? $cart_btn_text : __( 'Add to Cart', 'multiple-products-to-cart-for-woocommerce' ),
			);

			$localaized_values['key_fields'] = array(
				'orderby' => '.mpc-orderby',
			);

			// default quantity.
			if ( empty( $localaized_values['dqty'] ) || '' === $localaized_values['dqty'] ) {
				$localaized_values['dqty'] = 1;
			}

			if ( empty( $localaized_values['missed_option'] ) ) {
				$localaized_values['missed_option'] = __( 'Please select all options', 'multiple-products-to-cart-for-woocommerce' );
			}
			if ( empty( $localaized_values['blank_submit'] ) ) {
				$localaized_values['blank_submit'] = __( 'Please check one or more products', 'multiple-products-to-cart-for-woocommerce' );
			}

			// assets url.
			$localaized_values['imgassets'] = plugin_dir_url( MPC ) . 'assets/images/';

			// orderby supports.
			$localaized_values['orderby_ddown'] = array( 'price', 'title', 'date' );

			// apply filter.
			$localaized_values = apply_filters( 'mpca_update_local_vars', $localaized_values );

			// localize script.
			wp_localize_script( 'mpc-frontend', 'mpc_frontend', $localaized_values );
		}
	}
}

$mpc_loader = new MPC_Loader();
$mpc_loader->init_hooks();
