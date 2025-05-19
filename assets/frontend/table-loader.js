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
            // this.$oldScrolls = {};
            
            $(document).ready(() => {
                this.init();
            });
        }
        init(){
            const self = this;

            $(document).on('click', '.mpc-pagenumbers span', function(){
                console.log('loading page');
                mpcTableTemplate.loadTable();
            });
        }
    }

    new mpcTableLoader();
})(jQuery, window, document);
