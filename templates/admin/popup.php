<?php
/**
 * Admin popup templte.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      1.0
 */

defined( 'ABSPATH' ) || exit;

global $mpc__;

?>
<div id="mpcpop" class="mpc-popup">
	<div class="image-wrap">
		<span class="mpcpop-close"><?php echo esc_html__( 'X', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
		<div class="mpc-focus">
			<span></span> <?php echo esc_html__( 'is a PRO feature.', 'multiple-products-to-cart-for-woocommerce' ); ?><br>
			<a href="<?php echo esc_url( $mpc__['prolink'] ); ?>" target="_blank"><?php echo esc_html__( 'Upgrade Now', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
		</div>
		<div class="mpcex-features">
			<h4><?php echo esc_html__( 'More PRO features:', 'multiple-products-to-cart-for-woocommerce' ); ?></h4>
			<ul>
				<li><?php echo esc_html__( '5+ new columns like SKU, stock, category etc', 'multiple-products-to-cart-for-woocommerce' ); ?></li>
				<li><?php echo esc_html__( 'Subscription product types are supported', 'multiple-products-to-cart-for-woocommerce' ); ?></li>
				<li><?php echo esc_html__( 'Single add to cart for each product', 'multiple-products-to-cart-for-woocommerce' ); ?></li>
				<li><?php echo esc_html__( 'Sort or hide any columns', 'multiple-products-to-cart-for-woocommerce' ); ?></li>
				<li><?php echo esc_html__( 'Custom product order', 'multiple-products-to-cart-for-woocommerce' ); ?></li>
			</ul>
			<a href="<?php echo esc_url( $mpc__['prolink'] ); ?>" target="_blank"><?php echo esc_html__( 'See all PRO features', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
