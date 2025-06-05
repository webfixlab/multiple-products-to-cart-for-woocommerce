/**
 * Table frontend cart realted functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcTableCartHandler{
        constructor(){
            this.$rowData = {};
            
            $(document).ready(() => {
                this.cartEvents();
            });
        }
        cartEvents(){
            const self = this;
            $(document.body).on('click', '.mpc-cart .mpc-add-to-cart', function(e){
                self.addToCart($(this), e);
            });
            $(document.body).on('click', '.mpc-floating-total .float-label', function(){
                var wrap = $(this).closest('.mpc-container');
                wrap.find('.mpc-cart .mpc-add-to-cart').trigger('click');
            });
            $(document.body).on('click', '.mpc-fixed-cart', function(){
                $(this).closest('.mpc-container').find('.mpc-cart .mpc-add-to-cart').trigger('click');
            });
        }



        addToCart(item, e){
            var wrap = item.closest('.mpc-container');
            var data = this.getRequestData(wrap);

            if(!data || (typeof data === 'object' && $.isEmptyObject(data))) {
                e.preventDefault();
                return '';
            }

            if(mpc_frontend.redirect_url === 'ajax') {
                e.preventDefault();
                this.sendRequest(wrap, data);
            }else{
                wrap.find('input[name="mpc_cart_data"]').val(JSON.stringify(data));
            }
        }
        sendRequest( wrap, data ){
            const self = this;
            // remove loading animation.
            this.loaderAnimation( 'load', wrap );
            $.ajax({
                method : "POST",
                url    : mpc_frontend.ajaxurl,
                data   : {
                    'action'         : 'mpc_ajax_add_to_cart',
                    'mpca_cart_data' : data,
                    'cart_nonce'     : mpc_frontend.cart_nonce
                },
                success:function (response) {
                    self.processResponse(wrap, response);
                },
                error: function (errorThrown) {
                    console.log( errorThrown );
                }
            });
        }
        processResponse(wrap, response){
            // remove loading animation.
            this.loaderAnimation('close', wrap);
            this.processAddToCartResponse(wrap, response);

            // Call this function whenever you need to trigger a mini-cart update.
            this.updateMiniCart();
        }
        processAddToCartResponse(wrap, response){
            $(document.body).trigger('updated_cart_totals');
    
            wrap.find('.mpc-button a.mpc-loading').remove();
            wrap.find('.mpc-button input[type="submit"]').show();
    
            if(response.fragments){
                $.each(response.fragments, function(key, value){
                    $(key).replaceWith(value);
                });
            }
    
            $('body').find('.mpc-cart-messege').remove();
    
            var popup  = '';
            var notice = '';
            if(response.cart_message){
                popup  = `<div class="woocommerce-message" role="alert">${response.cart_message}</div>`;
                notice = popup;
            }
    
            if(response.error_message){
                popup  += `<div class="woo-err woocommerce-error" role="alert">${response.error_message}</div>`;
                notice += `<ul class="woocommerce-error" role="alert"><li>${response.error_message}</li></ul>`;
            }
    
            // add popup.
            $('body').append(`<div class="mpc-popup mpc-popify mpc-cart-messege"><div class="woocommerce">${popup}</div></div>`);
    
            // add table notice.
            wrap.closest('.mpc-container').prepend(`<div class="woocommerce-notices-wrapper mpc-cart-messege">${notice}</div>`);
    
            setTimeout(function(){
                $('body').find('.mpc-popify').remove();
                wrap.find('input[type="checkbox"]').each(function(){
                    if($(this).is(':checked')){
                        $(this).trigger('click');
                    }
                });
            }, 2000);
    
            setTimeout(function(){
                wrap.find('.mpc-cart-messege').remove();
            }, 7000);
        }
        updateMiniCart(){
            const event = new CustomEvent(
                'wc-blocks_added_to_cart', {
                    bubbles    : true,
                    cancelable : true,
                }
            );
            document.body.dispatchEvent(event);
        }
        



        getRequestData(wrap){
            const self = this;
            var data   = {};
            wrap.find('tr.cart_item').each(function(){
                var t = self.getRowData($(this), false);
                if(!t) return; // continue, return false - break;.

                $.each(t, function(id, d){
                    data[id] = d;
                });
            });

            if($.isEmptyObject(data)){
                mpc_notify(wrap, 'error', mpc_frontend.blank_submit);
                return false;
            }
            return data;
        }
        getRowData(row, is_single){
            const type = row.attr('data-type');
            if('grouped' === type) return false;

            var data     = {};
            var chk      = 0;
            var qty      = 0;
            var quantity = 1;

            // check if checkbox is checked for adding.
            if(typeof row.find('input[type="checkbox"]') != 'undefined' && row.find('input[type="checkbox"]').length > 0 && !is_single){
                chk++;
                if(row.find('input[type="checkbox"]').is(':checked')) chk++;
            }

            // if quantity box exists.
            if(typeof row.find('input[type="number"]') != 'undefined' && row.find('input[type="number"]').length > 0){
                qty++;

                if(row.find('input[type="number"]').val().length > 0 && parseInt(row.find('input[type="number"]').val()) > 0){
                    qty++;
                    quantity = parseInt(row.find('input[type="number"]').val());
                }
            }

            if((chk == 0 || chk == 2) && (qty == 0 || qty == 2)){
            }else return false;

            var id = varid = 0;
            id = parseInt(row.attr('data-id'));

            // if exists or has value, get variation id.
            if(typeof row.attr('data-variation_id') !== 'undefined' && row.attr('data-variation_id').length > 0){
                varid = parseInt(row.attr('data-variation_id'));
            }

            data[id] = {};
            data[id]['quantity'] = quantity;
            data[id]['type']     = row.attr('data-type');

            if(type === 'variable'){
                has_variation = true;
                var tt = ss = 0;
                row.find('select').each(function(){
                    tt++;
                    if($(this).val().length > 0) ss++;
                });

                if(tt === ss && tt !== 0){
                    data[id]['variation_id'] = varid;
                    data[id]['attributes']   = {};

                    row.find('select').each(function(){
                        data[id]['attributes'][$(this).attr('data-attribute_name')] = $(this).find('option:selected').val();
                    });
                }else{
                    return false;
                }
            }
            return data;
        }


        loaderAnimation(way, elem){
            var wrap = elem.closest('.mpc-container');
            // way = load or close.
            if(way === 'load'){
                wrap.find('table').before(`<span class="mpc-loader"><img src="${mpc_frontend.imgassets}loader.gif"></span>`);
            }else if(way === 'close'){
                $('body').find('.mpc-loader').remove();
            }
        }
    }

    new mpcTableCartHandler();
})(jQuery, window, document);