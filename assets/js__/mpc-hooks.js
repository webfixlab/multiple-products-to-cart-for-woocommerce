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
                getProductMeta: function( target, key ){
                    return this.state[ target.tableId ][ target.productId ][ key ];
                },
                identifyTable: function( target ){
                    return {
                        tableId:   parseInt( target.closest( 'table.mpc-wrap' ).attr( 'data-table_id' ) ),
                        productId: parseInt( target.closest( 'tr.cart_item' ).attr( 'data-id' ) )
                    };
                },
                getValidStockQuantity: function( field, target ){
                    const productObj = this.state[ target.tableId ][ target.productId ];

                    const stock = productObj.stock;
                    const qty   = productObj.qty;
                    
                    const tempQty = Math.max( 1, qty ); // considering default quantity = 1, for easier validation.
                    let validQty  = 'number' === typeof stock && 0 === stock ? 0 : (
                        stock && tempQty > stock && -1 !== stock ? stock : tempQty
                    ); // sequence is important here.

                    // if not variation found yet, keep qty 0.
                    validQty = 'variable' === productObj.type && $.isEmptyObject( productObj.variation ) ? 0 : validQty;

                    return 0 === qty && validQty > 0 ? 1 : Math.min( qty, validQty );
                },
                getTableTotal: function( target ) {
                    const tableData = this.state[ target.tableId ];
                    return tableData && tableData.length > 0 ? Object.values( tableData ).reduce( ( sum, item ) => {
                        const price = item.checked ? item.price : 0;
                        return sum + ( price * item.qty );
                    }, 0 ) : 0;
                },
                getTableCartData: function( target ){
                    const tableData = this.state[ target.tableId ];
                    const cartData  = {};
                    Object.keys( tableData ).forEach( i => {
                        if( true === tableData[i].checked && tableData[i].qty > 0 ){
                            cartData[ i ] = this.getProductCartData( tableData[i] );
                        }
                    });
                    return cartData;
                },
                getProductCartData: function( productData ){
                    const cartData = {
                        type: productData.type,
                        qty:  productData.qty,
                    };

                    if( 'variable' === productData.type ){
                        cartData['variation_id'] = productData.variation.variation_id;
                        cartData['attributes']   = productData.variation.attributes__;
                    }

                    return cartData;
                },
                resetVariationData: function( elm ){
                    const target = this.identifyTable( elm );

                    this.updateProductMeta( target, 'variation', {} );
                    this.updateProductMeta( target, 'price', '' );
                    this.updateProductMeta( target, 'stock', '' );
                    this.updateProductMeta( target, 'checked', false );
                }
            };
        }
	}
	new MPCHooks();
} )( jQuery, window, document );
