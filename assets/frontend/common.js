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
            $(document).ready(() => {});
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
    }

    window.mpcCommon = new mpcCommon();
})(jQuery, window, document);