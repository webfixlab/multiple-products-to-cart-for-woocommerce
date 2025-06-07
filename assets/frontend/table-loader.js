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
            $(document).ready(() => {
                this.loaderEvents();
            });
        }
        loaderEvents(){
            $(document.body).on('change', '.mpcp-cat-filter select', function(){
                loadTable($(this));
            });
            $(document.body).on('click', '.mpc-pagenumbers span', function(){
                if(!$(this).hasClass(current)) loadTable($(this));
            });
            $(document.body).on('change', '.mpc-orderby', function(){
                loadTable($(this));
            });
        }
        loadTable(item){
            const wrap = item.closest('.mpc-container');
            const page = item.hasClass('mpc-orderby') ? 1 : parseInt(item.text());
            // mpc_table_loader_request(page, wrap);
        }


        prepareRequest(){}
        sendRequest(){}
        processResponse(){}
    }

    new mpcTableLoader();
})(jQuery, window, document);