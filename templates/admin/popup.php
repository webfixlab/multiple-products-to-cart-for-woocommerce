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
		<span class="mpcpop-close dashicons dashicons-dismiss"></span>
		<div class="mpc-pro-tag">PRO</div>
		<div class="mpc-focus">
			<?php
				echo wp_kses_post(
					sprintf(
						// translators: %1$s: pro fiture name.
						__( 'Please upgrade to get %1$s and other advanced features.', 'multiple-products-to-cart-for-woocommerce' ),
						wp_kses_post( '<span></span>' )
					)
				);
				?>
		</div>
		<div class="mpcex-features">
			<p><?php echo esc_html__( 'Unlock advanced features like custom columns for different tables, support for more product types, and an \'Add to Cart\' button with the PRO version. These tools are designed to streamline your workflow, enhance your experience, and boost your sales. We\'re committed to delivering the best solutions for you, 24/7.', 'multiple-products-to-cart-for-woocommerce' ); ?> <a href="<?php echo esc_url( $mpc__['prolink'] ); ?>" target="_blank"><?php echo esc_html__( 'Read more', 'multiple-products-to-cart-for-woocommerce' ); ?></a></p>
		</div>
		<a class="mpc-get-pro" href="<?php echo esc_url( $mpc__['prolink'] ); ?>" target="_blank"><?php echo esc_html__( 'Upgrade Now', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
	</div>
</div>
