/**
 * Frontend hooks and filter registration class
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCHooks{
		constructor(){
			$(document).ready( () => this.initCustomMpcHooks() );
		}
		initCustomMpcHooks(){
            window.mpcHooks = {
                actions: {},
                filters: {},
                addAction: function( tag, callback ) {
                    if ( ! this.actions[ tag ]) this.actions[ tag ] = [];
                    this.actions[ tag ].push( callback );
                },
                doAction: function( tag, ...args ) {
                    if ( this.actions[ tag ] ) {
                        this.actions[ tag ].forEach( callback => callback( ...args ) );
                    }
                },
                addFilter: function( tag, callback ) {
                    if ( ! this.filters[ tag ] ) this.filters[ tag ] = [];
                    this.filters[ tag ].push( callback );
                },
                applyFilters: function( tag, data, ...args ) {
                    if ( ! this.filters[ tag ] ) return data;
                    return this.filters[ tag ].reduce( ( currentData, callback ) => {
                        return callback( currentData, ...args );
                    }, data );
                }
            };

            // common event handlers.
            window.mpcTables = {
                state: {},
                updateProductState: function( target, productData ){
                    if( ! this.state[ target.tableId ] ){
                        this.state[ target.tableId ] = [];
                    }
                    this.state[ target.tableId ][ target.productId ] = productData;
                },
                updateProductMeta: function( target, key, value ){
                    this.state[ target.tableId ][ target.productId ][ key ] = value;
                },
                identifyTable: function( target ){
                    return {
                        tableId:   parseInt( target.closest( 'table.mpc-wrap' ).attr( 'data-table_id' ) ),
                        productId: parseInt( target.closest( 'tr.cart_item' ).attr( 'data-id' ) )
                    };
                },
                getValidStockQuantity: function( field ){
                    const target = this.identifyTable( field );
                    const stock  = this.state[ target.tableId ][ target.productId ]['stock'];
                    let qty      = this.state[ target.tableId ][ target.productId ]['qty'];

                    // validate quantity.
                    qty = stock && 0 === stock ? 0 : (
                        stock && qty > stock ? stock : qty
                    );
                    this.state[ target.tableId ][ target.productId ]['qty'] = qty;
                    return qty;
                },
                getTableTotal: function( target ) {
                    const tableData = this.state[ target.tableId ];
                    return tableData && tableData.length > 0 ? Object.values( tableData ).reduce( ( sum, item ) => {
                        const price = item.checked ? item.price : 0;
                        return sum + price;
                    }, 0 ) : 0;
                }
            };
        }
	}
	new MPCHooks();
} )( jQuery, window, document );
