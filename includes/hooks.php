<?php
/**
 * Add template overriding option when loading template
 *
 * @package WordPress.
 * @subpackage Multiple Products to Cart for Woocommerce.
 * @since 1.0
 */

add_filter( 'mpc_template_loader', 'mpc_template_loader_', 10, 1 );
add_action( 'mpc_get_products', 'mpc_get_products_', 10 );

add_action( 'mpc_before_table', 'mpc_before_table_', 10 );

add_action( 'mpc_table_header', 'mpc_table_header_', 10 );
add_action( 'mpc_table_title_columns', 'mpc_table_title_columns_', 10 );

add_action( 'mpc_table_body', 'mpc_table_body_', 10 );

add_action( 'mpc_table_column_image', 'mpc_table_column_image_', 10 );
add_action( 'mpc_table_column_product', 'mpc_table_column_product_', 10 );
add_action( 'mpc_table_column_price', 'mpc_table_column_price_', 10 );
add_action( 'mpc_table_column_variation', 'mpc_table_column_variation_', 10 );
add_action( 'mpc_table_column_quantity', 'mpc_table_column_quantity_', 10 );
add_action( 'mpc_table_column_buy', 'mpc_table_column_buy_', 10 );
add_action( 'mpc_table_footer', 'mpc_table_footer_', 10 );

add_action( 'render_mpc_pagination', 'render_mpc_pagination', 10 );

add_action( 'mpc_table_total', 'mpc_table_total_', 10 );
add_action( 'mpc_table_add_to_cart_button', 'mpc_table_add_to_cart_button_', 10 );

add_action( 'wp_ajax_mpc_ajax_table_loader', 'mpc_ajax_table_loader' );
add_action( 'wp_ajax_nopriv_mpc_ajax_table_loader', 'mpc_ajax_table_loader' );

add_action( 'wp_ajax_mpc_ajax_add_to_cart', 'mpc_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_mpc_ajax_add_to_cart', 'mpc_ajax_add_to_cart' );


add_action( 'mpc_table_buy_btton', 'mpc_table_buying_checkbox', 10 );
add_action( 'mpc_table_header_content', 'mpc_table_header_orderby', 10 );
add_action( 'mpc_table_header_content', 'mpc_table_header_check_all', 30 );
add_action( 'mpcp_custom_variation_html', 'mpc_variation_scope_simple_product', 10 );
