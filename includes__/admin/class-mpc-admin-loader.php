<?php
/**
 * Plugin admin loader
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Loader' ) ) {

	/**
	 * Plugin admin loader class
	 */
	class MPC_Admin_Loader {

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
		 * Plugin installation handler
         *
         * @param string $pro_state Pro plugin status.
		 */
		public static function init( $pro_state ) {
            self::$pro_state   = $pro_state;
            self::$plugin_data = MPC_Core_Data::get_plugin();

			// add extra links right under plug.
			add_filter( 'plugin_action_links_' . plugin_basename( MPC ), array( __CLASS__, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'desc_meta' ), 10, 2 );

            add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
			add_action( 'admin_head', array( __CLASS__, 'admin_head' ) );

			self::register_cpt();
			add_action( 'wp_ajax_mpc_admin_search_box', array( __CLASS__, 'ajax_itembox_search' ) );
		}

		/**
		 * Add plugin action links on all plugins page
		 *
		 * @param array $links current plugin action links.
		 */
		public static function action_links( $links ) {
			$action_links = array();

			$action_links['settings'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=mpc-settings' ),
				esc_html__( 'Settings', 'multiple-products-to-cart-for-woocommerce' )
			);

            if( ! empty( self::$pro_state ) ){
                return array_merge( $action_links, $links );
            }

			$action_links['premium'] = sprintf(
				'<a href="%s" style="font-weight: bold;background: linear-gradient(94deg, #0090F7, #BA62FC, #F2416B, #F55600);background-clip: text;color: transparent;">%s</a>',
				esc_url( self::$plugin_data[ 'pro_plugin_url' ] ),
				__( 'Get PRO Plugin', 'multiple-products-to-cart-for-woocommerce' )
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Add plugin description meta data on all plugins page
		 *
		 * @param array  $links all meta data.
		 * @param string $file  plugin base file name.
		 */
		public static function desc_meta( $links, $file ) {
			// if it's not Role Based Product plugin, return.
			if ( plugin_basename( MPC ) !== $file ) {
				return $links;
			}

			return array_merge( $links, array(
                'apidocs' => sprintf(
                    '<a href="%s">%s</a>',
                    esc_url( self::$plugin_data[ 'contact_us_url' ] ),
                    esc_html__( 'Support', 'multiple-products-to-cart-for-woocommerce' )
                )
            ) );
		}

        /**
		 * Process admin head | handle notice and menu styling
		 */
		public static function admin_head() {
			self::admin_menu_css();
			self::remove_admin_notices();
		}

        /**
		 * Admin menu styling
		 */
		public static function admin_menu_css() {
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
							jQuery(this).attr( 'href', '<?php echo esc_url( self::$plugin_data[ 'pro_plugin_url' ] ); ?>' );
							jQuery(this).attr( 'target', '_blank' );
						}
					});
				});
			</script>
			<?php
		}

        /**
		 * Store all admin notices to global variable and remove all
		 */
		public static function remove_admin_notices() {
			// only apply to admin MPC setting page.
			$screen = get_current_screen();
			if ( ! in_array( $screen->id, self::$plugin_data[ 'admin_scopes' ], true ) ) {
				return;
			}

			// Remove all admin notices as we don't need to display in it's place.
			remove_all_actions( 'admin_notices' );
		}

		/**
		 * Admin menu
		 */
		public static function admin_menu() {
			// Main menu.
			add_menu_page(
				__( 'Multiple Products to Cart Settings', 'multiple-products-to-cart-for-woocommerce' ),
				__( 'Multiple Products', 'multiple-products-to-cart-for-woocommerce' ),
				'manage_options',
				'mpc-shortcodes',
				array( __CLASS__, 'all_tables_page' ),
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
				array( __CLASS__, 'create_new_table_page' )
			);

			add_submenu_page(
				'mpc-shortcodes',
				__( 'Multiple Products to Cart - Settings', 'multiple-products-to-cart-for-woocommerce' ),
				__( 'Settings', 'multiple-products-to-cart-for-woocommerce' ),
				'manage_options',
				'mpc-settings',
				array( __CLASS__, 'admin_settings_page' )
			);

			if ( empty( self::$pro_state ) ) {
				add_submenu_page(
					'mpc-shortcodes',
					__( 'Multiple Products to Cart - Get PRO', 'multiple-products-to-cart-for-woocommerce' ),
					'<span style="color: #ff8921;">' . __( 'Get PRO', 'multiple-products-to-cart-for-woocommerce' ) . '</span>',
					'manage_options',
					'mpc-get-pro',
					array( __CLASS__, 'pro_page' )
				);
			}
		}

        /**
		 * Saved tables admin menu page
		 */
		public static function all_tables_page() {
			MPC_Admin_Page::init( 'all-tables', self::$pro_state );
		}

		/**
		 * New table admin menu page
		 */
		public static function create_new_table_page() {
            MPC_Admin_Page::init( 'new-table', self::$pro_state );
		}

		/**
		 * Admin settings menu page
		 */
		public static function admin_settings_page() {
            MPC_Admin_Page::init( '', self::$pro_state );
		}

		/**
		 * Admin menu pro page
		 */
		public static function pro_page() {
			header( 'Location: ' . esc_url( self::$plugin_data[ 'pro_plugin_url' ] ) );
			exit;
		}

		/**
		 * Register custom post type for table
		 */
		public static function register_cpt() {
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
		 * Ajax search items for dorpdown combo-box
		 */
		public static function ajax_itembox_search() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'search_box_nonce' ) ) {
				return '';
			}

			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$type   = isset( $_POST['type_name'] ) ? sanitize_text_field( wp_unslash( $_POST['type_name'] ) ) : '';

			$limit = 50; // Limit the number of items.
			if ( 'cats' === $type ) {
				$args = array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,  // Set this to true if you only want categories with products.
					'name__like' => $search,  // Search by category name.
					'number'     => $limit,  // Limit the number of categories.
				);

				$product_categories = get_terms( $args );
				$categories         = array();
				if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
					foreach ( $product_categories as $category ) {
						$categories[] = array(
							'id'   => $category->term_id,
							'name' => $category->name,
						);
					}
				}

				wp_send_json( $categories );
			} else {
				$args = array(
					's'              => $search,
					'post_type'      => 'product',
					'posts_per_page' => $limit,
				);

				$products = array();
				$query    = new WP_Query( $args );
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						$products[] = array(
							'id'   => get_the_ID(),
							'name' => get_the_title(),
						);
					}
				}

				wp_reset_postdata();
				wp_send_json( $products );
			}

			wp_send_json( array() );
		}
	}
}
