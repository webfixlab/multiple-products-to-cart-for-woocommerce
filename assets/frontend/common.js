/**
 * Frontend common helper functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcCommon{
        constructor(){
            this.$filters  = {};
            this.$cartData = null;
            this.$currentWrap = null;
            $(document).ready(() => {});
        }



        imagePopup(url, action){
            const popup = $('#mpcpop');
            if(!popup) return;

            if(action === 'hide'){
                popup.hide();
                return;
            }

            if(!url) return;

            popup.find('.image-wrap img').attr('src', url);
            popup.show();
        }



        loaderAnimation(wrap, way){
            // way = load or close.
            if(way === 'load'){
                wrap.find('table').before(`<span class="mpc-loader"><img src="${mpc_frontend.imgassets}loader.gif"></span>`);
            }else if(way === 'close'){
                $('body').find('.mpc-loader').remove();
            }
        }
        formatPrice(price){
            return price.toLocaleString(mpc_frontend.locale, {
                minimumFractionDigits: mpc_frontend.dp,
                maximumFractionDigits: mpc_frontend.dp,
                useGrouping:           true
            });
        }



        addFilter(hookName, callback) {
            if (!this.$filters[hookName]) this.$filters[hookName] = [];
            this.$filters[hookName].push(callback);
        }
        applyFilters(hookName, value, ...args) {
            if (!this.$filters[hookName]) return value;
            return this.$filters[hookName].reduce((v, cb) => cb(v, ...args), value);
        }



        getRowData(row, overrideCheck){
            const type = row.attr('data-type');
            if('grouped' === type) return false;

            const checkBox    = row.find('input[type="checkbox"]');
            const qtyField    = row.find('input[type="number"]');
            if(!overrideCheck && checkBox.length !== 0 && !checkBox.is(':checked')) return false;
            if(qtyField.length !== 0 && qtyField.val() === 0) return false;
            
            let selected = 0;
            let atts = {};
            row.find('select').each(function(){
                const val = $(this).find('option:selected').val();
                atts[$(this).data('attribute_name')] = val;
                if(val.length !== 0) selected++;
            });
            const total    = row.find('select').length;
            if(total > 0 && total !== selected) return false;

            const variationId = parseInt(row.attr('data-variation_id'));
            console.log('variation id ' . variationId);
            if(type === 'variable' && (!variationId || variationId === 0)) return false;
            return {
                'quantity':     qtyField.length !== 0 ? parseInt(qtyField.val()) : 1,
                'type':         type,
                'variation_id': variationId,
                'attributes':   atts,
            };
        }
        getVariation(row){
            if(row.attr('data-type') !== 'variable') return '';

            const data = row.find('.row-variation-data').data('variation_data');
            if(!data) return '';

            const atts = {};
            let total  = 0, hasValue = 0;
            row.find('select').each(function(){
                const att = $(this).attr('name').replace('attribute_', '').toLowerCase();
                atts[att] = $(this).find('option:selected').val();

                total++;
                if(atts[att]) hasValue++;
            });

            if(total > 0 && total !== hasValue) return ''; // partial selection shouldn't yield any result.

            let variation = false;
            for(const id in data){
                if(!data[id].atts) return false;

                let total = 0, matched = 0;
                for(const att in data[id].atts){
                    total++;
                    const value = data[id].atts[att];
                    if(!value || atts[att].toLowerCase() === value.toLowerCase()){
                        matched++;
                    }
                }

                if(total > 0 && total === matched) variation = data[id];
            }
            return variation;
        }
    }

    window.mpcCommon = new mpcCommon();
})(jQuery, window, document);