/**
 * Frontend table cart events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCFrontAddToCart{
		constructor(){
			$( document ).ready( () => this.initEvents() );
		}
		initEvents(){
            window.mpcHooks.addAction( 'mpc_add_to_cart', ( cartData, wrap ) => this.addToCart( cartData, wrap ) );
            window.mpcHooks.addAction( 'mpc_update_wc_mini_cart', ( response ) => this.updateWCMiniCart( response ) );

            $( 'body' ).on( 'click', '.mpc-cart-messege', () => $( 'body' ).find('.mpc-cart-messege').remove() );

            $( 'form.mpc-cart .mpc-add-to-cart' ).on( 'click', ( e ) => this.triggerAddToCart( e ) );
            $( '.mpc-fixed-cart' ).on( 'click', ( e ) => this.triggerAddToCart( e ) );
        }
        addToCart( cart, wrap ){
            if( $.isEmptyObject( cart ) ){
                this.validationNotice( wrap, cart );
                return;
            }

            // confirmation notice to inform users what is happening in the cart.
            const msg = this.cartConfirmation( wrap, cart );
            if( msg.length > 0 && ! confirm( msg ) ){
                return;
            }

            if( 'ajax' !== mpc_frontend.redirect_url ){
                wrap.find( 'input[name="mpc_cart_data"]' ).val( JSON.stringify( cart ) );
                wrap.find( 'form.mpc-cart' ).submit();
                return;
            }

            this.sendAddToCartRequest( cart, wrap );
        }
        validationNotice( wrap, cartData ) {
            wrap.find( 'tbody tr.cart_item' ).each( ( _, el ) => {
                this.markQtyField( $( el ) );
                this.markCheckBox( $( el ) );
                this.markVariationAtts( $( el ) );
            } );
            
            this.addNotice( `<p class="woocommerce-error">${ mpc_frontend.cart_error }</p>`, wrap );

            $( 'html, body' ).animate( { scrollTop: wrap.offset().top - 60 }, 'slow' );
            setTimeout( () => wrap.find( '.woo-notices' ).remove(), 5000 );
        }
        addNotice( msg, wrap ){
            wrap.find( '.mpc-notice' ).remove();
            if( 'undefined' === typeof msg || 0 === msg.length ){
                return;
            }
            wrap.prepend( `<div class="woo-notices mpc-notice">${ msg }</div>` );
        }
        markQtyField( row ){
            const qtyField = row.find( '.mpc-product-quantity input[type="number"]' );
            if( ! qtyField || 0 === qtyField.length ){
                return;
            }

            const qty = qtyField.val().length > 0 ? parseInt( qtyField.val() ) : 0;
            if( qty > 1 ){
                qtyField.removeClass( 'mpc-faulty' );
                return;
            }
            
            // add marker.
            qtyField.addClass( 'mpc-faulty' );
        }
        markCheckBox( row ){
            const checkBox = row.find( 'input[type="checkbox"]' );
            if( checkBox.is( ':checked' ) ){
                checkBox.removeClass( 'mpc-faulty' );
                return;
            }
            checkBox.addClass( 'mpc-faulty' );
        }
        markVariationAtts( row ){
            const total = row.find( 'select.mpc-var-att' ).length;
            if( 0 === total ){
                return;
            }
            
            row.find( 'select.mpc-var-att' ).each( ( _, att ) => {
                const optVal = $( att ).find( 'option:selected' ).val();
                if( 0 === optVal.length ){
                    $( att ).addClass( 'mpc-faulty' );
                }else{
                    $( att ).removeClass( 'mpc-faulty' );
                }
            } );
        }
        cartConfirmation( wrap, cart ){
            const fields = { qty: false, buy: false }; // if quantity and buy checkbox fields found on the table.
            wrap.find( 'table.mpc-wrap tr.cart_item' ).each( ( _, row ) => {
                const qtyField = $( row ).find( '.mpc-product-quantity input[type="number"]' );
                const checkBox = $( row ).find( '.mpc-product-buy input[type="checkbox"]' );
                
                if( 'outofstock' !== $( row ).attr( 'stock_status' ) ){
                    fields.qty = qtyField && qtyField.length > 0;
                    fields.buy = checkBox && checkBox.length > 0;
                }
            } );

            const total = Object.keys( cart ).length;
            const msg   = ! fields.qty || ! fields.buy ? (
                1 === total ? mpc_frontend.cart_confirm.single : (
                    ! fields.qty ? `${total}x1 ${mpc_frontend.cart_confirm.plural}` : (
                        ! fields.buy ? `${total} ${mpc_frontend.cart_confirm.plural}` : ''
                    )
                )
            ) : '';

            return msg.length > 0 ? mpc_frontend.cart_confirm.msg.replace( '%s', msg ) : '';
        }
        sendAddToCartRequest( cart, wrap ){
            window.mpcHooks.doAction( 'mpc_spinner', 'load', wrap );
            // remove loading animation.
            $.ajax({
                method: "POST",
                url: mpc_frontend.ajaxurl,
                data: {
                    action:         'mpc_ajax_add_to_cart',
                    mpca_cart_data: cart,
                    cart_nonce:     mpc_frontend.cart_nonce
                },
                success: ( response ) => this.responseHandler( response, wrap ),
                error: ( errorThrown ) => console.log( errorThrown )
            });
        }
        responseHandler( response, wrap ){
            $( document.body ).trigger( 'updated_cart_totals' );
            window.mpcHooks.doAction( 'mpc_spinner', 'close', wrap );
            window.mpcHooks.doAction( 'mpc_update_wc_mini_cart', response );

            this.handleCartNotice( response, wrap );
        }
        updateWCMiniCart( response ){
            if ( ! response.fragments ) {
                return;
            }

            $.each( response.fragments, ( key, value ) => $( key ).replaceWith( value ) );

            const blockThemeEvent = new CustomEvent( 'wc-blocks_added_to_cart', {
                bubbles:    true,
                cancelable: true,
                detail:{
                    preserveCartData: false,
                    response: response
                }
            } );
            document.body.dispatchEvent( blockThemeEvent );
        }
        handleCartNotice( response, wrap ){
            $( 'body' ).find( '.mpc-cart-messege' ).remove();

            const msg    = response.error_message ? response.error_message : response.cart_message;
            const notice = response.error_message ? `<ul class="woocommerce-error" role="alert"><li>${msg}</li></ul>` : `<div class="woocommerce-message" role="alert">${ msg }</div>`;

            wrap.prepend( `<div class="woocommerce-notices-wrapper mpc-cart-messege">${notice}</div>` );
            $( 'body' ).append( `<div class="mpc-popup mpc-popify mpc-cart-messege"><div class="woocommerce"><div class="woocommerce-message" role="alert">${ msg }</div></div></div>` );
            
            $( 'html, body' ).animate( { scrollTop: wrap.offset().top - 60 }, 'slow' );
            this.removeCartNotices( wrap );
        }
        removeCartNotices( wrap ){
            setTimeout( () => $( 'body' ).find( '.mpc-popify' ).remove(), 2000 );
            setTimeout( () => wrap.find( '.mpc-cart-messege' ).remove(), 7000 );
        }

        triggerAddToCart( e ){
            e.preventDefault();
            const wrap = $( e.currentTarget ).closest( '.mpc-container' );

            const tableId = parseInt( wrap.find( 'table.mpc-wrap' ).attr( 'data-table_id' ) );
            const data    = window.mpcTables.getTableData( tableId );
            if( $.isEmptyObject( data ) ){
                this.addNotice( `<p class="woocommerce-error">${ mpc_frontend.blank_submit }</p>`, wrap );
                return;
            }

            let cart = {};
            Object.keys( data ).forEach( i => {
                const item = data[ i ];
                let ifAdd  = true === item.checked && item.qty > 0 && -1 !== [ 'simple', 'variable' ].indexOf( data.type );
                ifAdd      = 'variable' === item.type && $.isEmptyObject( item.variation ) ? false : ifAdd;

                if( ifAdd ){
                    cart[ item.product_id ] = {
                        type: item.type,
                        qty:  item.qty,
                    };
                    if( 'variable' === item.type ){
                        cart[ item.product_id ].variation_id = item.variation.variation_id;
                        cart[ item.product_id ].attributes   = item.variation.attributes__;
                    }
                }
            });

            cart = window.mpcHooks.applyFilters( 'mpc_cart_items_data', cart, data );
            window.mpcHooks.doAction( 'mpc_add_to_cart', cart, wrap );
        }
	}
	new MPCFrontAddToCart();
} )( jQuery, window, document );
