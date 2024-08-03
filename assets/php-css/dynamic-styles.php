<?php
/**
 * Dynamic CSS content.
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      1.0
 */

defined( 'ABSPATH' ) || exit;

global $mpctable__;

// add to cart button color and background color.
$btn_color      = ( get_option( 'mpc_button_text_color' ) ? get_option( 'mpc_button_text_color' ) : '#353535' );
$btn_background = ( get_option( 'wmc_button_color' ) ? get_option( 'wmc_button_color' ) : '#d3d3d3' );

// header and pagination color and background color.
$hnp_color      = ( get_option( 'mpc_head_text_color' ) ? get_option( 'mpc_head_text_color' ) : '#ffffff' );
$hnp_background = ( get_option( 'wmc_thead_back_color' ) ? get_option( 'wmc_thead_back_color' ) : '#535353' );

// product title color, font size, whether to bold it and also underline it.
$title_color     = get_option( 'mpc_protitle_color' );
$title_font_size = get_option( 'mpc_protitle_font_size' );
$bold_title      = get_option( 'mpc_protitle_bold_font' );
$title_underline = get_option( 'mpc_protitle_underline' );

// product image size.
$image_size = get_option( 'mpc_image_size' );
$image_size = ! empty( $image_size ) ? $image_size : '72';

?>
<style type="text/css">
	.mpc-wrap thead tr th, .mpc-pagenumbers span.current{
		<?php printf( 'background: %s;', esc_html( $hnp_background ) ); ?>
		<?php printf( 'color: %s;', esc_html( $hnp_color ) ); ?>
	}
	.mpc-button input.mpc-add-to-cart.wc-forward, button.mpce-single-add{
		<?php printf( 'background: %s;', esc_html( $btn_background ) ); ?>
		<?php printf( 'color: %s;', esc_html( $btn_color ) ); ?>
	}
	td.mpc-product-image, .mpcp-gallery, table.mpc-wrap img{
		width: <?php echo esc_attr( $image_size ); ?>px;
	}
	<?php if ( isset( $title_color ) && ! empty( $title_color ) ) : ?>
	.mpc-product-title a{
		color: <?php echo esc_html( $title_color ); ?>;
	}
	<?php
	endif;
	
	if ( 'on' === get_option( 'wmca_inline_dropdown' ) ) : ?>
		.mpc-wrap .variation-group > select{
			max-width: 100px;
		}
		.variation-group select{
			width: 100px;
		}
	<?php endif; ?>
	.mpc-container .mpc-product-title a{
		<?php
			if ( ! empty( $title_font_size ) ) {
				printf( 'font-size: %spx;', esc_attr( $title_font_size ) );
			}

			if ( ! empty( $bold_title ) && 'on' === $bold_title ) {
				echo esc_html( 'font-weight: bold;' );
			}

			if ( ! empty( $title_underline ) && 'on' === $title_underline ) {
				echo esc_html( 'text-decoration: underline;' );
			} else {
				echo esc_html( 'text-decoration: none;' );
			}
		?>
	}
	<?php do_action( 'mpc_dynamic_css' ); ?>
	@media screen and (max-width: 767px) {
		table.mpc-wrap tbody tr{
			min-height: <?php echo $image_size + ceil( ( $image_size * 20 ) / 100 ); ?>px;
		}
		table.mpc-wrap tbody tr:has(.gallery-item){
			min-height: <?php echo $image_size + ceil( ( $image_size * 45 ) / 100 ) + 45; ?>px;
		}
		table.mpc-wrap tbody tr td{
			padding-left: <?php echo $image_size + 13; ?>px;
		}
	}
</style>
