<?php
/**
 * Plugin admin page template functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Template' ) ) {

	/**
	 * Plugin admin template class
	 */
	class MPC_Admin_Template {

		/**
		 * Display admin page title
		 *
		 * @param string $contact_us_url Contact us URL.
		 */
		public static function page_title( $contact_us_url ) {
			?>
			<div id="mpcdp_logo"><?php echo esc_html__( 'Multiple Products to Cart', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
			<div id="mpcdp_customizer_wrapper"></div>
			<div id="mpcdp_toolbar_icons">
				<a class="mpcdp-tippy" target="_blank" href="<?php echo esc_url( $contact_us_url ); ?>" data-tooltip="<?php echo esc_html__( 'Support', 'multiple-products-to-cart-for-woocommerce' ); ?>">
				<span class="tab_icon dashicons dashicons-email"></span>
				</a>
			</div>
			<?php
		}

		/**
		 * Display nav item
		 *
		 * @param array $data Navigation item data.
		 */
		public static function navigation_item( $data ) {
			?>
			<a href="<?php echo esc_url( $data['url'] ); ?>">
				<div class="mpcdp_settings_tab_control <?php echo esc_attr( $data['class'] ); ?>" data-tab="<?php echo esc_attr( $data['slug'] ); ?>">
					<span class="dashicons <?php echo esc_attr( $data['icon'] ); ?>"></span>
					<span class="label">
						<?php echo esc_html( $data['tab'] ); ?>
					</span>
				</div>
			</a>
			<?php
		}

		/**
		 * Display admin settings page save button(s)
		 *
		 * @param string $settings_tab Settings tab.
		 */
		public static function save_btn( $settings_tab ) {
			if ( in_array( $settings_tab, array( 'all-tables', 'export', 'import' ), true ) ) {
				return;
			}

			$table_id = isset( $_GET['mpctable'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ? sanitize_key( wp_unslash( $_GET['mpctable'] ) ) : '';

			$btn_text = ! empty( $table_id ) ? esc_html__( 'Update Table', 'multiple-products-to-cart-for-woocommerce' ) : ( 'new-table' === $settings_tab ? esc_html__( 'Create Table', 'multiple-products-to-cart-for-woocommerce' ) : esc_html__( 'Save Changes', 'multiple-products-to-cart-for-woocommerce' ) );
			?>
			<div class="mpcdp_settings_submit">
				<div class="submit">
					<button class="mpcdp_submit_button">
						<div class="save-text"><?php echo esc_html( $btn_text ); ?>
						</div>
						<div class="save-text save-text-mobile"><?php echo esc_html( explode( ' ', $btn_text )[0] ); ?></div>
					</button>
				</div>
			</div>
			<?php
		}

		/**
		 * Show admin notices
		 *
		 * @param array $notice Notice data.
		 */
		public static function saved_settings_notice( $notice ) {
			if ( ! isset( $notice['msg'] ) || empty( $notice['msg'] ) ) {
				return;
			}
			?>
			<div class="mpc-notice mpcdp_settings_toggle mpcdp_container">
				<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
					<span class="theme_customizer_icon dashicons dashicons-saved"></span>
					<div class="mpcdp_settings_option_description">
						<div class="mpcdp_option_label"><?php echo esc_html( $notice['msg'] ); ?></div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Display shortcode table item
		 *
		 * @param int    $id    table id.
		 * @param string $title table title.
		 * @param string $desc  table description.
		 */
		public static function display_shortcode( $id, $title, $desc ) {
			?>
			<div class="mpcdp_settings_toggle mpcdp_container mpc-shortcode">
				<div class="mpcdp_settings_option visible">
					<div class="mpcdp_row">
						<?php self::display_shortcode_title( $title, $desc ); ?>
					</div>
					<div class="mpcdp_row">
						<?php self::display_shortcode_details( $id ); ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Display shortcode title
		 *
		 * @param string $title Shortcode title.
		 * @param string $desc  Shortcode description.
		 */
		private static function display_shortcode_title( $title, $desc ) {
			?>
			<div class="mpcdp_settings_option_description col-md-12">
				<div class="mpcdp_option_label"><?php echo esc_html( $title ); ?></div><div class="mpcdp_option_description">
					<?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Display shortcode header details
		 *
		 * @param int $id Shortcode table id.
		 */
		private static function display_shortcode_details( $id ) {
			$edit   = admin_url( 'admin.php?page=mpc-shortcode' );
			$delete = admin_url( 'admin.php?page=mpc-shortcodes' );
			$nonce  = wp_create_nonce( 'mpc_option_tab' );
			?>
			<div class="mpcdp_settings_option_description col-md-12">
				<textarea class="mpc-opt-sc" readonly >[woo-multi-cart table="<?php echo esc_attr( $id ); ?>"]</textarea>
			</div>
			<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-4 mpc-sc-btns">
				<span class="mpc-opt-sc-btn copy">
					<span class="dashicons dashicons-admin-page"></span>
					<span class="mpc-sc-label"><?php echo esc_html__( 'Copy', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
				</span>
				<a class="mpc-opt-sc-btn edit" href="<?php echo esc_url( $edit . '&tab=all-tables&mpctable=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">
					<span class="dashicons dashicons-welcome-write-blog"></span>
					<span class="mpc-sc-label"><?php echo esc_html__( 'Edit', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
				</a>
				<a class="mpc-opt-sc-btn delete" href="<?php echo esc_url( $delete . '&tab=all-tables&mpcscdlt=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">
					<span class="dashicons dashicons-trash"></span>
					<span class="mpc-sc-label"><?php echo esc_html__( 'Delete', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
				</a>
			</div>
			<?php
		}

		/**
		 * Display empty shortcode message
		 */
		public static function no_shortcode_notices() {
			$link = '<a href="' . esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ) . '">' . esc_html__( 'here', 'multiple-products-to-cart-for-woocommerce' ) . '</a>';
			?>
			<div class="mpcdp_settings_toggle mpcdp_container" style="margin-top: 30px;">
				<div class="mpcdp_settings_option visible">
					<div class="mpcdp_row">
						<div class="mpcdp_settings_option_description col-md-6">
							<div class="mpcdp_option_label"><?php echo esc_html__( 'No shortcodes found.', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
							<div class="mpcdp_option_description">
							<?php
							printf(
								// translators: %s: new product table crate link.
								esc_html__( 'Create a product table shortcode %s.', 'multiple-products-to-cart-for-woocommerce' ),
								wp_kses_post( $link )
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
		 * Display column sort section
		 *
		 * @param string $pro_state Pro state.
		 */
		public static function column_sorting( $pro_state ) {
			$value = get_option( 'wmc_sorted_columns' );

			$column_labels  = MPC_Core_Data::get_columns();
			$active_columns = ! empty( $value ) && ! is_array( $value ) ? explode( ',', str_replace( array( ' ', 'wmc_ct_' ), '', $value ) ) : array( 'image', 'product', 'price', 'variation', 'quantity', 'buy' );

			// remove pro columns on free version.
			$active_columns = empty( $pro_state ) ? array_diff( $active_columns, array( 'category', 'stock', 'tag', 'sku', 'rating' ) ) : $active_columns;
			?>
			<div class="mpcdp_settings_section">
				<div class="mpcdp_settings_section_title"><?php echo esc_html__( 'Column Sorting', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
				<div class="mpc-banner mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
					<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
						<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
							<?php self::column_sorting_desc( $pro_state ); ?>
						</div>
					</div>
				</div>
				<div class="mpcdp_settings_toggle mpcdp_container" id="column-sorting">
					<div class="mpcdp_settings_option visible">
						<div class="mpcdp_row">
							<div class="mpcdp_settings_option_description col-md-6">
								<div class="mpcdp_option_label"><?php echo esc_html__( 'Active Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
								<?php self::display_sorted_columns( $active_columns, $column_labels, $pro_state ); ?>
							</div>
							<div class="mpcdp_settings_option_description col-md-6">
								<div class="mpcdp_option_label"><?php echo esc_html__( 'Inactive Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
								<?php self::display_sorted_columns( array_diff( array_keys( $column_labels ), $active_columns ), $column_labels, $pro_state ); ?>
							</div>
						</div>
					</div>
				</div>
				<input
					class="mpc-sorted-cols"
					type="hidden"
					name="wmc_sorted_columns"
					value="<?php echo esc_html( $value ); ?>">
			</div>
			<?php
			wp_nonce_field( 'mpc_col_sort_save', 'mpc_col_sort' );
		}

		/**
		 * Display sorting columns description
		 *
		 * @param string $pro_state Pro state.
		 */
		private static function column_sorting_desc( $pro_state ) {
			?>
			<span class="theme_customizer_icon dashicons dashicons-list-view"></span>
			<div class="mpcdp_settings_option_description">
				<?php self::pro_ribbon( $pro_state ); ?>
				<div class="mpcdp_option_label"><?php echo esc_html__( 'Manage Product Table Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
				<div class="mpcdp_option_description">
					<?php echo esc_html__( 'Utilize the convenient drag-and-drop feature below to rearrange the order of the product table columns. You also have the ability to activate or deactivate any columns as needed.', 'multiple-products-to-cart-for-woocommerce' ); ?>
					<br>
					<br>
					<?php
					printf(
						// translators: %1$s: move dashicon html, %2$s: sort dashicon html.
						esc_html__( 'Also note, %1$s can move up, down, left, right, but %2$s only moves up-down.', 'multiple-products-to-cart-for-woocommerce' ),
						'<span class="dashicons dashicons-move"></span>',
						'<span class="dashicons dashicons-sort"></span>'
					);
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Pro field marker
		 *
		 * @param string $pro_state Pro state.
		 */
		private static function pro_ribbon( $pro_state ) {
			if ( ! empty( $pro_state ) ) {
				return;
			}
			?>
			<div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">
				<?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
			</div>
			<?php
		}

		/**
		 * Display sorted table columns
		 *
		 * @param array  $columns       All table columns.
		 * @param array  $column_labels Column labels.
		 * @param string $pro_state     Pro state.
		 */
		private static function display_sorted_columns( $columns, $column_labels, $pro_state ) {
			?>
			<div class="mpc-sortable mpca-sorted-options">
				<ul id="<?php echo ! empty( $pro_state ) ? 'active' : 'inactive'; ?>-mpc-columns" class="connectedSortable ui-sortable">
					<?php
					foreach ( $columns as $column ) {
						self::display_column_widget( $column, $column_labels, $pro_state );
					}
					?>
				</ul>
			</div>
			<?php
		}

		/**
		 * Display sorted table column item
		 *
		 * @param string $column        Column slug.
		 * @param array  $column_labels Column labels.
		 * @param string $pro_state     Pro state.
		 */
		private static function display_column_widget( $column, $column_labels, $pro_state ) {
			$label  = get_option( 'wmc_ct_' . esc_attr( $column ), $column_labels[ $column ] );
			$no_pro = empty( $pro_state ) && in_array( $column, array( 'category', 'stock', 'tag', 'sku', 'rating' ), true );
			?>
			<li
				class="ui-sortable-handle <?php echo 'variation' === $column || $no_pro ? 'mpc-stone-col' : 'ui-state-default'; ?>"
				data-meta_key="wmc_ct_<?php echo esc_attr( $column ); ?>">
				<?php if ( $no_pro ) : ?>
					<span><?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
				<?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</li>
			<?php
		}

		/**
		 * Display admin sidebar
		 *
		 * @param array  $plugin_data Plugin data.
		 * @param string $pro_state   Pro state.
		 */
		public static function sidebar( $plugin_data, $pro_state ) {
			self::sidebar_badge( $pro_state );
			self::sidebar_support( $plugin_data, $pro_state );
			self::sidebar_pro_info( $plugin_data, $pro_state );
			self::sidebar_customize();
		}

		/**
		 * Pro license activated badge
		 *
		 * @param string $pro_state Pro state.
		 */
		private static function sidebar_badge( $pro_state ) {
			if ( empty( $pro_state ) || 'activated' !== $pro_state ) {
				return;
			}
			?>
			<div class="mpc-pro-badge">
				<svg viewBox="0 0 24 24"><g><polyline points="7 13 10 16 17 9"></polyline><circle cx="12" cy="12" r="10"></circle></g></svg>
				<h3><?php echo esc_html__( 'License Activated', 'multiple-products-to-cart-for-woocommerce' ); ?></h3>
			</div>
			<?php
		}

		/**
		 * Support section
		 *
		 * @param array  $plugin_data Plugin data.
		 * @param string $pro_state   Pro state.
		 */
		private static function sidebar_support( $plugin_data, $pro_state ) {
			?>
			<div class="site-intro">
				<h3><?php echo empty( $pro_state ) ? esc_html__( 'Contact', 'multiple-products-to-cart-for-woocommerce' ) : esc_html__( 'Premium support', 'multiple-products-to-cart-for-woocommerce' ); ?></h3>
				<div class="tagline_side">
					<?php echo esc_html__( 'Our support is what makes us No.1. We are available round the clock for any support.', 'multiple-products-to-cart-for-woocommerce' ); ?>
				</div>
				<a href="<?php echo esc_url( $plugin_data['contact_us_url'] ); ?>" target="_blank"><?php echo esc_html__( 'Submit this form', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
			</div>
			<?php
		}

		/**
		 * Pro promotional message section
		 *
		 * @param array  $plugin_data Plugin data.
		 * @param string $pro_state   Pro state.
		 */
		private static function sidebar_pro_info( $plugin_data, $pro_state ) {
			if ( ! empty( $pro_state ) && 'activated' === $pro_state ) {
				return;
			}
			?>
			<div class="site-intro">
				<h3><?php echo empty( $pro_state ) ? esc_html__( 'Add premium version', 'multiple-products-to-cart-for-woocommerce' ) : esc_html__( 'Activate your license', 'multiple-products-to-cart-for-woocommerce' ); ?></h3>
				<div class="tagline_side">
					<?php echo empty( $pro_state ) ? esc_html__( 'Get exclusive PRO features, like support for Subscription and Grouped products, extra columns like - Stock, SKU, filter by Category, add to cart button for each product, AJAX search and Cart section, section by categories', 'multiple-products-to-cart-for-woocommerce' ) : esc_html__( 'Activate your license key to get regular new updates.', 'multiple-products-to-cart-for-woocommerce' ); ?>
				</div>
				<a href="<?php echo esc_url( $plugin_data['pro_plugin_url'] ); ?>" target="_blank"><?php echo empty( $pro_state ) ? esc_html__( 'Unlock all PRO features', 'multiple-products-to-cart-for-woocommerce' ) : esc_html__( 'Activate PRO', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
			</div>
			<?php
		}

		/**
		 * Display sidebar paid customization section
		 */
		private static function sidebar_customize() {
			?>
			<div class="site-intro">
				<h3><?php echo esc_html__( 'Add new feature', 'multiple-products-to-cart-for-woocommerce' ); ?></h3>
				<div class="tagline_side">
					<?php
					printf(
						esc_html__( 'Add any custom feature quickly.', 'multiple-products-to-cart-for-woocommerce' )
					);
					?>
				</div>
				<a href="https://webfixlab.com/wordpress-offer/" target="_blank"><?php echo esc_html__( 'Starting at $99', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
			</div>
			<?php
		}

		/**
		 * Display Pro popup
		 *
		 * @param string $pro_plugin_url Pro plugin URL.
		 */
		public static function popup( $pro_plugin_url ) {
			?>
			<span class="mpcpop-close dashicons dashicons-no"></span>
			<div class="mpc-pro-tag">PRO</div>
			<div class="mpc-focus">
				<?php
				printf(
					// translators: %s: HTML skeleton to inquired feature.
					esc_html__( 'Please upgrade to get %s and other advanced features.', 'multiple-products-to-cart-for-woocommerce' ),
					'<span></span>'
				);
				?>
			</div>
			<div class="mpcex-features">
				<p><?php echo esc_html__( 'Unlock advanced features like custom columns for different tables, support for more product types, and an \'Add to cart\' button with the PRO version. These tools are designed to streamline your workflow, enhance your experience, and boost your sales. We\'re committed to delivering the best solutions for you, 24/7.', 'multiple-products-to-cart-for-woocommerce' ); ?> <a href="<?php echo esc_url( $pro_plugin_url ); ?>" target="_blank"><?php echo esc_html__( 'Read more', 'multiple-products-to-cart-for-woocommerce' ); ?></a></p>
			</div>
			<a class="mpc-get-pro" href="<?php echo esc_url( $pro_plugin_url ); ?>" target="_blank"><?php echo esc_html__( 'Upgrade Now', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
			<?php
		}
	}
}
