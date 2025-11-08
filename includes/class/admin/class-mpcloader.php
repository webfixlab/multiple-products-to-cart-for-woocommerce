<?php
/**
 * Plugin loading class.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPCLoader' ) ) {
	/**
	 * Plugin loading class.
	 */
	class MPCLoader {



		/**
		 * Plugin initialize
		 */
		public function init() {
			add_action( 'init', array( $this, 'init_plugin' ) );
			register_activation_hook( MPC, array( $this, 'activate' ) );
			register_deactivation_hook( MPC, array( $this, 'deactivate' ) );

			add_action( 'before_woocommerce_init', array( $this, 'wc_init' ) );
		}



		/**
		 * Activate plugin
		 */
		public function activate() {
			// main plugin activatio process handler.
			$this->init_plugin();

			flush_rewrite_rules();

			$this->init_fields();
		}

		/**
		 * Deactivate plugin
		 */
		public function deactivate() {
			flush_rewrite_rules();
		}



		/**
		 * MPC initialize
		 */
		public function init_plugin() {
			load_plugin_textdomain( 'multiple-products-to-cart-for-woocommerce', false, plugin_basename( dirname( MPC ) ) . '/languages' );

			require MPC_PATH . 'includes/core-data.php';

			// check prerequisits.
			if ( ! $this->do_activate() ) {
				return;
			}

			// add extra links right under plug.
			add_filter( 'plugin_action_links_' . plugin_basename( MPC ), array( $this, 'plugin_extra_link' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_desc_meta' ), 10, 2 );

			// Include admin settings functions.
			require MPC_PATH . 'includes/class/admin/class-mpcadminhelper.php';
			require MPC_PATH . 'includes/class/admin/class-mpcsettings.php';

			$this->check_pro();

			include MPC_PATH . 'includes/class/admin/class-mpcadmintable.php';

			include MPC_PATH . 'includes/class/class-mpc-template.php';
			include MPC_PATH . 'includes/class/class-mpctable.php';
			include MPC_PATH . 'includes/functions.php';

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_script' ) );

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );

			$this->ask_feedback();
		}

		/**
		 * MPC Plugin activation
		 */
		public function do_activate() {

			// check if is_plugin_active founction not found | rare case.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$mpc = 'multiple-products-to-cart-for-woocommerce/multiple-products-to-cart-for-woocommerce.php';
			$wc  = 'woocommerce/woocommerce.php';

			$is_wc_active  = is_plugin_active( $wc );
			$is_mpc_active = is_plugin_active( $mpc );

			if ( ! $is_wc_active && $is_mpc_active ) {

				// if mpc active while wc is deactive.
				deactivate_plugins( $mpc );
				add_action( 'admin_notices', array( $this, 'wc_missing_notice' ) );

				return false;
			}

			return true;
		}

		/**
		 * Initialize some plugin options
		 */
		public function init_fields() {
			// assign default fields value.
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
		 * WC high speed order-storage optimization hook
		 */
		public function wc_init() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MPC, true );
			}
		}



		/**
		 * Process admin head | handle notice and menu styling
		 */
		public function admin_head() {
			$this->admin_menu_css();
			$this->admin_notice();
		}

		/**
		 * Admin menu
		 */
		public function admin_menu() {
			global $mpc__;

			// Main menu.
			add_menu_page(
				__( 'Multiple Products to Cart Settings', 'multiple-products-to-cart-for-woocommerce' ),
				__( 'Multiple Products', 'multiple-products-to-cart-for-woocommerce' ),
				'manage_options',
				'mpc-shortcodes',
				array( $this, 'saved_tables_page' ),
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
				array( $this, 'new_table_page' )
			);

			add_submenu_page(
				'mpc-shortcodes',
				__( 'Multiple Products to Cart - Settings', 'multiple-products-to-cart-for-woocommerce' ),
				__( 'Settings', 'multiple-products-to-cart-for-woocommerce' ),
				'manage_options',
				'mpc-settings',
				array( $this, 'admin_settings_page' )
			);

			if ( false === $mpc__['has_pro'] ) {
				add_submenu_page(
					'mpc-shortcodes',
					__( 'Multiple Products to Cart - Get PRO', 'multiple-products-to-cart-for-woocommerce' ),
					'<span style="color: #ff8921;">' . __( 'Get PRO', 'multiple-products-to-cart-for-woocommerce' ) . '</span>',
					'manage_options',
					'mpc-get-pro',
					array( $this, 'pro_page' )
				);
			}
		}



		/**
		 * Admin menu styling
		 */
		public function admin_menu_css() {

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
		 * Saved tables admin menu page
		 */
		public function saved_tables_page() {
			$this->load_settings( 'all-tables' );
		}

		/**
		 * New table admin menu page
		 */
		public function new_table_page() {
			$this->load_settings( 'new-table' );
		}

		/**
		 * Admin settings menu page
		 */
		public function admin_settings_page() {

			$tab = $this->get_tab();

			if ( 'new-table' === $tab || 'all-tables' === $tab ) {
				$tab = 'general-settings';
			}

			$this->load_settings( $tab );
		}

		/**
		 * Load admin settings content
		 *
		 * @param string $tab admin settings page template key.
		 */
		public function load_settings( $tab ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// show error/update messages.
			settings_errors( 'wporg_messages' );

			global $mpc__;

			// set current settings section and tab.
			$mpc__['settings_tab'] = $tab;

			include MPC_PATH . 'templates/admin/settings.php';
			require MPC_PATH . 'templates/admin/popup.php';
		}



		/**
		 * Admin menu pro page
		 */
		public function pro_page() {
			global $mpc__;
			header( 'Location: ' . esc_url( $mpc__['prolink'] ) );
			exit;
		}



		/**
		 * Plugin frontend scripts and style enqueue
		 */
		public function frontend_script() {
			global $mpc__;

			// enqueue style.
			wp_enqueue_style( 'mpc-frontend', plugin_dir_url( MPC ) . 'assets/frontend.css', array(), MPC_VER, 'all' );
			$this->dynamic_css();

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
		public function dynamic_css(){
			// add to cart button color and background color.
			$btn_color      = get_option( 'mpc_button_text_color', '#353535' );
			$btn_background = get_option( 'wmc_button_color', '#d3d3d3' );
			$btn_color      = empty( $btn_color ) ? '#353535' : $btn_color;
			$btn_background = empty( $btn_background ) ? '#d3d3d3' : $btn_background;

			// header and pagination color and background color.
			$hnp_color      = get_option( 'mpc_head_text_color', '#ffffff' );
			$hnp_background = get_option( 'wmc_thead_back_color', '#535353' );
			$hnp_color      = empty( $hnp_color ) ? '#ffffff' : $hnp_color;
			$hnp_background = empty( $hnp_background ) ? '#535353' : $hnp_background;

			// product title color, font size, whether to bold it and also underline it.
			$title_color     = get_option( 'mpc_protitle_color' );
			$title_font_size = get_option( 'mpc_protitle_font_size' );
			$bold_title      = get_option( 'mpc_protitle_bold_font' );
			$title_underline = get_option( 'mpc_protitle_underline' );

			// product image size.
			$image_size = get_option( 'mpc_image_size', '90' );

			$css = "
				.mpc-wrap thead tr th, .mpc-pagenumbers span.current, .mpc-fixed-header table thead tr th{
					background: {$hnp_background};
					color: {$hnp_color};
				}
				.mpc-button input.mpc-add-to-cart.wc-forward, button.mpce-single-add, span.mpc-fixed-cart{
					background: {$btn_background};
					color: {$btn_color};
				}
				td.mpc-product-image, .mpcp-gallery, table.mpc-wrap img{
					width: {$image_size}px;
				}
			";
			if ( ! empty( $title_color ) ) $css .= "
				.mpc-product-title a{
					color: {$title_color};
				}
			";

			if ( 'on' === get_option( 'wmca_inline_dropdown' ) ) $css .= "
				.mpc-wrap .variation-group > select{
					max-width: 100px;
				}
				.variation-group select{
					width: 100px;
				}
			";
			$css .= "
				.mpc-container .mpc-product-title a{";
			if( !empty( $title_font_size ) ) $css .= "font-size: {$title_font_size}px;";
			if( !empty( $bold_title ) && 'on' === $bold_title ) $css .= "font-weight: bold;";
			if( !empty( $title_underline ) && 'on' === $title_underline ) $css .= "text-decoration: underline;";
			else $css .= "text-decoration: none;";
			$tr_height      = $image_size + 17;
			$gallery_height = $image_size + ceil( ( $image_size * 47 ) / 100 ) + 24;
			$padding_left   = $image_size + 13;
			$css .= "
				}
				@media screen and (max-width: 767px) {
					table.mpc-wrap tbody tr{
						min-height: {$tr_height}px;
					}
					table.mpc-wrap tbody tr:has(.gallery-item){
						min-height: {$gallery_height}px;
					}
					#content table.mpc-wrap tbody tr td, #main-content table.mpc-wrap tbody tr td, #brx-content table.mpc-wrap tbody tr td, #main table.mpc-wrap tbody tr td, main table.mpc-wrap tbody tr td{
						padding-left: {$padding_left}px;
					}
				}
			";
			do_action( 'mpc_dynamic_css' );
			wp_add_inline_style( 'mpc-frontend', $css );
		}

		/**
		 * Admin scripts and style enqueue
		 */
		public function admin_script() {
			global $mpc__;

			$screen = get_current_screen();

			// multiple-products_page_mpc-shortcodes.
			if ( ! in_array( $screen->id, $mpc__['plugin']['screen'], true ) ) {
				return;
			}

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
		 * All plugins page - plugin extra links
		 *
		 * @param array $links all settings extra links.
		 */
		public function plugin_extra_link( $links ) {
			global $mpc__;

			$action_links = array();

			$action_links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=mpc-settings' ) ),
				__( 'Settings', 'multiple-products-to-cart-for-woocommerce' )
			);

			if ( ! in_array( 'activated', explode( ' ', $mpc__['prostate'] ), true ) ) {
				$action_links['premium'] = sprintf(
					'<a href="%s" style="font-weight: bold;background: linear-gradient(94deg, #0090F7, #BA62FC, #F2416B, #F55600);background-clip: text;color: transparent;">%s</a>',
					esc_url( $mpc__['prolink'] ),
					__( 'Get PRO Plugin', 'multiple-products-to-cart-for-woocommerce' )
				);
			}

			return array_merge( $action_links, $links );
		}

		/**
		 * All plugins page - plugin description meta data
		 *
		 * @param array  $links all plugins meta links.
		 * @param string $file  plugin main file name.
		 */
		public function plugin_desc_meta( $links, $file ) {
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

		/**
		 * Plugin useage feedback notice handler
		 */
		public function ask_feedback() {
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
				if ( $this->date_difference( 'mpca_rate_us', $mpc__['plugin']['notice_interval'], 'done' ) ) {
					// show notice to rate us after 15 days interval.
					add_action( 'admin_notices', array( $this, 'ask_feedback_notice' ) );

				}

				$proinfo = get_option( 'mpca_notify_pro' );
				if ( empty( $proinfo ) || '' === $proinfo ) {
					add_action( 'admin_notices', array( $this, 'pro_notice' ) );
				} elseif ( $this->date_difference( 'mpca_notify_pro', $mpc__['plugin']['notice_interval'], '' ) ) {
					// show notice to inform about pro version after 15 days interval.
					add_action( 'admin_notices', array( $this, 'pro_notice' ) );
				}
			}
		}



		/**
		 * Store all admin notices to global variable and remove all
		 */
		public function admin_notice() {

			global $mpc__;

			// only apply to admin MPC setting page.
			$screen = get_current_screen();
			if ( ! in_array( $screen->id, $mpc__['plugin']['screen'], true ) ) {
				return;
			}

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
		 * WooCommerce not active notice
		 */
		public function wc_missing_notice() {
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
		 * Plugin useage feedback notice
		 */
		public function ask_feedback_notice() {
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
		public function pro_notice() {
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



		/**
		 * Get current admin settings tab
		 */
		public function get_tab() {
			global $mpc__;

			$tab = 'new-table';

			if ( isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ) {
				if ( isset( $_GET['nonce'] ) && ! empty( $_GET['nonce'] ) &&
					wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
					$tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
				}
			}

			if ( isset( $mpc__['settings_tab'] ) && ! empty( $mpc__['settings_tab'] ) ) {
				$tab = sanitize_title( $mpc__['settings_tab'] );
			}

			return $tab;
		}

		/**
		 * Check if PRO plugin is active
		 */
		public function check_pro() {
			global $mpc__;

			// don't have pro.
			$mpc__['has_pro'] = false;

			// Pro state.
			$mpc__['prostate'] = 'none';

			// change states.
			do_action( 'mpca_change_pro_state' );
		}

		/**
		 * Check if notice interval is passed given interval
		 *
		 * @param string $key             notice type option name.
		 * @param int    $notice_interval days | notice interval.
		 * @param string $skip_           whether this notice purpose is complete.
		 */
		public function date_difference( $key, $notice_interval, $skip_ = '' ) {
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
}

$cls = new MPCLoader();
$cls->init();
