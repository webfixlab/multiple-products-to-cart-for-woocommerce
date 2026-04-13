<?php
/**
 * Plugin admin ajax functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Admin_Ajax' ) ) {

	/**
	 * Plugin admin ajax handler class
	 */
	class MPC_Admin_Ajax {

		/**
		 * Register ajax endpoint
		 */
		public static function init() {
			add_action( 'wp_ajax_mpc_admin_search_box', array( __CLASS__, 'ajax_itembox_search' ) );
		}

		/**
		 * Ajax search items for dorpdown combo-box
		 */
		public static function ajax_itembox_search() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'search_box_nonce' ) ) {
				return '';
			}

			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$type   = isset( $_POST['type_name'] ) ? sanitize_text_field( wp_unslash( $_POST['type_name'] ) ) : '';

			$limit = 50; // Limit the number of items.
			if ( 'cats' === $type ) {
				$args = array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,  // Set this to true if you only want categories with products.
					'name__like' => $search,  // Search by category name.
					'number'     => $limit,  // Limit the number of categories.
				);

				$product_categories = get_terms( $args );
				$categories         = array();
				if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
					foreach ( $product_categories as $category ) {
						$categories[] = array(
							'id'   => $category->term_id,
							'name' => $category->name,
						);
					}
				}

				wp_send_json( $categories );
			} else {
				$args = array(
					's'              => $search,
					'post_type'      => 'product',
					'posts_per_page' => $limit,
				);

				$products = array();
				$query    = new WP_Query( $args );
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						$products[] = array(
							'id'   => get_the_ID(),
							'name' => get_the_title(),
						);
					}
				}

				wp_reset_postdata();
				wp_send_json( $products );
			}

			wp_send_json( array() );
		}
	}
}
