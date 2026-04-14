/**
 * Frontend table cart events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCFrontTableCart{
		constructor(){
			$( document ).ready(
				() => this.initEvents()
			);
		}
		initEvents(){
            const self = this;
            $('body').on('click', '.mpc-cart-messege', function () {
                if (typeof $('body').find('.mpc-cart-message') != 'undefined') {
                    $('body').find('.mpc-cart-messege').remove();
                }
            });
            
            // table add to cart button clicked event.
            $('body').on('click', '.mpc-cart .mpc-add-to-cart', function (e) {
                self.mpc_table_add_to_cart($(this), e);
            });
            // floating add to cart button clicked event.
            $('.mpc-floating-total .float-label').on('click', function (e) {
                var wrap = $(this).closest('.mpc-container');
                wrap.find('.mpc-cart .mpc-add-to-cart').trigger('click');
            });
            $(document).on('click', '.mpc-fixed-cart', function () {
                $(this).closest('.mpc-container').find('.mpc-cart .mpc-add-to-cart').trigger('click');
            });

            // single add to cart.
            $('body').on('click', '.mpce-single-add', function (e) {
                e.preventDefault();
                var row = $(this).closest('tr.cart_item');
                var wrap = $(this).closest('.mpc-container');

                var data = mpc_get_add_to_cart_row_data(row, true);
                if (!data || (typeof data == 'object' && $.isEmptyObject(data))) {
                    self.mpc_notify(wrap, 'error', mpc_frontend.blank_submit);
                } else {
                    self.mpc_request_ajax_add_to_cart(wrap, data);
                }
            });
        }
        // table add to cart method.
        mpc_table_add_to_cart(item, e) {
            var wrap = item.closest('.mpc-container');
            var data = this.mpc_get_add_to_cart_request(wrap);
            if (!data || (typeof data == 'object' && $.isEmptyObject(data))) {
                e.preventDefault();
                return '';
            }
            
            wrap.find('input[name="mpc_cart_data"]').val(JSON.stringify(data));
            
            setTimeout( () => {
                if (mpc_frontend.redirect_url === 'ajax') {
                    e.preventDefault();
                    this.mpc_request_ajax_add_to_cart(wrap, JSON.parse(wrap.find('input[name="mpc_cart_data"]').val()));
                }
            }, 250 );
        }
        mpc_request_ajax_add_to_cart(wrap, data) {
            window.mpcHooks.addAction( 'mpc_spinner', 'load', wrap );
            // remove loading animation.
            $.ajax({
                method: "POST",
                url: mpc_frontend.ajaxurl,
                data: {
                    action: 'mpc_ajax_add_to_cart',
                    mpca_cart_data: data,
                    cart_nonce: mpc_frontend.cart_nonce
                },
                success: ( response ) => {
                    window.mpcHooks.addAction( 'mpc_spinner', 'close', wrap );
                    this.mpcajx_add_to_cart_response( wrap, response );
                    this.updateMiniCart();
                },
                error: ( errorThrown ) => console.log( errorThrown )
            });
        }

        // validate form/table before sending or adding to cart request.
        mpc_get_add_to_cart_request(wrap) {
            const self = this;
            var data = {};

            wrap.find('tr.cart_item').each(function () {
                var t = self.mpc_get_add_to_cart_row_data($(this), false);
                if (t == false) return;

                $.each(t, function (id, d) {
                    data[id] = d;
                });
            });

            if ($.isEmptyObject(data)) {
                this.mpc_notify(wrap, 'error', mpc_frontend.blank_submit);
                return false;
            }
            return data;
        }

        // handle response after AJAX added to cart.
        mpcajx_add_to_cart_response(table, response) {
            window.mpcHooks.addAction( 'updated_cart_totals' );

            table.find('.mpc-button a.mpc-loading').remove();
            table.find('.mpc-button input[type="submit"]').show();

            if (response.fragments) {
                $.each(response.fragments, function (key, value) {
                    $(key).replaceWith(value);
                });
            }

            $('body').find('.mpc-cart-messege').remove();

            var popup = '';
            var notice = '';
            if (response.cart_message) {
                popup  = '<div class="woocommerce-message" role="alert">' + response.cart_message + '</div>';
                notice = '<div class="woocommerce-message" role="alert">' + response.cart_message + '</div>';
            }
            if (response.error_message) {
                popup  += '<div class="woo-err woocommerce-error" role="alert">' + response.error_message + '</div>';
                notice += '<ul class="woocommerce-error" role="alert"><li>' + response.error_message + '</li></ul>';
            }

            // add popup.
            if(popup.length > 0){
                $('body').append('<div class="mpc-popup mpc-popify mpc-cart-messege"><div class="woocommerce">' + popup + '</div></div>');
            }

            // add table notice.
            table.closest('.mpc-container').prepend('<div class="woocommerce-notices-wrapper mpc-cart-messege">' + notice + '</div>');

            setTimeout(function () {
                $('body').find('.mpc-popify').remove();
                table.find('input[type="checkbox"]').each(function () {
                    if ($(this).is(':checked')) {
                        $(this).trigger('click');
                    }
                });
            }, 2000);

            setTimeout(function () {
                table.find('.mpc-cart-messege').remove();
            }, 7000);

        }
        // show notification - message for user.
        mpc_notify(table, type, msg) {
            var html = '';
            if (type == 'error') html = '<p class="woo-err woocommerce-error">' + msg + '</p>';

            if (table.find('.woo-notices').length > 0) table.find('.woo-notices').html(html);
            else table.prepend('<div class="woo-notices mpc-notice">' + html + '</div>');

            $('html, body').animate({
                scrollTop: $(table).offset().top - 60
            }, 'slow');

            setTimeout(function () {
                $('body').find('.mpc-popify').remove();
            }, 2000);

            setTimeout(function () {
                table.find('.woo-notices').remove();
            }, 5000);
        }

        mpc_get_add_to_cart_row_data(row, is_single) {
            if ('grouped' === row.attr('data-type')) return false;

            var data = {};
            var chk = 0;
            var qty = 0;
            var quantity = 1;

            if (typeof row.find('input[type="checkbox"]') != 'undefined' && row.find('input[type="checkbox"]').length > 0 && !is_single) {
                chk++;
                if (row.find('input[type="checkbox"]').is(':checked')) chk++;
            }
            if (typeof row.find('input[type="number"]') != 'undefined' && row.find('input[type="number"]').length > 0) {
                qty++;
                if (row.find('input[type="number"]').val().length > 0 && parseInt(row.find('input[type="number"]').val()) > 0) {
                    qty++;
                    quantity = parseInt(row.find('input[type="number"]').val());
                }
            }

            if ((chk == 0 || chk == 2) && (qty == 0 || qty == 2)) {
                var id = 0, varid = 0;
                id = parseInt(row.attr('data-id'));
                if (typeof row.attr('data-variation_id') != 'undefined' && row.attr('data-variation_id').length > 0) {
                    varid = parseInt(row.attr('data-variation_id'));
                }

                data[id] = {};
                data[id]['quantity'] = quantity;
                data[id]['type'] = row.attr('data-type');
                if (mpc_is_variable_product(row)) {
                    has_variation = true;
                    var tt = ss = 0;
                    row.find('select.mpc-var-att').each(function () {
                        tt++;
                        if ($(this).val().length > 0) ss++;
                    });

                    if (tt == ss && tt != 0) {
                        data[id]['variation_id'] = varid;
                        data[id]['attributes'] = {};
                        row.find('select.mpc-var-att').each(function () {
                            data[id]['attributes'][$(this).attr('data-attribute_name')] = $(this).find('option:selected').val();
                        });
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }

            return data;
        }
        // Example: Dispatching an 'added_to_cart' event to update the mini-cart.
        updateMiniCart(){
            const event = new CustomEvent(
                'wc-blocks_added_to_cart',
                {
                    bubbles: true,
                    cancelable: true,
                }
            );
            document.body.dispatchEvent(event);
        }
	}
	new MPCFrontTableCart();
} )( jQuery, window, document );
