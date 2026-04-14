/**
 * Frontend table page events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCFrontPageEvents{
		constructor(){
            this.oldScrolls = {};

            this.screenH = $( window ).height();
            this.screenW = window.screen.width;

			$(document).ready( () => this.initEvents() );
		}
		initEvents(){
            this.prepareStickyTable();

            $( window ).on('scroll', () => this.tableScroll() );
            if (this.screenW < 500) {
                this.prepareFreeHead();
            }
            $( window ).on('resize', () => {
                this.prepareStickyTable();
                this.tableScroll();
            });
            window.mpcHooks.addAction( 'mpc_table_loaded', ( response, wrap ) => {
                this.renderStickyHead( wrap );
                wrap.find('.mpc-fixed-header').remove();
                $('html, body').animate({
                    scrollTop: $(wrap).offset().top - 80
                }, 'slow');
            } );

            window.mpcHooks.doAction( 'mpc_spinner', ( action, wrap ) => this.tableLoadingSpinner( action, wrap ) );

            // image popup section.
            $( 'body' ).on( 'click', '.mpc-product-image img', ( e ) => this.mpc_image_popup_loader( $(e.currentTarget ) ) );
            $( 'body' ).on( 'click', '.mpc-product-image .moregallery', ( e ) => this.mpc_image_popup_loader( $( e.currentTarget ).closest( '.gallery-item' ).find( 'img' ) ) );
            $( document ).on( 'keyup', ( e ) => {
                if ( 27 === e.keyCode ) { // esc key presed.
                    $( '#mpcpop' ).hide();
                }
            } );
            $('#mpcpop').on('click',function (e) {
                if (e.target.tagName.toLowerCase() == 'img') {
                    $('#mpcpop .image-wrap img').attr('src', $(e.target).attr('data-fullimage'));
                    $('#mpcpop .mpc-gallery img').removeClass('mpcgi-selected');
                    $(e.target).addClass('mpcgi-selected');
                } else {
                    $('#mpcpop').hide()
                }
            });

            // on close button of popup box clicked, hide it.
            $( 'body' ).on( 'click', 'span.mpcpop-close', () => $( '#mpcpop' ).hide() );

            // does this still exist?
            $(document).on('click', '.mpc-to-top', function () {
                var btn = $(this);
                $('html, body').animate({
                    scrollTop: btn.closest('form').offset().top - 80
                }, 'slow');
            });
        }
        tableScroll() {
            const self = this;
            var currentScroll = $( window ).scrollTop();
            var cs = currentScroll; // current scroll offset.

            var tk = 0; // table key.
            $('body').find('table.mpc-wrap').each( function () {
                var table = $(this);
                var wrap = table.closest('.mpc-container');

                self.setStickyTop(wrap);

                var head = table.offset().top + 50;
                var tail = table.find('tbody tr:last-child').offset().top;

                // table head.
                let products = table.find('tbody tr');
                let tableStart = products[1] ? $(products[1]).offset().top : 0;
                let tableEnd = $(products[products.length - 1]).offset().top + $(products[products.length - 1])[0].offsetHeight;
                if ((cs + self.screenH) > tableStart && (cs + self.screenH) < tableEnd) {
                    wrap.find('.total-row').removeClass('mpc-fixed-total-m').addClass('mpc-fixed-total-m');
                } else {
                    wrap.find('.total-row').removeClass('mpc-fixed-total-m');
                }

                // fixed header.
                if (this.screenW > 500) {
                    if (cs > head && cs < tail) {
                        if (table.find('thead').length) {
                            table.closest('form').find('.mpc-fixed-header').show();
                        }
                    }
                    if (cs < head || cs > tail) {
                        if (table.find('thead').length) {
                            table.closest('form').find('.mpc-fixed-header').hide();
                        }
                    }
                }

                // filter section.
                if (currentScroll < self.oldScrolls[tk] && currentScroll > head && currentScroll < tail) {
                    var height = wrap.find('.mpc-table-header')[0].offsetHeight + 20;
                    if (wrap.find('.mpc-all-select').length) {
                        height += 32;
                    }

                    if (!wrap.find('.mpc-table-header').hasClass('mpc-fixed-filter')) {
                        wrap.css('margin-top', `${height}px`);
                    }

                    wrap.find('.mpc-table-header').removeClass('mpc-fixed-filter').addClass('mpc-fixed-filter');
                } else {
                    wrap.find('.mpc-table-header').removeClass('mpc-fixed-filter');
                    wrap.css('margin-top', '20px');
                }
                self.oldScrolls[tk] = currentScroll;
                tk++;
            });
        }
        setStickyTop(wrap) {
            let top = 0;
            const adminBar = $(document).find('#wpadminbar');
            if (typeof adminBar !== undefined && adminBar.length > 0) {
                if (adminBar.css('position') === 'fixed') {
                    top += adminBar.height();
                }
            }

            const elementorSticky = $(document).find('.elementor-sticky.elementor-sticky--active');
            if (typeof elementorSticky !== undefined && elementorSticky.length > 0) {
                const device = $(document).find('body').data('elementor-device-mode');
                elementorSticky.each(function () {
                    if (!$(this).is(':hidden') || (typeof device !== undefined && !$(this).hasClass('elementor-hidden-' + device))) {
                        top += $(this).height();
                    }
                });
            }

            const fixedColumns = wrap.find('.mpc-fixed-header');
            if (typeof fixedColumns !== undefined && !fixedColumns.is(':hidden')) {
                fixedColumns.css({ 'top': `${top}px` });
                const fixedColsHeight = fixedColumns.height();
                if (fixedColsHeight) {
                    top += fixedColsHeight;
                }
            }

            const fixedFilters = wrap.find('.mpc-table-header.mpc-fixed-filter');
            if (typeof fixedFilters !== undefined) {
                fixedFilters.css({ 'top': `${top}px` });
            }
        }
        prepareFreeHead() {
            $('body').find('.mpc-container').each(( _, el) => {
                var elemCount = $( el ).find('.mpc-table-header > div').length;
                $( el ).find('.mpc-table-header').toggleClass('mpc-free-head', elemCount > 3);
            });
        }
        prepareStickyTable() {
            $('body').find('table.mpc-wrap').each(( _, el) => this.renderStickyHead($(el)));
        }
        renderStickyHead(table) {
            const wrap = table.closest('.mpc-container');
            const vpw = window.innerWidth || document.documentElement.clientWidth; // viewPort width.
            
            let min = table.find('tbody tr:first-child td:first-child').offset().left;
            min = vpw < 768 ? 0 : min;

            wrap.find('.mpc-fixed-header').remove();
            if(vpw > 767){
                var html = '';
                table.find('thead th').each(function () {
                    var th = $(this);
                    html += `<th style="width:${th[0].offsetWidth}px;">${th.text()}</th>`;
                });
                html = `<table style="width:${table[0].offsetWidth}px;"><thead><tr>${html}</tr></thead></table>`;
                table.after(`<div class="mpc-fixed-header" style="left:${min}px;display:none;">${html}</div>`);
            }
            
            let width = vpw < 768 ? '100%' : `${table[0].offsetWidth}px`;
            wrap.find('.total-row').css({ 'width': `${width}` }); // fixed total section.
            wrap.find('.mpc-table-header').css({ 'left': `${min}px`, 'width': width }); // filter section.
        }
        tableLoadingSpinner( way, elem ) {
            var wrap = elem.closest('.mpc-container');
            if (way == 'load') {
                wrap.find('table').before('<span class="mpc-loader"><img src="' + mpc_frontend.imgassets + 'loader.gif"></span>');
            } else if (way == 'close') {
                $('body').find('.mpc-loader').remove();
            }
        }
        mpc_image_popup_loader(item) {
            var link = item.attr('data-fullimage');
            if (typeof link != 'undefined' && link.length > 0){}
            else link = item.attr('data-fullimage');

            var mpop = $('#mpcpop');
            mpop.find('img').attr('src', link);

            if (typeof image_src != 'undefined' && image_src != '') mpop.find('img').attr('src', image_src);

            this.mpc_render_gallery(item);
            mpop.show();
        }

        // should be moved to pro.
        mpc_render_gallery(item) {
            var row = item.closest('tr.cart_item');
            var gallery = row.find('.gallery-items').data('gallery');
            if (typeof gallery != 'undefined') {
                var html = '';
                var found = false;
                $.each(gallery,function (k, v) {
                    var cls = '';
                    if (v.thumb == item.attr('src') && found == false) {
                        cls = 'mpcgi-selected';
                        found = true;
                    }

                    html += '<img class="' + cls + '" src="' + v.thumb + '" data-fullimage="' + v.full + '">';
                });

                if (typeof $('#mpcpop .mpc-gallery') != 'undefined' && $('#mpcpop .mpc-gallery').length > 0) {
                    $('#mpcpop .mpc-gallery').replaceWith('<div class="mpc-gallery">' + html + '</div>');
                } else {
                    $('#mpcpop').append('<div class="mpc-gallery">' + html + '</div>');
                }
            } else {
                if (typeof $('.mpc-gallery') != 'undefined' && $('.mpc-gallery').length > 0) {
                    $('.mpc-gallery').remove();
                }
            }
        }
	}
	new MPCFrontPageEvents();
} )( jQuery, window, document );
