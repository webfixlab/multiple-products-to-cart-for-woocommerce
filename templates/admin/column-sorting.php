<?php
/**
 * Admin column sorting template.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      1.0
 */

defined( 'ABSPATH' ) || exit;

$cls = new MPCSettings();
$cls->save_sorted_columns();

$helper_cls = new MPCAdminHelper();
$value = get_option( 'wmc_sorted_columns' );

?>
<div class="mpcdp_settings_section">
	<div class="mpcdp_settings_section_title"><?php echo esc_html__( 'Column Sorting', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
	<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
		<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
			<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
				<span class="theme_customizer_icon dashicons dashicons-list-view"></span>
				<div class="mpcdp_settings_option_description">
					<div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new"><?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
					<div class="mpcdp_option_label"><?php echo esc_html__( 'Manage Product Table Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
					<div class="mpcdp_option_description">
						<?php echo esc_html__( 'Utilize the convenient drag-and-drop feature below to rearrange the order of the product table columns. You also have the ability to activate or deactivate any columns as needed.', 'multiple-products-to-cart-for-woocommerce' ); ?>
						<br>
						<br>
						<?php

						$move_icon = '<span class="dashicons dashicons-move"></span>';
						$sort_icon = '<span class="dashicons dashicons-sort"></span>';

						echo wp_kses_post(
							sprintf(
								// translators: %1$s: move dashicon html, %2$s: sort dashicon html.
								__( 'Also note, %1$s can move up, down, left, right, but %2$s only moves up-down.', 'multiple-products-to-cart-for-woocommerce' ),
								wp_kses_post( $move_icon ),
								wp_kses_post( $sort_icon )
							)
						);

						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="mpcdp_settings_toggle mpcdp_container" id="column-sorting">
		<div class="mpcdp_settings_option visible">
			<div class="mpcdp_row">
				<div class="mpcdp_settings_option_description col-md-6">
					<div class="mpcdp_option_label"><?php echo esc_html__( 'Active Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
					<div class="mpc-sortable mpca-sorted-options">
						<ul id="active-mpc-columns" class="connectedSortable ui-sortable">
							<?php $helper_cls->column_list( $value, true ); ?>
						</ul>
					</div>
				</div>
				<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
					<div class="mpcdp_option_label"><?php echo esc_html__( 'Inactive Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
					<div class="mpc-sortable mpca-sorted-options">
						<ul id="inactive-mpc-columns" class="connectedSortable ui-sortable">
							<?php $helper_cls->column_list( $value, false ); ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php

printf( '<input class="mpc-sorted-cols" type="hidden" name="wmc_sorted_columns" value="%s">', esc_html( $value ) );
wp_nonce_field( 'mpc_col_sort_save', 'mpc_col_sort' );
