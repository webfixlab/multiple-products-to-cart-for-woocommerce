/**
 * Frontend table loader events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCFrontTableLoader{
		constructor(){
            // this.$tableState = {}; // current table state.
			$( document ).ready(
				() => this.initEventTriggers()
			);
		}
		initEventTriggers(){
            // trigger table loader with table state.
            // on click table filters + category + tag + search.
            $( document ).on( 'mpc_table_loader', () => this.mpcTableLoader() );



            const self = this;
            $('body').on('change', '.mpc-orderby', function () {
                mpc_order_by_table($(this));
            });
            $('body').on('click', '.mpc-pagenumbers span', function () {
                if (!$(this).hasClass('current') && !$(this).hasClass('mpc-divider')) {
                    self.mpc_pagination_loader($(this));
                }
            });
            $('body').on('click', '.mpc-reset', function () {
                window.location.reload();
            });
        }
        mpcTableLoader(){
            //
        }
        


        mpc_order_by_table(elm) {
            var wrapper = elm.closest('.mpc-container');
            mpc_table_loader_request(1, wrapper);
        }
        mpc_table_loader_request(page, wrap) {
            $( document ).trigger( 'mpc_loader', [ 'load', wrap ] );
            var atts = mpc_get_atts(wrap);
            ajax_table_loader(atts, page, wrap);
        }
        mpc_get_atts(wrap) {
            var att_data = wrap.find('.mpc-table-query').data('atts');

            var atts = {};
            if (typeof att_data != 'undefined' && typeof att_data == 'object') {
                $.each(att_data, function (key, val) {
                    atts[key] = val;
                });
            }

            $.each(mpc_frontend.key_fields, function (shortcode_attr, attr_key) {
                // Shortcode_attr | shortcode attribute key - attr_key | identifier | .something or #something.
                if (typeof wrap.find(attr_key) != 'undefined' && typeof wrap.find(attr_key).val() != 'undefined') {
                    var attr_value = wrap.find(attr_key).val();

                    if (attr_value.length > 0) {
                        if (attr_value.indexOf('ASC') != -1) {
                            attr_value = attr_value.replace('-ASC', '');
                            atts.order = 'ASC';
                        } else if (attr_value.indexOf('DESC') != -1) {
                            attr_value = attr_value.replace('-DESC', '');
                            atts.order = 'DESC';
                        }

                        if (attr_key.indexOf('mpcp-cat-filter') != -1 && typeof atts[shortcode_attr] == 'undefined') {
                            atts.origin = 'dropdown_filter';
                        }

                        if (attr_value != 'menu_order') {
                            atts[shortcode_attr] = attr_value;
                        }
                    }
                }
            });

            return atts;
        }
        ajax_table_loader(atts, page, wrap) {
            let locale = $(document).find('html').attr('lang');
            locale = locale.replace( '-', '_' );
            $.ajax({
                method: "POST",
                url: mpc_frontend.ajaxurl,
                data: {
                    'action': 'mpc_ajax_table_loader',
                    'page': page,
                    'atts': atts,
                    'locale': locale,
                    'table_nonce': mpc_frontend.table_nonce
                },
                async: 'false',
                dataType: 'html',
                success: function (response) {
                    $( document ).trigger( 'mpc_loader', [ 'close', wrap ] );
                    if (response.length == 0) {
                        wrap.find('.mpc-pageloader').html('');
                        return;
                    }
                    mpc_table_loader_response(wrap, response);
                    $(document.body).trigger('mpc_table_loader', [wrap]);
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        }
        mpc_table_loader_response(wrapper, response) {
            // sanitize response.
            const start = response.indexOf('{');
            if( start !== -1 ) response = response.substring(start);
            
            var rp = JSON.parse(response);
            if (rp.status) {
                wrapper.find('.mpc-all-select, .mpc-table-footer').hide();
            } else {
                wrapper.find('.mpc-all-select, .mpc-table-footer').show();
            }

            if (rp.mpc_fragments) {
                $.each(rp.mpc_fragments, function (k, v) {
                    if (typeof wrapper.find(v.key) == 'undefined' || wrapper.find(v.key).length == 0) {
                        if (typeof v.parent != 'undefined') {
                            if (typeof v.adding_type != 'undefined') {
                                if (v.adding_type == 'prepend') {
                                    wrapper.find(v.parent).prepend(v.val);
                                }
                            }
                        } else {
                            wrapper.find(v.key).replaceWith(v.val);
                        }
                    } else {
                        wrapper.find(v.key).replaceWith(v.val);
                    }
                });
            }
        }
        mpc_pagination_loader(elm) {
            var wrap = elm.closest('.mpc-container');
            var page = parseInt(elm.text());
            mpc_table_loader_request(page, wrap);
        }
	}
	new MPCFrontTableLoader();
} )( jQuery, window, document );
