<?php
/**
 * Plugin asset loader
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Asset_Loader' ) ) {

	/**
	 * Plugin asset loader class
	 */
	class MPC_Asset_Loader {

        /**
         * If we should load uncompressed asset files
         * @var string
         */
        private static $suffix;

        /**
         * Pro plugin status
         * @var string
         */
        private static $pro_state;

		/**
         * Plugin core data
         * @var array
         */
        private static $plugin_data;
        
        /**
         * Init asset loader class
         *
         * @param string $pro_state Pro plugin status.
         */
        public static function init( $pro_state ){
            self::$pro_state   = $pro_state;
            // self::$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            self::$suffix      = '';

			self::$plugin_data = MPC_Core_Data::get_plugin();

            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_frontend_assets' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_assets' ) );
        }

        /**
		 * Plugin frontend scripts and style enqueue
		 */
		public static function load_frontend_assets() {
			wp_register_style( 'mpc-frontend', MPC_URL . 'assets/css/frontend' . self::$suffix . '.css', array(), MPC_VER, 'all' );
			wp_enqueue_style( 'mpc-frontend' );

			self::add_inline_css();

			// hooks and filters script.
			wp_register_script( 'mpc-hooks', MPC_URL . 'assets/js__/mpc-hooks' . self::$suffix . '.js', array( 'jquery' ), MPC_VER, true );
			wp_enqueue_script( 'mpc-hooks' );
			
			wp_register_script( 'mpc-table-loader', MPC_URL . 'assets/js__/table-loader' . self::$suffix . '.js', array( 'jquery' ), MPC_VER, true );
			wp_register_script( 'mpc-product-events', MPC_URL . 'assets/js__/product-events' . self::$suffix . '.js', array( 'jquery' ), MPC_VER, true );
			wp_register_script( 'mpc-page-events', MPC_URL . 'assets/js__/page-events' . self::$suffix . '.js', array( 'jquery' ), MPC_VER, true );
			wp_register_script( 'mpc-add-to-cart', MPC_URL . 'assets/js__/add-to-cart' . self::$suffix . '.js', array( 'jquery' ), MPC_VER, true );
			
			wp_enqueue_script( 'mpc-table-loader' );
			wp_enqueue_script( 'mpc-product-events' );
			wp_enqueue_script( 'mpc-page-events' );
			wp_enqueue_script( 'mpc-add-to-cart' );

			$localized_data = self::front_script_data();
			wp_localize_script( 'mpc-table-loader', 'mpc_frontend', $localized_data );
			wp_localize_script( 'mpc-product-events', 'mpc_frontend', $localized_data );
			wp_localize_script( 'mpc-page-events', 'mpc_frontend', $localized_data );
			wp_localize_script( 'mpc-add-to-cart', 'mpc_frontend', $localized_data );
		}

		/**
		 * Get frontend script localized data
		 * @return array
		 */
        private static function front_script_data(){
			return apply_filters( 'mpca_update_local_vars', array(
				'dp'             => get_option( 'woocommerce_price_num_decimals', 2 ),
				'ds'             => wc_get_price_decimal_separator(), // decimal separator.
				'ts'             => wc_get_price_thousand_separator(), // thousand separator.
				// 'dqty'           => get_option( 'wmca_default_quantity', 1 ),
				'locale'         => str_replace( '_', '-', get_locale() ),
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				// 'currency'       => get_woocommerce_currency_symbol(), // currency symbol.
				'reset_var'      => esc_html__( 'Clear', 'multiple-products-to-cart-for-woocommerce' ),
                'imgassets'      => MPC_URL . 'assets/images/',
				// 'cart_text'      => get_option( 'wmc_button_text', __( 'Add to cart', 'multiple-products-to-cart-for-woocommerce' ) ),
				'cart_nonce'     => wp_create_nonce( 'cart_nonce_ref' ),
				'table_nonce'    => wp_create_nonce( 'table_nonce_ref' ),
				'redirect_url'   => get_option( 'wmc_redirect', 'cart' ),
				'blank_submit'   => get_option( 'wmc_empty_form_text', __( 'Please check one or more products', 'multiple-products-to-cart-for-woocommerce' ) ),
				'missed_option'  => get_option( 'wmc_missed_variation_text', __( 'Please select all options', 'multiple-products-to-cart-for-woocommerce' ) ),
                // 'orderby_ddown'  => array( 'price', 'title', 'date' ),
			) );
        }

		/**
		 * Inline CSS for dynamic CSS property values
		 */
		public static function add_inline_css() {
			// add to cart button color and background color.
			$btn_color      = get_option( 'mpc_button_text_color', '' );
			$btn_background = get_option( 'wmc_button_color', '' );

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

			$css = '
				.mpc-wrap thead tr th, .mpc-pagenumbers span.current, .mpc-fixed-header table thead tr th{
					background: {$hnp_background}; color: {$hnp_color};
				}
				td.mpc-product-image, .mpcp-gallery, table.mpc-wrap img{
					width: {$image_size}px;
				}
			';

			// cart button css.
			$cart_btn  = ! empty( $btn_color ) ? 'color: ' . esc_html( $btn_color ) . ';' : '';
			$cart_btn .= ! empty( $btn_background ) ? 'background: ' . esc_html( $btn_background ) . ';' : '';
			if ( ! empty( $cart_btn ) ) {
				$css .= ".mpc-button input.mpc-add-to-cart.wc-forward, button.mpce-single-add, span.mpc-fixed-cart{{$cart_btn}}";
			}

			if ( ! empty( $title_color ) ) {
				$css .= '.mpc-product-title a{ color: {$title_color}; }';
			}

			if ( 'on' === get_option( 'wmca_inline_dropdown' ) ) {
				$css .= '.mpc-wrap .variation-group > select, .variation-group select{ max-width: 100px; }';
			}

			$css .= '.mpc-container .mpc-product-title a{';
			if ( ! empty( $title_font_size ) ) {
				$css .= "font-size: {$title_font_size}px;";
			}
			if ( ! empty( $bold_title ) && 'on' === $bold_title ) {
				$css .= 'font-weight: bold;';
			}
			if ( ! empty( $title_underline ) && 'on' === $title_underline ) {
				$css .= 'text-decoration: underline; }';
			} else {
				$css .= 'text-decoration: none; }';
			}

			$tr_height      = $image_size + 17;
			$gallery_height = $image_size + ceil( ( $image_size * 47 ) / 100 ) + 24;
			$padding_left   = $image_size + 13;
			$css           .= "
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

			ob_start();
			do_action( 'mpc_dynamic_css' );
			$css .= ob_get_clean();

			wp_add_inline_style( 'mpc-frontend', $css );
		}

		/**
		 * Admin scripts and style enqueue
		 */
		public static function load_admin_assets() {
            if( ! is_admin() || ! self::admin_in_scope() ){
                return;
            }

			wp_register_style( 'mpc-admin', MPC_URL . 'assets/css/admin/admin' . self::$suffix . '.css', array(), MPC_VER );
			wp_enqueue_style( 'mpc-admin' );

			wp_register_script( 'mpc-page-events', MPC_URL . 'assets/js__/admin/page-events' . self::$suffix . '.js', array( 'jquery' ), MPC_VER, true );
			wp_register_script( 'mpc-settings-events', MPC_URL . 'assets/js__/admin/settings-events' . self::$suffix . '.js', array( 'jquery', 'jquery-ui-slider', 'jquery-ui-sortable' ), MPC_VER, true );
			wp_register_script( 'mpc-shortcode-events', MPC_URL . 'assets/js__/admin/shortcode-events' . self::$suffix . '.js', array( 'jquery' ), MPC_VER, true );

			wp_enqueue_script( 'mpc-page-events' );
			wp_enqueue_script( 'mpc-settings-events' );
			wp_enqueue_script( 'mpc-shortcode-events' );

			$localized_data = self::admin_script_data();
            wp_localize_script( 'mpc-page-events', 'mpc_admin', $localized_data );
            wp_localize_script( 'mpc-settings-events', 'mpc_admin', $localized_data );
            wp_localize_script( 'mpc-shortcode-events', 'mpc_admin', $localized_data );
            
			self::admin_libraries();
		}

		/**
		 * Checks if admin in scope.
		 */
        private static function admin_in_scope(){
            $screen = get_current_screen();
            return in_array( $screen->id, self::$plugin_data[ 'admin_scopes' ], true );
        }

		/**
		 * Get admin localized data
		 */
        private static function admin_script_data(){
			return apply_filters( 'mpca_local_var', array(
				'nonce'        => wp_create_nonce( 'search_box_nonce' ),
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
                'has_pro'      => empty( self::$pro_state ),
			) );
        }

		/**
		 * Add support libraries for admin
		 */
		private static function admin_libraries(){
			// colorpicker style.
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			// Choices JS.
			wp_register_style( 'choices-css', MPC_URL . 'assets/lib/choices-js/choices.min.css', array(), MPC_VER );
			wp_register_script( 'choices-js', MPC_URL . 'assets/lib/choices-js/choices.min.js', array( 'jquery' ), MPC_VER, true );

			wp_enqueue_style( 'choices-css' );
			wp_enqueue_script( 'choices-js' );
		}
	}
}
