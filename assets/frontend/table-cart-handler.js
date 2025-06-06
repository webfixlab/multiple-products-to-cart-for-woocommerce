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
            this.$data = null;
            this.$wrap = null;
            
            $(document).ready(() => {
                this.cartEvents();
            });
        }
        cartEvents(){
            const self = this;
            $(document.body).on('click', '.mpc-cart .mpc-add-to-cart', function(e){
                self.setCartData($(this));
                if(!this.$data || mpc_frontend.redirect_url === 'ajax'){
                    e.preventDefault();
                }

                if(mpc_frontend.redirect_url === 'ajax') {
                    self.sendRequest();
                }else{
                    this.$wrap.find('input[name="mpc_cart_data"]').val(JSON.stringify(this.$data));
                }
            });
            $(document.body).on('click', '.mpc-floating-total .float-label', function(){
                var wrap = $(this).closest('.mpc-container');
                wrap.find('.mpc-cart .mpc-add-to-cart').trigger('click');
            });
            $(document.body).on('click', '.mpc-fixed-cart', function(){
                $(this).closest('.mpc-container').find('.mpc-cart .mpc-add-to-cart').trigger('click');
            });
        }



        sendRequest(){
            const self = this;
            // remove loading animation.
            this.loaderAnimation('load');
            $.ajax({
                method: "POST",
                url:    mpc_frontend.ajaxurl,
                data:   {
                    'action'         : 'mpc_ajax_add_to_cart',
                    'mpca_cart_data' : this.$data,
                    'cart_nonce'     : mpc_frontend.cart_nonce
                },
                success:function (response) {
                    self.processResponse(response);
                },
                error: function (errorThrown) {
                    console.log( errorThrown );
                }
            });
        }
        processResponse(response){
            // remove loading animation.
            this.loaderAnimation('close');
            this.processAddToCartResponse(response);

            // Call this function whenever you need to trigger a mini-cart update.
            this.updateMiniCart();
        }
        processAddToCartResponse(response){
            const self = this;
            $(document.body).trigger('updated_cart_totals');
    
            this.$wrap.find('.mpc-button a.mpc-loading').remove();
            this.$wrap.find('.mpc-button input[type="submit"]').show();
    
            if(response.fragments){
                $.each(response.fragments, function(key, value){
                    $(key).replaceWith(value);
                });
            }
    
            this.addCartMsg(response);
    
            setTimeout(function(){
                $('body').find('.mpc-popify').remove();
                self.resetTable();
            }, 2000);
    
            setTimeout(function(){
                self.removeCartMsg();
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



        setCartData(item){
            this.$wrap = item.closest('.mpc-container');
            this.$data = this.getRequestData(item);
        }
        getRequestData(item){
            const self = this;
            const wrap = item.closest('.mpc-container');

            let data   = {};
            wrap.find('tr.cart_item').each(function(){
                const rowData = self.getRowData($(this), false);
                if(!rowData) return; // continue, return false - break;.

                $.each(rowData, function(id, d){
                    data[id] = d;
                });
            });

            if($.isEmptyObject(data)){
                self.showNotification(wrap, 'error', mpc_frontend.blank_submit);
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
            const checkBox = row.find('input[type="checkbox"]');
            if(checkBox.length !== 0 && !is_single){
                chk++;
                if(checkBox.is(':checked')) chk++;
            }

            // if quantity box exists.
            const qtyField = row.find('input[type="number"]');
            if(qtyField.length !== 0){
                qty++;

                if(qtyField.val().length > 0 && parseInt(qtyField.val()) > 0){
                    qty++;
                    quantity = parseInt(qtyField.val());
                }
            }

            if((chk == 0 || chk == 2) && (qty == 0 || qty == 2)){
            }else return false;

            var id = 0, varid = 0;
            id = parseInt(row.attr('data-id'));

            // if exists or has value, get variation id.
            if(typeof row.attr('data-variation_id') !== 'undefined' && row.attr('data-variation_id').length > 0){
                varid = parseInt(row.attr('data-variation_id'));
            }

            data[id] = {};
            data[id]['quantity'] = quantity;
            data[id]['type']     = row.attr('data-type');

            if(type === 'variable'){
                // has_variation = true;
                var tt = 0, ss = 0;
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


        loaderAnimation(way){
            // way = load or close.
            if(way === 'load'){
                this.$wrap.find('table').before(`<span class="mpc-loader"><img src="${mpc_frontend.imgassets}loader.gif"></span>`);
            }else if(way === 'close'){
                $('body').find('.mpc-loader').remove();
            }
        }
        showNotification(table, type, msg){
            var html = type === 'error' ? `<p class="woo-err woocommerce-error">${msg}</p>` : '';

            const noticeWrap = table.find('.woo-notices');
            if(noticeWrap.length !== 0){
                noticeWrap.html(html);
            }else{
                table.prepend(`<div class="woo-notices mpc-notice">${html}</div>`);
            }

            $('html, body').animate({
                scrollTop: $(table).offset().top - 60
            }, 'slow');

            setTimeout(function(){
                $('body').find('.mpc-popify').remove();
            }, 2000);

            setTimeout(function(){
                table.find('.woo-notices').remove();
            }, 5000);
        }



        addCartMsg(response){
            $('body').find('.mpc-cart-messege').remove();
    
            var popup  = '', notice = '';
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
            this.$wrap.closest('.mpc-container').prepend(`<div class="woocommerce-notices-wrapper mpc-cart-messege">${notice}</div>`);
        }
        resetTable(){
            this.$wrap.find('table.mpc-wrap tbody tr').each(function(){
                const checkBox = $(this).find('.mpc-product-select input[type="checkbox"]');
                if(checkBox.length !== 0 && checkBox.is(':checked')) checkBox.trigger('click');
            });
        }
        removeCartMsg(){
            const cartMsg = this.$wrap.find('.mpc-cart-messege');
            if(cartMsg.length !== 0) cartMsg.remove();
        }
    }

    new mpcTableCartHandler();
})(jQuery, window, document);