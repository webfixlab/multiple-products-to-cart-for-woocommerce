<?php
/**
 * Admin column sorting template.
 *
 * @package WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since 1.0
 */

$cls = new MPCSettings();
$cls->save_sorted_columns();
?>
<div class="mpcdp_settings_section">
	<div class="mpcdp_settings_section_title">Column Sorting</div>
	<div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
		<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
			<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
				<span class="theme_customizer_icon dashicons dashicons-list-view"></span>
				<div class="mpcdp_settings_option_description">
					<div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">PRO</div>
					<div class="mpcdp_option_label">Manage Product Table Columns</div>
					<div class="mpcdp_option_description">
						Utilize the convenient drag-and-drop feature below to rearrange the order of the product table columns. You also have the ability to activate or deactivate any columns as needed.
						<br>
						<br>
						Also note, <span class="dashicons dashicons-move"></span> can move up, down, left, right, but <span class="dashicons dashicons-sort"></span> only moves up-down.
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="mpcdp_settings_toggle mpcdp_container" id="column-sorting">
		<div class="mpcdp_settings_option visible">
			<div class="mpcdp_row">
				<div class="mpcdp_settings_option_description col-md-6">
					<?php $cls->table_columns( 'Active Columns', true ); ?>
				</div>
				<div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
					<?php $cls->table_columns( 'Inactive Columns' ); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo sprintf( '<input class="mpc-sorted-cols" type="hidden" name="wmc_sorted_columns" value="%s">', esc_html( $cls->sorted_columns( false ) ) ); ?>
