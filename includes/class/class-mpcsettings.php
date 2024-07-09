<?php
/**
 * Admin Settings Class
 *
 * @package WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since 7.0
 */

if ( ! class_exists( 'MPCSettings' ) ) {
	class MPCSettings {
		function __construct(){}


		public function get_tab() {
			$tab = 'new-table';

			if ( isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ) {
				if ( isset( $_GET['nonce'] ) && ! empty( $_GET['nonce'] ) &&
					wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
					$tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
				}
			}

			global $mpc__;

			if ( isset( $mpc__['settings_tab'] ) && ! empty( $mpc__['settings_tab'] ) ) {
				$tab = sanitize_title( $mpc__['settings_tab'] );
			}

			return $tab;
		}
		public function notice( $msg ) {
			if ( ! isset( $_POST ) || empty( $_POST ) ) {
				return;
			}

			?>
			<div class="mpc-notice mpcdp_settings_section">
				<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
					<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
						<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
							<span class="theme_customizer_icon dashicons dashicons-saved"></span>
							<div class="mpcdp_settings_option_description">
								<div class="mpcdp_option_label"><?php echo esc_html( $msg ); ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		public function save_btn(){
			$tab = $this->get_tab();
			
			$long = 'Save Changes';
			$short = 'Save';

			if( 'new-table' === $tab ){
				$long = 'Create Table';
				$short = 'Create';
				
				if( isset( $_GET['mpctable'] ) && ! empty( $_GET['mpctable'] ) ){
					$long = 'Update Table';
					$short = 'Update';
					// $table_id = sanitize_key( wp_unslash( $_GET['mpctable'] ) );
				}
			}

			if( 'all-tables' !== $tab ) :
			?>
			<div class="mpcdp_settings_submit">
				<div class="submit">
					<button class="mpcdp_submit_button">
						<div class="save-text"><?php echo esc_html( $long ); ?></div>
						<div class="save-text save-text-mobile"><?php echo esc_html( $short ); ?></div>
					</button>
				</div>
			</div>
			<?php
			endif;
		}
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
		}


		public function menu() {
			$menus = array(
				'table'    => array(
					array(
						'tab'  => 'All Tables',
						'icon' => 'dashicons-saved',
					),
					array(
						'tab'  => 'New Table',
						'icon' => 'dashicons-shortcode',
					),
					array(
						'tab'  => 'General Settings',
						'icon' => 'dashicons-admin-settings',
					),
				),
				'settings' => array(
					array(
						'tab'  => 'General Settings',
						'icon' => 'dashicons-admin-settings',
					),
					array(
						'tab'  => 'Labels',
						'icon' => 'dashicons-text',
					),
					array(
						'tab'  => 'Appearence',
						'icon' => 'dashicons-admin-appearance',
					),
					array(
						'tab'  => 'Column Sorting',
						'icon' => 'dashicons-sort',
					),
				),
			);

			$tab = $this->get_tab();

			if ( in_array( $tab, array( 'all-tables', 'new-table' ), true ) ) {
				$navs = $menus['table'];
			} else {
				$navs = $menus['settings'];
			}

			$nonce = wp_create_nonce( 'mpc_option_tab' );

			// get current page.
			$page = admin_url( 'admin.php?page=mpc-settings' );

			foreach ( $navs as $nav ) {

				$nav_ = sanitize_title( $nav['tab'] );
				$url  = $page . '&tab=' . $nav_ . '&nonce=' . $nonce;

				if ( 'all-tables' === $nav_ ) {
					$url = admin_url( 'admin.php?page=mpc-shortcodes' );
				} elseif ( 'new-table' === $nav_ ) {
					$url = admin_url( 'admin.php?page=mpc-shortcode' );
				}

				$is_active = $nav_ === $tab ? 'active' : '';

				?>
				<a href="<?php echo esc_url( $url ); ?>">
					<div class="mpcdp_settings_tab_control <?php echo esc_attr( $is_active ); ?>" data-tab="<?php echo esc_attr( $nav_ ); ?>">
						<span class="dashicons <?php echo esc_attr( $nav['icon'] ); ?>"></span>
						<span class="label">
							<?php echo esc_html( $nav['tab'] ); ?>
						</span>
					</div>
				</a>
				<?php

			}
		}


		public function save_field( $fld ) {

			// without a name/key to find or save data, skip.
			if ( ! isset( $fld['key'] ) || empty( $fld['key'] ) ) {
				return;
			}

			$name = $fld['key'];

			// only checkbox field and no data? save it as unchecked (no).
			if ( 'checkbox' === $fld['type'] && ! isset( $_POST[ $name ] ) ) {
				update_option( $name, 'no' );
				return;
			}

			// without post data, skip.
			if ( ! isset( $_POST[ $name ] ) || empty( $_POST[ $name ] ) ) {
				return;
			}

			update_option( $name, sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) );
		}
		public function saving_field( $fld ) {
			global $mpc__;

			if ( ! isset( $_POST ) || empty( $_POST ) ) {
				return;
			}

			// don't save pro field data without existing pro version.
			if ( isset( $fld['pro'] ) && true === $fld['pro'] && false === $mpc__['has_pro'] ) {
				return;
			}

			$this->save_field( $fld );

			// handle followup also.
			if ( ! isset( $fld['followup'] ) || empty( $fld['followup'] ) ) {
				return;
			}

			foreach ( $fld['followup'] as $followup_field ) {
				$this->save_field( $followup_field );
			}
		}
		public function pro_ribbon( $fld ) {

			// if not pro field, skip.
			if ( ! isset( $fld['pro'] ) || empty( $fld['pro'] ) ) {
				return;
			}

			// if pro enabled, skip.
			global $mpc__;
			if ( isset( $mpc__['has_pro'] ) && true === $mpc__['has_pro'] ) {
				return;
			}

			?>
			<div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">PRO</div>
			<?php
		}
		public function switch_box( $fld, $value ) {
			if ( 'checkbox' !== $fld['type'] ) {
				return;
			}

			$checked = ! empty( $value ) && ( 'on' === $value || true === $value ) ? 'on' : 'off';

			?>
			<div class="hurkanSwitch hurkanSwitch-switch-plugin-box">
				<div class="hurkanSwitch-switch-box switch-animated-<?php echo esc_attr( $checked ); ?>">
					<a class="hurkanSwitch-switch-item <?php echo 'on' === $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-success  hurkanSwitch-switch-item-status-on">
						<span class="lbl"><?php echo esc_html( $fld['switch_text']['on'] ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
					<a class="hurkanSwitch-switch-item <?php echo 'off' === $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-  hurkanSwitch-switch-item-status-off">
						<span class="lbl"><?php echo esc_html( $fld['switch_text']['off'] ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
				</div>
			</div>
			<?php
		}
		public function field_inputs( $fld ) {
			if ( empty( $fld ) ) {
				return;
			}

			$name        = isset( $fld['key'] ) ? $fld['key'] : '';
			$placeholder = isset( $fld['placeholder'] ) ? $fld['placeholder'] : '';
			$class       = isset( $fld['class'] ) ? $fld['class'] : '';

			$value = get_option( $name );
			if ( empty( $value ) && isset( $fld['default'] ) ) {
				$value = $fld['default'];
			}

			global $mpc__;
			$pro_cls   = isset( $fld['pro'] ) && true === $fld['pro'] && false === $mpc__['has_pro'] ? 'mpcex-disabled' : '';
			$pro_label = isset( $fld['pro_label'] ) ? $fld['pro_label'] : '';
			$pro_label = empty( $pro_label ) ? $fld['label'] : $pro_label;

			if ( 'radio' === $fld['type'] && isset( $fld['options'] ) ) {

				echo '<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6"><div class="switch-field">';

				foreach ( $fld['options'] as $v => $lbl ) {
					$id = $fld['key'] . '_' . $v;

					$is_checked = $value === $v ? 'checked' : '';

					if ( 'wmc_redirect' === $fld['key'] && 'custom' === $v && false === $mpc__['has_pro'] ) {
						$pro_cls = 'mpcex-disabled';
					}

					echo sprintf(
						'<input type="radio" id="%s" name="wmc_redirect" value="%s" class="%s" title="%s" %s><label for="%s">%s</label>',
						esc_attr( $id ),
						esc_attr( $v ),
						esc_attr( $pro_cls ),
						esc_html( $pro_label ),
						esc_attr( $is_checked ),
						esc_attr( $id ),
						esc_html( $lbl ),
					);
				}

				echo '</div></div>';

			} elseif ( 'text' === $fld['type'] ) {

				echo '<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">';

				echo sprintf(
					'<input type="text" name="%s" id="%s" value="%s" placeholder="%s" class="%s %s" title="%s">',
					esc_attr( $name ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $placeholder ),
					esc_attr( $class ),
					esc_attr( $pro_cls ),
					esc_html( $pro_label )
				);

				echo '</div>';

			} elseif ( 'checkbox' === $fld['type'] ) {

				echo '<div class="input-field" style="display: none;">';

				$is_checked = ! empty( $value ) && 'on' === $value ? 'checked' : '';

				echo sprintf(
					'<input type="checkbox" name="%s" id="%s" data-off-title="%s" data-on-title="%s" class="hurkanSwitch-switch-input %s" title="%s" %s>',
					esc_attr( $name ),
					esc_attr( $name ),
					esc_html( $fld['switch_text']['off'] ),
					esc_html( $fld['switch_text']['on'] ),
					esc_attr( $pro_cls ),
					esc_html( $pro_label ),
					esc_attr( $is_checked )
				);

				echo '</div>';

			} elseif ( 'color' === $fld['type'] ) {
				echo '<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">';

				echo '<div class="mpc-colorp">';
				echo sprintf(
					'<input name="%s" type="text" class="mpc-colorpicker" value="%s" data-default-color="#ffffff">',
					esc_attr( $fld['key'] ),
					esc_html( $value )
				);

				echo '</div>';
				echo '</div>';
			}

			$this->switch_box( $fld, $value );
		}
		public function field_followup( $fld ) {
			if ( ! isset( $fld['followup'] ) || empty( $fld['followup'] ) ) {
				return;
			}

			$display = 'none';
			if ( isset( $fld['followup_depends'] ) && ! empty( $fld['followup_depends'] ) ) {

				$value = get_option( $fld['key'] );

				if ( ! empty( $value ) && $value === $fld['followup_depends'] ) {
					$display = 'block';
				}
			}

			foreach ( $fld['followup'] as $sfld ) :

				$name        = isset( $sfld['key'] ) ? $sfld['key'] : '';
				$label       = isset( $sfld['label'] ) ? $sfld['label'] : '';
				$desc        = isset( $sfld['desc'] ) ? $sfld['desc'] : '';
				$placeholder = isset( $sfld['placeholder'] ) ? $sfld['placeholder'] : '';

				$value = get_option( $name );
				?>
				<div class="mpcdp_settings_option" data-field-id="<?php echo esc_attr( $fld['key'] ); ?>" data-depends-on-<?php echo esc_attr( $fld['key'] ); ?>="on" data-depends-on="<?php echo esc_attr( $fld['key'] ); ?>" data-visible="false" style="display: <?php echo esc_attr( $display ); ?>;">
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-6">
							<div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
							<?php if ( ! empty( $desc ) ) : ?>
								<div class="mpcdp_option_description">
									<?php echo wp_kses_post( $desc ); ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
							<input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_html( $value ); ?>" placeholder="<?php echo esc_html( $placeholder ); ?>">
						</div>
					</div>
				</div>
				<?php
			endforeach;
		}
		public function field_settings( $fld ) {
			$name  = isset( $fld['key'] ) ? $fld['key'] : '';
			$label = isset( $fld['label'] ) ? $fld['label'] : '';
			$desc  = isset( $fld['desc'] ) ? $fld['desc'] : '';

			$this->saving_field( $fld );
			?>
			<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="<?php echo esc_attr( $name ); ?>">
				<div class="mpcdp_settings_option visible" data-field-id="<?php echo esc_attr( $name ); ?>">
					<?php $this->pro_ribbon( $fld ); ?>
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-6">
							<div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
							<?php if ( ! empty( $desc ) ) : ?>
								<div class="mpcdp_option_description">
									<?php echo wp_kses_post( $desc ); ?>
								</div>
							<?php endif; ?>
						</div>
						<?php $this->field_inputs( $fld ); ?>
					</div>
				</div>
				<?php $this->field_followup( $fld ); ?>
			</div>
			<?php
		}
		public function settings() {

			$tab = $this->get_tab();
			if ( 'new-table' !== $tab && 'all-tables' !== $tab ) {
				// show saved settings notice.
				$this->notice( 'Settings Saved' );
			}

			if ( in_array( $tab, array( 'new-table', 'all-tables', 'column-sorting' ), true ) ) {
				$path = MPC_PATH . 'templates/admin/template-parts/' . esc_attr( $tab ) . '.php';

				if ( file_exists( $path ) ) {
					include $path;
				}

				return;
			}

			global $mpc__;
			if ( ! isset( $mpc__['fields'][ $tab ] ) || ! isset( $mpc__['fields'][ $tab ] ) ) {
				return;
			}

			foreach ( $mpc__['fields'][ $tab ] as $section ) {

				echo '<div class="mpcdp_settings_section">';

				echo sprintf(
					'<div class="mpcdp_settings_section_title">%s</div>',
					esc_html( $section['section'] )
				);

				foreach ( $section['fields'] as $fld ) {
					$this->field_settings( $fld );
				}

				echo '</div>';
			}
		}

		/**
		 * Column Sorting
		 */
		public function save_sorted_columns() {
			if ( ! isset( $_POST ) || empty( $_POST ) ) {
				return;
			}

			if ( ! isset( $_POST['wmc_sorted_columns'] ) || empty( $_POST['wmc_sorted_columns'] ) ) {
				return;
			}

			update_option( 'wmc_sorted_columns', sanitize_text_field( wp_unslash( $_POST['wmc_sorted_columns'] ) ) );
		}


		public function sorted_columns( $ret_array = false ) {
			global $mpc__;
			$def = 'wmc_ct_image,wmc_ct_product,wmc_ct_price,wmc_ct_variation,wmc_ct_quantity,wmc_ct_buy';

			$cols = get_option( 'wmc_sorted_columns' );
			if ( empty( $cols ) ) {
				$cols = $def;
			}

			$cols = str_replace( ' ', '', $cols );

			if( $mpc__['has_pro'] ){
				return true === $ret_array ? explode( ',', $cols ) : $cols;
			}
			
			// if no pro.
			$c = explode( ',', $cols );
			$def_cols = explode( ',', $def );
			
			$cols = array();
			
			foreach( $c as $col ){
				if( ! in_array( $col, $def_cols, true ) ){
					continue;
				}
				$cols[] = $col;
			}

			return true === $ret_array ? $cols : implode( $cols );
		}
		public function columns_list( $is_active = false ) {
			global $mpc__;

			$all_columns = array(
				'wmc_ct_image'     => 'Image',
				'wmc_ct_product'   => 'Product',
				'wmc_ct_price'     => 'Price',
				'wmc_ct_variation' => 'Variation',
				'wmc_ct_quantity'  => 'Quantity',
				'wmc_ct_buy'       => 'Buy',
				'wmc_ct_category'  => 'Category',
				'wmc_ct_stock'     => 'Stock',
				'wmc_ct_tag'       => 'Tag',
				'wmc_ct_sku'       => 'SKU',
				'wmc_ct_rating'    => 'Rating',
			);

			// non-removeable, but moveable columns.
			$stones = array( 'wmc_ct_variation' );

			if ( ! $mpc__['has_pro'] ) {
				$stones = array( 'wmc_ct_variation', 'wmc_ct_category', 'wmc_ct_stock', 'wmc_ct_tag', 'wmc_ct_sku', 'wmc_ct_rating' );
			}

			// check if there are already sorted columns.
			$sorted = $this->sorted_columns( true );

			$cols = array();
			if ( true === $is_active ) {
				$cols = $sorted;
			} else {
				$cols = array_diff( array_keys( $all_columns ), $sorted );
			}

			foreach ( $cols as $name ) {
				// get column name.
				$label = get_option( $name );
				if ( empty( $label ) ) {
					$label = $all_columns[ $name ];
				}

				$stone_cls = in_array( $name, $stones, true ) ? 'mpc-stone-col' : 'ui-state-default';

				echo sprintf(
					'<li class="ui-sortable-handle %s" data-meta_key="%s">%s</li>',
					esc_attr( $stone_cls ),
					esc_attr( $name ),
					esc_html( $label )
				);

			}
		}
		public function table_columns( $label, $is_active = false ) {
			?>
			<div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
				<div class="mpc-sortable mpca-sorted-options">
					<ul id="active-mpc-columns" class="connectedSortable ui-sortable">
						<?php $this->columns_list( $is_active ); ?>
					</ul>
				</div>
			<?php
		}
	}
}
