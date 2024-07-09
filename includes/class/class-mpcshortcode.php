<?php

if ( ! class_exists( 'MPCShortcode' ) ) {
	class MPCShortcode {
		private $has_shortcode; // whather any shortcode saved or not.
		private $post_id; // cpt post id.
		private $table_id; // shortcode table id.
		private $notice;

		function __construct() {
			$this->has_shortcode = false;
			$this->post_id       = '';
			$this->table_id      = '';
			$this->notice        = array();
		}


		public function init() {
			add_action( 'init', array( $this, 'register_mpc_table' ) );
		}
		public function change_shortcode() {
			if ( is_admin() ) {
				$this->delete_shortcode();
				$this->save_shortcode();
				$this->show_notice();
			}
		}
		public function register_mpc_table() {
			register_post_type(
				'mpc_product_table',
				array(
					'labels'              => array(
						'name'               => _x( 'Mpc Product Tables', 'post type general name', 'namespace' ),
						'singular_name'      => _x( 'Product Table', 'post type singular name', 'namespace' ),
						'add_new'            => __( 'Add a New Product Table', 'namespace' ),
						'add_new_item'       => __( 'Add a New Product Table', 'namespace' ),
						'edit_item'          => __( 'Edit Product Table', 'namespace' ),
						'new_item'           => __( 'New Product Table', 'namespace' ),
						'view_item'          => __( 'View Product Table', 'namespace' ),
						'search_items'       => __( 'Search Mpc Product Tables', 'namespace' ),
						'not_found'          => __( 'Nothing Found', 'namespace' ),
						'not_found_in_trash' => __( 'Nothing found in Trash', 'namespace' ),
						'parent_item_colon'  => '',
					),
					'description'         => 'Mpc Product Tables',
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
							<span class="mpc-opt-sc-btn copy">Copy</span>
							<a class="mpc-opt-sc-btn edit" href="<?php echo esc_url( $edit . '&tab=new-table&mpctable=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">Edit</a>
							<a class="mpc-opt-sc-btn delete" href="<?php echo esc_url( $delete . '&tab=all-tables&mpcscdlt=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">Delete</a>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		public function no_shortcode() {
			if ( true === $this->has_shortcode ) {
				return;
			}
			?>
			<div class="mpcdp_settings_toggle mpcdp_container" style="margin-top: 30px;">
				<div class="mpcdp_settings_option visible">
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-6">
							<div class="mpcdp_option_label">No shortcodes found.</div>
							<div class="mpcdp_option_description">
								Create a product table shortcode <a href="<?php echo esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ); ?>">here</a>.
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
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

			echo '<div class="mpcdp_settings_section_title">All Product Tables</div>';

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
		public function shortcode_request() {
			global $mpc__;

			$ds = '';

			foreach ( $mpc__['fields']['new_table'] as $section ) {
				foreach ( $section['fields'] as $fld ) {

					if ( in_array( $fld['key'], array( 'shortcode_title', 'shortcode_desc' ), true ) ) {
						continue;
					}

					$key  = $fld['key'];
					$item = '';

					if ( isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ) {
						$val  = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
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
		public function save_shortcode() {
			if ( ! isset( $_POST ) || empty( $_POST ) ) {
				return;
			}

			if ( ! isset( $_POST['mpc_opt_sc'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_opt_sc'] ) ), 'mpc_opt_sc_save' ) ) {
				return;
			}

			$sr = $this->shortcode_request();

			$title = '';
			if ( isset( $_POST['shortcode_title'] ) && ! empty( $_POST['shortcode_title'] ) ) {
				$title = sanitize_text_field( wp_unslash( $_POST['shortcode_title'] ) );
			}

			$desc = '';
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

			/**
			 * Legacy delete code.
			 */
			if ( ! empty( $table_id ) && 'add' === $flag ) {
				delete_option( 'mpcasc_code' . $table_id );
			}

			if ( 'add' === $flag ) {
				$post_id = wp_insert_post(
					array(
						'post_type'      => 'mpc_product_table',
						'post_title'     => ! empty( $title ) ? $title : 'Product Table',
						'post_content'   => $desc,
						'post_status'    => 'publish',
						'comment_status' => 'closed',
						'ping_status'    => 'closed',
					)
				);

				if( empty( $title ) ){
					$title = ! empty( $title ) ? $title : 'Product Table';
					
					$a = wp_update_post(
						array(
							'ID'           => $post_id,
							'post_title'   => $title . ' #' . $post_id,
						)
					);
				}

			} else {
				// update.
				$a            = wp_update_post(
					array(
						'ID'           => $post_id,
						'post_title'   => ! empty( $title ) ? $title : 'Product Table #' . $post_id,
						'post_content' => $desc,
					)
				);

				$this->notice = array(
					'status'  => 'updated',
					'message' => 'Shortcode updated.',
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

				$url = $page . '&tab=new-table&mpctable=' . $table_id . '&nonce=' . $nonce;

				header( 'Location: ' . $url );
				exit();
			}
		}
		public function delete_shortcode() {
			// get table index.
			$table_id = $this->table_index();
			if ( empty( $table_id ) ) {
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
					'message' => 'Shortcode deleted.',
				);
			}

			$this->legacy_delete( $table_id );
		}
		public function show_notice() {

			// return if no notices found.
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
		public function show_shortcode() {
			$table_id = $this->table_index();
			if ( empty( $table_id ) ) {
				return;
			}

			$atts = $this->get_shortcode( true );

			$title = isset( $atts['shortcode_title'] ) ? $atts['shortcode_title'] : 'Product table';

			?>
			<div class="mpcdp_settings_section">
				<div class="mpcdp_settings_toggle mpcdp_container mpc-shortcode">
					<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
						<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
							<span class="theme_customizer_icon dashicons dashicons-shortcode"></span>
							<div class="mpcdp_settings_option_description">
								<div class="mpcdp_option_label"><?php echo esc_html( $title ); ?></div>
							</div>
						</div>
					</div>
					<div class="mpcdp_settings_option visible">
						<div class="mpcdp_row">
							<div class="mpcdp_settings_option_description col-md-6">
								<textarea class="mpc-opt-sc" readonly="">[woo-multi-cart table="<?php echo esc_attr( $table_id ); ?>"]</textarea>
							</div>
							<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
								<span class="mpc-opt-sc-btn copy">Copy</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}


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



		public function itembox_items( $fld ) {
			$items = array();
			$type  = isset( $fld['content_type'] ) ? $fld['content_type'] : '';

			if ( 'product_cat' === $type ) {
				$args       = array(
					'taxonomy'   => 'product_cat',
					'orderby'    => 'name',
					'hide_empty' => true,
				);
				$categories = get_categories( $args );

				if ( empty( $categories ) ) {
					return $items;
				}

				foreach ( $categories as $cat ) {
					$items[ $cat->term_id ] = $cat->name;
				}
			}

			if ( 'product' === $type ) {
				$args = array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				);

				// remove hooks for nuiscense.
				remove_all_filters( 'pre_get_posts' );
				remove_all_filters( 'posts_orderby' );

				// get products from query.
				$products = new WP_Query( $args );
				wp_reset_postdata();

				if ( empty( $products ) || empty( $products->posts ) ) {
					return $items;
				}

				foreach ( $products->posts as $post ) {
					$items[ $post->ID ] = $post->post_title;
				}
			}

			return $items;
		}
		public function itembox( $fld, $value = '' ) {
			global $mpc__;

			$name = isset( $fld['key'] ) ? $fld['key'] : '';
			$type = isset( $fld['content_type'] ) ? $fld['content_type'] : '';

			$v = array();
			if ( ! empty( $value ) ) {
				$v = explode( ',', str_replace( ' ', '', $value ) );
			}

			$vls = array();
			if ( ! empty( $v ) ) {
				foreach ( $v as $id ) {
					if ( in_array( $type, array( 'product', 'product_cat' ), true ) ) {
						$vls[] = (int) $id;
					} else {
						$vls[] = $id;
					}
				}
			}

			$items = array();
			if ( 'static' === $type ) {
				$items = $fld['options'];
			} else {
				$items = $this->itembox_items( $fld );
			}

			if ( empty( $items ) ) {
				$label = 'items';

				if ( 'product' === $type ) {
					$label = 'products';
				} elseif ( 'product_cat' === $type ) {
					$label = 'product categories';
				}

				echo sprintf(
					'<div class="mpc-no-items"><p>No %s found.</p></div>',
					esc_html( $label )
				);

				return;
			}

			$multiple = isset( $fld['multiple'] ) && $fld['multiple'] ? 'multiple' : '';
			echo sprintf(
				'<select id="%s" class="mpc-sc-itembox" %s>',
				esc_attr( $name ),
				esc_attr( $multiple )
			);

			foreach ( $items as $v => $label ) {

				$is_selected = in_array( $v, $vls, true ) ? 'selected' : '';

				if ( ! $mpc__['has_pro'] && isset( $fld['pro_options'] ) && in_array( $v, $fld['pro_options'], true ) ) {
					$is_selected = 'disabled';
				}

				echo sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $v ),
					esc_attr( $is_selected ),
					esc_html( $label )
				);
			}

			echo '</select>';

			echo sprintf(
				'<input type="hidden" class="choicesdp-field" name="%s" value="%s">',
				esc_attr( $name ),
				esc_html( $value )
			);
		}
		public function switchbox( $fld, $value = '' ) {
			if ( 'checkbox' !== $fld['type'] ) {
				return;
			}

			$checked = ! empty( $value ) && ( 'on' === $value || true === $value ) ? 'on' : 'off';

			$is_checked = 'on' === $checked ? 'checked' : '';

			echo sprintf(
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
		public function edit_item( $fld, $atts ) {
			$name  = isset( $fld['key'] ) ? $fld['key'] : '';
			$label = isset( $fld['label'] ) ? $fld['label'] : '';
			$desc  = isset( $fld['desc'] ) ? $fld['desc'] : '';

			$placeholder = isset( $fld['placeholder'] ) ? $fld['placeholder'] : '';
			$class       = isset( $fld['class'] ) ? $fld['class'] : '';

			$min = isset( $fld['min'] ) ? $fld['min'] : '';
			$max = isset( $fld['max'] ) ? $fld['max'] : '';

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

			?>
			<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="wmca_default_quantity">
				<div class="mpcdp_settings_option visible" data-field-id="wmca_default_quantity">
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-6">
							<div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
							<div class="mpcdp_option_description">
								<?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
							</div>
						</div>
						<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
							<?php
							if ( 'selectbox' === $fld['type'] ) {
								echo sprintf( '<div class="choicesdp %s">', esc_html( $name ) );
								$this->itembox( $fld, $value );
								echo '</div>';
							} elseif ( 'checkbox' === $fld['type'] ) {
								$this->switchbox( $fld, $value );
							} else {
								echo sprintf(
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
			</div>
			<?php
		}
		public function edit_shortcode() {
			global $mpc__;

			$atts = $this->get_shortcode( true );

			$this->show_shortcode();

			foreach ( $mpc__['fields']['new_table'] as $section ) {

				echo '<div class="mpcdp_settings_section">';

				echo sprintf(
					'<div class="mpcdp_settings_section_title">%s</div>',
					esc_html( $section['section'] )
				);

				foreach ( $section['fields'] as $fld ) {
					$this->edit_item( $fld, $atts );
				}

				echo '</div>';
			}
		}


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

					$i++;

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
		public function legacy_delete( $table_id ) {
			delete_option( 'mpcasc_code' . $table_id );
		}
	}
}

$mpc_opt_sc = new MPCShortcode();
$mpc_opt_sc->init();
