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
			window.mpcHooks.addAction( 'mpc_clear_variations', ( e ) => this.initClearVariations( e ) );
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
