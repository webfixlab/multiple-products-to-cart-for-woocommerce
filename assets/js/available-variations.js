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

			// window.mpcHooks.addAction( 'mpc_variation_changed', ( row, variation, attDropDown ) => this.filterAvailable( row, variation, attDropDown ) );
			// window.mpcHooks.addAction( 'mpc_clear_variations', ( e ) => this.initClearVariations( e ) );
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
				// console.log( 'no variation found', variations );
                return data;
            }

			const refAtt = elm.attr( 'data-attribute_name' ).replace( 'attribute_', '' ); // ref att name.
			const refVal = elm.val(); // ref att value.
			// console.log( 'ref att', refAtt, refVal );

			let available = {};
			// let s = variations.find( item => {
			// 	return 0 === item.attributes[ refAtt ].length || item.attributes[ refAtt ] === refVal;
			// } );
			// console.log( '[available variations]', s );
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
							// console.log( 'opt', opt, 'type', typeof opt, 'empty', $.isEmptyObject( opt ), 'length', opt.length );
						}
					} );
				}
				// console.log( 'item', item.attributes[ refAtt ], refVal, item.attributes );
				// const atts = Object.values( item.attributes ).filter( Boolean );
				// if( -1 !== atts.indexOf( refVal ) )
			} );

			if( $.isEmptyObject( available ) ){
				// console.log( 'empty available' );
				return data;
			}

			// console.log( 'avail', available );

			row.find( 'select.mpc-var-att' ).each( ( _, el ) => {
				const name = $( el ).attr( 'data-attribute_name' ).replace( 'attribute_', '' );
				if( name !== refAtt ){
					// console.log( name, ' - ', refAtt );
					$( el ).find( 'option' ).each( ( _, opt ) => {
						const val   = $( opt ).val();
						const items = available[ name ];
						$( opt ).toggle( 0 === val.length || 'undefined' === typeof items || $.isEmptyObject( items ) || -1 !== items.indexOf( val ) );
						// console.log( val, 'type', typeof items, 'empty', $.isEmptyObject( items ), 'length', items.length, 'index', items.indexOf( val ) );
					} );
				}
			} );
			
			return data;
		}
		clearRowVariation( data, elm ){
			elm.closest( 'tr.cart_item' ).find( 'select.mpc-var-att option' ).each( ( _, opt ) => $( opt ).toggle( true ) );
			// console.log( 'row', elm.closest( 'tr.cart_item' ), 'options', elm.closest( 'tr.cart_item' ).find( 'select.mpc-var-att option' ) )
			return data;
		}









		filterAvailable( row, variation, attDropDown ){
			const args = {
				row:         row,
				attDropDown: attDropDown,
				variations:  row.find( '.row-variation-data' ).data( 'variation_data' ),
				options:     {},
				attName:     attDropDown.attr( 'data-attribute_name' ).replace( 'attribute_', '' ),
				attVal:      attDropDown.find( 'option:selected' ).val(),
			};
			if( ! args.attVal || 0 === args.attVal.length ){
				this.clearVariations( attDropDown );
			}

			if( ! args.variations || 0 === Object.keys( args.variations ).length ){
				return;
			}

			args.options = this.getOptions( args );
			if( 0 === Object.keys( args.options ).length ){
				return;
			}

			row.find( 'select' ).each( ( _, el ) => this.filterOptions( args, $( el ) ) );
		}
		getOptions( args ){
			const options = {}; // att name => array of attribute values.
			Object.keys( args.variations ).forEach( i => {
				const attributes   = args.variations[ i ].attributes;
				const hasTargetAtt = 'undefined' !== attributes[ args.attName ] && args.attVal === attributes[ args.attName ];

				Object.keys( attributes ).forEach( varAttName => {
					const varAttVal = attributes[ varAttName ];

					this.updateAvailableOptions( options, {
						attName:     varAttName,
						attVal:      varAttVal,
						isTargetAtt: hasTargetAtt && varAttName !== args.attName,
					});
				} );
			} );
			// keep in mind, if nothing exists, that's ok, if key exists without any value, that's wrong.
			return options;
		}
		updateAvailableOptions( options, args ){
			// when it's not different attribute or there's no attribute value.
			if( ! args.isTargetAtt || ! args.attVal || 0 === args.attVal.length ){
				return;
			}

			if( 'undefined' === typeof options[ args.attName ] ){
				options[ args.attName ] = [];
			}
			options[ args.attName ].push( args.attVal );
		}
		filterOptions( args, attDropDown ){
			const attName = attDropDown.attr( 'data-attribute_name' ).replace( 'attribute_', '' );
			const attVal  = attDropDown.find( 'option:selected' ).val();
			if( args.attName === attName ){
				return;
			}

			attDropDown.find( 'option' ).each( ( _, el ) => {
				const varAttVal = $( el ).val();
				const ifShow    = 0 === varAttVal.length || 'undefined' === typeof args.options[ attName ] || -1 !== args.options[ attName ].indexOf( varAttVal );
				$( el ).toggle( ifShow );
			} );
		}
		initClearVariations( e ){
			this.clearVariations( $( e.currentTarget ) );
		}
		clearVariations( item ){
			item.closest( '.mpc-product-variation' ).find( 'select.mpc-var-att option' ).each( ( _, el ) => $( el ).show() );
		}
	}
	new MPCVariationHandler();
} )( jQuery, window, document );
