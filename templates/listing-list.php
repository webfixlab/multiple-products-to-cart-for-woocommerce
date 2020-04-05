<?php
/**
 * This template can be overridden by copying it to yourtheme/templates/listing-list.php.
 */
$wmc_button_text = ( get_option( 'wmc_button_text' ) ? get_option( 'wmc_button_text' ) : __( 'Add to Cart', 'mpc' ) );
$wmc_button_color = ( get_option( 'wmc_button_color' ) ? get_option( 'wmc_button_color' ) : '#000000' );
$wmc_thead_back_color = ( get_option( 'wmc_thead_back_color' ) ? get_option( 'wmc_thead_back_color' ) : '#000000' );
$variation = mpc_check_if_variation_exists( $ids );
?>
<div class="woo-notices"></div>
<div class="woocommerce-page woocommerce">
    <?php include( WMC_DIR . '/assets/css/style.php' ); ?>
    <style type="text/css">
    <?php if( $wmc_thead_back_color ) echo '.mpc-wrap thead tr th{ background: ' . $wmc_thead_back_color . '}'; ?>
    </style>
    <form class="cart" method="post" enctype="multipart/form-data">
    <table class="mpc-wrap" cellspacing="0">
        <thead>
            <tr>
                <th class="product-image"><?php echo __( 'Image', 'mpc' ); ?></th>
                <th class="product-name"><?php echo __( 'Product', 'mpc' ); ?></th>
                <th class="product-price-top"><?php echo __( 'Price', 'mpc' ); ?></th>
                <?php if( $variation ): ?>
                <th class="product-variation"><?php echo __( 'Variations', 'mpc' ); ?></th>
                <?php endif; ?>
                <th class="product-quantity"><?php echo __( 'Quantity', 'mpc' ); ?></th>
                <th class="product-add-to-cart"><?php echo __( 'Buy', 'mpc' ); ?></th>
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
                    $image_id  = $_product->get_image_id();
                    $full = wp_get_attachment_image_url( $image_id, 'full' );
                    $thumbnail = wp_get_attachment_image_url( $image_id, 'thumbnail' );
                    ?>
                    <?php
                    // $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image() );
                    if ( ! $_product->is_visible() ) {
                        echo $thumbnail;
                    } else {
                        printf( '<img width="300" height="254" src="%s" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" data-fullimage="%s">', $thumbnail, $full );
                        // printf( '<a href="%s">%s</a>', esc_url( $_product->get_permalink() ), $thumbnail );
                    } ?>
                </td>
                <td class="product-name">
                    <?php if ( ! $_product->is_visible() ){
                        echo __( apply_filters( 'woocommerce_cart_item_name', esc_html( $_product->get_title() ) ) . '&nbsp;', "mpc" );
                    } else{
                        echo __( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s </a>', esc_url( $_product->get_permalink() ), esc_html( $_product->get_title() ) ) ), "mpc" );
                    } ?>
                </td>
                <td class="product-price">
                    <?php echo __( $_product->get_price_html(), "mpc" ); ?>
                    <?php //echo '$'.apply_filters( 'woocommerce_cart_item_price', $_product->get_price() ); ?>
                </td>
                    <?php if( $variation ): ?>
                <td class="product-variation">
                        <?php
                        if( $_product->is_type( 'variable' ) ) {
                            $attributes = $_product->get_variation_attributes();
                            $loop = 0; ?>
                            </div>
                            <?php
                            foreach ( $attributes as $name => $options ) :
                                echo '<div class="variation-group">';
                                echo '<label>' . __( esc_html( $name ), "mpc" ) . '</label>';
                                $loop++; ?>
                                <select id="<?php echo esc_attr( sanitize_title( $name ) ); ?>" name="attribute_<?php echo sanitize_title( $name ).$id; ?>" data-attribute_name="attribute_<?php echo sanitize_title( $name ); ?>">
                                    <option value=""><?php echo __( 'Choose an option', 'mpc' ) ?>&hellip;</option>
                                    <?php
                                    if ( is_array( $options ) ) {
                                        foreach ( $options as $option ) {
                                            echo '<option value="' . esc_attr( $option ) . '">' . __( esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ), "mpc" ) . '</option>';
                                        }
                                    } ?>
                                </select>
                            <?php
                            echo '</div>';
                            endforeach;
                        }else {
                            echo '<span>' . __( "N/A", "mpc" ) . '</span>';
                        } ?>
                </td>
                    <?php endif; ?>
                <td class="product-quantity">
                    <?php if ( ! $_product->is_sold_individually() ){ ?>
                    <div class="quantity">
                        <label class="screen-reader-text" for="quantity_5d4224fa42a5f"><?php echo __( "Quantity", "mpc" ); ?></label>
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
            <input type="submit" class="single_add_to_cart_button button alt wc-forward" name="proceed" value="<?php echo __( $wmc_button_text, 'mpc' ); ?>" />
        </div>
    </div>
    </form>
    <div id="mpcpop"></div>
    <?php include( WMC_DIR . '/assets/js/scripts.php'); ?>
</div>
