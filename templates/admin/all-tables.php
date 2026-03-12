<?php
/**
 * Admin all tables template.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

$mpc_opt_sc = new MPCAdminTable();
$mpc_opt_sc->change_shortcode();

echo '<div class="mpcdp_settings_section">';
$mpc_opt_sc->table_list();
echo '</div>';
