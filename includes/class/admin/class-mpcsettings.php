<?php
/**
 * Admin Settings Class
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPCSettings' ) ) {

	/**
	 * Admin settings content displaying class
	 */
	class MPCSettings {



		/**
		 * Dsiplay admin settings page menu
		 */
		public function menu() {
			$tab = $this->get_tab();

			$menus = array(
				array(
					'tab'  => __( 'All Tables', 'multiple-products-to-cart-for-woocommerce' ),
					'icon' => 'dashicons-saved',
				),
				array(
					'tab'  => __( 'New Table', 'multiple-products-to-cart-for-woocommerce' ),
					'icon' => 'dashicons-shortcode',
				),
				array(
					'tab'  => __( 'General Settings', 'multiple-products-to-cart-for-woocommerce' ),
					'icon' => 'dashicons-admin-settings',
				),
				array(
					'tab'  => __( 'Labels', 'multiple-products-to-cart-for-woocommerce' ),
					'icon' => 'dashicons-text',
				),
				array(
					'tab'  => __( 'Appearence', 'multiple-products-to-cart-for-woocommerce' ),
					'icon' => 'dashicons-admin-appearance',
				),
				array(
					'tab'  => __( 'Column Sorting', 'multiple-products-to-cart-for-woocommerce' ),
					'icon' => 'dashicons-sort',
				),
				array(
					'tab'  => __( 'Export', 'multiple-products-to-cart-for-woocommerce' ),
					'icon' => 'dashicons-download',
				),
				array(
					'tab'  => __( 'Import', 'multiple-products-to-cart-for-woocommerce' ),
					'icon' => 'dashicons-upload',
				),
			);

			$nonce = wp_create_nonce( 'mpc_option_tab' );
			$page  = admin_url( 'admin.php?page=mpc-settings' );

			if ( isset( $_GET['nonce'] ) && ! empty( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
				$tab = isset( $_GET['mpctable'] ) && ! empty( $_GET['mpctable'] ) && 'new-table' === $tab ? 'all-tables' : $tab;
			}

			foreach ( $menus as $nav ) {
				$nav_ = sanitize_title( $nav['tab'] );

				$url = $page . '&tab=' . $nav_ . '&nonce=' . $nonce;
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

		/**
		 * Display admin settings page save button(s)
		 */
		public function save_btn() {
			$tab = $this->get_tab();

			$long  = 'Save Changes';
			$short = 'Save';

			if ( 'new-table' === $tab ) {
				$long  = 'Create Table';
				$short = 'Create';

				if ( isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
					if ( isset( $_GET['mpctable'] ) && ! empty( $_GET['mpctable'] ) ) {
						$long  = 'Update Table';
						$short = 'Save';
					}
				}
			}

			if ( 'all-tables' !== $tab && 'export' !== $tab && 'import' !== $tab ) :
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



		/**
		 * Admin settings page handler
		 */
		public function settings() {
			global $mpc__;

			$tab = $this->get_tab();
			if ( in_array( $tab, array( 'new-table', 'all-tables', 'column-sorting', 'import', 'export' ), true ) ) {
				$path = MPC_PATH . 'templates/admin/' . esc_attr( $tab ) . '.php';

				if ( file_exists( $path ) ) {
					include $path;
				}

				// I don't know why I added this.
				if ( ! isset( $mpc__['fields']['new_table'] ) || empty( $mpc__['fields']['new_table'] ) ) {
					return;
				}

				return;
			}

			if ( ! isset( $mpc__['fields'][ $tab ] ) || ! isset( $mpc__['fields'][ $tab ] ) ) {
				return;
			}

			$notice_done = false;
			foreach ( $mpc__['fields'][ $tab ] as $section ) {
				echo '<div class="mpcdp_settings_section">';

				printf(
					'<div class="mpcdp_settings_section_title">%s</div>',
					esc_html( $section['section'] )
				);

				if ( ! $notice_done ) {
					$notice_done = true;
					$this->show_notice();
				}

				foreach ( $section['fields'] as $fld ) {
					$this->saving_field( $fld );
					$this->field_settings( $fld );
				}

				echo '</div>';
			}
		}

		/**
		 * Display settings sidebar
		 */
		public function sidebar() {
			$path = MPC_PATH . 'templates/admin/sidebar.php';
			$path = apply_filters( 'mpc_settings_sidebar', $path );

			if ( file_exists( $path ) ) {
				include $path;
			}
		}

		/**
		 * Display notice
		 */
		public function show_notice() {
			$notice = '';
			if ( isset( $_POST['mpc_admin_settings'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_admin_settings'] ) ), 'mpc_admin_settings_save' ) ) {
				$notice = __( 'Settings Saved', 'multiple-products-to-cart-for-woocommerce' );
			}

			if ( empty( $notice ) ) {
				return;
			}

			?>
			<div class="mpc-notice mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
				<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
					<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
						<span class="theme_customizer_icon dashicons dashicons-saved"></span>
						<div class="mpcdp_settings_option_description">
							<div class="mpcdp_option_label"><?php echo esc_html( $notice ); ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}



		/**
		 * Pre-process saving settings field data
		 *
		 * @param array $fld settings field data.
		 */
		public function saving_field( $fld ) {
			global $mpc__;

			if ( ! isset( $_POST['mpc_admin_settings'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_admin_settings'] ) ), 'mpc_admin_settings_save' ) ) {
				return;
			}

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

		/**
		 * Save settings field data
		 *
		 * @param array $fld settings field data.
		 */
		public function save_field( $fld ) {
			$name = $fld['key'];

			// without a name/key to find or save data, skip.
			if ( ! isset( $name ) || empty( $name ) ) {
				return;
			}

			if ( ! isset( $_POST['mpc_admin_settings'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_admin_settings'] ) ), 'mpc_admin_settings_save' ) ) {
				return;
			}

			// only checkbox field and no data? save it as unchecked (no).
			if ( 'checkbox' === $fld['type'] && ! isset( $_POST[ $name ] ) ) {
				update_option( $name, 'no' );
				return;
			}

			// without post data, skip.
			if ( ! isset( $_POST[ $name ] ) || empty( $_POST[ $name ] ) ) {
				delete_option( $name );
				return;
			}

			update_option( $name, sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) );
		}




		/**
		 * Display settings field
		 *
		 * @param array $fld settings field data.
		 */
		public function field_settings( $fld ) {
			$name  = isset( $fld['key'] ) ? $fld['key'] : '';
			$label = isset( $fld['label'] ) ? $fld['label'] : '';
			$desc  = isset( $fld['desc'] ) ? $fld['desc'] : '';

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

		/**
		 * Display settings field follow-up, extra field options
		 *
		 * @param array $fld settings field data.
		 */
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



		/**
		 * Settings field PRO ribbon
		 *
		 * @param array $fld settings field data.
		 */
		public function pro_ribbon( $fld ) {
			global $mpc__;

			// if not pro field, skip.
			if ( ! isset( $fld['pro'] ) || empty( $fld['pro'] ) ) {
				return;
			}

			// if pro enabled, skip.
			if ( isset( $mpc__['has_pro'] ) && true === $mpc__['has_pro'] ) {
				return;
			}

			?>
			<div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">
				<?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
			</div>
			<?php
		}

		/**
		 * Settings field input field handler
		 *
		 * @param array $fld settings field data.
		 */
		public function field_inputs( $fld ) {
			global $mpc__;

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

			$pro_cls   = isset( $fld['pro'] ) && true === $fld['pro'] && false === $mpc__['has_pro'] ? 'mpcex-disabled' : '';
			$pro_label = isset( $fld['pro_label'] ) ? $fld['pro_label'] : '';
			$pro_label = empty( $pro_label ) ? $fld['label'] : $pro_label;

			if ( 'radio' === $fld['type'] && isset( $fld['options'] ) ) {
				echo '<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6"><div class="switch-field">';

				foreach ( $fld['options'] as $v => $lbl ) {
					$id         = $fld['key'] . '_' . $v;
					$is_checked = $value === $v ? 'checked' : '';

					if ( 'wmc_redirect' === $fld['key'] && 'custom' === $v && false === $mpc__['has_pro'] ) {
						$pro_cls = 'mpcex-disabled';
					}

					printf(
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

				printf(
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

				printf(
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
				printf(
					'<input name="%s" type="text" class="mpc-colorpicker" value="%s" data-default-color="">',
					esc_attr( $fld['key'] ),
					esc_html( $value )
				);

				echo '</div>';
				echo '</div>';
			} elseif ( 'number' === $fld['type'] ) {
				echo '<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">';

				printf(
					'<input type="number" name="%s" id="%s" value="%s" placeholder="%s" class="%s %s" title="%s" min="%s" max="%s">',
					esc_attr( $name ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $placeholder ),
					esc_attr( $class ),
					esc_attr( $pro_cls ),
					esc_html( $pro_label ),
					isset( $fld['min'] ) ? esc_attr( $fld['min'] ) : 1,
					isset( $fld['max'] ) ? esc_attr( $fld['max'] ) : 100,
				);

				echo '</div>';
			}

			$this->switch_box( $fld, $value );
		}

		/**
		 * Settings swich field
		 *
		 * @param array  $fld   settings field data.
		 * @param string $value saved field value.
		 */
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



		/**
		 * Get settings tab
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
		 * Save global table columns settings
		 */
		public function save_sorted_columns() {
			if ( ! isset( $_POST['mpc_col_sort'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_col_sort'] ) ), 'mpc_col_sort_save' ) ) {
				return;
			}

			if ( ! isset( $_POST ) || empty( $_POST ) ) {
				return;
			}

			if ( ! isset( $_POST['wmc_sorted_columns'] ) || empty( $_POST['wmc_sorted_columns'] ) ) {
				return;
			}

			update_option( 'wmc_sorted_columns', sanitize_text_field( wp_unslash( $_POST['wmc_sorted_columns'] ) ) );
		}
	}
}
