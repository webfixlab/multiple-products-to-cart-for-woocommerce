<?php
/**
 * Plugin admin helper class.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPCAdminHelper' ) ) {

	/**
	 * Admin helper functionalities
	 */
	class MPCAdminHelper {



		/**
		 * Display active or inactive columns in a list
		 *
		 * @param string  $value     saved columns.
		 * @param boolean $is_active wheather to show saved or active columns.
		 */
		public function column_list( $value, $is_active ) {
			$labels = array(
				'image'     => __( 'Image', 'multiple-products-to-cart-for-woocommerce' ),
				'product'   => __( 'Product', 'multiple-products-to-cart-for-woocommerce' ),
				'price'     => __( 'Price', 'multiple-products-to-cart-for-woocommerce' ),
				'variation' => __( 'Variation', 'multiple-products-to-cart-for-woocommerce' ),
				'quantity'  => __( 'Quantity', 'multiple-products-to-cart-for-woocommerce' ),
				'buy'       => __( 'Buy', 'multiple-products-to-cart-for-woocommerce' ),
				'category'  => __( 'Category', 'multiple-products-to-cart-for-woocommerce' ),
				'stock'     => __( 'Stock', 'multiple-products-to-cart-for-woocommerce' ),
				'tag'       => __( 'Tag', 'multiple-products-to-cart-for-woocommerce' ),
				'sku'       => __( 'SKU', 'multiple-products-to-cart-for-woocommerce' ),
				'rating'    => __( 'Rating', 'multiple-products-to-cart-for-woocommerce' ),
			);

			$cols = $this->sorted_columns( $value );
			if ( ! $is_active ) {
				$cols['columns'] = array_diff( array_keys( $labels ), $cols['columns'] );
			}

			foreach ( $cols['columns'] as $col ) {
				$label = get_option( 'wmc_ct_' . esc_attr( $col ) );
				if ( empty( $label ) ) {
					$label = $labels[ $col ];
				}

				$stone_cls = in_array( $col, $cols['stones'], true ) ? 'mpc-stone-col' : 'ui-state-default';

				printf(
					'<li class="ui-sortable-handle %s" data-meta_key="wmc_ct_%s">%s</li>',
					esc_attr( $stone_cls ),
					esc_attr( $col ),
					esc_html( $label )
				);
			}
		}

		/**
		 * Get table columns settings, either active or inactive
		 *
		 * @param string $value sorted columns in comma separated way.
		 */
		protected function sorted_columns( $value ) {
			global $mpc__;

			$stones = array();

			// columns only available in pro plugin.
			$pro_cols = array( 'category', 'stock', 'tag', 'sku', 'rating' );

			// get saved columns.
			$cols = array( 'image', 'product', 'price', 'variation', 'quantity', 'buy' );
			if ( ! empty( $value ) && ! is_array( $value ) ) {
				$cols = explode( ',', str_replace( array( ' ', 'wmc_ct_' ), '', $value ) ); // check if array str_replace works or not?
			}

			// remove pro columns if pro does not exist.
			if ( ! $mpc__['has_pro'] ) {
				$cols   = array_diff( $cols, $pro_cols );
				$stones = $pro_cols;
			}

			array_push( $stones, 'variation' );

			// variation is a stone column, it shouldn't be removed.
			if ( ! in_array( 'variaion', $cols, true ) ) {
				array_push( $stones, 'variation' );
			}

			return array(
				'columns' => $cols,
				'stones'  => $stones,
			);
		}
	}
}
