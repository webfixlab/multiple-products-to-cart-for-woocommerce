<?php
/**
 * Admin shortcode generator templte.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      1.0
 */

defined( 'ABSPATH' ) || exit;

$mpc_opt_sc = new MPCAdminTable();

?>
<div class="mpcdp_settings_section">
	<?php $mpc_opt_sc->change_shortcode(); ?>
	<?php $mpc_opt_sc->edit_shortcode(); ?>
</div>
<div class="mpca-content new-table">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ); ?>" class="mpcasc-reset">
		<span class="button-secondary">
			<?php echo esc_html__( 'Reset', 'multiple-products-to-cart-for-woocommerce' ); ?>
		</span>
	</a>
</div>

<?php
wp_nonce_field( 'mpc_opt_sc_save', 'mpc_opt_sc' );
