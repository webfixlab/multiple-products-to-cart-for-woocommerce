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
			window.mpcHooks.addAction( 'mpc_variation_changed', ( row, variation, attDropDown ) => this.filterAvailable( row, variation, attDropDown ) );
			window.mpcHooks.addAction( 'mpc_clear_variations', ( e ) => this.clearVariations( e ) );
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
				// reset everything. not done yet.
			}

			if( ! args.variations || 0 === Object.keys( args.variations ).length ){
				return;
			}

			args.options = this.getOptions( args );
			if( 0 === Object.keys( args.options ).length ){
				// console.log( 'no options, skipping', args.options );
				return;
			}
			// console.log( 'options', args.options );

			row.find( 'select' ).each( ( _, el ) => this.filterOptions( args, $( el ) ) );

			// find available variations.
			// filter out options which are available.
		}
		getOptions( args ){
			const options = {}; // att name => array of attribute values.
			Object.keys( args.variations ).forEach( i => {
				const attributes      = args.variations[ i ].attributes;
				const ifSameVariation = 'undefined' !== attributes[ args.attName ] && args.attVal === attributes[ args.attName ];
				// console.log( 'has same variation attribute?', ifSameVariation, ' : ', args.attVal, atts[ args.attName ] );

				Object.keys( args.variations[ i ].attributes ).forEach( varAttName => {
					const varAttVal = args.variations[ i ].attributes[ varAttName ];
					const isEmpty   = ! varAttVal || 0 === varAttVal.length;
					const isOtherAtt = ifSameVariation && varAttName !== args.attName && ! isEmpty;
					
					
					if( isOtherAtt && 'undefined' === typeof options[ varAttName ] ){
						options[ varAttName ] = [];
					}
					// console.log( args.attName, varAttName, 'empty?', isEmpty );

					if( isOtherAtt ){
						// console.log( 'pushed', varAttName, varAttVal );
						options[ varAttName ].push( varAttVal );
					}
				});
			});
			// keep in mind, if nothing exists, that's ok, if key exists without any value, that's wrong.
			return options;
		}
		filterOptions( args, attDropDown ){
			const attName = attDropDown.attr( 'data-attribute_name' ).replace( 'attribute_', '' );
			const attVal  = attDropDown.find( 'option:selected' ).val();
			if( args.attName === attName ){
				// console.log( 'same att', args.attName );
				return;
			}

			// console.log( 'not same att', attName, args.attName );
			attDropDown.find( 'option' ).each( ( _, el ) => {
				const varAttVal = $( el ).val();
				const ifShow    = 0 === varAttVal.length || 'undefined' === typeof args.options[ attName ] || -1 !== args.options[ attName ].indexOf( varAttVal );
				$( el ).toggle( ifShow );
			} );
		}


		clearVariations( e ){
			const section = $( e.currentTarget ).closest( '.mpc-product-variation' );
			section.find( 'select.mpc-var-att option' ).each( ( _, el ) => {
				$( el ).show();
			});
		}
	}
	new MPCVariationHandler();
} )( jQuery, window, document );
