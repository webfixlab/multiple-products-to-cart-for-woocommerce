<?php
/**
 * Table template functions
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      9.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPC_Table_Template' ) ) {

	/**
	 * Table template class
	 */
	class MPC_Table_Template {

		/**
		 * Frontent template data
		 * @var array
		 */
		private static $data;

		/**
		 * Initialize table actions and filters.
		 */
		public static function init(){
			self::setup_frontend_data();

			// header.
			add_action( 'mpc_table_filters', array( __CLASS__, 'table_orderby' ), 10 );
			add_action( 'mpc_table_actions', array( __CLASS__, 'table_check_all' ), 10 );
			
			// content.
			add_action( 'mpc_table_title_columns', array( __CLASS__, 'display_table_header' ), 10 );
			add_action( 'mpc_table_body', array( __CLASS__, 'display_table_body' ), 10 );

			// table columns.
			add_action( 'mpc_table_column_image', array( __CLASS__, 'display_product_image' ), 10, 1 );
			add_action( 'mpc_table_column_product', array( __CLASS__, 'display_product_details' ), 10, 1 );
			add_action( 'mpc_table_column_price', array( __CLASS__, 'display_product_price' ), 10, 1 );

			add_action( 'mpc_table_column_variation', array( __CLASS__, 'display_product_variations' ), 10, 1 );

			add_action( 'mpc_table_column_quantity', array( __CLASS__, 'display_product_quantity' ), 10, 1 );
			add_action( 'mpc_table_column_buy', array( __CLASS__, 'display_product_checkbox' ), 10, 1 );
			
			// footer.
			add_action( 'mpc_table_footer', array( __CLASS__, 'table_footer' ), 10 );
			add_action( 'mpc_table_total', array( __CLASS__, 'table_total' ), 10 );
			add_action( 'mpc_table_add_to_cart_button', array( __CLASS__, 'add_to_cart_btn_all' ), 10 );
			

			add_action( 'wp_footer', array( __CLASS__, 'image_popup' ) );
		}

		/**
		 * Get global data into class static variable for ease of access
		 */
		private static function setup_frontend_data(){
			global $mpc_table__;

			$data = &$mpc_table__;
		}

		/**
		 * Display orderby filter dropdown
		 */
		public static function table_orderby() {
			self::setup_frontend_data();

			$show_filter = get_option( 'wmc_show_products_filter' );
			if( ! empty( $show_filter ) || 'on' !== $show_filter ){
				return;
			}

			$title_filter = get_option( 'mpc_show_title_dopdown' );
			if( empty( $title_filter ) || 'on' === $title_filter ){
				$title_asc = get_option( 'mpc_sddt_title_asc' );
				self::$data['orderby']['title-ASC'] = empty( $title_asc ) ? __( 'Title: A to Z', 'multiple-products-to-cart-for-woocommerce' ) : $title_asc;

				$title_desc = get_option( 'mpc_sddt_title_desc' );
				self::$data['orderby']['title-ASC'] = empty( $title_desc ) ? __( 'Title: Z to A', 'multiple-products-to-cart-for-woocommerce' ) : $title_desc;
			}
			?>
			<div class="mpc-sort">
				<select
					name="mpc_orderby"
					class="mpc-orderby"
					title="<?php echo esc_html__( 'Table order by', 'multiple-products-to-cart-for-woocommerce' ); ?>">
					<?php self::table_orderby_options(); ?>
				</select>
			</div>
			<?php
		}

		/**
		 * Display filter options
		 */
		private static function table_orderby_options(){
			foreach ( self::$data['orderby'] as $slug => $label ) {
				?>
				<option
					value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php
			}
		}

		/**
		 * Display check all products checkbox
		 */
		public static function table_check_all() {
			self::setup_frontend_data();

			$show_all_check = get_option( 'wmc_show_all_select' );
			if( ! empty( $show_all_check ) && 'on' !== $show_all_check ){
				return; // option hidden.
			}

			$show_product_check = get_option( 'mpc_add_to_cart_checkbox' );
			if( ! empty( $show_product_check ) && 'on' !== $show_product_check ) {
				return;
			}
			?>
			<div class="mpc-all-select">
				<label><?php echo esc_html( get_option( 'wmc_select_all_text', __( 'Select All', 'multiple-products-to-cart-for-woocommerce' ) ) ); ?></label>
				<input
					type="checkbox"
					class="mpc-check-all">
			</div>
			<?php
		}



		/**
		 * Display table header columns
		 */
		public static function display_table_header() {
			self::setup_frontend_data();
			?>
			<thead>
				<tr>
					<?php self::display_table_header_columns(); ?>
				</tr>
			</thead>
			<?php
		}

		/**
		 * Display table columns with labels
		 */
		private static function display_table_header_columns(){
			foreach ( self::$data['columns'] as $slug => $label ) {
				?>
				<th
					for="<?php echo esc_attr( $slug ); ?>"
					class="mpc-product-<?php echo esc_attr( str_replace( 'wmc_ct_', '', $slug ) ); ?>"><?php echo esc_html( $label ); ?></th>
				<?php
			}
		}

		/**
		 * Display table body with all the rows
		 */
		public static function display_table_body() {
			self::setup_frontend_data();

			foreach ( self::$data['products'] as $product_id ) {
				self::display_table_row( $product_id );
				do_action( 'mpc_table_row' );
			}
		}

		/**
		 * Display product table row
		 *
		 * @param int $product_id Product id.
		 */
		private static function display_table_row( $product_id ) {
			$product = wc_get_product( (int) $product_id );
			?>
			<tr
				class="cart_item"
				data-variation_id="0"
				data-type="<?php echo esc_attr( $product->get_type() ); ?>"
				data-id="<?php echo esc_attr( $product_id ); ?>"
				stock="<?php echo esc_attr( $product->get_stock_quantity() ); ?>"
				stock_status="<?php echo esc_attr( $product->get_stock_status() ); ?>"
				data-price="<?php echo esc_attr( MPC_Product_Data::get_price_amount( $product ) ); ?>">
				<?php self::add_row_columns_action( $product ); ?>
			</tr>
			<?php
		}
		private static function add_row_columns_action( $product ){
			foreach ( self::$data['columns'] as $slug => $label ) {
				do_action( 'mpc_table_column_' . str_replace( 'wmc_ct_', '', $slug ), $product );
			}
		}


		
		/**
		 * Display product image
		 *
		 * @param object $product Product object.
		 */
		public static function display_product_image( $product ) {
			self::setup_frontend_data();

			$image_id = $product->get_image_id();
			$thumb = empty( $image_id ) ? self::$data['image']['thumb'] : wp_get_attachment_image_url( $image_id, 'thumbnail' );
			$full  = empty( $image_id ) ? self::$data['image']['full'] : wp_get_attachment_image_url( $image_id, 'large' );
			?>
			<td for="image" class="mpc-product-image">
				<div class="mpcpi-wrap">
					<?php self::display_on_sale( $product ); ?>
					<img src="<?php echo esc_url( $thumb ); ?>" class="mpc-product-image attachment-<?php echo esc_attr( $thumb ); ?> size-<?php echo esc_attr( $thumb ); ?>" alt="" data-fullimage="<?php echo esc_url( $full ); ?>">
				</div>
				<?php do_action( 'init_mpc_gallery', $product ); ?>
			</td>
			<?php
		}

		/**
		 * Display on sale badge if the product is on sale
		 *
		 * @param object $product Product object.
		 */
		private static function display_on_sale( $product ){
			if( ! $product->is_on_sale() ){
				return;
			}

			$show_on_sale = get_option( 'mpc_show_on_sale' );
			if( ! empty( $show_on_sale ) && 'on' !== $show_on_sale ){
				return;
			}
			?>
			<span class="wfl-sale">
				<?php echo esc_html__( 'sale', 'multiple-products-to-cart-for-woocommerce' ); ?>
			</span>
			<?php
		}

		/**
		 * Display product title and description
		 *
		 * @param object $product Product object.
		 */
		public static function display_product_details( $product ) {
			?>
			<td for="title" class="mpc-product-name">
				<div class="mpc-product-title">
					<?php self::display_product_title( $product ); ?>
					<?php self::display_product_description( $product ); ?>
				</div>
			</td>
			<?php
		}

		/**
		 * Display on sale badge if the product is on sale
		 *
		 * @param object $product Product object.
		 */
		private static function display_product_title( $product ){
			?>
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_title() ); ?></a>
			<?php
		}

		/**
		 * Display on sale badge if the product is on sale
		 *
		 * @param object $product Product object.
		 */
		private static function display_product_description( $product ){
			if( isset( self::$data['atts']['desc'] ) && false === (bool) self::$data['atts']['desc'] ){
				return;
			}

			$desc = $product->get_short_description();
			$desc = empty( $desc ) ? $product->get_description() : $desc;
			?>
			<div class="woocommerce-product-details__short-description">
				<p><?php echo esc_html( wp_strip_all_tags( do_shortcode( $desc ) ) ); ?></p>
			</div>
			<?php
		}

		/**
		 * Display product price and price range
		 *
		 * @param object $product Product object.
		 */
		public static function display_product_price( $product ) {
			?>
			<td for="price" class="mpc-product-price">
				<div class="mpc-single-price" style="display:none;">
					<?php
						if ( 'variable' === $product->get_type() ) { // for variable products only.
							self::total_price();
						}
					?>
				</div>
				<div class="mpc-range">
					<?php echo $product->get_price_html(); ?>
				</div>
			</td>
			<?php
		}



		/**
		 * Display product variations
		 *
		 * @param object $product Product object.
		 */
		public static function display_product_variations( $product ) {
			self::setup_frontend_data();
			?>
			<td for="variation" class="mpc-product-variation">
				<div
					class="row-variation-data"
					data-variation_data="<?php echo wc_esc_json( wp_json_encode( $product->get_available_variations( 'array' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></div>
				<?php self::display_variation_attributes( $product ); ?>
				<?php do_action( 'mpcp_custom_variation_html' ); // add custom variation html content. ?>
			</td>
			<?php
		}

		/**
		 * Display product variation attribute dropdowns
		 *
		 * @param object $product Product object.
		 */
		private static function display_variation_attributes( $product ) {
			$attributes = $product->get_variation_attributes();
			if( 'variable' !== $product->get_type() || empty( $attributes ) || ! is_array( $attributes ) ){
				self::no_variation_text();
				return;
			}
			
			$default_atts = $product->get_default_attributes();

			foreach( $attributes as $att_name => $options ){
				$att_name_sanitized = sanitize_title( $att_name );

				$terms = taxonomy_exists( $att_name ) ? array_filter( wc_get_product_terms( $product->get_id(), $att_name, array( 'fields' => 'all' ) ), function( $term ) use( $options ){
					return in_array( $term->slug, $options );
				} ) : $options;

				$terms = array_map( function( $term ) use( $default_atts, $att_name_sanitized ){
					$is_obj = is_object( $term );
					$slug   = $is_obj ? $term->slug : $term;

					return array(
						'name'     => $is_obj ? $term->name : $term,
						'slug'     => $slug,
						'selected' => isset( $default_atts[ $att_name_sanitized ] ) && $slug === $default_atts[ $att_name_sanitized ]
					);
				}, $terms );

				$default_option = self::$data['labels']['variation_prefix'] . wc_attribute_label( $att_name );
				?>
				<select
					class="mpc-var-att <?php echo esc_attr( $att_name_sanitized ); ?>"
					name="attribute_<?php echo esc_attr( $att_name_sanitized . $product->get_id() ); ?>"
					data-attribute_name="attribute_<?php echo esc_attr( $att_name_sanitized ); ?>">
					<option value=""><?php echo esc_html( $default_option ); ?></option>
					<?php self::display_variation_attribute( $terms ); ?>
				</select>
				<?php
			}

			if( ! empty( $default_atts ) ){
				?>
				<div class="clear-button">
					<a class="reset_variations" href="#"><?php echo esc_html__( 'Clear', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
				</div>
				<?php
			}
		}

		/**
		 * Display variation attribute
		 *
		 * @param array $terms Attribute option terms.
		 */
		private static function display_variation_attribute( $terms ){
			foreach( $terms as $term ){
				?>
				<option
					data-value="<?php echo esc_html( $term['slug'] ); ?>"
					value="<?php echo esc_html( $term['slug'] ); ?>" <?php echo $term['selected'] ? 'selected' : ''; ?>><?php echo esc_html( $term['name'] ); ?></option>
				<?php
			}
		}

		/**
		 * Display no variation message
		 */
		private static function no_variation_text() {
			?>
			<span class="mpc-empty-var"><?php echo esc_html( get_option( 'wmc_empty_value_text', __( 'N/A', 'multiple-products-to-cart-for-woocommerce' ) ) ); ?></span>
			<?php
		}



		/**
		 * Display product quantity
		 *
		 * @param object $product Product object.
		 */
		public static function display_product_quantity( $product ) {
			// skip for grouped product.
			if ( 'grouped' === $product->get_type() ) {
				?>
				<td></td>
				<?php
				return;
			}

			$stock = 'instock' === $product->get_stock_status() ? $product->get_stock_quantity() : '';
			$stock = $product->is_sold_individually() ? 1 : $stock;
			
			$quantity = (int) get_option( 'wmca_default_quantity', 1 );
			$quantity = ! empty( $stock ) && $stock > 0 ? min( $stock, $quantity ) : $quantity;
			?>
			<td for="quantity" class="mpc-product-quantity">
				<input
					type="number"
					step="1"
					min="<?php echo esc_attr( $quantity ); ?>"
					max="<?php echo ! empty( $stock )  ?>"
					name="quantity<?php echo $product->get_id(); ?>"
					value="<?php echo esc_attr( $quantity ); ?>"
					title="<?php esc_html__( 'Quantity', 'multiple-products-to-cart-for-woocommerce' ); ?>"
					size="4"
					inputmode="numeric">
			</td>
			<?php
		}

		/**
		 * Display product add to cart column
		 *
		 * @param object $product Product object.
		 */
		public static function display_product_checkbox( $product ) {
			$checked = is_array( self::$data['atts']['selected'] ) ? in_array( $product->get_id(), self::$data['atts']['selected'], true ) : false;
			?>
			<td for="buy" class="mpc-product-select">
				<input
					type="checkbox"
					name="product_ids[]"
					value="<?php echo $product->get_id(); ?>"
					checked="<?php echo $checked ? 'checked' : ''; ?>">
			</td>
			<?php
		}



		/**
		 * Display product table footer
		 */
		public static function table_footer() {
			self::setup_frontend_data();
			
			do_action( 'mpc_table_total' );
			?>
			<div class="mpc-button">
				<div>
					<input type="hidden" name="add_more_to_cart" value="1">
					<?php self::table_reset_btn(); ?>
					<?php do_action( 'mpc_table_add_to_cart_button' ); ?>
				</div>
			</div>
			<?php self::display_pagination(); ?>
			<div
				class="mpc-table-query"
				data-query="<?php echo wc_esc_json( wp_json_encode( self::$data['args'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
				data-atts="<?php echo wc_esc_json( wp_json_encode( self::$data['atts'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></div>
			<?php
		}

		/**
		 * Display cart total price
		 */
		public static function table_total() {
			self::setup_frontend_data();
			?>
			<div class="total-row">
				<div class="total-price">
					<span class="total-label"><?php echo esc_html( get_option( 'wmc_total_button_text', __( 'Total', 'multiple-products-to-cart-for-woocommerce' ) ) ); ?></span>
					<span class="mpc-total"><?php self::total_price(); ?></span>
				</div>
				<span class="mpc-fixed-cart"><?php echo esc_html( get_option( 'wmc_button_text', __( 'Add to cart', 'multiple-products-to-cart-for-woocommerce' ) ) ); ?></span>
			</div>
			<?php
		}

		/**
		 * Display total price template | WooCommerce
		 *
		 * @param int $total given total price.
		 */
		private static function total_price( $total = '' ) {
			$total = empty( $total ) ? 0 : $total;
			?>
			<span class="woocommerce-Price-amount amount">
				<bdi>
					<?php
						printf(
							esc_attr( get_woocommerce_price_format() ),
							'<span class="woocommerce-Price-currencySymbol">' . esc_attr( get_woocommerce_currency_symbol() ) . '</span>',
							'<span class="total-price">' . esc_attr( $total ) . '</span>'
						);
					?>
				</bdi>
			</span>
			<?php
		}

		/**
		 * Display table reset button
		 */
		private static function table_reset_btn(){
			$show_reset = get_option( 'wmca_show_reset_btn' );
			if( ! empty( $show_reset ) && 'on' !== $show_reset ) {
				return;
			}
			?>
			<input
				type="reset"
				class="mpc-reset"
				value="<?php echo esc_html( get_option( 'wmc_reset_button_text', __( 'Reset', 'multiple-products-to-cart-for-woocommerce' ) ) ); ?>">
			<?php
		}

		/**
		 * Display add to cart button
		 */
		public static function add_to_cart_btn_all() {
			?>
			<input
				type="submit"
				class="mpc-add-to-cart button alt wc-forward"
				name="proceed"
				value="<?php echo esc_html( get_option( 'wmc_button_text', __( 'Add to cart', 'multiple-products-to-cart-for-woocommerce' ) ) ); ?>" />
			<?php
		}

		/**
		 * Display numbered pagination
		 */
		private static function display_pagination() {
			if( isset( self::$data['atts']['pagination'] ) && false === (bool) self::$data['atts']['pagination'] ){
				return;
			}
			
			$limit = (int) self::$data['atts']['limit'] || 10;
			if( self::$data['total'] < $limit ){
				return;
			}
			?>
			<div class="mpc-pagination">
				<div class="mpc-inner-pagination">
					<div class="mpc-pagenumbers" data-max_page="<?php echo esc_attr( self::$data['max_page'] ); ?>">
						<?php self::display_pagination_numbers(); ?>
					</div>
				</div>
				<?php self::display_pagination_range(); ?>
			</div>
			<?php
		}

		/**
		 * Display pagination numbers
		 */
		private static function display_pagination_numbers() {
			if( 1 === self::$data['max_page'] ){
				return;
			}

			$paged    = self::$data['paged'];
			$max_page = self::$data['max_page'];

			$pages = $max_page < 5 ? range( 1, $max_page ) : array();
			if ( $max_page > 5 ) {
				$pages = array_merge( array( 1, $max_page ), range( $paged - 1, $paged + 1 ) );
				$pages = array_unique( $pages );
				sort( $pages );
			}

			for ( $i = 0; $i < count( $pages ); $i++ ) {
				$page = $pages[ $i ];
				if( 0 === $page || $page > $max_page ){
					continue;
				}

				if( $i > 0 && $page - $pages[ $i -1 ] > 1 ){
					echo '<span class="mpc-divider">-</span>';
				}
				?>
				<span
					class="<?php echo $page === $paged ? 'current' : ''; ?>"><?php echo esc_attr( $page ); ?></span>
				<?php
			}
		}

		/**
		 * Display current products range of total
		 */
		private static function display_pagination_range() {
			$show_range = get_option( 'wmc_show_pagination_text' );
			if( ! empty( $show_range ) && 'on' !== $show_range ){
				return;
			}

			$paged = self::$data['paged'];
			$limit = isset( self::$data['atts']['limit'] ) ? (int) self::$data['atts']['limit'] : 10;

			$range_start   = max( 1, ( $paged - 1 ) * $limit );
			$range_end     = min( $paged * $limit, self::$data['total'] );
			$product_range = number_format( $range_start ) . ' - ' . number_format( $range_end );
			?>
			<div class="mpc-product-range" data-page_limit="<?php echo esc_attr( $limit ); ?>">
				<?php
					printf(
						// translators: $1$s: saved prefix, $2$s: starting range, %3$s: where the pagination ends.
						esc_html__( '%1$s %2$s of %3$s products', 'multiple-products-to-cart-for-woocommerce' ),
						esc_html( get_option( 'wmc_pagination_text' ) ),
						'<span class="ranges">' . esc_html( $product_range ) . '</span>',
						'<span class="max_product">' . esc_attr( self::$data['total'] ) . '</span>'
					);
				?>
			</div>
			<?php
		}

		/**
		 * Display table image popup
		 */
		public static function image_popup() {
			?>
			<div id="mpcpop" class="mpc-popup">
				<div class="image-wrap">
					<span class="dashicons dashicons-dismiss mpcpop-close"></span>
					<img src="">
				</div>
			</div>
			<?php
		}
	}
}
