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
            this.$filters = {};
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
            if (!this.$filters[hookName]) {
                this.$filters[hookName] = [];
            }
            this.$filters[hookName].push(callback);
        }
        applyFilters(hookName, value, ...args) {
            if (!this.$filters[hookName]) return value;
            return this.$filters[hookName].reduce((v, cb) => cb(v, ...args), value);
        }
    }

    window.mpcCommon = new mpcCommon();
})(jQuery, window, document);