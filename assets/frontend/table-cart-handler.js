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
                if(!self.hasCartData()) e.preventDefault();

                if(mpc_frontend.redirect_url === 'ajax') self.sendRequest();
                else self.prepareNonAjaxCartData();
            });
            $(document.body).on('click', '.mpc-fixed-cart', function(){
                $(this).closest('.mpc-container').find('.mpc-cart .mpc-add-to-cart').trigger('click');
            });
        }



        // request preparation.
        setCartData(item){
            this.$wrap = item.closest('.mpc-container');
            this.$data = this.getRequestData();
        }
        getRequestData(){
            const self = this;
            let data   = {};
            this.$wrap.find('tr.cart_item').each(function(){
                const rowData = self.getRowData($(this));
                const id      = parseInt($(this).attr('data-id'));
                if(rowData) data[id] = rowData;
            });

            if($.isEmptyObject(data)) this.notifyMsg(this.$wrap, 'error', mpc_frontend.blank_submit);
            return data;
        }
        getRowData(row){
            const type = row.attr('data-type');
            if('grouped' === type) return false;

            const variationId = parseInt(row.attr('data-variation_id'))
            const checkBox    = row.find('input[type="checkbox"]');
            const qtyField    = row.find('input[type="number"]');

            let atts = {};
            let totalAtts = 0, selectedAtts = 0;
            row.find('select').each(function(){
                const att = $(this).attr('data-attribute_name');
                const val = $(this).find('option:selected').val();

                totalAtts++;
                if(val.length !== 0){
                    atts[att] = val;
                    selectedAtts++;
                }
            });

            if(checkBox.length !== 0 && !checkBox.is(':checked')) return false;
            if(qtyField.length !== 0 && qtyField.val() === 0) return false;
            if(type === 'variable' && (!variationId || variationId === 0)) return false;
            if(totalAtts > 0 && totalAtts !== selectedAtts) return false;

            return {
                'quantity':     qtyField.length !== 0 ? parseInt(qtyField.val()) : 1,
                'type':         type,
                'variation_id': variationId,
                'attributes':   atts,
            };
        }
        hasCartData(){
            return !this.$data || mpc_frontend.redirect_url === 'ajax' ? false : true;
        }



        // process response section.
        sendRequest(){
            const self = this;
            window.mpcCommon.loaderAnimation(this.$wrap, 'load'); // remove loading animation.

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
            window.mpcCommon.loaderAnimation(this.$wrap, 'close');
            this.processAddToCartResponse(response);

            // Call this function whenever you need to trigger a mini-cart update.
            this.updateMiniCart();
        }
        processAddToCartResponse(response){
            const self = this;
            $(document.body).trigger('updated_cart_totals');

            this.$wrap.find('input[type="submit"]').show();
    
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
        updateMiniCart(){
            const event = new CustomEvent(
                'wc-blocks_added_to_cart', {
                    bubbles    : true,
                    cancelable : true,
                }
            );
            document.body.dispatchEvent(event);
        }
        prepareNonAjaxCartData(){
            this.$wrap.find('input[name="mpc_cart_data"]').val(JSON.stringify(this.$data));
        }



        notifyMsg(table, type, msg){
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
    }

    new mpcTableCartHandler();
})(jQuery, window, document);