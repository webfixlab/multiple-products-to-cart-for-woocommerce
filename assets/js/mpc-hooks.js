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
                    if ( ! this.actions[ tag ] ) this.actions[ tag ] = [];
                    this.actions[ tag ].push( callback );
                },
                doAction: function( tag, ...args ) {
                    if ( this.actions[ tag ] ) {
                        this.actions[ tag ].forEach( callback => callback( ...args ) );
                    }
                },
                addFilter: function( tag, callback ) {
                    if ( ! this.filters[ tag ] ) {
                        this.filters[ tag ] = [];
                    }
                    this.filters[ tag ].push( callback );
                },
                applyFilters: function( tag, data, ...args ) {
                    if ( ! this.filters[ tag ] ) {
                        return data;
                    }
                    return this.filters[ tag ].reduce( ( currentData, callback ) => {
                        return callback( currentData, ...args );
                    }, data );
                }
            };

            // common event handlers.
            window.mpcTables = {
                state:  {}, // complete table state with all necessary data.
                updateProduct: function( target, productData ){
                    if( ! this.state[ target.tableId ] ){
                        this.state[ target.tableId ] = [];
                    }
                    this.state[ target.tableId ][ target.productId ] = productData;
                },
                updateProductMeta: function( target, key, value ){
                    this.state[ target.tableId ][ target.productId ][ key ] = value;
                },
                getTableData: function( tableId ){
                    return this.state[ tableId ];
                },
                updateTableData: function( tableId, data ){
                    this.state[ tableId ] = data;
                },
                getRowData: function( table_id, product_id ){
                    return this.state[ table_id ][ product_id ];
                },
                getProductMeta: function( target, key ){
                    return this.state[ target.tableId ][ target.productId ][ key ];
                },
                identifyTable: function( target ){
                    return {
                        tableId:   parseInt( target.closest( 'table.mpc-wrap' ).attr( 'data-table_id' ) ),
                        productId: parseInt( target.closest( 'tr.cart_item' ).attr( 'data-id' ) )
                    };
                },
                // getValidStockQuantity: function( field, target ){
                //     const item = this.state[ target.tableId ][ target.productId ];

                //     // don't judge until you have solid reason to judge.
                //     if( 'variable' === item.type && $.isEmptyObject( item.variation ) ){
                //         return {
                //             qty:         item.qty,
                //             auto_update: false,
                //             disabled:    false
                //         };
                //     }
                    
                //     const stock = item.stock ?? -1;

                //     // return 0 only when it's out of stock, else at least 1.
                //     const valid = 'number' === typeof stock && 0 === stock ? 0 : (
                //         item.qty > stock && -1 !== stock ? stock : Math.max( 1, item.qty )
                //     );

                //     let autoUpdate = '-' !== item.qty_state && valid > 0; // when not reducing quantity.
                    
                //     item.qty     = autoUpdate ? Math.max( 1, item.qty ) : Math.min( valid, item.qty );
                //     item.checked = autoUpdate ? true : ( 0 === valid ? false : item.checked );

                //     return {
                //         qty:         item.qty,
                //         auto_update: autoUpdate,
                //         disabled:    0 === valid
                //     };
                // },
                // resetVariationData: function( elm ){
                //     const target = this.identifyTable( elm );

                //     this.updateProductMeta( target, 'variation', {} );
                //     this.updateProductMeta( target, 'price', '' );
                //     this.updateProductMeta( target, 'stock', '' );
                //     this.updateProductMeta( target, 'checked', false );
                // }
            };
        }
	}
	new MPCHooks();
} )( jQuery, window, document );
