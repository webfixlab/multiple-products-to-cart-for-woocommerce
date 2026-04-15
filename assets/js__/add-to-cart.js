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
            $( 'body' ).on( 'click', '.mpc-cart .mpc-add-to-cart', ( e ) => this.addCartHook( e, 'table' ) );
            $( '.mpc-floating-total .float-label' ).on( 'click', ( e ) => this.addCartHook( e, 'table' ) );
            $( document ).on( 'click', '.mpc-fixed-cart', ( e ) => this.addCartHook( e, 'table' ) );

            $( 'body' ).on( 'click', '.mpc-cart-messege', () => $( 'body' ).find('.mpc-cart-messege').remove() );

            window.mpcHooks.addAction( 'mpc_add_to_cart', ( cartData, wrap ) => this.handleAddToCart( cartData, wrap ) );
        }
        addCartHook( e ){
            e.preventDefault();
            const wrap = $( e.currentTarget ).closest( '.mpc-container' );

            const tableId  = parseInt( wrap.find( 'table.mpc-wrap' ).attr( 'data-table_id' ) );
            const cartData = window.mpcTables.getCartData( {
                tableId: tableId,
            } );

            window.mpcHooks.doAction( 'mpc_add_to_cart', cartData, wrap );
        }
        handleAddToCart( cartData, wrap ){
            if( 0 === Object.keys( cartData ).length ){
                this.validationNotice( wrap, `<p class="woocommerce-error">${ mpc_frontend.blank_submit }</p>` );
                return;
            }

            if( 'ajax' !== mpc_frontend.redirect_url ){
                wrap.find( 'input[name="mpc_cart_data"]' ).val( JSON.stringify( cartData ) );
                return;
            }
            this.sendAddToCartRequest( cartData, wrap );
        }
        sendAddToCartRequest( cartData, wrap ){
            window.mpcHooks.doAction( 'mpc_spinner', 'load', wrap );
            // remove loading animation.
            $.ajax({
                method: "POST",
                url: mpc_frontend.ajaxurl,
                data: {
                    action:         'mpc_ajax_add_to_cart',
                    mpca_cart_data: cartData,
                    cart_nonce:     mpc_frontend.cart_nonce
                },
                success: ( response ) => this.responseHandler( response, wrap ),
                error: ( errorThrown ) => console.log( errorThrown )
            });
        }
        responseHandler( response, wrap ){
            window.mpcHooks.addAction( 'updated_cart_totals' );
            window.mpcHooks.doAction( 'mpc_spinner', 'close', wrap );

            if ( response.fragments ) {
                $.each( response.fragments, ( key, value ) => $( key ).replaceWith( value ) );
            }

            this.handleCartNotice( response, wrap );
            this.updateMiniCart();
        }
        validationNotice( wrap, msg ) {
            const noticeWrap = wrap.find( '.woo-notices' );
            if( noticeWrap && noticeWrap.length > 0 ){
                noticeWrap.html( msg );
            }else{
                wrap.find( 'table.mpc-wrap' ).prepend( `<div class="woo-notices mpc-notice">${ msg }</div>` );
            }

            $( 'html, body' ).animate( { scrollTop: table.offset().top - 60 }, 'slow' );
            setTimeout( () => wrap.find( '.woo-notices' ).remove(), 5000 );
        }
        handleCartNotice( response, wrap ){
            $( 'body' ).find( '.mpc-cart-messege' ).remove();

            const msg    = response.error_message ? response.error_message : response.cart_message;
            const notice = response.error_message ? `<ul class="woocommerce-error" role="alert"><li>${msg}</li></ul>` : `<div class="woocommerce-message" role="alert">${ msg }</div>`;

            wrap.find( 'table.mpc-wrap' ).prepend( `<div class="woocommerce-notices-wrapper mpc-cart-messege">${notice}</div>` );
            $( 'body' ).append( `<div class="mpc-popup mpc-popify mpc-cart-messege"><div class="woocommerce"><div class="woocommerce-message" role="alert">${ msg }</div></div></div>` );
            
            $( 'html, body' ).animate( { scrollTop: table.offset().top - 60 }, 'slow' );
            this.removeCartNotices( wrap );
        }
        removeCartNotices( wrap ){
            setTimeout( () => $( 'body' ).find( '.mpc-popify' ).remove(), 2000 );
            setTimeout( () => wrap.find( '.mpc-cart-messege' ).remove(), 7000 );
        }
        // Example: Dispatching an 'added_to_cart' event to update the mini-cart.
        updateMiniCart(){
            const event = new CustomEvent( 'wc-blocks_added_to_cart', {
                bubbles:    true,
                cancelable: true,
            } );
            document.body.dispatchEvent( event );
        }
	}
	new MPCFrontAddToCart();
} )( jQuery, window, document );
