<?php
/**
 * This template can be overridden by copying it to yourtheme/templates/listing-list.php.
 */

$wmc_button_text = ( get_option( 'wmc_button_text' ) ? get_option( 'wmc_button_text' ) : 'Add to Cart' );
$wmc_button_color = ( get_option( 'wmc_button_color' ) ? get_option( 'wmc_button_color' ) : '#000000' );
$variation = mpc_check_if_variation_exists( $ids );
?>
<div class="woo-notices"></div>
<div class="woocommerce-page woocommerce">    

    <?php include( WMC_DIR . '/assets/css/style.php' ); ?>

    <form class="cart" method="post" enctype="multipart/form-data">

    <table class="mpc-wrap" cellspacing="0">

        <thead>

            <tr>

                <th class="product-image"><?php _e( 'Image', 'mpc' ); ?></th>

                <th class="product-name"><?php _e( 'Product', 'mpc' ); ?></th>

                <th class="product-price-top"><?php _e( 'Price', 'mpc' ); ?></th>

                <?php if( $variation ): ?>                    

                    <th class="product-variation"><?php _e( 'Variations', 'mpc' ); ?></th>

                <?php endif; ?>

                <th class="product-quantity"><?php _e( 'Quantity', 'mpc' ); ?></th>

                <th class="product-add-to-cart"><?php _e( 'Buy', 'mpc' ); ?></th>

            </tr>

        </thead>

        <tbody>

        <?php

        if( count( $ids ) > 0 ) : 

        	foreach( $ids as $id ) :

            $post_obj = get_post( $id );

            $_product = wc_get_product( $id );

            if( $_product->is_type( 'grouped' ) ) continue;

            if( isset( $post_obj->post_parent ) ){

                $pp = wc_get_product( $post_obj->post_parent );

                if( !empty( $pp ) && $pp->is_type( 'grouped' ) ) continue;

            }

            if ( isset( $_product ) && $_product->exists() ) { ?>

            <tr class="cart_item <?php echo esc_attr( sanitize_title( $_product->get_type() ) ); ?>">

                <td class="product-image">

                <?php

                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image() );

                if ( ! $_product->is_visible() ) {

                    echo $thumbnail;

                } else {

                    printf( '<a href="%s">%s</a>', esc_url( $_product->get_permalink() ), $thumbnail );

                } ?>

                </td>

                <td class="product-name">

                <?php if ( ! $_product->is_visible() ){

                    echo apply_filters( 'woocommerce_cart_item_name', esc_html( $_product->get_title() ) ) . '&nbsp;';

                } else{

                    echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s </a>', esc_url( $_product->get_permalink() ), esc_html( $_product->get_title() ) ) );

                } ?>

                </td>

                <td class="product-price">

                    <?php echo $_product->get_price_html(); ?>

                    <?php //echo '$'.apply_filters( 'woocommerce_cart_item_price', $_product->get_price() ); ?>

                </td>

                <?php if( $variation ): ?>

                    <td class="product-variation">

                    <?php

                    if( $_product->is_type( 'variable' ) ) {

                        $attributes = $_product->get_variation_attributes();

                        $loop = 0;

                        ?>

                        </div>

                        <?php
                        foreach ( $attributes as $name => $options ) :

                            echo '<div class="variation-group">';

                            echo '<label>' . esc_html( $name ) . '</label>';

                            $loop++; ?>

                            <select id="<?php echo esc_attr( sanitize_title( $name ) ); ?>" name="attribute_<?php echo sanitize_title( $name ).$id; ?>" data-attribute_name="attribute_<?php echo sanitize_title( $name ); ?>">

                                <option value=""><?php echo __( 'Choose an option', 'woocommerce' ) ?>&hellip;</option>

                                <?php

                                if ( is_array( $options ) ) {

                                    foreach ( $options as $option ) {

                                        echo '<option value="' . esc_attr( $option ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';

                                    }

                                } ?>

                            </select>

                        <?php
                        echo '</div>';
                        endforeach;

                    }else {
                        echo '<span>N/A</span>';
                    }

                    ?>

                    </td>

                <?php endif; ?>

                <td class="product-quantity">

                <?php if ( ! $_product->is_sold_individually() ){ ?>

                    <div class="quantity">

                        <label class="screen-reader-text" for="quantity_5d4224fa42a5f">Quantity</label>

                        <input type="number" class="input-text qty text" step="1" min="1" max="" name="quantity<?php echo $id; ?>" value="1" title="Qty" size="4" inputmode="numeric">

                    </div>

                <?php } ?>

                </td>

                <td class="product-select">

                    <input type="checkbox" name="product_ids[]" value="<?php echo $id; ?>">

                </td>

            </tr>

            <?php

	        }

        	endforeach;

        endif;

        ?>

        </tbody>

    </table>

    <div class="mpc-button">

        <div>

            <input type="hidden" name="add_more_to_cart" value="1">

            <input type="submit" class="single_add_to_cart_button button alt wc-forward" name="proceed" value="<?php esc_html_e( $wmc_button_text, 'woocommerce' ); ?>" />

        </div>

    </div>

    </form>

    <?php include( WMC_DIR . '/assets/js/scripts.php'); ?>

</div>