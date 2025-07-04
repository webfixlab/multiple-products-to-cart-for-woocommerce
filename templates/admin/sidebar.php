<?php
/**
 * Admin sidebar template.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      1.0
 */

defined( 'ABSPATH' ) || exit;

global $mpc__;

?>
<div class="sidebar_top">
	<h2><?php echo __( 'Boost your tables to the next level', 'multiple-products-to-cart-for-woocommerce' ); ?></h2>
	<div class="tagline_side">
		<?php
			echo wp_kses_post(
				sprintf(
					// translators: %1$s: line brake, %2$s: line brake.
					__( 'Popular PRO features: Product category dropdown, AJAX-powered search, 4 additional columns, and subscription product support. Upgrade to the PRO version to unlock the full power of the plugin!', 'multiple-products-to-cart-for-woocommerce' )
				)
			);
			?>
	</div>
	<div><a href="<?php echo esc_url( $mpc__['prolink'] ); ?>" target="_blank"><?php echo __( 'Get PRO license now', 'multiple-products-to-cart-for-woocommerce' ); ?></a></div>
</div>
<div class="site-intro">
	<h2><?php echo __( 'Missing any features? No worries!', 'multiple-products-to-cart-for-woocommerce' ); ?></h2>
	<a href="https://webfixlab.com/wordpress-offer/" target="_blank"><?php echo __( 'Customize for $99 only', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
</div>
<div class="mpca_sidebar">
	<h2><?php echo __( 'Dedicated Support Team', 'multiple-products-to-cart-for-woocommerce' ); ?></h2>
	<div class="tagline_side">
		<?php echo __( 'Our support is what makes us No.1. We are available round the clock for any support.', 'multiple-products-to-cart-for-woocommerce' ); ?>
	</div>
	<div>
		<a href="<?php echo esc_url( $mpc__['plugin']['request_quote'] ); ?>" target="_blank">
			<?php echo __( 'Send Request', 'multiple-products-to-cart-for-woocommerce' ); ?>
		</a>
	</div>
</div>
