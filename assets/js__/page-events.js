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


            this.allWraps  = null; // all table wrappers.
            this.scrollTop = 0; // current scroll top.
            this.prevScrollTop = 0; // keep old scroll position to find scroll direction.
            this.windowHeight  = $( window ).height();
            this.adminBar = 0; // all things that are sticky and not of our plugin.

			$(document).ready( () => this.initEvents() );
		}
		initEvents(){
            window.mpcHooks.addAction( 'mpc_spinner', ( action, wrap ) => this.tableLoadingSpinner( action, wrap ) );
            window.mpcHooks.addAction( 'mpc_table_loaded', ( response, wrap ) => this.tableLoadedEventHandler( wrap ) );

            window.mpcHooks.addAction( 'mpc_image_popup', ( e ) => this.renderImagePopup( e ) );
            $( '.mpc-container' ).on( 'click', '.mpc-product-image img', ( e ) => this.handleImagePopup( e ) );

            $( 'span.mpcpop-close' ).on( 'click', ( e ) => this.hidePopup( e ) );
            $( document ).on( 'keyup', ( e ) => this.hidePopup( e ) );

            this.allWraps = $( document.body ).find( '.mpc-container' );

            $( window ).on( 'resize', () => this.screenResizeEventHandler() );
            $( window ).on( 'scroll', () => this.windowScrollEventHandler() );
            this.renderAllTablesStickyElements();
            this.getAdminBarHeight();
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
            if( e.keyCode && 27 === e.keyCode ){
                $( '#mpcpop' ).hide();
            }
            $( '#mpcpop' ).hide();
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

            wrap.find( '.mpc-table-header' ).css( {
                'left': `${ positionLeft }px`,
                'width': viewPort < 768 ? '100%' : `${ wrap.find( 'table.mpc-wrap' )[0].offsetWidth }px`
            } ); // filter section.
            wrap.find( '.total-row' ).css( {
                'width': viewPort < 768 ? '100%' : `${ wrap.find( 'table.mpc-wrap' )[0].offsetWidth }px`
            } ); // fixed total section.
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

            this.scrollTop = $( window ).scrollTop() + this.adminBar;

            $.each( this.allWraps, ( _, el ) => {
                const wrap = $( el );
                const scrollState = this.setupTableOffsets( wrap );
                
                this.hasStickyHeaderEvent( wrap, scrollState );
                this.hasStickyFilterEvent( wrap, scrollState );
                this.hasStickyFooterEvent( wrap, scrollState );
            });

            this.prevScrollTop = this.scrollTop;
        }
        setupTableOffsets( wrap, scrollState ){
            const firstRow = wrap.find( 'table.mpc-wrap tbody tr:first-child' ).offset().top;
            const lastRow  = wrap.find( 'table.mpc-wrap tbody tr:last-child' );

            const tableTop    = wrap.find( 'table.mpc-wrap' ).offset().top;
            const tableBottom = lastRow.offset().top + lastRow[0].offsetHeight;
            return {
                isStickyFooter:  this.scrollTop + this.windowHeight < tableBottom,
                isStickyColumns: this.scrollTop > firstRow && this.scrollTop < tableBottom,
                isStickyFilter:  this.scrollTop < this.prevScrollTop && this.scrollTop < tableBottom && this.scrollTop > tableTop
            };
        }
        hasStickyFilterEvent( wrap, scrollState ){
            // when scrolling up and scrolling within table.
            let height = wrap.find( '.mpc-table-header' )[0].offsetHeight + 20;
            wrap.css( 'margin-top', scrollState.isStickyFilter ? `${height}px` : '20px' );

            const tableFilters = wrap.find( '.mpc-table-header' );
            tableFilters.toggleClass( 'mpc-fixed-filter', scrollState.isStickyFilter );
            tableFilters.css( { 'top': scrollState.isStickyColumns ? this.adminBar + wrap.find( '.mpc-fixed-header' ).height() : this.adminBar } );
        }
        hasStickyHeaderEvent( wrap, scrollState ){
            if( this.screenW < 500 ){
                return;
            }

            // show sticky header when we're past scrolling table header.
            const tableHeader = wrap.find( '.mpc-fixed-header' );
            tableHeader.css( { 'top': `${this.adminBar}px` } );
            tableHeader.toggle( scrollState.isStickyColumns );
        }
        hasStickyFooterEvent( wrap, scrollState ){
            // if current scroll + screen height < total row offset top.
            wrap.find( '.total-row' ).toggleClass( 'mpc-fixed-total-m', scrollState.isStickyFooter );
        }
        getAdminBarHeight(){
            const adminBar = $( document.body ).find( '#wpadminbar' );
            if( adminBar && adminBar.length > 0 ){
                this.adminBar = 'fixed' === adminBar.css( 'position' ) ? adminBar.height() : 0;
            }

            const device = $( document ).find( 'body' ).data( 'elementor-device-mode' );
            const elementorItems = $( document ).find( '.elementor-sticky.elementor-sticky--active' );
            if ( elementorItems && elementorItems.length > 0 && device && device.length > 0 ) {
                elementorItems.each( ( _, el ) => {
                    this.adminBar = ! $( el ).is( ':hidden' ) || ! $( el ).hasClass( `elementor-hidden-${device}` ) ? this.adminBar + $( el ).height() : this.adminBar;
                });
            }
        }
	}
	new MPCFrontPageEvents();
} )( jQuery, window, document );
