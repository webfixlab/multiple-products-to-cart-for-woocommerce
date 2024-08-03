<?php
/**
 * Frontend product table class.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPCTable' ) ) {

	/**
	 * Frontend table displaying class
	 */
	class MPCTable {



		/**
		 * Initialize table hooks and actions
		 */
		public function init() {
			add_shortcode( 'woo-multi-cart', array( $this, 'product_table' ) );

			add_action( 'wp_ajax_mpc_ajax_table_loader', array( $this, 'product_table_ajax' ) );
			add_action( 'wp_ajax_nopriv_mpc_ajax_table_loader', array( $this, 'product_table_ajax' ) );

			add_action( 'mpc_get_products', array( $this, 'run_products_query' ), 10 );

			add_action( 'wp_loaded', array( $this, 'add_to_cart' ), 15 );
			add_action( 'wp_ajax_mpc_ajax_add_to_cart', array( $this, 'add_to_cart_ajax' ) );
			add_action( 'wp_ajax_nopriv_mpc_ajax_add_to_cart', array( $this, 'add_to_cart_ajax' ) );
		}



		/**
		 * Add to cart handler
		 */
		public function add_to_cart() {
			if ( ! class_exists( 'WC_Form_Handler' ) ) {
				return;
			}

			if ( ! isset( $_POST['cart_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cart_nonce'] ) ), 'cart_nonce_ref' ) ) {
				return;
			}

			// only for mpc plugin add to cart event.
			if ( ! isset( $_REQUEST['mpc_cart_data'] ) ) {
				return;
			}

			remove_action( 'wp_loaded', array( WC_Form_Handler::class, 'add_to_cart_action' ), 20 );

			$d = sanitize_text_field( wp_unslash( $_REQUEST['mpc_cart_data'] ) );
			$d = json_decode( $d, true );

			$this->do_add_to_cart( $d, 'submission' );
		}

		/**
		 * Ajax add to cart handler
		 */
		public function add_to_cart_ajax() {

			if ( ! isset( $_POST['cart_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cart_nonce'] ) ), 'cart_nonce_ref' ) ) {
				return;
			}

			// check ajax add to cart data.
			if ( ! isset( $_POST['mpca_cart_data'] ) ) {
				return;
			}

			// unslash and sanitize array data.
			$this->do_add_to_cart( wp_unslash( $_POST['mpca_cart_data'] ), 'ajax' );
		}

		/**
		 * Add to cart process
		 *
		 * @param array  $data   product data for adding then to cart.
		 * @param string $method add to cart method, ajax or not.
		 */
		public function do_add_to_cart( $data, $method ) {
			if ( empty( $data ) ) {
				return;
			}

			$add_to_cart_missed = false;   // flag to find if any error occured while adding products to cart.
			$all_products       = array(); // array of product id => quantity.

			foreach ( $data as $product_id => $product ) {
				$flag = false;
				$key  = '';

				if( 'grouped' === $product['type'] ){
					continue;
				}

				if ( strpos( $product['type'], 'variable' ) !== false && isset( $product['variation_id'] ) ) {
					$flag = WC()->cart->add_to_cart( $product_id, $product['quantity'], $product['variation_id'], $product['attributes'] );
				} else {
					$key  = WC()->cart->add_to_cart( $product_id, $product['quantity'] );
					$flag = false !== $key ? true : false;
				}

				if ( false !== $flag ) {
					do_action( 'woocommerce_ajax_added_to_cart', $product_id );
					$all_products[ $product_id ] = $product['quantity'];
				} else {
					$add_to_cart_missed = true;
				}

				do_action( 'mpc_after_add_to_cart', $product_id, $key );
			}

			if ( 'ajax' === $method ) {
				$resonse                 = $this->cart_refreshed_fragments();
				$resonse['req']          = $data;
				$resonse['cart_message'] = wc_add_to_cart_message( $all_products, true, true );

				if ( $add_to_cart_missed ) {
					$resonse['error_message'] = $this->cart_format_error();
				}

				wp_send_json( $resonse );
			} else {
				wc_add_to_cart_message( $all_products, true, false );
				$this->cart_redirect();
			}
		}

		/**
		 * Redirect to URL after successful add to cart
		 *
		 * @param string $url URL to redirect after add to cart.
		 */
		public function cart_redirect( $url = '' ) {
			// if admin option set to cart.
			if ( 'cart' === get_option( 'wmc_redirect' ) ) {
				$url = wc_get_cart_url();
			}

			// filter - modify given url.
			$url = apply_filters( 'mpc_add_to_cart_redirect_url', $url );

			if ( ! empty( $url ) && '' !== $url ) {
				wp_safe_redirect( $url );
				exit;
			}
		}

		/**
		 * WooCommerce frontend mini cart html data
		 */
		public function cart_refreshed_fragments() {
			ob_start();

			woocommerce_mini_cart();

			$mini_cart = ob_get_clean();

			$data = array(
				'fragments' => apply_filters(
					'woocommerce_add_to_cart_fragments',
					array(
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
					)
				),
				'cart_hash' => WC()->cart->get_cart_hash(),
			);

			return $data;
		}

		/**
		 * Process add to cart errors
		 */
		public function cart_format_error() {
			$notices = wc_get_notices( 'error' );

			if ( empty( $notices ) || ! is_array( $notices ) ) {
				$notices = array( __( 'There was an error adding to the cart. Please try again.', 'multiple-products-to-cart-for-woocommerce' ) );
			}

			$result    = '';
			$error_fmt = apply_filters( 'wc_product_table_cart_error_format', '<span class="cart-error">%s</span>' );

			foreach ( $notices as $notice ) {
				$notice_text = isset( $notice['notice'] ) ? $notice['notice'] : $notice;
				$result     .= sprintf( $error_fmt, $notice_text );
			}

			wc_clear_notices();
			return $result;
		}



		/**
		 * Product table shortcode loader
		 *
		 * @param array $atts product table shortcode attributes.
		 */
		public function product_table( $atts ) {

			// if no products found from shortcode return.
			if ( ! $this->get_table( $atts ) ) {
				return;
			}

			ob_start();
			require_once apply_filters( 'mpc_template_loader', MPC_PATH . 'assets/php-css/dynamic-styles.php' );

			// Load main table template file.
			include apply_filters( 'mpc_template_loader', MPC_PATH . 'templates/listing-list.php' );

			$content = ob_get_contents();
			ob_get_clean();

			return do_shortcode( $content );
		}

		/**
		 * Ajax product table loader
		 */
		public function product_table_ajax() {
			global $mpctable__;
			global $MPCTemplate;

			$response = array( 'status' => '' );

			if ( ! isset( $_POST['table_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['table_nonce'] ) ), 'table_nonce_ref' ) ) {
				$response['status'] = 'error';
				$response['msg']    = __( 'Nonce verification failed.', 'multiple-products-to-cart-for-woocommerce' );
			}

			if ( ! isset( $_POST ) || ( ! isset( $_POST['page'] ) ) ) {
				$response['status'] = 'error';
				$response['msg']    = __( 'POST - page or atts variable not found.', 'multiple-products-to-cart-for-woocommerce' );
			}

			$atts = array(); // shortcode attribute data.
			if ( isset( $_POST['atts'] ) ) {
				$atts = array_map( 'sanitize_text_field', wp_unslash( $_POST['atts'] ) );
			}

			$this->get_table( $atts );

			if ( ! isset( $mpctable__['products'] ) || empty( $mpctable__['products'] ) ) {
				wp_send_json(
					array(
						'status'        => 'error',
						'msg'           => __( 'No posts found!', 'multiple-products-to-cart-for-woocommerce' ),
						'mpc_fragments' => array(
							array(
								'key' => 'table.mpc-wrap',
								'val' => sprintf(
									'<table class="mpc-wrap"><tr><td><span class="mpc-search-empty">%s</span></td></tr></table>',
									esc_html__( 'Sorry! No products found!', 'multiple-products-to-cart-for-woocommerce' )
								),
							),
							array(
								'key'         => '.mpc-product-range',
								'parent'      => '.mpc-button', // if key element not found add to parent.
								'adding_type' => 'prepend',
								'val'         => '',
							),
							array(
								'key'         => '.mpc-pagenumbers',
								'parent'      => '.mpc-inner-pagination',
								'adding_type' => 'prepend',
								'val'         => '',
							),
						),
					)
				);
			}

			if ( 'error' === $response['status'] ) {
				wp_send_json( $response );
			}

			ob_start();

			// display table body content.
			mpc_display_table();

			$response[] = array(
				'key' => 'table.mpc-wrap',
				'val' => ob_get_clean(),
			);

			ob_start();
			$MPCTemplate->display_table_pagination_range();
			$response[] = array(
				'key'         => '.mpc-product-range',
				'parent'      => '.mpc-button', // if key element not found add to parent.
				'adding_type' => 'prepend',
				'val'         => ob_get_clean(),
			);

			ob_start();
			$MPCTemplate->numbered_pagination();
			$response[] = array(
				'key'         => '.mpc-pagenumbers',
				'parent'      => '.mpc-inner-pagination',
				'adding_type' => 'prepend',
				'val'         => ob_get_clean(),
			);

			wp_send_json(
				array(
					'mpc_fragments' => $response,
				)
			);
		}



		/**
		 * Get product table data
		 *
		 * @param array $atts product table shortcode attributes.
		 */
		public function get_table( $atts ) {
			global $mpctable__;

			$this->init_fields();

			$this->process_atts( $atts );

			// Do action to get products data.
			do_action( 'mpc_get_products' );

			// If not products are found - return.
			if ( ! isset( $mpctable__['products'] ) || empty( $mpctable__['products'] ) ) {
				return;
			}

			// If no columns found - return.
			if ( ! isset( $mpctable__['columns_list'] ) || empty( $mpctable__['columns_list'] ) ) {
				return false;
			}

			// modify table data.
			do_action( 'mpc_modify_table_data' );

			return true;
		}

		/**
		 * Run product table query
		 */
		public function run_products_query() {
			global $mpctable__;

			// get wp_query arguments from shortcode attributes.
			$args               = $this->get_wp_query( $mpctable__['attributes'], $mpctable__['paged'] );
			$mpctable__['args'] = $args;

			// remove hooks for nuiscense.
			remove_all_filters( 'pre_get_posts' );
			remove_all_filters( 'posts_orderby' );

			// get products from query.
			$products = new WP_Query( $args );
			wp_reset_postdata();

			// save result attributes for future reference.
			if ( empty( $products ) ) {
				return;
			}

			// Get all table data.
			$this->get_table_data( $products );

			$this->get_columns();

			if ( ! $mpctable__['attributes']['pagination'] ) {
				return;
			}

			$mpctable__['query']['total']    = $products->found_posts;
			$mpctable__['query']['max_page'] = $products->max_num_pages;
		}

		/**
		 * Generate WP_Query from shortcode attributes to get products
		 *
		 * @param array $atts  shortcode attributes.
		 * @param int   $paged paged variable.
		 */
		public function get_wp_query( $atts, $paged ) {
			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => (int) $atts['limit'],
				'paged'          => $paged,
			);

			if ( ! empty( $args['posts_per_page'] ) && $args['posts_per_page'] > 100 ) {
				$args['posts_per_page'] = 100;
			}

			// if pagination set to false, set limit to 100.
			if ( isset( $atts['pagination'] ) && false === $atts['pagination'] ) {
				$args['posts_per_page'] = 100;
			} else {
				$args['posts_per_page'] = (int) $args['posts_per_page'];
			}

			// special ordering support.
			$special_support = apply_filters( 'mpc_get_orderby_list', array( 'price' ) );

			if ( in_array( $atts['orderby'], array( 'title', 'date' ), true ) ) {
				$args['orderby'] = ( '' !== $atts['orderby'] ? $atts['orderby'] : 'date' );
			}

			if ( isset( $atts['order'] ) ) {
				$args['order'] = ( '' !== $atts['order'] ? strtoupper( $atts['order'] ) : 'DESC' );
			}

			// order by price.
			if ( in_array( $atts['orderby'], $special_support, true ) ) {

				// get actual key instead of given stuff.
				if ( 'price' === $atts['orderby'] ) {
					$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				} else {
					$args['meta_key'] = $atts['orderby']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				}

				$args['orderby'] = 'meta_value_num';
			}

			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'      => '_stock_status',
					'value'    => 'instock',
					'complare' => '=',
				),
				array(
					'key'     => '_price',
					'compare' => 'EXISTS',
				),
			);

			if ( isset( $atts['ids'] ) && '' !== $atts['ids'] ) {
				$args['post__in'] = $atts['ids'];
			}

			// exclude posts if skip_products attribute is given.
			if ( isset( $atts['skip_products'] ) && ! empty( $atts['skip_products'] ) ) {
				$args['post__not_in'] = $atts['skip_products'];
			}

			// product type(s).
			$att_types = isset( $atts['type'] ) ? $atts['type'] : array( 'simple', 'variable' );
			$supported = apply_filters( 'mpc_change_product_types', array( 'simple', 'variable' ) );
			
			// sanitize types.
			$types     = array_map( function( $val ){ return sanitize_title( $val ); }, $att_types );
			$supported = array_map( function( $val ){ return sanitize_title( $val ); }, $supported );
			
			// filter appropriate types.
			$types     = in_array( 'all', $types, true ) ? $supported : array_intersect( $types, $supported );
			if( empty( $types ) ){
				$types = array( 'simple', 'variable' );
			}

			$args['tax_query'] = array( 'relation' => 'AND' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

			$args['tax_query'][] = array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $types,
			);

			$term_ids = $this->get_term_ids( $atts['cats'], 'product_cat' );
			if( false !== $term_ids ){
				$args['tax_query'][] = $term_ids;
			}

			$term_ids = $this->get_term_ids( $atts['tags'], 'product_tag' );
			if( false !== $term_ids ){
				$args['tax_query'][] = $term_ids;
			}

			return apply_filters( 'mpc_modify_query', $args, $atts );
		}

		/**
		 * Get term ids after checking either id or slug from given attribute
		 *
		 * @param array  $atts     shortcode attributes.
		 * @param string $taxonomy in which taxonomy the term ids belong to.
		 */
		public function get_term_ids( $atts, $taxonomy ){
			if( ! isset( $atts ) || '' === $atts ) {
				return false;
			}

			if ( ! is_array( $atts ) ) {
				$atts = explode( ',', str_replace( ' ', '', $atts ) );
			}
			
			if( ! is_array( $atts ) || empty( $atts ) ) {
				return false;
			}
			
			// terms can either be id or slug.
			$extracted_term_ids = array();
			foreach ( $atts as $term_id ) {
				$term = get_term_by( 'id', $term_id, $taxonomy );

				if ( ! empty( $term ) && isset( $term->term_id ) ) {
					$extracted_term_ids[] = $term->term_id;
				} else {
					$term = get_term_by( 'slug', $term_id, $taxonomy );
					if ( ! empty( $term ) && isset( $term->term_id ) ) {
						$extracted_term_ids[] = $term->term_id;
					}
				}
			}

			if ( empty( $extracted_term_ids ) ) {
				return false;
			}

			return array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $extracted_term_ids,
			);
		}

		/**
		 * Create table data array after post-processing getting products
		 *
		 * @param object $products all table products.
		 */
		public function get_table_data( $products ) {
			global $mpctable__;

			$mpctable__['products']      = array();
			$mpctable__['has_variation'] = false;
			$data                        = array();

			foreach ( $products->posts as $ppost ) {
				$id = $ppost->ID;

				// Get product object.
				$product = wc_get_product( $id );

				if ( ! $product ) {
					continue;
				}

				$product_data = $this->product_data( $id, $product );
				if( false === $product_data || empty( $product_data ) ){
					continue;
				}

				// handle 3rd party codes.
				$data[ $id ] = apply_filters( 'mpcp_modify_product_data', $product_data, $product );
			}

			// update columns list if no variation exists.
			do_action( 'mpc_final_column_processing' );

			$mpctable__['products'] = $data;
		}

		/**
		 * Get single product data
		 *
		 * @param int    $id      product id.
		 * @param object $product product object.
		 */
		public function product_data( $id, $product ){
			global $mpctable__;
			$data = array();

			$data = array(
				'type'              => $product->get_type(),
				'title'             => $product->get_title(),
				'url'               => $product->get_permalink(),
				'desc'              => wp_strip_all_tags( do_shortcode( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ) ),
				'price'             => $product->get_price_html(),
				'sold_individually' => $product->is_sold_individually(),
				'sku'               => $product->get_sku(),
				'stock'             => $product->get_stock_quantity(),
				'stock_status'      => $product->get_stock_status(),
				'terms'             => array(),
				'on_sale'           => $product->is_on_sale(),
			);

			$backorder = $product->get_backorders();
			if( 'yes' === $backorder || 'notify' === $backorder ){
				$data['stock'] = '';
			}

			// category.
			$terms = get_the_terms( $id, 'product_cat' );
			if ( ! empty( $terms ) ) {
				$ids = array();

				foreach ( $terms as $term ) {
					$ids[] = $term->term_id;
				}

				$data['terms'] = $ids;
			}

			// Images.
			$imgid = $product->get_image_id();

			if ( ! empty( $imgid ) ) {
				$data['image_id'] = $imgid;
			}

			$thumb = isset( $mpctable__['image_sizes'] ) ? $mpctable__['image_sizes']['thumb'] : 'thumb';
			$full  = isset( $mpctable__['image_sizes'] ) ? $mpctable__['image_sizes']['full'] : 'full';

			if ( $imgid ) {
				$data['images'] = array(
					$thumb => wp_get_attachment_image_url( $imgid, $thumb ),
					$full  => wp_get_attachment_image_url( $imgid, $full ),
				);
			} else {
				$data['images'] = array(
					$thumb => $mpctable__['default_imgs']['thumb'],
					$full  => $mpctable__['default_imgs']['full'],
				);
			}

			// If this product is checked/selected.
			if ( ( is_array( $mpctable__['attributes']['selected'] ) && in_array( $id, $mpctable__['attributes']['selected'], true ) ) || 'all' === $mpctable__['attributes']['selected'] ) {
				$data['is_selected'] = true;
			} else {
				$data['is_selected'] = false;
			}

			// For variable products only.
			if ( false !== strpos( $data['type'], 'variable' ) ) {

				$mpctable__['has_variation'] = true;

				// Variation data - dynamic price handling.
				$variation_data = $this->get_variation_data( $product );

				// For displaying it frontend, use the following way wc_esc_json( wp_json_encode( $variation_data ) ).
				if ( $variation_data ) {
					$data['variation_data'] = $variation_data;
				}

				// default product attributes - if any of them is selected pre-defined.
				$default_attributes = $product->get_default_attributes();

				// product attributes.
				$attributes = $product->get_variation_attributes();

				if ( ! is_array( $attributes ) ) {
					return false;
				}

				// For modified attributes.
				$attributes_ = array();

				// Sort Global Attributes according to backend sequence.
				foreach ( $attributes as $name => $options ) {

					// Sanitize name.
					$name_   = sanitize_title( $name );
					$options = $this->sort_variation_options( $options, $product, $name_ );

					// Modified options variable - for storing additional data.
					$options_ = array();
					foreach ( $options as $option ) {
						$option_ = array();

						if ( is_array( $option ) ) {
							$option_ = array(
								'name'  => $option['name'],
								'value' => $option['slug'],
								'slug'  => $option['slug'],
							);
						} else {
							$option_ = array(
								'name'  => $option,
								'value' => $option,
								'slug'  => $option,
							);
						}

						// Check if variation option is in stock.
						$is_in_stock = $this->variation_is_in_stock( $product, $name, $option_['slug'] );

						if ( ! $is_in_stock ) {
							continue;
						}

						$option_['name'] = esc_html( apply_filters( 'woocommerce_variation_option_name', $option_['name'], null, $name, $product ) );

						$option_['slug'] = sanitize_title( $option_['slug'] );

						// Check if this option is default.
						if ( isset( $default_attributes[ $name_ ] ) && sanitize_title( $default_attributes[ $name_ ] ) === $option_['slug'] ) {
							$option_['is_selected'] = true;
						} else {
							$option_['is_selected'] = false;
						}

						array_push( $options_, $option_ );
					}

					$attributes_[ $name ] = array(
						'label'   => wc_attribute_label( $name ),
						'options' => $options_,
					);
				}

				if ( $attributes_ ) {
					$data['attributes'] = $attributes_;
				}
			} else {
				// Pure price.
				$data['price_'] = $product->get_price();

				if ( strpos( $data['type'], 'subscription' ) !== false ) {
					// get sign up fee - subscription product type.
					$supfee = (int) get_post_meta( $id, '_subscription_sign_up_fee', true );
					if ( '' !== $supfee && ! empty( $supfee ) ) {
						$data['price_'] += $supfee;
					}
				}
			}

			return $data;
		}

		/**
		 * Get product variation data | JSON
		 *
		 * @param object $product product object.
		 */
		public function get_variation_data( $product ) {
			global $mpctable__;

			$childrens = $product->get_children();
			if ( ! $childrens ) {
				return array();
			}

			$data = array();

			foreach ( $childrens as $child_id ) {
				$variation = wc_get_product( $child_id );

				// if not in stock or not and enabled to show out of stock.
				if ( ( ! $variation || ! $variation->is_in_stock() ) && ! $mpctable__['options']['mpc_show_stock_out'] ) {
					continue;
				}

				// get all options per attribute.
				$atts = $variation->get_attributes();

				// sanitize.
				$atts_sanitized = array();
				foreach ( $atts as $key => $value ) {
					$atts_sanitized[ $key ] = sanitize_title( $value );
				}

				$c = array( 'attributes' => $atts_sanitized );

				// get variation price.
				$price = $variation->get_price();

				$price = (float) $price;

				// if subscription type.
				if ( strpos( $product->get_type(), 'subscription' ) !== false ) {
					$supfee = get_post_meta( $child_id, '_subscription_sign_up_fee', true );
					if ( '' !== $supfee && ! empty( $supfee ) ) {
						$supfee = (float) $supfee;
						$price += $supfee;
					}
				}
				$c['price'] = $price;

				// get variation image, if no image set woocommerce default image.
				$image_id = get_post_meta( $child_id, '_thumbnail_id', true );
				if ( $image_id ) {
					$c['image']['thumbnail'] = wp_get_attachment_image_src( $image_id, 'thumbnail' )[0];
					$c['image']['full']      = wp_get_attachment_image_src( $image_id, 'large' )[0];
				} else {
					// check beforehand.
					$c['image']['thumbnail'] = $mpctable__['default_imgs']['thumb'];
					$c['image']['full']      = $mpctable__['default_imgs']['full'];
				}

				// hook for modifying image thumbnail.
				$img                     = apply_filters(
					'mpc_table_thumbnail',
					array(
						'image_id'   => $image_id,
						'thumbnail'  => $c['image']['thumbnail'],
						'full'       => $c['image']['full'],
						'thumb_size' => 'thumbnail',
					)
				);
				$c['image']['thumbnail'] = $img['thumbnail'];

				// get sku.
				$c['sku'] = $variation->get_sku();

				// stock.
				$c['stock_status'] = $variation->get_stock_status();
				$c['stock']        = $variation->get_stock_quantity();
				if ( empty( $c['stock'] ) || '' === $c['stock'] ) {
					$c['stock'] = __( 'In stock', 'multiple-products-to-cart-for-woocommerce' );
				} else {
					$c['stock'] .= __( ' in stocks', 'multiple-products-to-cart-for-woocommerce' );
				}

				// variation short description.
				if ( $mpctable__['options']['mpc_show_variation_desc'] ) {
					$desc = $variation->get_description();
					if ( ! empty( $desc ) ) {
						$c['desc'] = strlen( $desc ) > 70 ? substr( $desc, 0, 70 ) . '...' : $desc;
					}
				}

				// hook to add additional variation data.
				$c                 = apply_filters( 'mpc_modify_js_data', $c, $variation );
				$data[ $child_id ] = $c;
			}

			return $data;
		}

		/**
		 * Sort global variation options according to their term order
		 *
		 * @param array  $options       attribute options.
		 * @param object $product       product object.
		 * @param string $sanitize_name product attribute name | sanitized.
		 */
		public function sort_variation_options( $options, $product, $sanitize_name ) {

			// Check if this is a Global Attributes.
			if ( false === strpos( $sanitize_name, 'pa_' ) ) {
				return $options;
			}

			$terms  = array();
			$values = wc_get_object_terms( $product->get_id(), $sanitize_name );
			foreach ( $values as $wc_term ) {
				$terms[] = array(
					'name' => $wc_term->name,
					'slug' => $wc_term->slug,
				);
			}

			if ( empty( $terms ) ) {
				return $options;
			}

			return $terms;
		}

		/**
		 * Check if given product variation is in stock | Upgrade (...)
		 *
		 * @param object  $product     product object.
		 * @param string  $attr_name   variation attribute name.
		 * @param string  $attr_value  variation attribute option value.
		 * @param boolean $return_type if true, return variation id, else return stock status.
		 */
		public function variation_is_in_stock( $product, $attr_name, $attr_value, $return_type = false ) {
			$is_in_stock  = false;
			$variation_id = 0;

			$childrens  = $product->get_children();
			$attr_name  = sanitize_title( $attr_name );
			$attr_value = sanitize_title( $attr_value );

			foreach ( $childrens as $child_id ) {
				if ( 0 !== $variation_id ) {
					break;
				}

				// get variation type object.
				$variation = wc_get_product( $child_id );

				foreach ( $variation->get_attributes() as $name => $option ) {

					// for checking given option of current attribute.
					if ( sanitize_title( $name ) === $attr_name ) {

						if ( empty( $option ) || sanitize_title( $option ) === $attr_value ) {

							// keep variation id in $variation_id.
							$variation_id = $child_id;

							if ( $variation->is_in_stock() ) {
								$is_in_stock = true;
							}

							// modify flag for third party interjection.
							$is_in_stock = apply_filters( 'mpc_variation_status', $is_in_stock, $variation );
							break;

						} elseif ( empty( $option ) ) {

							// if "any option" enabled | nothing more.
							$is_in_stock = true;
							break;
						}
					}
				}
			}

			if ( false === $return_type ) {
				return $variation_id;
			} else {
				return $is_in_stock;
			}
		}
		

		
		/**
		 * Initialize product table data
		 */
		public function init_fields() {

			global $mpctable__;

			$mpctable__ = array(
				'image_sizes'     => array(
					'thumb' => 'thumbnail',
					'full'  => 'large', // or we should use full?
				),
				'quantity'        => array(
					'min' => 0,
					'max' => '', // leave it blank for undefined.
				),
				'orderby_options' => array(
					'menu_order' => __( 'Default sorting', 'multiple-products-to-cart-for-woocommerce' ),
					'price-ASC'  => __( 'Price: Low to High', 'multiple-products-to-cart-for-woocommerce' ),
					'price-DESC' => __( 'Price: High to Low', 'multiple-products-to-cart-for-woocommerce' ),
				),
				'labels'          => array(
					'wmc_ct_image'                  => __( 'Image', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_product'                => __( 'Product', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_price'                  => __( 'Price', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_variation'              => __( 'Variation', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_quantity'               => __( 'Quantity', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_buy'                    => __( 'Buy', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_category'               => __( 'Category', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_stock'                  => __( 'Stock', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_tag'                    => __( 'Tag', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_sku'                    => __( 'SKU', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_ct_rating'                 => __( 'Rating', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_button_text'               => __( 'Add to Cart', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_reset_button_text'         => __( 'Reset', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_total_button_text'         => __( 'Total', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_pagination_text'           => __( 'Showing Products', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_select_all_text'           => __( 'Select All', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_option_text'               => '',
					'wmc_empty_form_text'           => __( 'Please check one or more products', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_thead_back_color'          => '', // get option value.
					'wmc_button_color'              => '', // same get option value.
					'wmc_empty_value_text'          => __( 'N/A', 'multiple-products-to-cart-for-woocommerce' ),
					'wmc_missed_variation_text'     => __( 'Please select all options', 'multiple-products-to-cart-for-woocommerce' ),
					'wmca_default_quantity'         => 0,
					'wmc_redirect'                  => '',
					'mpce_single_order_button_text' => __( 'Add', 'multiple-products-to-cart-for-woocommerce' ),
					'mpcp_empty_result_text'        => __( 'Sorry! No products found.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				'options'         => array(
					// checkboxes here.
					'wmc_show_pagination_text'    => '',
					'wmc_show_products_filter'    => '',
					'wmc_show_all_select'         => '',
					'wmca_show_reset_btn'         => '',
					'wmca_single_cart'            => '',
					'wmca_inline_dropdown'        => '',
					'wmca_allow_sku_sort'         => '',
					'wmca_show_header'            => '',
					'mpc_show_title_dopdown'      => '',
					'mpc_show_new_quantity_box'   => '',
					'mpc_show_ajax_search'        => '',
					'mpc_show_ajax_cat_filter'    => '',
					'mpc_show_ajax_tag_filter'    => '',
					'mpc_show_stock_out'          => '',
					'mpc_show_total_price'        => '',
					'mpc_show_add_to_cart_button' => '',
					'mpc_add_to_cart_checkbox'    => '',
					'mpc_show_variation_desc'     => '',
					'mpc_show_product_gallery'    => '',
					'mpc_show_cat_counter'        => '',
					'mpc_show_category_subheader' => '',
					'mpc_show_on_sale'            => '',
				),
				'woocommerce'     => array(
					'decimal_point' => get_option( 'woocommerce_price_num_decimals', 2 ),
				),
				'product_types'   => array( 'simple', 'variable' ),
				'default_imgs'    => array(
					'thumb' => wc_placeholder_img_src(),
					'full'  => wc_placeholder_img_src( 'full' ),
				),
			);

			// populate frontend data structure.
			foreach ( $mpctable__['labels'] as $key => $label ) {
				$data = get_option( $key );
				if ( ! empty( $data ) && '' !== $data ) {
					$mpctable__['labels'][ $key ] = $data;
				}
			}

			// option data( specially checkboxs ).
			foreach ( $mpctable__['options'] as $key => $label ) {
				$value = get_option( $key );
				if ( ! empty( $value ) && '' !== $value ) {
					if ( 'on' === $value ) {
						$mpctable__['options'][ $key ] = true;
					} else {
						$mpctable__['options'][ $key ] = false;
					}
				}
			}

			// default quantity.
			if ( get_option( 'wmca_default_quantity' ) ) {
				$mpctable__['labels']['wmca_default_quantity'] = get_option( 'wmca_default_quantity' );
			}

			// change orderby texts.
			$a = get_option( 'mpc_sddt_default' );
			if ( ! empty( $a ) && '' !== $a ) {
				$mpctable__['orderby_options']['menu_order'] = $a;
			}

			$a = get_option( 'mpc_sddt_price_asc' );
			if ( ! empty( $a ) && '' !== $a ) {
				$mpctable__['orderby_options']['_price-ASC'] = $a;
			}

			$a = get_option( 'mpc_sddt_price_desc' );
			if ( ! empty( $a ) && '' !== $a ) {
				$mpctable__['orderby_options']['_price-DESC'] = $a;
			}

			// check if has pro.
			$mpctable__['has_pro'] = false;

			// Hook to modify frontend core data.
			do_action( 'mpc_frontend_core_data' );
		}

		/**
		 * Process shortcode to array
		 *
		 * @param array $atts table shortcode.
		 */
		public function process_atts( $atts ) {
			global $mpctable__;

			$mpctable__['attributes__'] = $this->sanitize_boolean( $atts );
			$mpctable__['attributes']   = $this->parse_atts( $atts );

			// get page.
			$mpctable__['paged'] = 1;
			if ( isset( $_POST['table_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['table_nonce'] ) ), 'table_nonce_ref' ) ) {
				if ( isset( $_POST['page'] ) && ! empty( $_POST['page'] ) ) {
					$mpctable__['paged'] = (int) sanitize_key( $_POST['page'] );
				}
			}

			// given table attribute, check code.
			if ( ! isset( $mpctable__['attributes']['table'] ) || empty( $mpctable__['attributes']['table'] ) ) {
				return;
			}

			$mpc_opt_sc = new MPCAdminTable();
			$table_id   = (int) $mpctable__['attributes']['table'];

			$code = $mpc_opt_sc->get_frontend_shortcode( $table_id, false );
			if ( empty( $code ) ) {
				return; // given table returned nothing.
			}
			
			$code = str_replace( '[', '', $code );
			$code = str_replace( ']', '', $code );
			$code = str_replace( 'woo-multi-cart', '', $code );

			$atts = shortcode_parse_atts( $code );

			// keep original shortcode attributes.
			$mpctable__['attributes__'] = $this->sanitize_boolean( $atts );

			// assign to global table data structure.
			$mpctable__['attributes'] = $this->parse_atts( $atts );
		}

		/**
		 * Validate and see all attributes are within scopes
		 *
		 * @param array $atts shortcode attributes.
		 */
		public function parse_atts( $atts ) {

			// Reference attributes.
			$ref_atts = array(
				'table'         => '',
				'limit'         => 10,
				'orderby'       => '',
				'order'         => 'DESC',
				'ids'           => '',
				'skip_products' => '',
				'cats'          => '',
				'tags'          => '',
				'type'          => 'all',
				'link'          => 'true',
				'description'   => 'false',
				'selected'      => '',
				'pagination'    => 'true',
				'columns'       => '',
			);

			// Hook for editing shortcode attributes.
			$ref_atts = apply_filters( 'mpc_filter_attributes', $ref_atts );
			$atts     = shortcode_atts( $ref_atts, $atts, 'woo-multi-cart' );

			$atts = $this->sanitize_boolean( $atts );

			// comma separated attributes.
			$cs_atts = array( 'selected', 'ids', 'skip_products', 'cats', 'tags', 'type', 'columns' );

			foreach ( $cs_atts as $type ) {

				// for selected all, skip.
				if ( 'selected' === $type && 'all' === $atts[ $type ] ) {
					continue;
				}

				if ( isset( $atts[ $type ] ) && '' !== $atts[ $type ] ) {
					$tmp   = str_replace( ' ', '', $atts[ $type ] );
					$tmp   = explode( ',', $tmp );
					$tmp_a = array();

					foreach ( $tmp as $a ) {
						if ( false === in_array( $a, $tmp_a, true ) ) {
							if ( 'type' === $type || 'columns' === $type ) {
								array_push( $tmp_a, $a );
							} else {
								array_push( $tmp_a, (int) $a );
							}
						}
					}

					$atts[ $type ] = $tmp_a;
				}
			}

			return $atts;
		}

		/**
		 * Convert possible boolean values if it's not
		 *
		 * @param array $atts shortcode attributes.
		 */
		public function sanitize_boolean( $atts ) {

			if ( false === is_array( $atts ) ) {
				return $atts;
			}

			foreach ( $atts as $key => $value ) {
				if ( ! isset( $value ) || empty( $value ) || '' === $value ) {
					continue;
				}

				if ( gettype( $value ) === 'string' ) {
					$value = sanitize_title( $value );

					// convert string true | false attribute value to boolean.
					if ( strpos( $value, 'true' ) !== false ) {
						$atts[ $key ] = true;
					} elseif ( strpos( $value, 'false' ) !== false ) {
						$atts[ $key ] = false;
					}
				}
			}

			return $atts;
		}

		/**
		 * Find possible table header columns
		 */
		public function get_columns() {
			global $mpctable__;

			$cols = array();
			if ( isset( $mpctable__['attributes']['columns'] ) ) {
				$cols = $mpctable__['attributes']['columns'];
			}

			$value = '';
			if ( empty( $cols ) ) {
				$value = get_option( 'wmc_sorted_columns' );
			}

			if ( ! empty( $value ) ) {
				$cols = explode( ',', str_replace( ' ', '', $value ) );
			}

			if ( empty( $cols ) ) {
				return;
			}

			$cols = array_map(
				function ( $value ) {
					return false === strpos( $value, 'wmc_ct_' ) ? 'wmc_ct_' . $value : $value;
				},
				$cols
			);

			if ( ! $mpctable__['has_variation'] ) {
				$cols = array_diff( $cols, array( 'wmc_ct_variation' ) );
			}

			$pro_cols = array( 'wmc_ct_category', 'wmc_ct_stock', 'wmc_ct_tag', 'wmc_ct_sku', 'wmc_ct_rating' );

			// fallback to free from pro.
			if ( ! $mpctable__['has_pro'] ) {
				$cols = array_diff( $cols, $pro_cols );
			}

			$mpctable__['columns_list'] = $cols;
		}
	}
}

$tblcls = new MPCTable();
$tblcls->init();
