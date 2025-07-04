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
            $(document).ready(() => {
                console.log(mpc_frontend);
                this.cartEvents();
            });
        }
        cartEvents(){
            const self = this;
            $(document.body).on('click', '.mpc-add-to-cart input[type="submit"]', function(e){
                self.setCartData($(this));
                if(!window.mpcCommon.$cartData) return;
                if(mpc_frontend.settings.cart_method === 'ajax'){
                    e.preventDefault();
                    $(document.body).trigger('mpc_add_to_cart');
                } else self.prepareNonAjaxCartData();
            });
            $(document.body).on('click', '.mpc-fixed-cart', function(){
                $(this).closest('.mpc-container').find('.mpc-cart .mpc-add-to-cart').trigger('click');
            });
            
            $(document.body).on('mpc_add_to_cart', function(){
                self.sendRequest();
            });
        }



        // request preparation.
        setCartData(item){
            window.mpcCommon.$currentWrap = item.closest('.mpc-container');
            window.mpcCommon.$cartData = this.getRequestData();
        }
        getRequestData(){
            let data   = {};
            window.mpcCommon.$currentWrap.find('tr.cart_item').each(function(){
                const rowData = window.mpcCommon.getRowData($(this), false);
                const id      = parseInt($(this).attr('data-id'));
                if(rowData) data[id] = rowData;
            });

            if($.isEmptyObject(data)) this.notifyMsg(window.mpcCommon.$currentWrap, 'error', mpc_frontend.labels.blank_submit);
            return data;
        }



        // process response section.
        sendRequest(){
            const self = this;
            window.mpcCommon.loaderAnimation(window.mpcCommon.$currentWrap, 'load'); // remove loading animation.

            $.ajax({
                method: "POST",
                url:    mpc_frontend.ajaxurl,
                data:   {
                    'action'         : 'mpc_ajax_add_to_cart',
                    'mpca_cart_data' : window.mpcCommon.$cartData,
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
            window.mpcCommon.loaderAnimation(window.mpcCommon.$currentWrap, 'close');
            this.processAddToCartResponse(response);

            // Call this function whenever you need to trigger a mini-cart update.
            this.updateMiniCart();
        }
        processAddToCartResponse(response){
            const self = this;
            $(document.body).trigger('updated_cart_totals');

            window.mpcCommon.$currentWrap.find('input[type="submit"]').show();
    
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
    
            var popup = '', notice = '';
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
            window.mpcCommon.$currentWrap.closest('.mpc-container').prepend(`<div class="woocommerce-notices-wrapper mpc-cart-messege">${notice}</div>`);
        }
        resetTable(){
            window.mpcCommon.$currentWrap.find('table.mpc-wrap tbody tr').each(function(){
                const checkBox = $(this).find('.mpc-product-select input[type="checkbox"]');
                if(checkBox.length !== 0 && checkBox.is(':checked')) checkBox.trigger('click');
            });
        }
        removeCartMsg(){
            const cartMsg = window.mpcCommon.$currentWrap.find('.mpc-cart-messege');
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
            window.mpcCommon.$currentWrap.find('input[name="mpc_cart_data"]').val(JSON.stringify(window.mpcCommon.$cartData));
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