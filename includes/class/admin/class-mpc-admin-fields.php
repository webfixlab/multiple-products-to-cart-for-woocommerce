<?php
/**
 * Admin input fields related functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Fields' ) ) {

	/**
	 * Admin Input Fields Class
	 */
	class MPC_Admin_Fields {

        /**
         * Field data.
         * @var array
         */
        private static $field;

        /**
         * Field name.
         * @var string
         */
        private static $name;

        /**
         * Field value.
         * @var string
         */
        private static $value;

        /**
         * Field placeholder.
         * @var string
         */
        private static $placeholder;

        /**
         * Field class.
         * @var string
         */
        private static $class;

        /**
         * Field disable class for Pro field.
         * @var string
         */
        private static $pro_cls;

        /**
         * Field popup title for Pro field.
         * @var string
         */
        private static $pro_label;

        /**
         * Plugin core data.
         * @var array
         */
        private static $data;



        /**
         * Set input field value to appropriate data
         *
         * @param array $field Field data.
         */
        public static function set( $field ){
            global $mpc__;
            if( empty( $field ) ){
                return;
            }
            
            self::$data = $mpc__;

            self::$field = $field;

            self::$name        = isset( $field['key'] ) ? $field['key'] : '';
			self::$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
			self::$class       = isset( $field['class'] ) ? $field['class'] : '';

			self::$value = get_option( self::$name );
			if ( empty( self::$value ) && isset( $field['default'] ) ) {
				self::$value = $field['default'];
			}

			self::$pro_cls   = isset( $field['pro'] ) && true === $field['pro'] && false === self::$data['has_pro'] ? 'mpcex-disabled' : '';
			self::$pro_label = isset( $field['pro_label'] ) ? $field['pro_label'] : '';
			self::$pro_label = empty( self::$pro_label ) ? $field['label'] : self::$pro_label;
        }

        /**
         * Get field dynamically
         *
         * @param array $field Field data.
         */
        public static function get( $field ){
            self::set( $field );
            
            switch ($field['type']) {
                case 'text':
                    self::text();
                    break;

                case 'number':
                    self::number();
                    break;

                case 'checkbox':
                    self::checkbox();
                    self::swich_box();
                    break;

                case 'radio':
                    self::radio();
                    break;

                case 'color':
                    self::color();
                    break;
                
                default:
                    break;
            }
        }



        /**
         * Text type input field
         */
        public static function text(){
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <input
                    type="text"
                    name="<?php echo esc_attr( self::$name ); ?>"
                    id="<?php echo esc_attr( self::$name ); ?>"
                    value="<?php echo esc_attr( self::$value ); ?>"
                    placeholder="<?php echo esc_attr( self::$placeholder ); ?>"
                    class="<?php echo esc_attr( self::$class ) . ' ' . esc_attr( self::$pro_cls ); ?>"
                    title="<?php echo esc_html( self::$pro_label ); ?>">
            </div>
            <?php
        }

        /**
         * Number type input field
         */
        public static function number(){
            $min = self::$field['min'] ?? '';
            $max = self::$field['max'] ?? '';
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <input
                    type="number"
                    name="<?php echo esc_attr( self::$name ); ?>"
                    id="<?php echo esc_attr( self::$name ); ?>"
                    value="<?php echo esc_attr( self::$value ); ?>"
                    placeholder="<?php echo esc_attr( self::$placeholder ); ?>"
                    class="<?php echo esc_attr( self::$class ) . ' ' . esc_attr( self::$pro_cls ); ?>"
                    title="<?php echo esc_html( self::$pro_label ); ?>"
                    min="<?php echo esc_attr( $min ); ?>"
                    max="<?php echo esc_attr( $max ); ?>">
            </div>
            <?php
        }

        /**
         * Checkbox type input field
         */
        public static function checkbox(){
            $checked   = ! empty( $value ) && ( 'on' === $value || true === $value ) ? 'checked' : '';

            $label_on  = self::$field['switch_text']['on'] ?? '';
            $label_off = self::$field['switch_text']['off'] ?? '';
            ?>
            <div class="input-field" style="display: none;">
                <input
                    type="checkbox"
                    name="<?php echo esc_attr( self::$name ); ?>"
                    id="<?php echo esc_attr( self::$name ); ?>"
                    class="hurkanSwitch-switch-input <?php echo esc_attr( self::$pro_cls ); ?>"
                    title="<?php echo esc_html( self::$pro_label ); ?>"
                    data-on-title="<?php echo esc_html( $label_on ); ?>"
                    data-off-title="<?php echo esc_html( $label_off ); ?>"
                    <?php echo esc_attr( $checked ); ?>>
            </div>
            <?php
        }

        /**
         * Radio button type input field
         */
        public static function radio(){
            if( empty( self::$field['options'] ) ) return;
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <div class="switch-field">
                    <?php
                        foreach ( self::$field['options'] as $value => $label ) {
                            self::radio_option( $value, $label );
                        }
                    ?>
                </div>
            </div>
            <?php
        }

        /**
         * Color input field
         */
        public static function color(){
            ?>
            <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                <div class="mpc-colorp">
                    <input
                        type="text"
                        name="<?php echo esc_attr( self::$name ); ?>"
                        class="mpc-colorpicker"
                        value="<?php echo esc_attr( self::$value ); ?>"
                        data-default-color="">
                </div>
            </div>
            <?php
        }



        /**
         * Switch box field
         */
        public static function swich_box(){
            $checked   = ! empty( $value ) && ( 'on' === $value || true === $value ) ? 'on' : 'off';

            $label_on  = self::$field['switch_text']['on'] ?? '';
            $label_off = self::$field['switch_text']['off'] ?? '';
			?>
			<div class="hurkanSwitch hurkanSwitch-switch-plugin-box">
				<div class="hurkanSwitch-switch-box switch-animated-<?php echo esc_attr( $checked ); ?>">
					<a class="hurkanSwitch-switch-item <?php echo 'on' === $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-success  hurkanSwitch-switch-item-status-on">
						<span class="lbl"><?php echo esc_html( $label_on ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
					<a class="hurkanSwitch-switch-item <?php echo 'off' === $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-  hurkanSwitch-switch-item-status-off">
						<span class="lbl"><?php echo esc_html( $label_off ); ?></span>
						<span class="hurkanSwitch-switch-cursor-selector"></span>
					</a>
				</div>
			</div>
			<?php
        }

        /**
         * Single radio button
        
         * @param string $value Option value.
         * @param string $label Option label.
         * @return void
         */
        public static function radio_option( $value, $label ){
            $id      = self::$name . '_' . $value;
            $checked = self::$value === $value ? 'checked' : '';
            $pro_cls = 'wmc_redirect' === self::$name && 'custom' === $value && false === self::$data['has_pro'] ? 'mpcex-disabled' : '';
            ?>
            <input
                type="radio"
                name="<?php echo esc_attr( self::$name ); ?>"
                id="<?php echo esc_attr( $id ); ?>"
                value="<?php echo esc_attr( $value ); ?>"
                class="<?php echo esc_attr( $pro_cls ); ?>"
                title="<?php echo esc_html( self::$pro_label ); ?>"
                <?php echo esc_attr( $checked ); ?>>
            <label for="<?php echo esc_attr( $id ); ?>">
                <?php echo esc_html( $label ); ?>
            </label>
            <?php
        }
	}
}
