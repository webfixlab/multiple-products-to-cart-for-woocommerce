<?php
/**
 * Frontend common data functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Front_Data' ) ) {

	/**
	 * Frontend common data class
	 */
	class MPC_Front_Data {

		/**
		 * Set frontend global variable up with necessary data
		 *
		 * @param array $atts Shortcode attributes.
		 * @param array $data Product data.
		 * @return void
		 */
		public static function setup_frontend_data( $atts, $data ){
			global $mpc_table__;

			$mpc_table__ = array(
				'atts'    => $atts,
				'orderby' => array(
					'menu_order' => __( 'Default sorting', 'multiple-products-to-cart-for-woocommerce' ),
					'price-ASC'  => __( 'Price: Low to High', 'multiple-products-to-cart-for-woocommerce' ),
					'price-DESC' => __( 'Price: High to Low', 'multiple-products-to-cart-for-woocommerce' ),
				),
				'columns' => self::get_table_columns( $atts, $data ),
				'image'   => array(
					'thumb' => wc_placeholder_img_src(),
					'full'  => wc_placeholder_img_src( 'full' ),
				),
				'labels' => array(
					'variation_prefix' => get_option( 'wmc_option_text' ),
				),
				'settings' => array(
					'stock' => get_option( 'mpc_show_stock_out' ),
					'desc'  => get_option( 'mpc_show_variation_desc' ),
				),
			);

			if( isset( $atts['selected' ] ) && ! empty( $atts['selected'] ) ){
				$mpc_table__['atts']['selected'] = explode( ',', str_replace( ' ', '', $atts['selected'] ) );
			}

			$title_filter = get_option( 'mpc_show_title_dopdown' );
			if( empty( $title_filter ) || 'on' === $title_filter ){
				$title_asc = get_option( 'mpc_sddt_title_asc' );
				$mpc_table__['orderby']['title-ASC'] = empty( $title_asc ) ? __( 'Title: A to Z', 'multiple-products-to-cart-for-woocommerce' ) : $title_asc;

				$title_desc = get_option( 'mpc_sddt_title_desc' );
				$mpc_table__['orderby']['title-DESC'] = empty( $title_desc ) ? __( 'Title: Z to A', 'multiple-products-to-cart-for-woocommerce' ) : $title_desc;
			}

			$mpc_table__ = array_merge( $mpc_table__, $data );

			do_action( 'mpc_frontend_core_data' );
		}

        /**
		 * Get a list of all appropriate table columns
		 *
		 * @param array $atts Shortcode attributes.
		 * @param array $data Product data.
		 * @return array
		 */
        private static function get_table_columns( $atts, $data ){
            $columns = isset( $atts['columns'] ) ? $atts['columns'] : get_option( 'wmc_sorted_columns' );
			$columns = empty( $columns ) ? array() : explode( ',', str_replace( ' ', '', $columns ) );

            if( ! empty( $columns ) && false === strpos( $columns[0], 'wmc_ct_' ) ){
                $columns = array_map( function ( $col ) {
                    return 'wmc_ct_' . $col;
                }, $columns );
            }

			$default = array( 'wmc_ct_image', 'wmc_ct_product', 'wmc_ct_price', 'wmc_ct_variation', 'wmc_ct_quantity', 'wmc_ct_buy'  );
			
            // remove variation column if no variable products in the query results.
            $default = self::has_variable_products( $data['products'] ) ? $default : array_diff( $default, array( 'wmc_ct_variation' ) );
			
            // filter out extra columns.
            $columns = empty( $columns ) ? $default : array_intersect( $default, $columns );

            $labels = array(
                'wmc_ct_image'     => __( 'Image', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_product'   => __( 'Product', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_price'     => __( 'Price', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_variation' => __( 'Variation', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_quantity'  => __( 'Quantity', 'multiple-products-to-cart-for-woocommerce' ),
                'wmc_ct_buy'       => __( 'Buy', 'multiple-products-to-cart-for-woocommerce' )
            );

            $cols = array();
            foreach( $columns as $col ){
                $cols[ $col ] = $labels[ $col ];
            }
			
            return $cols;
        }

		/**
		 * Checks if any variable products exist in given product ids
		 *
		 * @param array $product_ids Product IDs.
		 * @return bool
		 */
		private static function has_variable_products( $product_ids ){
			global $wpdb;

			$ids_string = implode( ',', $product_ids );
			$has_variable = $wpdb->get_var( "
				SELECT COUNT(tr.object_id)
				FROM {$wpdb->term_relationships} tr
				JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				WHERE t.slug = 'variable'
				AND tr.object_id IN ($ids_string)
			" );

			return $has_variable > 0;
		}
	}
}
