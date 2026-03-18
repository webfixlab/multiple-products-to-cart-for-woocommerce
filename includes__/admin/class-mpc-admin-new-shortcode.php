<?php
/**
 * Plugin admin new shortcode table functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_New_Shortcode' ) ) {

	/**
	 * Plugin admin new shortcode table class
	 */
	class MPC_Admin_New__Shortcode{

		/**
         * Pro plugin status
         * @var string
         */
        private static $pro_state;

		/**
         * Saved shortcode attributes
         * @var array
         */
        private static $atts = array();

		/**
         * Shortcode field item data
         * @var array
         */
        private static $field;
        
		public static function init_new_table( $pro_state ){
			self::$pro_state = $pro_state;

			self::setup_table();

			$title = MPC_Core_Data::get_new_table()[0]['section'];
			?>
			<div class="mpcdp_settings_section">
				<div class="mpcdp_settings_section_title">
					<?php echo !empty( $title ) ? esc_html( $title ) : __( 'Edit Product Table', 'multiple-products-to-cart-for-woocommerce' ); ?>
				</div>
				<?php // $this->show_notice(); ?>
				<?php // $this->show_shortcode(); ?>
				<?php self::render_fields(); ?>
			</div>
			<?php
		}
		private static function setup_table(){
			$table_id = isset( $_GET['mpctable'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ? sanitize_key( wp_unslash( $_GET['mpctable'] ) ) : '';

			if( empty( $table_id ) ){
				return;
			}

			// support for legacy code added here too.
			$shortcode = get_post_status( (int) $table_id ) ? get_post_meta( (int) $table_id, 'shortcode', true ) : get_option( "mpcasc_code{$table_id}" );
			if( empty( $shortcode ) ){
				return;
			}

			$shortcode = str_replace( '[', '', $shortcode );
			$shortcode = str_replace( ']', '', $shortcode );
			$shortcode = str_replace( 'woo-multi-cart', '', $shortcode );

			self::$atts = ! empty( $shortcode ) && strlen( $shortcode ) > 10 ? shortcode_parse_atts( $code ) : array();
			self::$atts = is_array( self::$atts ) ? self::$atts : array();
		}
		private static function render_fields(){
			foreach( MPC_Core_Data::get_new_table()[0]['fields'] as $field ){
				?>
				<div class="mpcdp_settings_toggle mpcdp_container">
					<div class="mpcdp_settings_option visible">
						<?php self::render_field( $field ); ?>
					</div>
				</div>
				<?php
			}
		}
		private static function render_field( $field ){
			?>
			<div class="mpcdp_row">
				<div class="mpcdp_settings_option_description col-md-12">
					<?php self::field_title( $field ); ?>
				</div>
			</div>
			<div class="mpcdp_row row-<?php echo esc_attr( $field['key'] ); ?>">
				<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-12">
					<?php self::navigate_field( $field ); ?>
				</div>
			</div>
			<?php
		}

		private static function field_title( $field ){
			$label = $field['label'];
			?>
			<div class="mpcdp_option_label"><?php echo esc_html( $field['label'] ); ?></div>
			<div class="mpcdp_option_description">
				<?php echo 'sortable' === $field['type'] ? self::field_desc_sorted_columns() : esc_html( $field['desc'] ); ?>
			</div>
			<?php
		}
		private static function field_desc_sorted_columns(){
			echo __( 'Utilize the convenient drag-and-drop feature below to rearrange the order of the product table columns. You also have the ability to activate or deactivate any columns as needed.', 'multiple-products-to-cart-for-woocommerce' );
			printf(
				// translators: %1$s: move icon html, %2$s: sort icon html.
				__( 'Also note, %1$s can move up, down, left, right, but %2$s only moves up-down.', 'multiple-products-to-cart-for-woocommerce' ),
				'<span class="dashicons dashicons-move"></span>',
				'<span class="dashicons dashicons-sort"></span>',
			);
		}
		private static function navigate_field( $field ){
			if( 'sortable' === $field['type'] ){
				self::render_field_sortable( $field );
			} elseif ( 'selectbox' === $field['type'] ){
				self::render_field_selectbox( $field );
			} elseif ( 'checkbox' === $field['type'] ){
				self::render_field_checkbox( $field );
			} else {
				self::render_field_text( $field );
			}
		}
		private static function render_field_sortable( $field ){
			$key = $field['key'];

			$all_columns = MPC_Core_Data::get_columns();
			
			$value = !isset( self::$atts[ $key ] ) || empty( self::$atts[ $key ] ) ? get_option( 'wmc_sorted_columns' ) : self::$atts[ $key ];

			// get saved columns.
			$active_columns = !empty( $value ) && !is_array( $value ) ? explode( str_replace( array( ' ', 'wmc_ct_' ), '', $value ) ) : array( 'image', 'product', 'price', 'variation', 'quantity', 'buy' );

			// remove pro columns on free version.
			$active_columns = empty( self::$pro_state ) ? array_diff( $active_columns, array( 'category', 'stock', 'tag', 'sku', 'rating' ) ) : $active_columns;
			?>
			<div class="mpcdp_row row-column-sorting">
				<input
					type="hidden"
					class="mpc-sorted-cols"
					name="<?php echo esc_attr( $key ); ?>"
					value="<?php echo esc_html( $value ); ?>">
				<div class="mpcdp_settings_option_description col-md-6">
					<div class="mpcdp_option_label"><?php echo esc_html__( 'Active Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
					<div class="mpc-sortable mpca-sorted-options">
						<?php self::display_columns( $all_columns, $active_columns, true ); ?>
					</div>
				</div>
				<div class="mpcdp_settings_option_description col-md-6">
					<div class="mpcdp_option_label"><?php echo esc_html__( 'Active Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
					<div class="mpc-sortable mpca-sorted-options">
						<?php self::display_columns( $all_columns, $active_columns, false ); ?>
					</div>
				</div>
			</div>
			<?php
		}
		private static function display_columns( $all_columns, $active_columns, $is_active ){
			$active_columns = $is_active ? $active_columns : array_diff( array_keys( $all_columns ), $active_columns );
			?>
			<ul id="<?php echo $is_active ? 'active' : 'inactive'; ?>-mpc-columns" class="connectedSortable ui-sortable">
				<?php
					foreach( $active_columns as $column ){
						self::display_column( $column );
					}
				?>
			</ul>
			<?php
		}
		private static function display_column( $column ){
			$label = get_option( 'wmc_ct_' . esc_attr( $col ) );
			if ( empty( $label ) ) {
				$label = $labels[ $col ];
			}

			$class = 'variation' === $column || ( empty( self::$pro_state ) && in_array( $column, array( 'category', 'stock', 'tag', 'sku', 'rating' ), true ) ) ? 'mpc-stone-col' : 'ui-state-default';
			?>
			<li
				class="ui-sortable-handle <?php echo esc_attr( $class ); ?>"
				data-meta_key="wmc_ct_<?php echo esc_attr( $column ); ?>">
				<?php echo esc_html( $all_columns[ $column ] ); ?>
			</li>
			<?php
		}
		private static function render_field_selectbox( $field ){
			$key = $field['key'];
			?>
			<div class="choicesdp <?php echo esc_html( $key ); ?>">
				<input
					type="hidden"
					class="choicesdp-field"
					name="<?php echo esc_html( $key ); ?>"
					value="<?php echo isset( self::$atts[ $key ] ) ? esc_html( self::$atts[ $key ] ) : ( isset( $field['default'] ) ? esc_html( $field['default'] ) : '' ); ?>"
				<select
					id="<?php echo esc_html( $key ); ?>"
					class="mpc-sc-itembox"
					<?php echo isset( $field['multiple'] ) && $field['multiple'] ? 'multiple' : ''; ?>>
					<?php self::render_selectbox_options( $field ); ?>
				</select>
			</div>
			<?php
		}

		private static function render_selectbox_options( $field ){
			$key = $field['key'];

			$saved = isset( self::$atts[ $key ] ) ? self::$atts[ $key ] : '';
			$saved = is_array( $saved ) ? $saved : explode( ',', str_replace( ' ', '', $saved ) );

			$pro_options = 'static' === $field['content_type'] && empty( self::$pro_state ) && isset( $field['pro_options'] ) ? $field['pro_options'] : array(); // pro options, which aren't allowed in free.

			$options = 'static' === $field['content_type'] ? $field['options'] : $saved;
			foreach( $options as $value => $label ){
				$class = 'static' === $field['content_type'] ? (
					in_array( $value, $pro_options, true ) ? 'disabled' : (
						in_array( $value, $saved, true ) ? 'selected' : ''
					)
				) : 'selected';
				?>
				<option
					value="<?php echo 'static' === $field['content_type'] ? esc_attr( $value ) : esc_attr( $label ); ?>"
					<?php echo esc_attr( $class ); ?>><?php echo 'static' === $field['content_type'] ? esc_html( $label ) : esc_html( self::get_selectbox_option_label( $field, $value ) ); ?></option>
				<?php
			}
		}
		private static function get_selectbox_option_label( $field, $option ){
			if( 'cats' === $field['key'] ){
				$term = get_term( (int) $option );
				return $term->name;
			}
			return get_the_title( (int) $option );
		}
		private static function render_field_checkbox( $field ){
			$key = $field['key'];
			
			// checks saved attribute value or falls back to default value.
			$checked = isset( self::$atts[ $key ] ) ? ( self::$atts[ $key ] || 'true' === self::$atts[ $key ] || 'on' === self::$atts[ $key ] ) : ( isset( $field['default'] ) && 'on' === $field['default'] );
			?>
			<div class="input-field" style="display:none;">
				<input
					type="checkbox"
					name="<?php echo esc_attr( $key ); ?>"
					id="<?php echo esc_attr( $key ); ?>"
					data-off-title="<?php echo esc_attr( $field['switch_text']['off'] ); ?>"
					data-on-title="<?php echo esc_attr( $field['switch_text']['on'] ); ?>"
					<?php echo $checked ? 'checked' : ''; ?>>
			</div>
			<?php
			self::display_switch( $field, $checked );
		}
		private static function display_switch( $field, $checked ){
			?>
			<div class="hurkanSwitch hurkanSwitch-switch-plugin-box">
				<div class="hurkanSwitch-switch-box switch-animated-<?php echo esc_attr( $checked ); ?>">
					<a class="hurkanSwitch-switch-item <?php echo $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-success  hurkanSwitch-switch-item-status-on">
						<span class="lbl"><?php echo esc_html( $field['switch_text']['on'] ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
					<a class="hurkanSwitch-switch-item <?php echo ! $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-  hurkanSwitch-switch-item-status-off">
						<span class="lbl"><?php echo esc_html( $field['switch_text']['off'] ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
				</div>
			</div>
			<?php
		}
		private static function render_field_text( $field ){
			$key = $field['key'];
			?>
			<input
				type="text"
				name="<?php echo esc_attr( $key ); ?>"
				id="<?php echo esc_attr( $key ); ?>"
				value="<?php echo isset( self::$atts[ $key ] ) ? esc_html( self::$atts[ $key ] ) : ( isset( $field['default'] ) ? esc_html( $field['default'] ) : '' ); ?>"
				class="<?php echo esc_attr( $field['class'] ); ?>"
				placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
				<?php echo isset( $field['min'] ) ? 'min="' . esc_attr( $field['min'] ) . '"' : "" ?>
				<?php echo isset( $field['max'] ) ? 'max="' . esc_attr( $field['max'] ) . '"' : "" ?>>
			<?php
		}
	}
}
