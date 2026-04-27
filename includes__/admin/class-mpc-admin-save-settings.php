<?php
/**
 * Plugin admin save settings functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Save_Settings' ) ) {

	/**
	 * Plugin admin save settings class
	 */
	class MPC_Admin_Save_Settings {

        /**
         * Pro plugin status
         * @var string
         */
        private static $pro_state;

        /**
         * Current admin state notice
         * @var array
         */
        private static $notice = array( 'status' => '', 'msg' => '' );

		/**
		 * Plugin installation handler
         *
         * @param string $tab       Settings tab.
         * @param string $pro_state Pro plugin status.
		 */
		public static function init( $tab, $pro_state ) {
            if( 'import' === $tab || 'export' === $tab ){
                return;
            }

            self::$pro_state = $pro_state;

            if( 'all-tables' === $tab ){
                self::if_delete_table();    
                return;
            }

            if( 'new-table' === $tab ){
                self::add_new_table();
            } elseif ( 'general-settings' === $tab ) {
                self::save_fields( MPC_Core_Data::get_general_settings() );
            } elseif( 'labels' === $tab ) {
                self::save_fields( MPC_Core_Data::get_labels() );
            } elseif( 'appearence' === $tab ) {
                self::save_fields( MPC_Core_Data::get_appearence() );
            } elseif ( 'column-sorting' === $tab ){
                self::save_columns_order();
            }

            return self::$notice;
		}

        /**
         * Create or update new shortcode
         */
        private static function add_new_table(){
            if ( ! isset( $_POST['mpc_opt_sc'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_opt_sc'] ) ), 'mpc_opt_sc_save' ) ) {
				return;
			}

            $table_id = isset( $_GET['mpctable'] ) ? sanitize_key( wp_unslash( $_GET['mpctable'] ) ) : '';

            $form_data = array();
            foreach( MPC_Core_Data::get_new_table()[0]['fields'] as $field ){
                $key               = $field['key'];
                $form_data[ $key ] = 'checkbox' === $field['type'] ? ( isset( $_POST[ $key ] ) ? 'true' : 'false' ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
            }

            if( empty( $table_id ) ){
                $table_id = wp_insert_post( array(
                    'post_type'      => 'mpc_product_table',
                    'post_title'     => empty( $table_id ) && empty( $form_data['shortcode_title'] ) ? __( 'Product Table', 'multiple-products-to-cart-for-woocommerce' ) : $form_data['shortcode_title'],
                    'post_content'   => $form_data['shortcode_desc'],
                    'post_status'    => 'publish',
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                ) );

                self::$notice = array(
                    'status' => 'success',
                    'msg'    => __( 'Shortcode created.', 'multiple-products-to-cart-for-woocommerce' )
                );
            }else{
                $args = array( 'ID' => (int) $table_id );
                if( !empty( $form_data['shortcode_title'] ) ){
                    $args['post_title'] = $form_data['shortcode_title'];
                }
                if( !empty( $form_data['shortcode_desc'] ) ){
                    $args['post_content'] = $form_data['shortcode_desc'];
                }
                $table_id = wp_update_post( $args );

                self::$notice = array(
                    'status' => 'updated',
                    'msg'    => __( 'Shortcode updated.', 'multiple-products-to-cart-for-woocommerce' )
                );
            }

            $shortcode = self::save_shortcode_meta( $form_data, $table_id );
            update_post_meta( $table_id, 'shortcode', "[woo-multi-cart {$shortcode}]" );
            // update_post_meta( $table_id, 'table_id', $table_id ); // to accomodate legacy table.
        }

        /**
         * Delete shortcode table
         */
        private static function if_delete_table(){
            if( !isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ){
                return;
            }

            $table_id = isset( $_GET['mpcscdlt'] ) ? sanitize_key( wp_unslash( $_GET['mpcscdlt'] ) ) : '';
            if( empty( $table_id ) ){
                return;
            }
            
            if( get_post_status( (int) $table_id ) ){
                wp_delete_post( (int) $table_id, true );
            }

            // delete legacy shortcode. DEPCRICATED.
            delete_option( "mpcasc_code{$table_id}" );

            self::$notice = array( 'status' => 'deleted', 'msg' => __( 'Shortcode deleted.', 'multiple-products-to-cart-for-woocommerce' ) );

            return $table_id;
        }

        /**
         * Get shortcode string and save as CPT meta
         *
         * @param array $form_data Shortcode form data.
         * @param int   $table_id  Shotcode table ID.
         * @return string
         */
        private static function save_shortcode_meta( $form_data, $table_id ){
            $shortcode = '';
            foreach( $form_data as $key => $value ){
                if( 'shortcode_title' === $key || 'shortcode_desc' === $key ){
                    continue;
                }
                $shortcode .= "{$key}=\"{$value}\" ";
            }
            return $shortcode;
        }

        /**
         * Save admin settings fields
         *
         * @param array $sections All input fields on this tab.
         */
        private static function save_fields( $sections ){
            if ( ! isset( $_POST['mpc_admin_settings'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_admin_settings'] ) ), 'mpc_admin_settings_save' ) ) {
                return;
            }

            foreach( $sections as $section ){
                foreach ( $section['fields'] as $field ) {
                    if( isset( $field['pro'] ) && empty( self::$pro_state ) ) {
                        continue;
                    }

                    $key   = $field['key'];
                    $value = 'checkbox' === $field['type'] ? ( isset( $_POST[ $key ] ) ? 'on' : 'no' ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );

                    self::save_field( $field, $value );

                    if( ! isset( $field['followup'] ) || empty( $field['followup'] ) ){
                        continue;
                    }

                    // handle followup fields.
                    foreach( $field['followup'] as $field ){
                        if( isset( $field['pro'] ) && empty( self::$pro_state ) ) {
                            continue;
                        }

                        $key   = $field['key'];
                        $value = 'checkbox' === $field['type'] ? ( isset( $_POST[ $key ] ) ? 'on' : 'no' ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );

                        self::save_field( $field, $value );
                    }

                }
            }

            self::$notice = array(
                'status' => 'success',
                'msg'    => __( 'Settings Saved', 'multiple-products-to-cart-for-woocommerce' )
            );
        }

        /**
         * Save settings field
         *
         * @param array  $field Input field data.
         * @param string $value Input field value, sanitized.
         */
        private static function save_field( $field, $value ){
            if( 'checkbox' === $field['type'] ) {
                update_option( $field['key'], $value );
                return;
            }

            if( empty( $value ) ){
                delete_option( $field['key'] );
                return;
            }

            update_option( $field['key'], $value );
        }

        /**
         * Save sorted columns order
         */
        private static function save_columns_order(){
            if ( ! isset( $_POST['mpc_col_sort'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_col_sort'] ) ), 'mpc_col_sort_save' ) ) {
				return;
			}

			if ( ! isset( $_POST['wmc_sorted_columns'] ) || empty( $_POST['wmc_sorted_columns'] ) ) {
				return;
			}

			update_option( 'wmc_sorted_columns', sanitize_text_field( wp_unslash( $_POST['wmc_sorted_columns'] ) ) );

            self::$notice = array(
                'status' => 'success',
                'msg'    => __( 'Settings Saved', 'multiple-products-to-cart-for-woocommerce' )
            );
        }
	}
}
