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
			$( document ).ready( () => this.initEventTriggers() );
		}
		initEventTriggers(){
            window.mpcHooks.addAction( 'mpc_load_table', ( e, type ) => this.ajaxTableLoader( e, type ) );

            $( document ).on( 'change', '.mpc-orderby', ( e ) => this.loadTableEventHandler( e, 'orderby' ) );
            $( document ).on( 'click', '.mpc-pagenumbers span', ( e ) => this.loadTableEventHandler( e, 'pagination' ) );
        }
        loadTableEventHandler( e, type ){
            if( 'pagination' === type ){
                $( e.currentTarget ).closest( '.mpc-pagenumbers' ).find( 'span' ).each( ( _, el ) => $( el ).removeClass( 'current' ) );
                $( e.currentTarget ).addClass( 'current' );
            }
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
                const value     = filterWrap.find( 'option:selected' ).val();
                args['order']   = value.indexOf( 'ASC' ) !== -1 ? 'ASC' : 'DESC';
                args['orderby'] = value.replace( '-' + args['order'], '' );
            }
            args[ 'page' ] = parseInt( wrap.find( '.mpc-pagenumbers span.current' ).text() );

            return window.mpcHooks.applyFilters( 'mpc_table_args', args, wrap );
        }
        requestNewTable( args, wrap ){
            window.mpcHooks.doAction( 'mpc_spinner', 'load', wrap );
            $.ajax({
                method: "POST",
                url: mpc_frontend.ajaxurl,
                data: {
                    'action':      'mpc_ajax_table_loader',
                    'page':        args.page,
                    'atts':        JSON.stringify( args ),
                    'locale':      this.getLocale(),
                    'table_nonce': mpc_frontend.table_nonce
                },
                async: 'false',
                dataType: 'html',
                success: ( response ) => {
                    this.tableResponseHandler( response, wrap );
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
            window.mpcHooks.doAction( 'mpc_spinner', 'close', wrap );

            // sanitize response.
            const start = response.indexOf( '{' );
            if( start !== -1 ) response = response.substring( start );
            
            var rp = JSON.parse( response );

            wrap.find( '.mpc-all-select, .mpc-table-footer' ).toggle( ! rp || ! rp.status );
            if ( ! rp.mpc_fragments || 0 === rp.mpc_fragments.length ) {
                return;
            }

            Object.keys( rp.mpc_fragments ).forEach( key => {
                wrap.find( rp.mpc_fragments[key].key ).replaceWith( rp.mpc_fragments[key].val );
            });
        }
	}
	new MPCFrontTableLoader();
} )( jQuery, window, document );
