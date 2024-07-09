<?php
/**
 * Admin shortcode generator templte.
 *
 * @package WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since 1.0
 */

global $mpc__;

// I don't know why I added this.
if ( ! isset( $mpc__['fields']['new_table'] ) || empty( $mpc__['fields']['new_table'] ) ) {
	return;
}

echo '<div class="mpcdp_settings_section">';
$mpc_opt_sc = new MPCShortcode();
$mpc_opt_sc->change_shortcode();
$mpc_opt_sc->edit_shortcode();
echo '</div>';

?>
<div class="mpca-content new-table">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ); ?>" class="mpcasc-reset"><span class="button-secondary">Reset</span></a>
</div>

<?php wp_nonce_field( 'mpc_opt_sc_save', 'mpc_opt_sc' ); ?>
