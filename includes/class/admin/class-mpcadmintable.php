<?php
/**
 * Frontend table class.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPCAdminTable' ) ) {
	/**
	 * Admin table handler class
	 */
	class MPCAdminTable {



		/**
		 * Whather any shortcode saved or not
		 *
		 * @var boolean.
		 */
		private $has_shortcode;

		/**
		 * Table post type if
		 *
		 * @var int.
		 */
		private $post_id;

		/**
		 * Shortcode table id
		 *
		 * @var int.
		 */
		private $table_id;

		/**
		 * Table page admin notices
		 *
		 * @var array.
		 */
		private $notice;



		/**
		 * Initialize table class variables
		 */
		public function __construct() {
			$this->has_shortcode = false;
			$this->post_id       = '';
			$this->table_id      = '';
			$this->notice        = array();
		}

		/**
		 * Initialize table class
		 */
		public function init() {
			add_action( 'init', array( $this, 'register_mpc_table' ) );
			add_action( 'wp_ajax_mpc_admin_search_box', array( $this, 'ajax_itembox_search' ) );
		}

		/**
		 * Register custom post type for table
		 */
		public function register_mpc_table() {

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
		 * Table shortcode update and handle notice
		 */
		public function change_shortcode() {
			if ( ! is_admin() ) {
				return;
			}

			$this->delete_shortcode();
			$this->save_shortcode();
			$this->show_notice();
		}



		/**
		 * Display shortcode table item
		 *
		 * @param int    $id    table id.
		 * @param string $title table title.
		 * @param string $desc  table description.
		 */
		public function list_item( $id, $title, $desc ) {
			$edit   = admin_url( 'admin.php?page=mpc-shortcode' );
			$delete = admin_url( 'admin.php?page=mpc-shortcodes' );
			$nonce  = wp_create_nonce( 'mpc_option_tab' );

			?>
			<div class="mpcdp_settings_toggle mpcdp_container mpc-shortcode">
				<div class="mpcdp_settings_option visible">
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-12">
							<div class="mpcdp_option_label"><?php echo esc_html( $title ); ?></div><div class="mpcdp_option_description">
								<?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
							</div>
						</div>
					</div>
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-6">
							<textarea class="mpc-opt-sc" readonly >[woo-multi-cart table="<?php echo esc_attr( $id ); ?>"]</textarea>
						</div>
						<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
							<span class="mpc-opt-sc-btn copy">
								<?php echo esc_html__( 'Copy', 'multiple-products-to-cart-for-woocommerce' ); ?>
							</span>
							<a class="mpc-opt-sc-btn edit" href="<?php echo esc_url( $edit . '&tab=new-table&mpctable=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">
								<?php echo esc_html__( 'Edit', 'multiple-products-to-cart-for-woocommerce' ); ?>
							</a>
							<a class="mpc-opt-sc-btn delete" href="<?php echo esc_url( $delete . '&tab=all-tables&mpcscdlt=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">
								<?php echo esc_html__( 'Delete', 'multiple-products-to-cart-for-woocommerce' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Display no shortcode message
		 */
		public function no_shortcode() {

			if ( true === $this->has_shortcode ) {
				return;
			}

			?>
			<div class="mpcdp_settings_toggle mpcdp_container" style="margin-top: 30px;">
				<div class="mpcdp_settings_option visible">
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-6">
							<div class="mpcdp_option_label"><?php echo esc_html__( 'No shortcodes found.', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
							<div class="mpcdp_option_description">
								<?php

									$link = sprintf(
										'<a href="%s">%s</a>',
										esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ),
										__( 'here', 'multiple-products-to-cart-for-woocommerce' )
									);

									echo wp_kses_post(
										sprintf(
											// translators: %1$s: new product table crate link.
											__( 'Create a product table shortcode %1$s.', 'multiple-products-to-cart-for-woocommerce' ),
											wp_kses_post( $link )
										)
									);

								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Display all table shortcode list
		 */
		public function table_list() {

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

			echo wp_kses_post(
				sprintf(
					'<div class="mpcdp_settings_section_title">%s</div>',
					__( 'All Product Tables', 'multiple-products-to-cart-for-woocommerce' )
				)
			);

			if ( ! empty( $tables ) && ! empty( $tables->posts ) ) {

				$this->has_shortcode = true;

				foreach ( $tables->posts as $post ) {
					$id = get_post_meta( $post->ID, 'table_id', true );
					$this->list_item( $id, $post->post_title, $post->post_content );
				}
			}

			$this->display_legacy_list();
			$this->no_shortcode();
		}



		/**
		 * Get table id
		 */
		public function table_index() {

			$table_id = '';

			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
				return '';
			}

			if ( isset( $_GET['mpctable'] ) ) {
				$table_id = (int) sanitize_key( wp_unslash( $_GET['mpctable'] ) );
			} elseif ( isset( $_GET['mpcscdlt'] ) ) {
				$table_id = (int) sanitize_key( wp_unslash( $_GET['mpcscdlt'] ) );
			}

			return $table_id;
		}

		/**
		 * Get shortcode table id of custom post type
		 *
		 * @param int $table_id table id.
		 */
		public function cpt_id( $table_id ) {

			$cpt_id = '';

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

			if ( ! empty( $tables ) && ! empty( $tables->posts ) ) {

				foreach ( $tables->posts as $post ) {
					$id = get_post_meta( $post->ID, 'table_id', true );

					if ( ! empty( $id ) ) {
						$id = (int) $id;
					}

					if ( $id === $table_id ) {
						$cpt_id = $post->ID;
					}
				}
			}

			return $cpt_id;
		}

		/**
		 * Save shortcode table item
		 */
		public function save_shortcode() {

			if ( ! isset( $_POST ) || empty( $_POST ) ) {
				return;
			}

			// verify nonce.
			if ( ! isset( $_POST['mpc_opt_sc'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_opt_sc'] ) ), 'mpc_opt_sc_save' ) ) {
				return;
			}

			$sr    = $this->shortcode_request();
			$title = '';
			$desc  = '';

			if ( isset( $_POST['shortcode_title'] ) && ! empty( $_POST['shortcode_title'] ) ) {
				$title = sanitize_text_field( wp_unslash( $_POST['shortcode_title'] ) );
			}

			if ( isset( $_POST['shortcode_desc'] ) && ! empty( $_POST['shortcode_desc'] ) ) {
				$desc = sanitize_text_field( wp_unslash( $_POST['shortcode_desc'] ) );
			}

			$post_id  = '';
			$table_id = $this->table_index();

			$flag = '';

			if ( empty( $table_id ) ) {
				$flag = 'add';
			} else {
				$post_id = $this->cpt_id( $table_id );

				if ( empty( $post_id ) ) {
					$flag = 'add';
				}
			}

			// Legacy delete code.
			if ( ! empty( $table_id ) && 'add' === $flag ) {
				delete_option( 'mpcasc_code' . $table_id );
			}

			if ( 'add' === $flag ) {
				$post_id = wp_insert_post(
					array(
						'post_type'      => 'mpc_product_table',
						'post_title'     => ! empty( $title ) ? $title : __( 'Product Table', 'multiple-products-to-cart-for-woocommerce' ),
						'post_content'   => $desc,
						'post_status'    => 'publish',
						'comment_status' => 'closed',
						'ping_status'    => 'closed',
					)
				);

				if ( empty( $title ) ) {
					$title = ! empty( $title ) ? $title : __( 'Product Table', 'multiple-products-to-cart-for-woocommerce' );

					$a = wp_update_post(
						array(
							'ID'         => $post_id,
							'post_title' => $title . ' #' . $post_id,
						)
					);
				}
			} else {
				// update shortcode.
				$a = wp_update_post(
					array(
						'ID'           => $post_id,
						'post_title'   => ! empty( $title ) ? $title : __( 'Product Table #', 'multiple-products-to-cart-for-woocommerce' ) . $post_id,
						'post_content' => $desc,
					)
				);

				$this->notice = array(
					'status'  => 'updated',
					'message' => __( 'Shortcode updated.', 'multiple-products-to-cart-for-woocommerce' ),
				);
			}

			if ( ! empty( $post_id ) ) {
				if ( empty( $table_id ) ) {
					$table_id = $post_id;
				}

				if ( 'add' === $flag ) {
					add_post_meta( $post_id, 'shortcode', $sr );
					add_post_meta( $post_id, 'table_id', $table_id );
				} else {
					update_post_meta( $post_id, 'shortcode', $sr );
					update_post_meta( $post_id, 'table_id', $table_id );
				}
			}

			if ( 'add' === $flag ) {
				// redirect to url.
				$page  = admin_url( 'admin.php?page=mpc-shortcode' );
				$nonce = wp_create_nonce( 'mpc_option_tab' );

				$url = $page . '&tab=new-table&mpctable=' . esc_attr( $table_id ) . '&nonce=' . esc_attr( $nonce ) . '&created=yes';

				header( 'Location: ' . $url );
				exit();
			}
		}

		/**
		 * Handle shortcode table update request
		 */
		public function shortcode_request() {

			global $mpc__;

			// vefiry nonce again.
			if ( ! isset( $_POST['mpc_opt_sc'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_opt_sc'] ) ), 'mpc_opt_sc_save' ) ) {
				return '';
			}

			$ds = '';

			foreach ( $mpc__['fields']['new_table'] as $section ) {
				foreach ( $section['fields'] as $fld ) {

					if ( in_array( $fld['key'], array( 'shortcode_title', 'shortcode_desc' ), true ) ) {
						continue;
					}

					$key  = $fld['key'];
					$item = '';

					if ( isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ) {
						$val = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );

						// add variation column as it's a must have column.
						if ( 'columns' === $fld['key'] && strpos( $val, 'variation' ) === false ) {
							$val .= ! empty( $val ) ? ', variation' : 'variation';
						}

						// order attribute miss-match recovery.
						if ( 'order' === $key ) {
							if ( ! in_array( $val, array( 'desc', 'asc', 'custom' ), true ) ) {
								$val = 'desc';
							}
						}

						$item = $key . '="' . $val . '"';
					}

					if ( 'checkbox' === $fld['type'] ) {
						$item = ! isset( $_POST[ $key ] ) ? 'false' : 'true';
						$item = $key . '="' . $item . '"';
					}

					if ( empty( $item ) ) {
						continue;
					}

					if ( strlen( $ds ) > 0 ) {
						$ds .= ' ' . $item;
					} else {
						$ds = $item;
					}
				}
			}

			$ds = '[woo-multi-cart ' . $ds . ']';

			return $ds;
		}

		/**
		 * Delete shortcode table item
		 */
		public function delete_shortcode() {

			// get table index.
			$table_id = $this->table_index();

			if ( empty( $table_id ) ) {
				return;
			}

			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
				return;
			}

			if ( ! isset( $_GET['mpcscdlt'] ) ) {
				return;
			}

			$cpt_id = $this->cpt_id( $table_id );

			if ( ! empty( $cpt_id ) ) {
				wp_delete_post( $cpt_id, true );

				$this->notice = array(
					'status'  => 'deleted',
					'message' => __( 'Shortcode deleted.', 'multiple-products-to-cart-for-woocommerce' ),
				);
			}

			$this->legacy_delete( $table_id );
		}

		/**
		 * Display table page notices
		 */
		public function show_notice() {
			// check for created flag.
			if ( isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
				if( isset( $_GET['created'] ) ){
					$this->notice = array(
						'status'  => 'succcess',
						'message' => __( 'Shortcode created.', 'multiple-products-to-cart-for-woocommerce' ),
					);
				}
			}

			if ( empty( $this->notice ) ) {
				return;
			}

			?>
			<div class="mpc-notice mpcdp_settings_section">
				<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
					<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
						<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
							<span class="theme_customizer_icon dashicons dashicons-saved"></span>
							<div class="mpcdp_settings_option_description">
								<div class="mpcdp_option_label"><?php echo esc_html( $this->notice['message'] ); ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Show shortcode table item in header
		 */
		public function show_shortcode() {
			$table_id = $this->table_index();

			if ( empty( $table_id ) ) {
				return;
			}

			$title = '';
			$desc  = '';
			if ( ! empty( $this->post_id ) ) {
				$post  = get_post( $this->post_id );
				$title = $post->post_title;
				$desc  = $post->post_content;
			}

			$title = $title ?? __( 'Product table', 'multiple-products-to-cart-for-woocommerce' );
			$desc  = $desc ?? __( 'Product table shortcode details.', 'multiple-products-to-cart-for-woocommerce' );

			?>
			<div class="mpcdp_settings_section">
				<div class="mpcdp_settings_toggle mpcdp_container mpc-shortcode" data-toggle-id="footer_theme_customizer">
					<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
						<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
							<div class="mpcdp_settings_option_description">
								<div class="mpcdp_option_label">
									<span class="theme_customizer_icon dashicons dashicons-shortcode"></span>
									<?php echo esc_html( $title ); ?>
								</div>
								<?php if ( ! empty( $desc ) ) : ?>
									<div class="mpcdp_option_description">
										<?php echo wp_kses_post( $desc ); ?>
									</div>
								<?php endif; ?>
								<div class="mpcdp_row" style="margin-top: 30px;">
									<div class="mpcdp_settings_option_description col-md-6">
										<textarea class="mpc-opt-sc" readonly="">[woo-multi-cart table="<?php echo esc_attr( $table_id ); ?>"]</textarea>
									</div>
									<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
										<span class="mpc-opt-sc-btn copy">
											<?php echo esc_html__( 'Copy', 'multiple-products-to-cart-for-woocommerce' ); ?>
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}



		/**
		 * Parse shortcode attributes
		 *
		 * @param string $code shortcode attributes string.
		 */
		public function parse_atts_array( $code ) {

			$code = str_replace( '[', '', $code );
			$code = str_replace( ']', '', $code );
			$code = str_replace( 'woo-multi-cart', '', $code );

			if ( empty( $code ) && strlen( $code ) < 10 ) {
				return '';
			}

			$atts = shortcode_parse_atts( $code );

			return $atts;
		}

		/**
		 * Define boolean values of shortcode attributes
		 *
		 * @param array $atts shortcode attributes.
		 */
		public function redefine_boolean( $atts ) {

			$atts_ = array();

			foreach ( $atts as $name => $value ) {

				if ( false !== strpos( $value, 'true' ) ) {

					$atts_[ $name ] = true;

				} elseif ( false !== strpos( $value, 'false' ) ) {

					$atts_[ $name ] = false;

				} elseif ( is_numeric( $value ) ) {

					$atts_[ $name ] = (int) $value;

				} else {

					$atts_[ $name ] = $value;

				}
			}

			return $atts_;
		}

		/**
		 * Get shortcode data
		 *
		 * @param boolean $attribute if yes, return shortcode string, else parsed array.
		 */
		public function get_shortcode( $attribute = false ) {

			$table_id = $this->table_index();

			if ( empty( $table_id ) ) {
				return array();
			}

			$code = '';

			$cpt_id = $this->cpt_id( $table_id );

			if ( ! empty( $cpt_id ) ) {

				$this->table_id = $table_id;
				$this->post_id  = $cpt_id;

				$code = get_post_meta( $cpt_id, 'shortcode', true );
			} else {
				/**
				 * Legacy code - will be deleted in later versions.
				 */
				$code = get_option( 'mpcasc_code' . $table_id );
			}

			if ( empty( $code ) ) {
				return array();
			}

			$code = wp_unslash( $code );

			if ( false === $attribute ) {
				return $code;
			}

			$atts = $this->parse_atts_array( $code );

			if ( empty( $atts ) || ! is_array( $atts ) ) {
				return array();
			}

			$atts = $this->redefine_boolean( $atts );

			return $atts;
		}

		/**
		 * Get frontend shortcode data
		 *
		 * @param int     $table_id  shortcode table id.
		 * @param boolean $attribute return attribute string if yes, else parsed attributes array.
		 */
		public function get_frontend_shortcode( $table_id, $attribute = false ) {

			$code = '';

			$cpt_id = $this->cpt_id( $table_id );

			if ( ! empty( $cpt_id ) ) {

				$this->table_id = $table_id;
				$this->post_id  = $cpt_id;

				$code = get_post_meta( $cpt_id, 'shortcode', true );

			} else {

				/**
				 * Legacy code - will be deleted in later versions.
				 */
				$code = get_option( 'mpcasc_code' . $table_id );

			}

			if ( empty( $code ) ) {
				return '';
			}

			$code = wp_unslash( $code );

			if ( false === $attribute ) {
				return '[woo-multi-cart ' . $code . ']';
			}

			$atts = $this->parse_atts_array( $code );

			if ( empty( $atts ) || ! is_array( $atts ) ) {
				return array();
			}

			$atts = $this->redefine_boolean( $atts );

			return $atts;
		}



		/**
		 * Display single shortcode table content
		 */
		public function edit_shortcode() {
			global $mpc__;

			$atts = $this->get_shortcode( true );
			$this->show_shortcode();

			foreach ( $mpc__['fields']['new_table'] as $section ) {

				// get section title.
				$title = ! empty( $atts ) ? __( 'Edit Product Table', 'multiple-products-to-cart-for-woocommerce' ) : $section['section'];

				?>
				<div class="mpcdp_settings_section">
					<div class="mpcdp_settings_section_title">
						<?php echo wp_kses_post( $title ); ?>
					</div>
					<?php foreach ( $section['fields'] as $fld ) : ?>
						<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="wmca_default_quantity">
							<div class="mpcdp_settings_option visible" data-field-id="wmca_default_quantity">
								<?php $this->edit_item( $fld, $atts ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<?php

			}
		}

		/**
		 * Display shortcode attribute fields
		 *
		 * @param array $fld  shortcode input field data.
		 * @param array $atts shortcode attributes parsed array data.
		 */
		public function edit_item( $fld, $atts ) {
			$name        = isset( $fld['key'] ) ? $fld['key'] : '';
			$label       = isset( $fld['label'] ) ? $fld['label'] : '';
			$desc        = isset( $fld['desc'] ) ? $fld['desc'] : '';
			$class       = isset( $fld['class'] ) ? $fld['class'] : '';
			$placeholder = isset( $fld['placeholder'] ) ? $fld['placeholder'] : '';

			$min   = isset( $fld['min'] ) ? $fld['min'] : '';
			$max   = isset( $fld['max'] ) ? $fld['max'] : '';
			$value = '';

			if ( isset( $atts[ $name ] ) ) {
				$value = $atts[ $name ];
			} elseif ( isset( $fld['default'] ) && ! empty( $fld['default'] ) ) {
				$value = $fld['default'];
			}

			// custom.
			if ( ! empty( $this->post_id ) ) {
				$post = get_post( $this->post_id );

				if ( 'shortcode_title' === $fld['key'] ) {
					$value = $post->post_title;
				} elseif ( 'shortcode_desc' === $fld['key'] ) {
					$value = $post->post_content;
				}
			}

			if ( in_array( $fld['key'], array( 'ids', 'selected', 'skip_products', 'cats' ), true ) ) {

				?>
				<div class="mpcdp_row">
					<div class="mpcdp_settings_option_description col-md-12">
						<div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
						<div class="mpcdp_option_description">
							<?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
						</div>
					</div>
				</div>
				<div class="mpcdp_row">
					<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-12">
						<div class="choicesdp <?php echo esc_html( $name ); ?>">
							<?php $this->itembox( $fld, $value ); ?>
						</div>
					</div>
				</div>
				<?php

				return;

			}

			if ( 'sortable' === $fld['type'] ) {

				?>
				<div class="mpcdp_row">
					<div class="mpcdp_settings_option_description col-md-12">
						<div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
						<div class="mpcdp_option_description">
							<?php echo esc_html__( 'Utilize the convenient drag-and-drop feature below to rearrange the order of the product table columns. You also have the ability to activate or deactivate any columns as needed.', 'multiple-products-to-cart-for-woocommerce' ); ?>
							<br>
							<br>
							<?php
								$move_icon = '<span class="dashicons dashicons-move"></span>';
								$sort_icon = '<span class="dashicons dashicons-sort"></span>';

								echo wp_kses_post(
									sprintf(
										// translators: %1$s: move icon html, %2$s: sort icon html.
										__( 'Also note, %1$s can move up, down, left, right, but %2$s only moves up-down.', 'multiple-products-to-cart-for-woocommerce' ),
										wp_kses_post( $move_icon ),
										wp_kses_post( $sort_icon ),
									)
								);
							?>
						</div>
					</div>
				</div>
				<div class="mpcdp_row row-column-sorting">
					<?php $this->sortable( $fld, $value ); ?>
				</div>
				<?php

				return;
			}

			?>
			<div class="mpcdp_row">
				<div class="mpcdp_settings_option_description col-md-6">
					<div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
					<div class="mpcdp_option_description">
						<?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
					</div>
				</div>
				<div class="mpcdp_settings_option_description col-md-6">
					<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-12">
						<?php
							if ( 'selectbox' === $fld['type'] ) {
								printf( '<div class="choicesdp %s">', esc_html( $name ) );

								$this->itembox( $fld, $value );

								echo '</div>';
							} elseif ( 'checkbox' === $fld['type'] ) {
								$this->switchbox( $fld, $value );
							} else {
								printf(
									'<input type="text" name="%s" id="%s" min="%s" max="%s" value="%s" placeholder="%s" class="%s">',
									esc_attr( $name ),
									esc_attr( $name ),
									esc_attr( $min ),
									esc_attr( $max ),
									esc_attr( $value ),
									esc_attr( $placeholder ),
									esc_html( $class )
								);
							}
						?>
					</div>
				</div>
			</div>
			<?php
		}



		/**
		 * Display shortcode input field switchbox
		 *
		 * @param array  $fld  shortcode input field data.
		 * @param string $value saved field value.
		 */
		public function switchbox( $fld, $value = '' ) {

			if ( 'checkbox' !== $fld['type'] ) {
				return;
			}

			$checked = ! empty( $value ) && ( 'on' === $value || true === $value ) ? 'on' : 'off';

			$is_checked = 'on' === $checked ? 'checked' : '';

			printf(
				'<div class="input-field" style="display:none;"><input type="checkbox" name="%s" id="%s" data-off-title="%s" data-on-title="%s" class="hurkanSwitch-switch-input" title="%s" %s></div>',
				esc_attr( $fld['key'] ),
				esc_attr( $fld['key'] ),
				esc_html( $fld['switch_text']['off'] ),
				esc_html( $fld['switch_text']['on'] ),
				esc_html( $fld['label'] ),
				esc_attr( $is_checked )
			);

			?>
			<div class="hurkanSwitch hurkanSwitch-switch-plugin-box">
				<div class="hurkanSwitch-switch-box switch-animated-<?php echo esc_attr( $checked ); ?>">
					<a class="hurkanSwitch-switch-item <?php echo 'on' === $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-success  hurkanSwitch-switch-item-status-on" style="width:100px !important">
						<span class="lbl"><?php echo esc_html( $fld['switch_text']['on'] ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
					<a class="hurkanSwitch-switch-item <?php echo 'off' === $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-  hurkanSwitch-switch-item-status-off" style="width:90px !important">
						<span class="lbl"><?php echo esc_html( $fld['switch_text']['off'] ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
				</div>
			</div>
			<?php
		}

		/**
		 * Sshortcode input field item dropdown combo-box
		 *
		 * @param array  $fld  shortcode input field data.
		 * @param string $value saved field value.
		 */
		public function itembox( $fld, $value = '' ) {
			global $mpc__;

			$name = isset( $fld['key'] ) ? $fld['key'] : '';
			$type = isset( $fld['content_type'] ) ? $fld['content_type'] : '';

			$values = array();

			if ( is_array( $value ) ) {
				$values = $value;
			} else {
				$values = explode( ',', str_replace( ' ', '', $value ) );
			}

			if ( 'columns' === $fld['key'] && ! in_array( 'variation', $values, true ) ) {
				$values[] = 'variation';
			}

			$multiple = isset( $fld['multiple'] ) && $fld['multiple'] ? 'multiple' : '';

			printf(
				'<select id="%s" class="mpc-sc-itembox" %s>',
				esc_attr( $name ),
				esc_attr( $multiple )
			);

			if ( 'static' === $type ) {
				foreach ( $fld['options'] as $val => $label ) {

					$is_selected = in_array( $val, $values, true ) ? 'selected' : '';

					if ( ! $mpc__['has_pro'] && isset( $fld['pro_options'] ) && in_array( $val, $fld['pro_options'], true ) ) {
						$is_selected = 'disabled';
					}

					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $val ),
						esc_attr( $is_selected ),
						esc_html( $label )
					);

				}
			} else {
				foreach ( $values as $id ) {
					if ( empty( $id ) ) {
						continue;
					}

					$id = (int) $id;

					$is_selected = 'selected';

					if ( ! $mpc__['has_pro'] && isset( $fld['pro_options'] ) && in_array( $id, $fld['pro_options'], true ) ) {
						$is_selected = 'disabled';
					}

					if ( 'cats' === $fld['key'] ) {
						$term  = get_term( $id );
						$label = $term->name;
					} else {
						// skipping further checking as it will only be products.
						$label = get_the_title( $id );
					}

					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $id ),
						esc_attr( $is_selected ),
						esc_html( $label )
					);
				}
			}

			echo '</select>';

			if ( is_array( $value ) ) {
				$value = implode( ',', $value );
			}

			printf(
				'<input type="hidden" class="choicesdp-field" name="%s" value="%s">',
				esc_attr( $name ),
				esc_html( $value )
			);
		}

		/**
		 * Ajax search items for dorpdown combo-box
		 */
		public function ajax_itembox_search() {

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

				$query    = new WP_Query( $args );
				$products = array();

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

		/**
		 * Shortcode input field column sorting
		 *
		 * @param array  $fld   shortcode input field data.
		 * @param string $value saved field value.
		 */
		public function sortable( $fld, $value ) {
			$helper_cls = new MPCAdminHelper();

			if ( empty( $value ) ) {
				$value = get_option( 'wmc_sorted_columns' );
			}

			?>
			<div class="mpcdp_settings_option_description col-md-6">
				<div class="mpcdp_option_label"><?php echo esc_html__( 'Active Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
				<div class="mpc-sortable mpca-sorted-options">
					<ul id="active-mpc-columns" class="connectedSortable ui-sortable">
						<?php $helper_cls->column_list( $value, true ); ?>
					</ul>
				</div>
			</div>
			<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
				<div class="mpcdp_option_label"><?php echo esc_html__( 'Inactive Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
				<div class="mpc-sortable mpca-sorted-options">
					<ul id="inactive-mpc-columns" class="connectedSortable ui-sortable">
						<?php $helper_cls->column_list( $value, false ); ?>
					</ul>
				</div>
			</div>
			<?php

			printf(
				'<input type="hidden" class="mpc-sorted-cols" name="%s" value="%s">',
				esc_attr( $fld['key'] ),
				esc_html( $value )
			);
		}



		/**
		 * DEPRICATED !!!
		 * Get old shortcode table id.
		 *
		 * @param string $return_type wheather return in-between empty table id or find a new one.
		 */
		public function legacy_index( $return_type ) {

			$index = (int) get_option( 'mpcasc_counter' );

			if ( ! empty( $index ) ) {

				$i          = 1;
				$_index     = 1;
				$empty_slot = 1;

				// at any given non-empty index, check 10 step ahead for safely finding index.
				while ( $i < ( $_index + 9 ) ) {

					// check if shortcode exists.
					$shortcode = get_option( 'mpcasc_code' . $i );

					if ( ! empty( $shortcode ) ) {
						$_index = $i + 1;
					} elseif ( 1 === $empty_slot ) {
						$empty_slot = $i;
					}

					++$i;

					// fail save everything | as no one should have 100 saved shortcodes.
					if ( 250 === $i ) {
						break;
					}
				}

				update_option( 'mpcasc_counter', $_index );

				if ( 'empty_slot' === $return_type ) {
					return $empty_slot;
				} elseif ( 'final_index' === $return_type ) {
					return $_index;
				}
			} else {

				update_option( 'mpcasc_counter', 1 );
				return 1;

			}
		}

		/**
		 * DEPRICATED !!!
		 * Display old shortcode table list
		 */
		public function display_legacy_list() {

			$index = $this->legacy_index( 'final_index' );

			if ( empty( $index ) || '' === $index ) {
				return;
			}

			$index = (int) $index;

			for ( $i = $index; $i > 0; $i-- ) {

				$code = get_option( 'mpcasc_code' . $i );

				if ( empty( $code ) || '' === $code ) {
					continue;
				}

				$this->has_shortcode = true;

				$this->list_item( $i, '', '' );
			}
		}

		/**
		 * DEPRICATED !!!
		 * Delete old shortcode table
		 *
		 * @param int $table_id old shortcode table id.
		 */
		public function legacy_delete( $table_id ) {

			delete_option( 'mpcasc_code' . $table_id );
		}
	}
}

$mpc_opt_sc = new MPCAdminTable();
$mpc_opt_sc->init();
