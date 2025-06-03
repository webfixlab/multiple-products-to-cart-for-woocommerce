/**
 * Table frontend global storage
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcHooks{
        constructor(){
            this.$filters = {};
            // const filters = {};
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

    window.mpcHooks = new mpcHooks();
})(jQuery, window, document);
