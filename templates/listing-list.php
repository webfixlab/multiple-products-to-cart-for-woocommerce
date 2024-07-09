<?php
/**
 * Shortcode table template.
 * 
 * This template can be overridden by copying it to yourtheme/templates/listing-list.php.
 * Or, with a filter hook.
 * 
 * SOMETIMES, we will update this file and you should update your theme file(s) as well. This prevents any potential issues.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'mpc_after_wrap' );

?>
<div class="woocommerce-page woocommerce mpc-container">
	<?php do_action( 'mpc_before_table' ); ?>
	<form class="mpc-cart" method="post" enctype="multipart/form-data" data-current_page="1">
		<div class="mpc-table-header">
			<?php do_action( 'mpc_table_header' ); ?>
		</div>    
		<?php mpc_display_table(); ?>
		<input type="hidden" name="mpc_cart_data" value="">
		<div class="mpc-table-footer">
			<?php do_action( 'mpc_table_footer' ); ?>
		</div>
		<?php wp_nonce_field( 'cart_nonce_ref', 'cart_nonce' ); ?>
	</form>
	<?php do_action( 'mpc_after_table' ); ?>
</div>
<?php

do_action( 'mpc_after_wrap' );
