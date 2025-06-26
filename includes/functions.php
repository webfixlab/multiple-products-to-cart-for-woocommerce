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
	global $mpctable__;

	// no image class.
	$cls = in_array( 'wmc_ct_image', $mpctable__['columns_list'], true ) ? '' : 'mpc-no-image';

	?>
	<table class="mpc-wrap <?php echo esc_attr( $cls ); ?>" cellspacing="0">
		<?php do_action( 'mpc_table_title_columns' ); ?>
		<tbody>
			<?php do_action( 'mpc_table_body' ); ?>
		</tbody>
	</table>
	<?php
}
