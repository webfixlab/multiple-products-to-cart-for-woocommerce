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
            this.state = {
                adminBar:  0, // all things that are sticky and not of our plugin.
                scroll:    0, // previous scroll top.
                colHeight: 0,
                oldScroll: 0,
            };

            this.allWraps = null; // all table wrappers.

			$(document).ready( () => this.initEvents() );
		}
		initEvents(){
            window.mpcHooks.addAction( 'mpc_spinner', ( action, wrap ) => this.tableLoadingSpinner( action, wrap ) );
            window.mpcHooks.addAction( 'mpc_table_loaded', ( response, wrap ) => this.tableLoadedEventHandler( wrap ) );

            window.mpcHooks.addAction( 'mpc_image_popup', ( e ) => this.renderImagePopup( e ) );
            $( '.mpc-container' ).on( 'click', '.mpc-product-image img', ( e ) => this.handleImagePopup( e ) );
            $( 'span.mpcpop-close' ).on( 'click', ( e ) => this.hidePopup( e) );
            $( document ).on( 'keyup', ( e ) => this.hidePopup( e ) );
            $( '#mpcpop' ).on( 'click', ( e ) => this.hidePopup( e ) );

            this.allWraps = $( document.body ).find( '.mpc-container' );

            $( window ).on( 'resize', () => this.screenResizeEventHandler() );
            $( window ).on( 'scroll', () => this.windowScrollEventHandler() );

            this.renderAllTablesStickyElements();
            this.getAdminBarHeight();

            this.setupSelectAllHideLabel();
        }
        tableLoadingSpinner( way, wrap ) {
            if( 'load' === way ){
                wrap.find( 'table.mpc-wrap' ).before( `<span class="mpc-loader"><img src="${ mpc_frontend.imgassets }loader.gif"></span>` );
            }else{
                wrap.find( '.mpc-loader' ).remove();
            }
        }
        handleImagePopup( e ) {
            window.mpcHooks.doAction( 'mpc_image_popup', e );
        }
        renderImagePopup( e ){
            const imgSrc = $( e.currentTarget ).attr( 'data-fullimage' );
            $( '#mpcpop' ).find( 'img' ).attr( 'src', imgSrc );
            $( '#mpcpop' ).toggle( imgSrc && imgSrc.length > 0 );
        }
        hidePopup( e ){
            if( $( e ).hasClass( 'mpcpop-close' ) ){
                $( '#mpcpop' ).hide();
            }else if( e.keyCode && 27 === e.keyCode ){
                $( '#mpcpop' ).hide();
            } else if( 'img' !== e.target.tagName.toLowerCase() ){
                $( '#mpcpop' ).hide();
            }
            
        }

        tableLoadedEventHandler( wrap ){
            this.renderStickyElements( wrap );
            wrap.find( '.mpc-fixed-header' ).remove();

            $( 'html, body' ).animate( {
                scrollTop: wrap.offset().top - 80
            }, 'slow' );
        }



        screenResizeEventHandler(){
            this.getAdminBarHeight();
            this.renderAllTablesStickyElements();
            this.windowScrollEventHandler();
        }
        renderAllTablesStickyElements(){
            if( ! this.allWraps || 0 === this.allWraps.length ){
                return;
            }

            $.each( this.allWraps, ( _, el ) => this.renderStickyElements( $( el ) ) );
        }
        renderStickyElements( wrap ){
            const viewPort   = window.innerWidth || document.documentElement.clientWidth;
            let positionLeft = wrap.find( 'tbody tr:first-child td:first-child' ).offset().left;
            positionLeft     = viewPort > 768 ? positionLeft : 0;

            this.renderStickyHeader( wrap, positionLeft );

            const currentWidth = viewPort < 768 ? '100%' : `${ wrap.find( 'table.mpc-wrap' )[0].offsetWidth }px`;

            const tableFilter = wrap.find( '.mpc-table-header' );
            if( tableFilter && tableFilter.length > 0 ){
                tableFilter.css({
                    'width': currentWidth
                });
            }

            const tableTotal = wrap.find( '.total-row' );
            if( tableTotal && tableTotal.length > 0 ){
                tableTotal.css({
                    'left': `${ positionLeft }px`,
                    'width': currentWidth
                });
            }
            
            const tableBtn = wrap.find( '.mpc-button' );
            if( tableBtn && tableBtn.length > 0 ){
                tableBtn.css( {
                    'width': currentWidth
                } );
            }
        }
        renderStickyHeader( wrap, positionLeft ){
            wrap.find( '.mpc-fixed-header' ).remove();

            const tableHeaders = wrap.find( 'table.mpc-wrap thead th' );
            if( ! tableHeaders || 0 === tableHeaders.length || 0 === positionLeft ){
                return; // skip if no headers or it's mobile view.
            }

            let tableHeaderHtml = '';
            tableHeaders.each( ( _, el ) => {
                tableHeaderHtml += `<th style="width:${ $( el ).offsetWidth }px;">${ $( el ).text() }</th>`;
            });

            wrap.find( 'table.mpc-wrap' ).after( `<div class="mpc-fixed-header" style="left:${ positionLeft }px;display:none;"><table style="width: ${ wrap.find( 'table.mpc-wrap' )[0].offsetWidth }px;"><thead><tr>${ tableHeaderHtml }</tr></thead></table></div>` );
        }


        windowScrollEventHandler(){
            if( ! this.allWraps || 0 === this.allWraps.length ){
                return;
            }

            this.state.scroll = $( window ).scrollTop() + this.state.adminBar;

            $.each( this.allWraps, ( _, el ) => {
                const wrap = $( el );

                const stickyHeader   = wrap.find( '.mpc-fixed-header' );
                this.state.colHeight = stickyHeader && stickyHeader.length > 0 ? stickyHeader.height() : this.state.colHeight;

                const isSticky = this.setupTableOffsets( wrap );
                this.hasStickyHeaderEvent( wrap, isSticky );
                this.hasStickyFilterEvent( wrap, isSticky );
                this.hasStickyFooterEvent( wrap, isSticky );
            });

            this.state.oldScroll = this.state.scroll;
        }
        setupTableOffsets( wrap, isSticky ){
            const firstRow = wrap.find( 'table.mpc-wrap tbody tr:first-child' ).offset().top;
            const lastRow  = wrap.find( 'table.mpc-wrap tbody tr:last-child' );

            const tableTop     = wrap.find( 'table.mpc-wrap' ).offset().top;
            const tableBottom  = lastRow.offset().top + lastRow[0].offsetHeight;
            const scrollBottom = this.state.scroll + $( window ).height();

            return {
                footer:      scrollBottom > firstRow && scrollBottom < tableBottom,
                tableHeader: this.state.scroll > firstRow && this.state.scroll < tableBottom,
                filter:      this.state.scroll < this.state.oldScroll && this.state.scroll < tableBottom && this.state.scroll > tableTop
            };
        }
        hasStickyFilterEvent( wrap, isSticky ){
            // when scrolling up and scrolling within table.
            let height = wrap.find( '.mpc-table-header' )[0].offsetHeight + 20;
            wrap.css( 'margin-top', isSticky.filter ? `${height}px` : '20px' );

            const tableFilters = wrap.find( '.mpc-table-header' );
            if( tableFilters.find( '.mpc-filters div' ).length + tableFilters.find( '.mpc-all-actions div' ).length < 1 ){
                return;
            }
            tableFilters.toggleClass( 'mpc-fixed-filter', isSticky.filter );
            tableFilters.css( { 'top': isSticky.tableHeader ? this.state.adminBar + this.state.colHeight : this.state.adminBar } );
        }
        hasStickyHeaderEvent( wrap, isSticky ){
            if( window.screen.width < 500 ){
                return;
            }
            
            const tableHeader = wrap.find( '.mpc-fixed-header' );
            if( ! tableHeader || 0 === tableHeader.length ){
                this.renderStickyElements( wrap );
            }

            // show sticky header when we're past scrolling table header.
            tableHeader.css( { 'top': `${this.state.adminBar}px` } );
            tableHeader.toggle( isSticky.tableHeader );
        }
        hasStickyFooterEvent( wrap, isSticky ){
            // if current scroll + screen height < total row offset top.
            wrap.find( '.total-row' ).toggleClass( 'mpc-fixed-total-m', isSticky.footer );
        }
        getAdminBarHeight(){
            const adminBar = $( document.body ).find( '#wpadminbar' );
            if( adminBar && adminBar.length > 0 ){
                this.state.adminBar = 'fixed' === adminBar.css( 'position' ) ? adminBar.height() : 0;
            }

            const device = $( document ).find( 'body' ).data( 'elementor-device-mode' );
            const elementorItems = $( document ).find( '.elementor-sticky.elementor-sticky--active' );
            if ( elementorItems && elementorItems.length > 0 && device && device.length > 0 ) {
                elementorItems.each( ( _, el ) => {
                    this.state.adminBar = ! $( el ).is( ':hidden' ) || ! $( el ).hasClass( `elementor-hidden-${device}` ) ? this.state.adminBar + $( el ).height() : this.state.adminBar;
                });
            }
        }

        setupSelectAllHideLabel(){
            $( '.mpc-container' ).each( ( _, el ) => {
                const selectAll = $( el ).find( '.mpc-all-select' );
                if( selectAll && selectAll.length > 0 ){
                    selectAll.find( 'span') .toggle( $( el ).width() > 768 );
                }
            } );
        }
	}
	new MPCFrontPageEvents();
} )( jQuery, window, document );
