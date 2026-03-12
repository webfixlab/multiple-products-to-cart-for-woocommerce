<?php
/**
 * Export admin template.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.2.0
 */

defined( 'ABSPATH' ) || exit;

global $mpc__;
$pro_cls = false === $mpc__['has_pro'] ? 'mpcex-disabled' : '';
?>
<div class="mpcdp_settings_section">
	<div class="mpcdp_settings_section_title"><?php echo esc_html__( 'Export Settings', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
	<div class="mpcdp_settings_toggle mpcdp_container" id="export-success">
		<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
			<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field mpc-export-notice">
				<span class="theme_customizer_icon dashicons dashicons-saved"></span>
				<div class="mpcdp_settings_option_description">
					<div class="mpcdp_option_label"><?php echo esc_html__( 'Please wait while we are getting your file ready for download...', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
				</div>
			</div>
		</div>
	</div>
	<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
		<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
			<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
				<span class="theme_customizer_icon dashicons dashicons-download"></span>
				<div class="mpcdp_settings_option_description">
					<?php if ( false === $mpc__['has_pro'] ) : ?>
						<div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">
							<?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
						</div>
					<?php endif; ?>
					<div class="mpcdp_option_label">Export MPC Tables and Settings</div>
					<div class="mpcdp_option_description">
						<br>
						Click on `Export` to export tables and settings.
						<br><br>
						You will find either a `mpc_export.json` or an enumarated `mpc_export(1).json` file in your `Downloads` folder. You can use this file to import it later or to other websites.
						<br><br>
						<?php if ( false === $mpc__['has_pro'] ) : ?>
							The export feature is only available for PRO plugin.
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="mpcdp_settings_toggle mpcdp_container">
		<div class="mpcdp_settings_option visible">
			<div class="mpcdp_row">
				<div class="mpcdp_settings_option_description col-md-6">
					<div class="mpcdp_option_label">Export</div>
					<div class="mpcdp_option_description">Export tables and settings</div>
				</div>
				<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
					<div class="mpcdp_settings_submit mpc-file">
						<div class="submit">
							<button id="mpc-export" class="mpcdp_submit_button <?php echo esc_attr( $pro_cls ); ?>" title="Export">
								<div class="save-text">Export settings</div>
								<div class="save-text save-text-mobile">Export</div>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
wp_nonce_field( 'mpc_export_nonce', 'mpc_export' );
do_action( 'mpc_pro_export' );
