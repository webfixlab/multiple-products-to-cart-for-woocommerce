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
				'paged'    => $paged,
				'atts'     => $atts,
				'args'     => $args,
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

		/**
		 * Extract price from price html
		 *
		 * @param object $product Product object.
		 * @return float
		 */
		public static function get_price_amount( $product ){
			return self::extract_price_from_html( $product->get_price_html() );
		}

		/**
		 * Extract float price from WooCommerce HTML price
		 *
		 * @param string $price_html Product price html.
		 * @return float
		 */
		private static function extract_price_from_html( $price_html ) {
			if ( empty( $price_html ) ) {
				return 0.0;
			}

			$price_text = '';
			if ( preg_match( '/<ins[^>]*>(.*?)<\/ins>/is', $price_html, $m ) ) {
				$price_text = $m[1];
			} elseif ( preg_match( '/<bdi[^>]*>(.*?)<\/bdi>/is', $price_html, $m ) ) {
				$price_text = $m[1];
			}
			if ( empty( $price_text ) ) {
				return 0.0;
			}

			$price_text = wp_strip_all_tags( $price_text );
			$price_text = html_entity_decode( $price_text );
			return (float) wc_format_decimal( $price_text, wc_get_price_decimals() );
		}

		/**
		 * Get available product variations data
		 *
		 * @param object $product Product object.
		 * @param array  $options Settings options.
		 * @return array
		 */
		public static function get_available_variations( $product, $options ) {
			$variation_ids = $product->get_children();
			if ( empty( $variation_ids ) ) {
				return array();
			}

			$options['type'] = $product->get_type();

			$available_variations = array();
			foreach ( $variation_ids as $variation_id ) {
				$variation_data = self::get_variation_data( $variation_id, $options );
				if( ! empty( $variation_data ) ){
					$available_variations[] = $variation_data;
				}
			}

			return $available_variations;
		}

		/**
		 * Get variation data for frontend use
		 *
		 * @param int   $variation_id Variation ID.
		 * @param array $options      Admin settings options.
		 * @return array
		 */
		private static function get_variation_data( $variation_id, $options ){
			$variation = wc_get_product( $variation_id );

			$price   = self::extract_price_from_html( $variation->get_price_html() );
			$sub_fee = false !== strpos( $options['type'], 'subscription' ) ? get_post_meta( $variation_id, '_subscription_sign_up_fee', true ) : 0;
			$stock   = $variation->get_stock_quantity();

			$data = array(
				'attributes'   => $variation->get_attributes(),
				'price'        => empty( $sub_fee ) ? $price : $price + (float) $sub_fee,
				'sku'          => $variation->get_sku(),
				'stock_status' => $variation->get_stock_status(),
				'stock'        => empty( $stock ) ? '' : $stock,
				'stock_txt'    => __( 'In stock', 'multiple-products-to-cart-for-woocommerce' ),
			);

			$image_id = get_post_meta( $variation_id, '_thumbnail_id', true );
			if( ! empty( $image_id ) ){
				$data['image'] = array(
					'thumb' => wp_get_attachment_image_src( $image_id, 'thumbnail' )[0],
					'full'  => wp_get_attachment_image_src( $image_id, 'large' )[0]
				);
			}

			if( empty( $options['desc'] ) || 'on' === $options['desc'] ){
				$desc = $variation->get_description();
				if ( ! empty( $desc ) ) {
					$data['desc'] = strlen( $desc ) > 70 ? substr( $desc, 0, 70 ) . '...' : $desc;
				}
			}

			return $data;
		}
	}
}
