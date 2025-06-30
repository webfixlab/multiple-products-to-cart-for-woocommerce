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
            $('body').find('table.mpc-wrap').each(function(){
                var table = $(this);
                if(table.find('thead').length && !table.find('thead').is(':hidden')){
                    self.renderStickyHead($(this));
                }
            });
        }
        renderStickyHead(table){
            var min  = 99999;
            var html = '';
            table.find('thead th').each(function(){
                const th = $(this);
                if(th.offset().left < min) min = th.offset().left;
    
                html += `<th style="width:${th[0].offsetWidth}px;">${th.text()}</th>`;
            });

            var width = table[0].offsetWidth;
            let wrap  = table.closest('.mpc-container');
            html      = `<table style="width:${width}px;"><thead><tr>${html}</tr></thead></table>`;
            html      = `<div class="mpc-fixed-header" style="left:${min}px;display:none;">${html}</div>`;
            wrap.find('.mpc-fixed-header').remove();
            table.after(html);
        
            wrap.find('.total-row').css({'width': `${width}px`});
        
            var header       = wrap.find('.mpc-table-header');
            width            = width < 401 ? '100%' : `${width}px`;
            var headerHeight = header[0].offsetHeight;
            headerHeight     = headerHeight > 100 ? 55 : headerHeight;
            header.css({'left': `${min}px`, 'width' : width, 'min-height' : `${headerHeight}px`});
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
            var tk = 0; // table key.
            $('body').find('table.mpc-wrap').each(function(){
                self.tableScrollHandler($(this), tk);
                tk++;
            });
        }
        tableScrollHandler(table, tk){
            var wrap = table.closest('.mpc-container');

            var currentScroll = $(window).scrollTop();
            var cs            = currentScroll; // current scroll offset.

            var head = table.offset().top + 50;
            var tail = table.find('tbody tr:last-child').offset().top;

            // table head.
            let products   = table.find('tbody tr');
            let tableStart = products[1] ? $( products[1] ).offset().top : 0;
            let tableEnd   = $(products[products.length - 1]).offset().top + $(products[products.length - 1])[0].offsetHeight;

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
            if(wrap.find('.mpc-table-header').length){
                this.$oldScrolls[tk] = currentScroll;
                return;
            }

            if(currentScroll < this.$oldScrolls[tk] && currentScroll > head && currentScroll < tail){
                var height = wrap.find('.mpc-table-header')[0].offsetHeight + 20;
                if(wrap.find('.mpc-all-select').length !== 0) height += 32;

                if(!wrap.find('.mpc-table-header').hasClass('mpc-fixed-filter')) wrap.css('margin-top', `${height}px`); // if check all products exists, add it's height.

                wrap.find('.mpc-table-header').removeClass('mpc-fixed-filter').addClass('mpc-fixed-filter');
            }else{
                wrap.find('.mpc-table-header').removeClass('mpc-fixed-filter');
                wrap.css('margin-top', '20px');
            }

            this.$oldScrolls[tk] = currentScroll;
        }
    }

    new mpcTableHelper();
})(jQuery, window, document);
