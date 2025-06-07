/**
 * Table loader functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcTableLoader{
        constructor(){
            this.$wrap = null;
            this.$page = 1;
            this.$atts = null;

            $(document).ready(() => {
                this.loaderEvents();
            });
        }
        loaderEvents(){
            const self = this;
            $(document.body).on('change', '.mpcp-cat-filter select', function(){
                self.loadTable($(this));
            });
            $(document.body).on('click', '.mpc-pagenumbers span', function(){
                if(!$(this).hasClass('current')) self.loadTable($(this));
            });
            $(document.body).on('change', '.mpc-orderby', function(){
                self.loadTable($(this));
            });
        }
        loadTable(item){
            this.$wrap = item.closest('.mpc-container');
            this.$page = item.hasClass('mpc-orderby') ? 1 : parseInt(item.text());
            
            window.mpcCommon.loaderAnimation(this.$wrap, 'load');
            this.setRequestData();
            this.sendRequest();
        }


        setRequestData(){
            const wrap   = this.$wrap;
            var att_data = wrap.find('.mpc-table-query').data('atts');

            var atts = {};
            if(typeof att_data !== 'undefined' && typeof att_data === 'object'){
                $.each(att_data, function(key, val){
                    atts[key] = val;
                });
            }

            $.each(mpc_frontend.key_fields, function(shortcode_attr, attr_key){
                // Shortcode_attr | shortcode attribute key - attr_key | identifier | .something or #something.
                if(typeof wrap.find(attr_key) !== 'undefined' && typeof wrap.find(attr_key).val() !== 'undefined') return false;

                var attr_value = wrap.find(attr_key).val();
                if(attr_value.length > 0){
                    if(attr_value.indexOf('ASC') !== -1){
                        attr_value = attr_value.replace('-ASC', '');
                        atts.order = 'ASC';
                    }else if(attr_value.indexOf('DESC') !== -1){
                        attr_value = attr_value.replace('-DESC', '');
                        atts.order = 'DESC';
                    }

                    if(attr_key.indexOf('mpcp-cat-filter') !== -1 && typeof atts[shortcode_attr] === 'undefined') atts.origin = 'dropdown_filter';
                    if(attr_value !== 'menu_order') atts[shortcode_attr] = attr_value;
                }
            });
            this.$atts = atts;
        }
        sendRequest(){
            const self = this;
            $.ajax({
                method: "POST",
                url:    mpc_frontend.ajaxurl,
                data: {
                    'action':      'mpc_ajax_table_loader',
                    'page':        self.$page,
                    'atts':        self.$atts,
                    'table_nonce': mpc_frontend.table_nonce
                },
                async:    'false',
                dataType: 'html',
                success:  function(response){
                    window.mpcCommon.loaderAnimation(self.$wrap, 'close');

                    // Empty response - return.
                    if(response.length === 0) self.$wrap.find('.mpc-pageloader').html('');
                    else self.processResponse(response);
                },
                error: function(errorThrown){
                    console.log(errorThrown);
                }
            });
        }
        processResponse(response){
            const wrapper = this.$wrap;
            $(document.body).trigger('mpc_table_loaded');

            this.updateContent(response);

            $('html, body').animate({
                scrollTop: wrapper.offset().top - 80 // animate to table top.
            }, 'slow');

            // remove sticky header     | wrapper.find('.mpc-fixed-header').remove();
            // new sticky header render | renderStickyHead(wrapper.find('table'));

            // calculate total price    | mpc_dynamic_product_pricing();
            // select all handler       | mpc_init_select_all( wrapper );
        }
        updateContent(response){
            const wrap = this.$wrap;
            const rp   = JSON.parse(response);
            
            const footerWrap = wrap.find('.mpc-all-select, .mpc-table-footer');
            if(rp.status) footerWrap.hide(); // if there was an error getting products.
            else footerWrap.show();
            
            if(!rp.mpc_fragments) return;

            $.each(rp.mpc_fragments, function(k, v){
                const element = wrap.find(v.key);
                const parent  = v.parent ? wrap.find(v.parent) : false;
                
                if(element.length !== 0) element.html(v.val);
                else if(parent.length !== 0){
                    if(v.adding_type === 'prepend') parent.prepend(v.val);
                    else parent.html(v.val);
                }
            });
        }
    }

    new mpcTableLoader();
})(jQuery, window, document);