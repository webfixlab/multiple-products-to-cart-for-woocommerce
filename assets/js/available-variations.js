/**
 * Variation attributes handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCVariationHandler{
		constructor(){
			$( () => this.initEventTriggers() );
		}
		initEventTriggers(){
			window.mpcHooks.addFilter( 'mpc_field_changed', ( data, elm, type ) => this.applyAttChange( data, elm, type ) );
		}
		applyAttChange( data, elm, type ){
			data = 'att' === type ? this.filterVariations( data, elm ) : data;
			data = 'clear' === type ? this.clearRowVariation( data, elm ) : data;

			return data;
		}
		filterVariations( data, elm ){
			const row        = elm.closest( 'tr.cart_item' );
			const variations = row.find( '.row-variation-data' ).data( 'variation_data' );
            if( $.isEmptyObject( variations ) ){
                return data;
            }

			const refAtt = elm.attr( 'data-attribute_name' ).replace( 'attribute_', '' ); // ref att name.
			const refVal = elm.val(); // ref att value.

			let available = {};
			Object.values( variations ).forEach( ( item ) => {
				if( 0 === item.attributes[ refAtt ].length || item.attributes[ refAtt ] === refVal ){
					Object.keys( item.attributes ).forEach( ( name ) => { // attribute name.
						const opt = item.attributes[ name ];
						if( name !== refAtt && 'undefined' !== typeof opt && opt.length > 0 ){
							if( 'undefined' === typeof available[ name ] ){
								available[ name ] = [ opt ];
							}else{
								available[ name ].push( opt );
							}
						}
					} );
				}
			} );

			if( $.isEmptyObject( available ) ){
				return data;
			}

			row.find( 'select.mpc-var-att' ).each( ( _, el ) => {
				const name = $( el ).attr( 'data-attribute_name' ).replace( 'attribute_', '' );
				if( name !== refAtt ){
					$( el ).find( 'option' ).each( ( _, opt ) => {
						const val   = $( opt ).val();
						const items = available[ name ];
						$( opt ).toggle( 0 === val.length || 'undefined' === typeof items || $.isEmptyObject( items ) || -1 !== items.indexOf( val ) );
					} );
				}
			} );
			
			return data;
		}
		clearRowVariation( data, elm ){
			elm.closest( 'tr.cart_item' ).find( 'select.mpc-var-att option' ).each( ( _, opt ) => $( opt ).toggle( true ) );
			return data;
		}
	}
	new MPCVariationHandler();
} )( jQuery, window, document );
