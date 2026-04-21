<?php
/**
 * Plugin admin page input field functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Field' ) ) {

	/**
	 * Plugin admin field rendering class
	 */
	class MPC_Admin_Field {

        /**
         * Field data
         * @var array
         */
        private static $field;

        /**
         * Pro plugin status
         * @var string
         */
        private static $pro_state;

        /**
         * Current field value
         * @var string
         */
        private static $field_value = '';

        /**
         * Display settings option field
         *
         * @param array  $field     Input field data.
         * @param string $pro_state Pro plugin status.
         */
        public static function init( $field, $pro_state ){
            self::$field     = $field;
            self::$pro_state = $pro_state;

            self::$field_value = get_option( $field['key'], $field['default'] ?? '' );
			?>
			<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="<?php echo esc_attr( $field['key'] ); ?>">
				<div class="mpcdp_settings_option visible" data-field-id="<?php echo esc_attr( $field['key'] ); ?>">
					<?php self::pro_ribbon(); ?>
					<div class="mpcdp_row">
						<?php self::field_title(); ?>
						<?php self::render_field(); ?>
					</div>
				</div>
				<?php self::render_field_followup( $field ); ?>
			</div>
			<?php
        }

        /**
		 * Settings field PRO ribbon
		 */
		private static function pro_ribbon() {
            // is pro field but has no pro plugin.
			if( ! isset( self::$field['pro'] ) || ! empty( self::$pro_state ) ) {
                return;
            }
			?>
			<div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">
				<?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
			</div>
			<?php
		}

        private static function field_title(){
            ?>
            <div class="mpcdp_settings_option_description col-md-6">
                <div class="mpcdp_option_label"><?php echo esc_html( self::$field['label'] ); ?></div>
                <?php self::field_description(); ?>
            </div>
            <?php
        }
        private static function field_description(){
            if( empty( self::$field['desc'] ) ){
                return;
            }
            ?>
            <div class="mpcdp_option_description">
                <?php echo wp_kses_post( self::$field['desc'] ); ?>
            </div>
            <?php
        }

        /**
		 * Settings field input field handler
		 */
		private static function render_field() {
            $classes = array();
            if( isset( $field['class'] ) ) $classes[] = self::$field['class'];
            if( isset( $field['pro'] ) && empty( self::$pro_state ) ) $classes[] = 'mpcex-disabled';

			if( 'text' === self::$field['type'] ){
                self::render_field_input( self::$field, $classes );
            }elseif( 'number' === self::$field['type'] ){
                self::render_field_number( self::$field, $classes );
            }elseif( 'color' === self::$field['type'] ){
                self::render_field_color( self::$field, $classes );
            }elseif( 'radio' === self::$field['type'] ){
                self::render_field_radio( self::$field, $classes );
            }elseif( 'checkbox' === self::$field['type'] ){
                self::render_field_checkbox( self::$field, $classes );
            }
		}

        private static function render_field_input( $field, $classes ){
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <input
                    type="text"
                    name="<?php echo esc_attr( $field['key'] ); ?>"
                    id="<?php echo esc_attr( $field['key'] ); ?>"
                    value="<?php echo esc_attr( self::$field_value ); ?>"
                    placeholder="<?php echo esc_html( $field['placeholder'] ); ?>"
                    class="<?php echo esc_html( implode( ' ', $classes ) ); ?>"
                    title="<?php echo isset( $field['pro_label'] ) ? esc_html( $field['pro_label'] ) : esc_html( $field['label'] ); ?>">
            </div>
            <?php
        }
        private static function render_field_number( $field, $classes ){
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <input
                    type="number"
                    name="<?php echo esc_attr( $field['key'] ); ?>"
                    id="<?php echo esc_attr( $field['key'] ); ?>"
                    value="<?php echo esc_attr( self::$field_value ); ?>"
                    placeholder="<?php echo esc_html( $field['placeholder'] ); ?>"
                    class="<?php echo esc_html( implode( ' ', $classes ) ); ?>"
                    title="<?php echo isset( $field['pro_label'] ) ? esc_html( $field['pro_label'] ) : esc_html( $field['label'] ); ?>"
                    min="<?php echo isset( $field['min'] ) ? esc_attr( $field['min'] ) : 1; ?>"
                    max="<?php echo isset( $field['min'] ) ? esc_attr( $field['min'] ) : 100; ?>">
			</div>
            <?php
        }
        private static function render_field_color( $field, $classes ){
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <div class="mpc-colorp">
                    <input
                        type="text"
                        name="<?php echo esc_attr( $field['key'] ); ?>"
                        class="mpc-colorpicker <?php echo esc_html( implode( ' ', $classes ) ); ?>"
                        value="<?php echo esc_attr( self::$field_value ); ?>"
                        data-default-color="">
                </div>
            </div>
            <?php
        }
        private static function render_field_radio( $field, $classes ){
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <div class="switch-field">
                    <?php
                        foreach( $field['options'] as $opt_value => $opt_label ){
                            self::render_field_radio_option( $field, array(
                                'value'   => $opt_value,
                                'label'   => $opt_label,
                                'classes' => $classes,
                                'checked' => $opt_value === self::$field_value ? 'checked' : ''
                            ));
                        }
                    ?>
                </div>
            </div>
            <?php
        }
        private static function render_field_radio_option( $field, $option ){
            $key = $field['key'] . ' ' . $option['value'];
            ?>
            <input
                type="radio"
                name="<?php echo esc_attr( $key ); ?>"
                id="<?php echo esc_attr( $key ); ?>"
                value="<?php echo esc_attr( $option['value'] ); ?>"
                class="<?php echo esc_html( implode( ' ', $option['classes'] ) ); ?>"
                title="<?php echo isset( $field['pro_label'] ) ? esc_html( $field['pro_label'] ) : esc_html( $field['label'] ); ?>"
                <?php echo esc_attr( $option['checked'] ); ?>>
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option['label'] ); ?></label>
            <?php
        }
        private static function render_field_checkbox( $field, $classes ){
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <input
                    type="checkbox"
                    style="display:none;"
                    name="<?php echo esc_attr( $field['key'] ); ?>"
                    id="<?php echo esc_attr( $field['key'] ); ?>"
                    value="<?php echo esc_attr( self::$field_value ); ?>"
                    class="<?php echo esc_html( implode( ' ', $classes ) ); ?>"
                    title="<?php echo esc_html( $field['label'] ); ?>"
                    data-on-title="<?php echo esc_html( $field['switch_text']['on'] ); ?>"
                    data-off-title="<?php echo esc_html( $field['switch_text']['off'] ); ?>"
                    <?php echo 'on' === self::$field_value ? 'checked' : ''; ?>>
                    <?php self::render_field_checkbox_switch( $field ); ?>
			</div>
            <?php
        }
        private static function render_field_checkbox_switch( $field ){
            $value = empty( self::$field_value ) ? 'off' : self::$field_value;
            ?>
			<div class="hurkanSwitch hurkanSwitch-switch-plugin-box">
				<div class="hurkanSwitch-switch-box switch-animated-<?php echo esc_attr( $value ); ?>">
					<a class="hurkanSwitch-switch-item <?php echo 'on' === $value ? 'active' : ''; ?> hurkanSwitch-switch-item-color-success  hurkanSwitch-switch-item-status-on">
						<span class="lbl"><?php echo esc_html( $field['switch_text']['on'] ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
					<a class="hurkanSwitch-switch-item <?php echo 'off' === $value ? 'active' : ''; ?> hurkanSwitch-switch-item-color-  hurkanSwitch-switch-item-status-off">
						<span class="lbl"><?php echo esc_html( $field['switch_text']['off'] ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
				</div>
			</div>
			<?php
        }

        private static function render_field_followup( $field ){
            if( ! isset( $field['followup'] ) || empty( $field['followup'] ) ){
                return;
            }

            $display = isset( $field['followup_depends'] ) && $field['followup_depends'] === self::$field_value ? 'block' : 'none';

            foreach( $field['followup'] as $field_followup ){
                self::render_followup_field( $field_followup, $display );
            }
        }
        private static function render_followup_field( $field, $display ){
            ?>
            <div
                class="mpcdp_settings_option"
                data-field-id="<?php echo esc_attr( $field['key'] ); ?>"
                data-depends-on-<?php echo esc_attr( $field['key'] ); ?>="on"
                data-depends-on="<?php echo esc_attr( $field['key'] ); ?>"
                data-visible="false"
                style="display: <?php echo esc_attr( $display ); ?>;">
                <div class="mpcdp_row">
                    <div class="mpcdp_settings_option_description col-md-6">
                        <div class="mpcdp_option_label"><?php echo esc_html( $field['label'] ); ?></div>
                        <?php if ( ! empty( $field['desc'] ) ) : ?>
                            <div class="mpcdp_option_description">
                                <?php echo wp_kses_post( $field['desc'] ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                        <input
                            type="text"
                            name="<?php echo esc_attr( $field['key'] ); ?>"
                            id="<?php echo esc_attr( $field['key'] ); ?>"
                            value="<?php echo esc_html( self::$field_value ); ?>"
                            placeholder="<?php echo esc_html( $field['placeholder'] ); ?>">
                    </div>
                </div>
            </div>
            <?php
        }
	}
}
