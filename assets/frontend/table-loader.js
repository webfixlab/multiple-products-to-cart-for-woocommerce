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

            const self = this;

            $(document).ready(() => {
                this.loaderEvents();
            });
            $(document.body).on('mpc_load_table', function(event, item){
                self.loadTable(item);
            });
        }
        loaderEvents(){
            const self = this;
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

            const filter = wrap.find('select[name="mpc_orderby"]');
            if(filter.length !== 0){
                const filterBy  = filter.val();
                atts['order']   = filterBy.indexOf('ASC') !== -1 ? 'ASC' : 'DESC';
                atts['orderby'] = filterBy.replace(`-${atts['order']}`, '');
            }

            this.$atts = mpcCommon.applyFilters('mpcTableLoaderData', atts, this.$wrap);
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
            const wrap = this.$wrap;

            this.updateContent(response);

            $('html, body').animate({
                scrollTop: wrap.offset().top - 80 // animate to table top.
            }, 'slow');

            $(document.body).trigger('mpc_table_loaded');

            // remove sticky header     | wrap.find('.mpc-fixed-header').remove();
            // new sticky header render | renderStickyHead(wrap.find('table'));

            // calculate total price    | mpc_dynamic_product_pricing();
            // select all handler       | mpc_init_select_all( wrap );
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
                
                if(element.length !== 0) element.replaceWith(v.val);
                else if(parent.length !== 0){
                    if(v.adding_type === 'prepend') parent.prepend(v.val);
                    else parent.replaceWith(v.val);
                }
            });
        }
    }

    new mpcTableLoader();
})(jQuery, window, document);