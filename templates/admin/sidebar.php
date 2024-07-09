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
<div class="site-intro">
	<h2><?php echo esc_html__( 'Missing any features? No worries!', 'multiple-products-to-cart-for-woocommerce' ); ?></h2>
	<a href="https://webfixlab.com/wordpress-offer/" target="_blank"><?php echo esc_html__( 'Customize for $99 only', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
</div>
<div class="mpca_sidebar">
	<div class="sidebar_top">
		<h1><?php echo esc_html__( 'Upgrade to PRO Now', 'multiple-products-to-cart-for-woocommerce' ); ?></h1>
		<div class="tagline_side">
			<?php

				echo wp_kses_post(
					sprintf(
						// translators: %1$s: line brake, %2$s: line brake.
						__( 'Supercharge Your WooCommerce Stores %1$swith our light, fast and feature-rich %2$sPRO version.', 'multiple-products-to-cart-for-woocommerce' ),
						wp_kses_post( '<br>' ),
						wp_kses_post( '<br>' )
					)
				);

			?>
		</div>
		<div><a href="<?php echo esc_url( $mpc__['prolink'] ); ?>" target="_blank"><?php echo esc_html__( 'Upgrade PRO', 'multiple-products-to-cart-for-woocommerce' ); ?></a></div>
	</div>
	<div class="sidebar_bottom">
		<ul>
			<?php

			$texts = array(
				array(
					'bold' => __( '5+ new columns SKU, stock, category', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'etc for the product table.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Search box:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'AJAX powered live search box.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Category filter:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Super fast category filter option.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Subscription product:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Supscription product types are supported.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Single add to cart:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Show add to cart button for each product.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Column hide & sort:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Show or hide or sort any columns as your business needs.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Custom product order:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Products can be custom sorted.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Redirect to custom page:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Choose any page/URL to redirect after add to cart button clicked.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Hide table header:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Show or hide your product table header.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Sort by SKU:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Customer can sort the products by SKU.', 'multiple-products-to-cart-for-woocommerce' ),
				),
				array(
					'bold' => __( 'Rocket speed support:', 'multiple-products-to-cart-for-woocommerce' ),
					'text' => __( 'Most of our customer\'s problem solved within 24 hours of their first contact.', 'multiple-products-to-cart-for-woocommerce' ),
				),
			);

			foreach ( $texts as $list ) {
				echo wp_kses_post(
					sprintf(
						'<li><span class="dashicons dashicons-yes-alt"></span><strong>%s</strong> %s</li>',
						esc_html( $list['bold'] ),
						esc_html( $list['text'] )
					)
				);
			}

			?>
		</ul>
	</div>
	<div class="support">
		<h3><?php echo esc_html__( 'Dedicated Support Team', 'multiple-products-to-cart-for-woocommerce' ); ?></h3>
		<p><?php echo esc_html__( 'Our support is what makes us No.1. We are available round the clock for any support.', 'multiple-products-to-cart-for-woocommerce' ); ?></p>
		<p>
			<a href="<?php echo esc_url( $mpc__['plugin']['request_quote'] ); ?>" target="_blank">
				<strong>
					<?php echo esc_html__( 'Send Request', 'multiple-products-to-cart-for-woocommerce' ); ?>
				</strong>
			</a>
		</p>
	</div>
</div>
