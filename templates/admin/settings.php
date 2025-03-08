<?php
/**
 * Admin settings main template.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

global $mpc__;
global $MPCSettings;

$MPCSettings = new MPCSettings();

$tab = $MPCSettings->get_tab();

?>
<form method="post" action="" id="mpcdp_settings_form" enctype="multipart/form-data">
	<div id="mpcdp_settings" class="mpcdp_container">
		<div id="mpcdp_settings_page_header">
			<div id="mpcdp_logo"><?php echo esc_html__( 'Multiple Products to Cart', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
			<div id="mpcdp_customizer_wrapper"></div>
			<div id="mpcdp_toolbar_icons">
				<a class="mpcdp-tippy" target="_blank" href="<?php echo esc_url( $mpc__['plugin']['support'] ); ?>" data-tooltip="<?php echo esc_html__( 'Support', 'multiple-products-to-cart-for-woocommerce' ); ?>">
				<span class="tab_icon dashicons dashicons-email"></span>
				</a>
			</div>
		</div>
		<div class="mpcdp_row">
			<div class="col-md-3" id="left-side">
				<div class="mpcdp_settings_sidebar" data-sticky-container="" style="position: relative;">
					<div class="mpcdp_sidebar_tabs">
						<div class="inner-wrapper-sticky">
							<?php $MPCSettings->menu(); ?>
							<?php $MPCSettings->save_btn(); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6" id="middle-content">
				<div class="mpcdp_settings_content">
					<?php
						echo sprintf(
							'<div id="%s" class="hidden mpcdp_settings_tab active" data-tab="%s" style="display: block;">',
							esc_attr( $tab ),
							esc_attr( $tab )
						);

						$MPCSettings->settings();
					?>
					</div>
				</div>
			</div>
			<div id="right-side">
				<div class="mpcdp_settings_promo">
					<div id="wfl-promo">
						<?php $MPCSettings->sidebar(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php wp_nonce_field( 'mpc_admin_settings_save', 'mpc_admin_settings' ); ?>
</form>
