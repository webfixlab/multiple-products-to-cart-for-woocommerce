<?php
/**
 * Frontend table template functions.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPCTemplate' ) ) {

	/**
	 * Template functions class
	 */
	class MPCTemplate {



		/**
		 * Frontend table data
		 *
		 * @var array
		 */
		private $data;

		/**
		 * Current product id for a row
		 *
		 * @var int
		 */
		private $id;



		/**
		 * Update class data regularly
		 */
		public function class_data() {
			global $mpctable__;

			$this->data = $mpctable__;
			$this->id   = isset( $this->data['psid'] ) ? $this->data['psid'] : '';
		}



		/**
		 * Initialization
		 */
		public function init() {
			add_action( 'wp_footer', array( $this, 'image_popup' ) );

			add_filter( 'mpc_template_loader', array( $this, 'template_loader' ), 10, 1 );

			add_action( 'mpc_before_table', array( $this, 'table_settings' ), 10 );
			add_action( 'mpc_table_header', array( $this, 'before_table' ), 10 );
			add_action( 'mpc_table_header_content', array( $this, 'table_orderby' ), 10 );
			add_action( 'mpc_table_header_content', array( $this, 'table_check_all' ), 30 );

			add_action( 'mpc_table_title_columns', array( $this, 'table_header' ), 10 );
			add_action( 'mpc_table_body', array( $this, 'table_body' ), 10 );

			add_action( 'mpc_table_column_image', array( $this, 'row_image' ), 10 );
			add_action( 'mpc_table_column_product', array( $this, 'row_product' ), 10 );
			add_action( 'mpc_table_column_price', array( $this, 'row_price' ), 10 );
			add_action( 'mpc_table_column_variation', array( $this, 'row_variation' ), 10 );
			add_action( 'mpcp_custom_variation_html', array( $this, 'no_variation_text' ), 10 );
			add_action( 'mpc_table_column_quantity', array( $this, 'row_quantity' ), 10 );
			add_action( 'mpc_table_column_buy', array( $this, 'row_buy' ), 10 );

			add_action( 'mpc_table_buy_btton', array( $this, 'row_buy_checkbox' ), 10 );

			add_action( 'mpc_table_total', array( $this, 'table_total' ), 10 );
			add_action( 'mpc_table_add_to_cart_button', array( $this, 'table_cart_button' ), 10 );

			add_action( 'mpc_table_footer', array( $this, 'table_footer' ), 10 );
		}


		/**
		 * Display table image popup
		 */
		public function image_popup() {
			$this->class_data();

			if ( ! isset( $this->data['products'] ) || empty( $this->data['products'] ) ) {
				return;
			}

			?>
			<div id="mpcpop" class="mpc-popup">
				<div class="image-wrap">
					<span class="dashicons dashicons-dismiss mpcpop-close"></span>
					<img src="">
					<h4 class="mpcpop-title"></h4>
					<p class="mpcpop-price"><?php $this->total_price(); ?></p>
				</div>
			</div>
			<?php
			
		}

		/**
		 * Override default product table template
		 *
		 * @param string $path override if given, else use default template file.
		 */
		public function template_loader( $path = '' ) {
			// Extract the filename from the path.
			$filename = basename( $path );

			// Construct the path to the file in the theme directory.
			$path_override = get_stylesheet_directory() . '/templates/' . $filename;

			// Check if the file exists in the theme directory.
			if ( file_exists( $path_override ) ) {
				// Return the path to the file in the theme directory.
				return $path_override;
			}

			// If the file doesn't exist in the theme directory, return the original path.
			return $path;
		}



		/**
		 * Keep a copy of shortcode for ajax pagination or filtering purpose
		 */
		public function table_settings() {
			$this->class_data();

			$atts = '';
			if ( isset( $this->data['attributes__'] ) && ! empty( $this->data['attributes__'] ) ) {
				$atts = $this->data['attributes__'];
			}

			?>
			<div class="mpc-table-query" data-atts="<?php echo wc_esc_json( wp_json_encode( $atts ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></div>
			<?php
		}

		/**
		 * Display table header container
		 */
		public function before_table() {
			$this->class_data();
			do_action( 'mpc_table_header_content' );
		}

		/**
		 * Display table header columns
		 */
		public function table_header() {
			$this->class_data();

			if ( ! isset( $this->data['columns_list'] ) || empty( $this->data['columns_list'] ) ) {
				return '';
			}

			?>
			<thead>
				<tr>
					<?php
						foreach ( $this->data['columns_list'] as $key ) {
							printf(
								'<th for="%s" class="mpc-product-%s">%s</th>',
								esc_attr( $key ),
								esc_attr( str_replace( 'wmc_ct_', '', $key ) ),
								esc_html( $this->data['labels'][ $key ] )
							);
						}
					?>
				</tr>
			</thead>
			<?php
			
		}

		/**
		 * Display orderby filter dropdown
		 */
		public function table_orderby() {
			$this->class_data();

			// check if admin option enabled for showing.
			if ( ! isset( $this->data['options']['wmc_show_products_filter'] ) || empty( $this->data['options']['wmc_show_products_filter'] ) ) {
				return;
			}

			if ( ! isset( $this->data['options']['mpc_show_title_dopdown'] ) || $this->data['options']['mpc_show_title_dopdown'] ) {

				// add title sorting option if needed.
				$this->data['orderby_options']['title-ASC'] = __( 'Title: A to Z', 'multiple-products-to-cart-for-woocommerce' );
				$a = get_option( 'mpc_sddt_title_asc' );
				if ( ! empty( $a ) && '' !== $a ) {
					$this->data['orderby_options']['title-ASC'] = $a;
				}

				$this->data['orderby_options']['title-DESC'] = __( 'Title: Z to A', 'multiple-products-to-cart-for-woocommerce' );
				$a = get_option( 'mpc_sddt_title_desc' );
				if ( ! empty( $a ) && '' !== $a ) {
					$this->data['orderby_options']['title-DESC'] = $a;
				}
			}

			$saved_slug = $this->data['attributes']['orderby'] . '-' . $this->data['attributes']['order'];

			?>
			<div class="mpc-sort">
				<select name="mpc_orderby" class="mpc-orderby" title="<?php echo esc_html__( 'Table order by', 'multiple-products-to-cart-for-woocommerce' ); ?>">
					<?php
						foreach ( $this->data['orderby_options'] as $slug => $label ) {
							$selected = sanitize_title( $slug ) === $saved_slug ? 'selected' : '';

							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $slug ),
								esc_attr( $selected ),
								esc_html( $label )
							);
						}
					?>
				</select>
				<input type="hidden" name="paged" value="1" />
			</div>
			<?php

		}

		/**
		 * Display check all products checkbox
		 */
		public function table_check_all() {
			$this->class_data();

			// check if select all checkbox is enabled.
			if ( ! isset( $this->data['options']['wmc_show_all_select'] ) || false === $this->data['options']['wmc_show_all_select'] ) {
				return;
			}

			?>
			<div class="mpc-all-select">
				<label><?php echo esc_html( $this->data['labels']['wmc_select_all_text'] ); ?></label>
				<input type="checkbox" class="mpc-check-all">
			</div>
			<?php

		}



		/**
		 * Display table body with all the rows
		 */
		public function table_body() {
			global $mpctable__;

			$this->class_data();

			foreach ( $this->data['products'] as $id => $prod ) {
				$mpctable__['psid'] = $id;

				$this->table_row( $id );

				do_action( 'mpc_table_row' );
			}
		}

		/**
		 * Display product table row
		 *
		 * @param int $id product id of the current table row.
		 */
		public function table_row( $id ) {
			$this->class_data();

			$product = $this->data['products'][ $id ]; // get product data of the table row.

			printf(
				'<tr class="cart_item %s" data-varaition_id="0" data-type="%s" data-id="%s" stock="%s" data-price="%s">',
				esc_attr( $product['type'] ),
				esc_attr( $product['type'] ),
				esc_attr( $id ),
				esc_attr( $product['stock'] ),
				isset( $product['price_'] ) ? esc_attr( $product['price_'] ) : ''
			);

			// display each column at columns_list.
			foreach ( $this->data['columns_list'] as $key ) {
				do_action( 'mpc_table_column_' . str_replace( 'wmc_ct_', '', $key ) ); // render the row column content. $key = column name.
			}

			echo '</tr>';
		}



		/**
		 * Display table image column
		 */
		public function row_image() {
			$this->class_data();

			$prod = $this->data['products'][ $this->id ];

			// Get actual dynamic image sizes (registered).
			$thumb = $this->data['image_sizes']['thumb'];
			$full  = $this->data['image_sizes']['full'];

			?>
			<td for="image" class="mpc-product-image" data-pimg-thumb="<?php echo esc_url( $prod['images'][ $thumb ] ); ?>" data-pimg-full="<?php echo esc_url( $prod['images'][ $full ] ); ?>">
				<div class="mpcpi-wrap">
					<?php if ( $this->data['options']['mpc_show_on_sale'] && $prod['on_sale'] ) : ?>
						<span class="wfl-sale">
							<?php echo esc_html__( 'sale', 'multiple-products-to-cart-for-woocommerce' ); ?>
						</span>
					<?php endif; ?>
					<img src="<?php echo esc_url( $prod['images'][ $thumb ] ); ?>" class="mpc-product-image attachment-<?php echo esc_attr( $thumb ); ?> size-<?php echo esc_attr( $thumb ); ?>" alt="" data-fullimage="<?php echo esc_url( $prod['images'][ $full ] ); ?>">
					<div class="mpc-popup-title" style="display: none;"><?php echo esc_html( $prod['title'] ); ?></div>
					<div class="mpc-popup-price" style="display: none;"><?php echo isset( $prod['price'] ) && ! empty( $prod['price'] ) ? wp_kses_post( $prod['price'] ) : ''; ?></div>
				</div>
				<?php do_action( 'init_mpc_gallery' ); ?>
			</td>
			<?php
		}

		/**
		 * Display product title and description
		 */
		public function row_product() {
			$this->class_data();

			$prod = $this->data['products'][ $this->id ];
			$html = '';

			// display extra stuff before product title.
			if( isset( $prod['parent'] ) ){
				$title = $prod['parent']['title'];

				if( $this->data['attributes']['link'] ){
					$title = sprintf(
						'<a href="%s">%s</a>',
						esc_url( $prod['parent']['url'] ),
						$prod['parent']['title']
					);
				}

				$html .= sprintf( '<div class="product-parent">%s</div>', $title );
			}

			// product title.
			$title = $prod['title'];
			if( $this->data['attributes']['link'] ){
				$title = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $prod['url'] ),
					$prod['title']
				);
			}

			$html .= $title;

			// product description.
			if( $this->data['attributes']['description'] ){
				$html .= sprintf(
					'<div class="woocommerce-product-details__short-description"><p>%s</p></div>',
					$prod['desc']
				);
			}

			?>
			<td for="title" class="mpc-product-name">
				<div class="mpc-product-title">
					<?php echo wp_kses_post( $html ); ?>
				</div>
			</td>
			<?php
		}

		/**
		 * Display product price and price range
		 */
		public function row_price() {
			$this->class_data();

			$prod = $this->data['products'][ $this->id ];

			?>
			<td for="price" class="mpc-product-price">
				<div class="mpc-single-price" style="display:none;">
					<?php
						// for variable products only.
						if ( strpos( $prod['type'], 'variable' ) !== false ) {
							$this->total_price();
						}
					?>
				</div>
				<div class="mpc-range">
					<?php
						echo isset( $prod['price'] ) && ! empty( $prod['price'] ) ? wp_kses(
							$prod['price'],
							array(
								'span' => array(
									'class' => array(),
								),
								'bdi'  => array(),
								'del'  => array(),
								'ins'  => array(),
							)
						) : '';
					?>
				</div>
			</td>
			<?php
		}

		/**
		 * Display table variation column
		 */
		public function row_variation() {
			$this->class_data();

			// if no variable product exists in current session, return.
			if ( false === $this->data['has_variation'] ) {
				return;
			}

			?>
			<td for="variation" class="mpc-product-variation">
				<?php
					// add custom variation html content.
					do_action( 'mpcp_custom_variation_html' );

					// display variation attributes.
					$this->variation_options();
				?>
			</td>
			<?php
		}

		/**
		 * Display content for simple product on variation scope of a table page
		 */
		public function no_variation_text() {
			$this->class_data();

			$prod = $this->data['products'][ $this->id ];

			if ( false === strpos( $prod['type'], 'simple' ) ) {
				return;
			}

			printf(
				'<span>%s</span>',
				esc_html( $this->data['labels']['wmc_empty_value_text'] )
			);
		}

		/**
		 * Display product variation attribute dropdowns
		 */
		public function variation_options() {
			$this->class_data();

			$prod = $this->data['products'][ $this->id ];

			if ( ! isset( $prod['attributes'] ) ) {
				return;
			}

			$option_label = ! empty( $this->data['labels']['wmc_option_text'] ) ? $this->data['labels']['wmc_option_text'] . ' ' : '';

			?>
			<div class="row-variation-data" data-variation_data="<?php echo wc_esc_json( wp_json_encode( $prod['variation_data'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></div>
			<?php

			$total   = 0;
			$checked = 0;

			foreach ( $prod['attributes'] as $name => $data ) :
				$name_ = sanitize_title( $name );

				$total++;

				?>
				<div class="variation-group">
					<select class="<?php echo esc_attr( $name_ ); ?>" name="attribute_<?php echo esc_attr( $name_ . $this->id ); ?>" data-attribute_name="attribute_<?php echo esc_attr( $name_ ); ?>">
						<?php
							// dynamic dropdown intro from settings.
							printf(
								'<option value="">%s</option>',
								esc_html( $option_label . $data['label'] )
							);

							foreach ( $data['options'] as $option ) {
								if( true === $option['is_selected'] ){
									$checked++;
								}

								printf(
									'<option data-value="%s" value="%s" %s>%s</option>',
									esc_attr( $option['slug'] ),
									esc_html( $option['value'] ),
									true === $option['is_selected'] ? 'selected' : '',
									esc_html( $option['name'] )
								);
							}
						?>
					</select>
				</div>
				<?php

			endforeach;

			// display reset variaion button if all checked or have values.
			$clear_btn = $total === $checked && $total > 0 ? sprintf( '<a class="reset_variations" href="#">%s</a>', esc_html__( 'Clear', 'multiple-products-to-cart-for-woocommerce' ) ) : '';

			printf(
				'<div class="clear-button">%s</div>',
				wp_kses_post( $clear_btn )
			);
		}

		/**
		 * Display product quantity
		 */
		public function row_quantity() {
			$this->class_data();

			$prod = $this->data['products'][ $this->id ];

			// skip for grouped product.
			if( 'grouped' === $prod['type'] ){
				echo wp_kses_post( '<td></td>' );
				return;
			}

			$default_qty = isset( $this->data['labels']['wmca_default_quantity'] ) ? (int) $this->data['labels']['wmca_default_quantity'] : $this->data['quantity']['min'];
			$minimum_qty = $this->data['quantity']['min'] ?? 1; // minimum should be at least 1.
			$maximum_qty = $this->data['quantity']['max'];

			// stock & max quantity.
			if ( isset( $prod['stock_'] ) ) {
				$maximum_qty = $prod['stock_'];

				// If stock exceeds current default, change back to to stock.
				if ( $default_qty > $maximum_qty ) {
					$default_qty = $maximum_qty;
				}
			}

			// If sold individually, set quantity to 1.
			if ( true === $prod['sold_individually'] ) {
				$maximum_qty = 1;
				$default_qty = 1;
			}

			?>
			<td for="quantity" class="mpc-product-quantity">
				<div class="quantity">
					<?php
						printf(
							'<input type="number" class="input-text qty text" step="1" min="%s"%s name="quantity%s" value="%s" data-default="%s" title="%s" size="4" inputmode="numeric"%s>',
							esc_attr( $minimum_qty ),
							'' === $maximum_qty ? '' : ' max="' . esc_attr( $maximum_qty ) . '"',
							esc_attr( $this->id ),
							esc_html( $default_qty ),
							esc_html( $default_qty ),
							esc_html__( 'Quantity', 'multiple-products-to-cart-for-woocommerce' ),
							isset( $prod['stock_'] ) ? ' data-current_stock="' . esc_html( $prod['stock_'] ) . '"' : ''
						);
					?>
				</div>
			</td>
			<?php

		}

		/**
		 * Display product add to cart column
		 */
		public function row_buy() {
			$this->class_data();

			?>
			<td for="buy" class="mpc-product-select">
				<?php do_action( 'mpc_table_buy_btton' ); ?>
			</td>
			<?php

		}

		/**
		 * Display product buying checkbox
		 */
		public function row_buy_checkbox() {
			$this->class_data();

			$prod = $this->data['products'][ $this->id ];

			printf(
				'<input type="checkbox" name="product_ids[]" value="%s" %s %s>',
				esc_attr( $this->id ),
				true === $prod['is_selected'] ? 'checked="checked"' : '',
				false !== strpos( $prod['type'], 'variable' ) ? '' : 'data-price="' . esc_html( $prod['price_'] ) . '"'
			);
		}

		/**
		 * Display cart total price
		 */
		public function table_total() {
			$this->class_data();

			?>
			<div class="total-row">
				<span class="total-label"><?php echo esc_html( $this->data['labels']['wmc_total_button_text'] ); ?></span>
				<span class="mpc-total"><?php $this->total_price(); ?></span>
			</div>
			<?php

		}

		/**
		 * Display add to cart button
		 */
		public function table_cart_button() {
			$this->class_data();

			?>
			<input type="submit" class="mpc-add-to-cart single_add_to_cart_button button alt wc-forward" name="proceed" value="<?php echo esc_html( $this->data['labels']['wmc_button_text'] ); ?>" />
			<?php

		}



		/**
		 * Display total price template | WooCommerce
		 *
		 * @param int $total given total price.
		 */
		public function total_price( $total = '' ) {
			$this->class_data();

			// If not given anything, it will show 0 | else that price.
			if ( empty( $total ) ) {
				$total = 0;
			}

			?>
			<span class="woocommerce-Price-amount amount">
				<bdi>
					<span class="total-price"><?php echo esc_attr( $total ); ?></span>
					<span class="woocommerce-Price-currencySymbol"><?php echo wp_kses_post( get_woocommerce_currency_symbol() ); ?></span>
				</bdi>
			</span>
			<?php
		}

		/**
		 * Display product table footer
		 */
		public function table_footer() {
			global $mpctable__;

			// display total price.
			do_action( 'mpc_table_total' );

			?>
			<div class="mpc-button">
				<?php $this->display_table_pagination_range(); ?>
				<div>
					<input type="hidden" name="add_more_to_cart" value="1">
					<?php if ( true === $mpctable__['options']['wmca_show_reset_btn'] ) : ?>
						<input type="reset" class="mpc-reset" value="<?php echo esc_html( $mpctable__['labels']['wmc_reset_button_text'] ); ?>">
					<?php endif; ?>
					<?php do_action( 'mpc_table_add_to_cart_button' ); ?>
				</div>
			</div>
			<?php $this->render_mpc_pagination(); ?>
			<div class="mpc-table-query" data-atts="<?php echo ! empty( $mpctable__['attributes__'] ) && '' !== $mpctable__['attributes__'] ? wc_esc_json( wp_json_encode( $mpctable__['attributes__'] ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></div>
			<?php

		}

		/**
		 * Display current products range of total
		 */
		public function display_table_pagination_range() {
			global $mpctable__;

			// if admin settings option is not enabled return.
			if ( ! $mpctable__['options']['wmc_show_pagination_text'] ) {
				return;
			}

			// if shortcode attribute is set to no pagination.
			if ( ! $mpctable__['attributes']['pagination'] ) {
				return;
			}

			// and finally, if pagination enabled, current range is within set limit.
			if ( $mpctable__['query']['total'] <= $mpctable__['attributes']['limit'] ) {
				return;
			}

			// Current page.
			$page = $mpctable__['paged'];
			if ( empty( $page ) || '' === $page ) {
				$page = 1;
			}

			// display current page products range.
			$product_range = '';

			if ( $mpctable__['query']['total'] > $mpctable__['attributes']['limit'] ) {
				$product_range = ( ( $page - 1 ) * $mpctable__['attributes']['limit'] + 1 ) . ' - ';

				// check if max range is within max.
				if ( ( $page * $mpctable__['attributes']['limit'] ) <= $mpctable__['query']['total'] ) {
					$product_range .= ( $page * $mpctable__['attributes']['limit'] );

				} else {
					$product_range .= $mpctable__['query']['total'];
				}
			} else {
				$product_range = ( ( $page - 1 ) * $mpctable__['attributes']['limit'] + 1 ) . ' - ' . $mpctable__['query']['total'];
			}

			?>
			<div class="mpc-product-range" data-page_limit="<?php echo esc_attr( $mpctable__['attributes']['limit'] ); ?>">
				<p>
					<?php echo esc_html( $mpctable__['labels']['wmc_pagination_text'] ); ?> <strong><span class="ranges"><?php echo esc_html( $product_range ); ?></span> / <span class="max_product"><?php echo esc_attr( $mpctable__['query']['total'] ); ?></soan></strong>
				</p>
			</div>
			<?php

		}

		/**
		 * Display numbered pagination
		 */
		public function render_mpc_pagination() {
			global $mpctable__;

			if ( ! $mpctable__['attributes']['pagination'] || $mpctable__['query']['total'] <= $mpctable__['attributes']['limit'] ) {
				return;
			}

			?>
			<div class="mpc-pagination">
				<div class="mpc-inner-pagination">
					<?php $this->numbered_pagination(); ?>
				</div>
			</div>
			<?php

		}

		/**
		 * Display pagination numbers
		 */
		public function numbered_pagination() {
			global $mpctable__;

			// get current page and maximum page number.
			$paged    = ! empty( $mpctable__['paged'] ) ? (int) $mpctable__['paged'] : 1;
			$max_page = (int) $mpctable__['query']['max_page'];

			$pages = $this->pagination_numbers( $paged, $max_page );
			if ( empty( $pages ) || ! is_array( $pages ) ) {
				return;
			}

			// current pages counter.
			$total_pages = count( $pages );

			?>
			<div class="mpc-pagenumbers" data-max_page="<?php echo esc_attr( $mpctable__['query']['max_page'] ); ?>">
				<?php
					for ( $i = 0; $i < $total_pages; $i++ ) {
						if ( 0 === $pages[ $i ] || $pages[ $i ] > $max_page ) {
							continue;
						}

						if ( $i > 0 && abs( $pages[ ( $i - 1 ) ] - $pages[ $i ] ) > 1 ) {
							echo '...';
						}
						?>
						<span <?php echo $pages[ $i ] === $paged ? 'class="current"' : ''; ?>>
							<?php echo esc_attr( $pages[ $i ] ); ?>
						</span>
						<?php
					}
				?>
			</div>
			<?php

		}

		/**
		 * Display pagination number
		 *
		 * @param int $paged current page.
		 * @param int $limit maximum page limit.
		 */
		public function pagination_numbers( $paged, $limit ) {
			if ( 1 === $limit ) {
				return array();
			} elseif ( $limit < 5 ) {
				return range( 1, $limit );
			}

			$pages = array( 1, $limit ); // all pages to display in the pagination list.

			$pages = array_merge( $pages, range( $paged - 1, $paged + 1 ) );
			$pages = array_unique( $pages );
			sort( $pages );

			return $pages;
		}
	}
}

global $MPCTemplate;

$MPCTemplate = new MPCTemplate();
$MPCTemplate->init();
