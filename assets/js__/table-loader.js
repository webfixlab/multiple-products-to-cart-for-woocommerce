/**
 * Frontend table loader events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCFrontTableLoader{
		constructor(){
            // this.$tableState = {}; // current table state.
			$( document ).ready( () => this.initEventTriggers() );
		}
		initEventTriggers(){
            window.mpcHooks.addAction( 'mpc_load_table', ( e, type ) => this.ajaxTableLoader( e, type ) );

            $( 'body' ).on( 'change', '.mpc-orderby', ( e ) => this.loadTableEventHandler( e, 'orderby' ) );
            $( 'body' ).on( 'click', '.mpc-pagenumbers span', ( e ) => this.loadTableEventHandler( e, 'pagination' ) );

            window.mpcHooks.addAction( 'mpc_table_loaded', ( response, wrap ) => this.tableResponseHandler( response, wrap ) );
        }
        loadTableEventHandler( e, type ){
            window.mpcHooks.doAction( 'mpc_load_table', e, type );
        }
        ajaxTableLoader( e, type ){
            const elm  = $( e.currentTarget );
            const wrap = elm.closest( '.mpc-container' );
            const args = this.getArgs( wrap, type );
            this.requestNewTable( args, wrap );
        }
        getArgs( wrap, type ){
            const args = wrap.find( '.mpc-table-query' ).data( 'atts' );
            
            const filterWrap = wrap.find( '.mpc-orderby' );
            if( filterWrap && filterWrap.length > 0 ){
                args[ 'orderby' ] = filterWrap.find( 'option:selected' ).val();
            }

            args[ 'page' ] = 1;
            const pageWrap = wrap.find( '.mpc-pagenumbers span' );
            if( 'pagination' === type && pageWrap && pageWrap.length > 0 ){
                pageWrap.each( ( _, el ) => {
                    if( $( el ).hasClass( 'current' ) || $( el ).hasClass( 'mpc-divider' ) ){
                        args[ 'page' ] = parseInt( $( el ).text() );
                    }
                } );
            }

            return window.mpcHooks.applyFilters( 'mpc_table_args', args, wrap );
        }
        requestNewTable( args, wrap ){
            window.mpcHooks.addAction( 'mpc_spinner', 'load', wrap );
            $.ajax({
                method: "POST",
                url: mpc_frontend.ajaxurl,
                data: {
                    'action':      'mpc_ajax_table_loader',
                    'page':        args.page,
                    'atts':        args,
                    'locale':      this.getLocale(),
                    'table_nonce': mpc_frontend.table_nonce
                },
                async: 'false',
                dataType: 'html',
                success: ( response ) => {
                    window.mpcHooks.doAction( 'mpc_table_loaded', response, wrap );
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        }
        getLocale(){
            // get current locale for AJAX translation.
            return $( document ).find( 'html' ).attr( 'lang' ).replace( '-', '_' );
        }
        tableResponseHandler( response, wrap ){
            window.mpcHooks.addAction( 'mpc_spinner', 'close', wrap );

            // sanitize response.
            const start = response.indexOf( '{' );
            if( start !== -1 ) response = response.substring( start );
            
            var rp = JSON.parse( response );

            wrap.find( '.mpc-all-select, .mpc-table-footer' ).toggle( ! rp || ! rp.status );
            if ( ! rp.mpc_fragments || 0 === rp.mpc_fragments.length ) {
                return;
            }

            $.each( rp.mpc_fragments, function ( k, v ) {
                let elm = wrap.find( v.key );
                elm = ! elm || 0 === elm.length ? wrap.find( 'v.parent' ) : elm;
                if( 'prepend' === v.adding_type ){
                    elm.prepend( v.val );
                }else{
                    elm.replaceWith( v.val );
                }
            });
        }
	}
	new MPCFrontTableLoader();
} )( jQuery, window, document );
