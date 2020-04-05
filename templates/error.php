<div>
	<?php if( !is_plugin_active( 'woocommerce/woocommerce.php' ) ):
		print_f(
			'<h3 style="color: #fff;background: #585353;padding: 15px 10px;border-radius: 5px;">%s <a href="%s" style="color: #96588a;font-weight: 400;">%s</a> %s</h3>',
			 __( "Please install and activate", "mpc" ),
			 esc_url( "https://wordpress.org/plugins/woocommerce/" ),
			 __( "WooCommerce", "mpc" ),
			 __( "plugin first.", "mpc" )
		);
		endif; ?>
</div>
