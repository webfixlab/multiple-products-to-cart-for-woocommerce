<?php
/**
 * Table add to cart functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Product_Data' ) ) {

	/**
	 * Table add to cart class
	 */
	class MPC_Product_Data {

		/**
		 * Get product table data
		 *
		 * @param array $atts  product table shortcode attributes.
		 * @param int   $paged current page.
		 */
		public static function get_products( array $atts, int $paged ) {
			$args = self::get_query_args( $atts, $paged );

			// remove hooks for nuiscense.
			remove_all_filters( 'pre_get_posts' );
			remove_all_filters( 'posts_orderby' );

			$result = new WP_Query( $args );
			wp_reset_postdata();

			$products = apply_filters( 'mpc_modify_get_products', $result->posts, $args );

			return array(
				'products' => $products,
				'total'    => $result->found_posts,
				'max_page' => $result->max_num_pages
			);
		}

		/**
		 * Generate WP_Query from shortcode attributes to get products
		 *
		 * @param array $atts  shortcode attributes.
		 * @param int   $paged paged variable.
		 */
		private static function get_query_args( array $atts, int $paged ) {
			$limit   = isset( $atts['limit'] ) && ! empty( $atts['limit'] ) ? (int) $atts['limit'] : 100;
			$orderby = $atts['orderby'] ?? '';

			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => isset( $atts['pagination'] ) && false === (bool) $atts['pagination'] ? 100 : $limit,
				'paged'          => $paged,
				'orderby'        => empty( $orderby ) || ! in_array( $orderby, array( 'price', 'title', 'date' ) ) ? 'date' : $orderby,
				'order'          => isset( $atts['order'] ) && ! empty( $atts['order'] ) ? strtoupper( $atts['order'] ) : 'DESC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'      => '_stock_status',
						'value'    => 'instock',
						'complare' => '=',
					),
					array(
						'key'     => '_price',
						'compare' => 'EXISTS',
					),
				),
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'relation' => 'AND', 
				)
			);

			if( 'price' === $args['orderby'] ){
				$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value_num';
			}

			if ( isset( $atts['ids'] ) && '' !== $atts['ids'] ) {
				$args['post__in'] = explode( ',',  str_replace( ' ', '', $atts['ids'] ) );
			}

			if ( isset( $atts['skip_products'] ) && ! empty( $atts['skip_products'] ) ) {
				$args['post__not_in'] = explode( ',',  str_replace( ' ', '', $atts['skip_products'] ) );
			}

			if ( isset( $atts['type'] ) && ! empty( $atts['type'] ) ) {
				$product_types = explode( ',',  str_replace( ' ', '', $atts['type'] ) );
				$args['tax_query'][] = array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => in_array( 'all', $product_types, true ) ? array( 'simple', 'variable' ) : array_intersect( array( 'simple', 'variable' ), $product_types ),
				);
			}

			if ( isset( $atts['cats'] ) && ! empty( $atts['cats'] ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => explode( ',',  str_replace( ' ', '', $atts['cats'] ) ),
				);
			}

			if ( isset( $atts['tags'] ) && ! empty( $atts['tags'] ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_tag',
					'field'    => 'slug',
					'terms'    => explode( ',',  str_replace( ' ', '', $atts['tags'] ) ),
				);
			}

			return apply_filters( 'mpc_query_args', $args, $atts );
		}
	}
}
