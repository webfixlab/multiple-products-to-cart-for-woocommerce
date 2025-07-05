/**
 * Table helper functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcTableHelper{
        constructor(){
            this.$screen = {
                'width':  window.screen.width,
                'height': $(window).height()
            };
            this.$oldScrolls = {};
            
            $(document).ready(() => {
                this.init();
            });
        }
        init(){
            const self = this;

            this.prepareAllTables();
            if(this.$screen.width < 500 ) {
                this.prepareFreeHead();
            }

            $(document).on('click', '.mpc-to-top', function(){
                self.moveToTop($(this));
            });
            $(document).on('click', '.mpc-fixed-cart', function(){
                self.fixedAddToCartBtnTrigger($(this));
            });
            $(document).on('keyup', function(e){
                self.onEscBtnPressed(e.keyCode);
            });

            $(window).on('scroll', function(){
                self.scrollHandler();
            });
            $(window).on('resize', function(){
                self.prepareAllTables();
                self.scrollHandler();
            });
        }



        prepareAllTables(){
            const self = this;
            $('body').find('.mpc-container').each(function(){
                self.renderStickyHead($(this));
            });
        }
        renderStickyHead(wrap){
            const table = wrap.find('table.mpc-wrap')
            wrap.find('.mpc-fixed-header').remove();
            
            const left  = $(table[0]).offset().left; // first column distanct to the left.
            const width = table[0].offsetWidth;

            this.addStickyHeaderColumns(table, left, width);
            
            const totalRow = wrap.find('.total-row');
            if(totalRow.length !== 0) totalRow.css({'width': `${width}px`});
            
            this.addStickyHeaderFilters(wrap, table, left, width);
        }
        addStickyHeaderColumns(table, left, width){
            const header = table.find('thead');
            if(header.length === 0) return;

            let html = '';
            header.find('th').each(function(){
                const th = $(this);
                html += `<th style="width:${th[0].offsetWidth}px;">${th.text()}</th>`;
            });

            html = `<table style="width:${width}px;"><thead><tr>${html}</tr></thead></table>`;
            html = `<div class="mpc-fixed-header" style="left:${left}px;display:none;">${html}</div>`;
            table.after(html);
        }
        addStickyHeaderFilters(wrap, table, left, width){
            const thead  = table.find('thead');
            const header = wrap.find('.mpc-table-header');
            
            let top    = thead.length !== 0 ? thead.find('th')[0].offsetHeight : 0;
            let height = header[0].offsetHeight;

            top    = $(document.body).hasClass('admin-bar') ? top + 31 : top;
            width  = width < 401 ? '100%' : `${width}px`;
            height = height > 100 ? 55 : height;
            header.css({'left': `${left}px`, 'width' : width, 'min-height' : `${height}px`, 'top': `${top}px`});
        }


        moveToTop(btn){
            $('html, body').animate({
                scrollTop: btn.closest('form').offset().top - 80
            },'slow');
        }
        fixedAddToCartBtnTrigger(btn){
            btn.closest('.mpc-container').find('.mpc-cart .mpc-add-to-cart').trigger('click');
        }
        onEscBtnPressed(keyCode){
            if(keyCode === 27) $('#mpcpop').hide();
        }


        prepareFreeHead(){
            $('body').find('.mpc-container').each(function(){
                const elemCount = $(this).find('.mpc-table-header > div').length;
                if(elemCount < 3) $(this).find('.mpc-table-header').removeClass('mpc-free-head').addClass('mpc-free-head');
            });
        }
        scrollHandler(){
            const self = this;
            var tabldId = 0; // table key.
            $('body').find('table.mpc-wrap').each(function(){
                self.tableScrollHandler($(this), tabldId);
                tabldId++;
            });
        }
        tableScrollHandler(table, tabldId){
            var wrap = table.closest('.mpc-container');

            var currentScroll = $(window).scrollTop();
            var cs            = currentScroll; // current scroll offset.

            var head = table.offset().top + 50;
            var tail = table.find('tbody tr:last-child').offset().top;

            let products   = table.find('tbody tr');
            let tableStart = products[1] ? $( products[1] ).offset().top : 0;
            let tableEnd   = $(products[products.length - 1]).offset().top + $(products[products.length - 1])[0].offsetHeight;

            // total section.
            const totalRow = wrap.find('.total-row');
            if((cs + this.$screen.height) > tableStart && (cs + this.$screen.height) < tableEnd){
                totalRow.removeClass('mpc-fixed-total-m').addClass('mpc-fixed-total-m');
            }else{
                totalRow.removeClass('mpc-fixed-total-m');
            }

            // fixed header.
            if(this.$screen.width > 500){
                const fixedHeader = table.closest('form').find('.mpc-fixed-header');
                if(cs > head && cs < tail){
                    if(table.find('thead').length !== 0) fixedHeader.show();
                }
                if(cs < head || cs > tail){
                    if(table.find('thead').length !== 0) fixedHeader.hide();
                }
            }

            // filter section.
            if(wrap.find('.mpc-table-header').length === 0){
                this.$oldScrolls[tabldId] = currentScroll;
                return;
            }

            if(currentScroll < this.$oldScrolls[tabldId] && currentScroll > head && currentScroll < tail){
                var height = wrap.find('.mpc-table-header')[0].offsetHeight + 20;
                if(wrap.find('.mpc-all-select').length !== 0) height += 32;

                if(!wrap.find('.mpc-table-header').hasClass('mpc-fixed-filter')) wrap.css('margin-top', `${height}px`); // if check all products exists, add it's height.

                wrap.find('.mpc-table-header').removeClass('mpc-fixed-filter').addClass('mpc-fixed-filter');
            }else{
                wrap.find('.mpc-table-header').removeClass('mpc-fixed-filter');
                wrap.css('margin-top', '20px');
            }

            this.$oldScrolls[tabldId] = currentScroll;
        }
    }

    new mpcTableHelper();
})(jQuery, window, document);
