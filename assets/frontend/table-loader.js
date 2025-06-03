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
            this.$tables = {};
            $(document).ready(() => {
                this.initTableLoader();
            });
        }
        initTableLoader(){
            this.$tables.wraps = $(document).find('.mpc-container-loading');
            this.$tables.count = 0;
            this.loadNextTable();
        }
        loadNextTable(){
            const self  = this;
            const delay = this.$tables.count === 0 ? 3000 : 1000;
            setTimeout(function(){
                self.requestLoaderData();
            }, delay);
        }



        requestLoaderData(){
            const tableWrap = $(this.$tables.wraps[this.$tables.count]);
            const requestParams = {
                action: 'mpc_lazy_loader',
                nonce:  mpc_frontend.table_nonce,
                atts:   JSON.stringify(tableWrap.data('atts')),
            };
            this.getReponse(tableWrap, requestParams);
        }
        async getReponse(tableWrap, requestParams){
            const self = this;
            try{
                const response = await fetch(mpc_frontend.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: new URLSearchParams(requestParams)
                });
                const data = await response.json();
                self.processResponse(tableWrap, data);
            }catch(error){
                self.handleLoadingError(tableWrap);
                console.log(error);
            }
        }
        processResponse(tableWrap, response){
            this.updateProducts(response);
            this.removeSkeleton(tableWrap);
            mpcTableTemplate.loadTable(tableWrap, response);
            
            this.$tables.count++;
            if(this.$tables.count < this.$tables.wraps.length){
                this.loadNextTable(); // load next table, if any.
            }else{
                $(document.body).trigger('mpc_init_tables');
                console.log(mpc_frontend);
            }
        }


        
        updateProducts(data){ // update our localized data with products.
            if(!data) return;

            mpc_frontend.products = {};
            for(const id in data.products){
                mpc_frontend.products[id] = data.products[id];
            }
        }
        removeSkeleton(tableWrap){
            tableWrap.empty();
            tableWrap.removeClass('mpc-container-loading');
            tableWrap.addClass('mpc-container');
        }
        handleLoadingError(tableWrap){
            tableWrap.removeClass('mpc-container-loading');
            tableWrap.addClass('woocommerce-info'); // similar class: woocommerce-message, woocommerce-error.
            tableWrap.text('Sorry! There was an error loading table.');
        }
    }

    new mpcTableLoader();
})(jQuery, window, document);
