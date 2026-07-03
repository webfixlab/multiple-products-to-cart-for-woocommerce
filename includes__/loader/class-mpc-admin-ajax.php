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
		 * Results limit
		 *
		 * @var int
		 */
		private static $limit = 50;

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

			wp_send_json( 'cats' === $type ? self::get_taxonomies( $search ) : self::get_products( $search ) );
		}

		/**
		 * Find all taxonomies by given search string
		 *
		 * @param string $search Search string.
		 * @return array{id: int, name: string[]} Found taxonomies.
		 */
		private static function get_taxonomies( $search ) {
			$args = array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,  // Set this to true if you only want categories with products.
				// 'name__like' => $search,  // Search by category name.
				'search'     => $search,  // Search by category name.
				'number'     => self::$limit,  // Limit the number of categories.
			);

			$results = get_terms( $args );
			if ( empty( $results ) || is_wp_error( $results ) ) {
				return array();
			}

			$all_tax = array();

			foreach ( $results as $tax ) {
				$all_tax[] = array(
					'id'   => $tax->term_id,
					'name' => $tax->name,
				);
			}

			return $all_tax;
		}

		/**
		 * Find all products by given search string
		 *
		 * @param string $search Search string.
		 * @return array{id: int|mixed, name: mixed|string[]} Found products.
		 */
		private static function get_products( $search ) {
			$args = array(
				's'              => $search,
				'post_type'      => 'product',
				'posts_per_page' => self::$limit,
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'relation' => 'AND',
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array( 'simple', 'variable' ),
					),
				),
			);

			$args = apply_filters( 'mpc_admin_ajax_search_products', $args );

			$results = new WP_Query( $args );
			if ( empty( $results ) || is_wp_error( $results ) ) {
				return array();
			}

			$products = array();

			foreach ( $results->posts as $post ) {
				$products[] = array(
					'id'   => $post->ID,
					'name' => $post->post_title,
				);
			}

			return $products;
		}
	}
}
