<?php
/**
 * Frontend table functions.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Display product table
 */
function mpc_display_table() {
	?>
	<table class="mpc-wrap" cellspacing="0">
		<?php do_action( hook_name: 'mpc_table_title_columns' ); ?>
		<tbody>
			<?php do_action( 'mpc_table_body' ); ?>
		</tbody>
	</table>
	<?php
}
