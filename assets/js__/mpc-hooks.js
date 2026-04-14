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
        }
	}
	new MPCHooks();
} )( jQuery, window, document );
