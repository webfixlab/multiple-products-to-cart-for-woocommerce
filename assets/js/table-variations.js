/**
 * Variation attributes handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      8.1.0
 */

( function ( $, window, document ) {
	class MPCVariationHandler{
		constructor(){
			this.$state = {}; // current event and functionality state.
			$( () => this.initSwatchs() );
		}
		initSwatchs(){
			$( document ).on(
				'change',
				'.mpc-var-att',
				e => this.initAvailableVariations( $( e.currentTarget ) )
			);
			$( document ).on(
				'click',
				'a.reset_variations',
				e => this.clearVariations( e )
			);
		}
		initAvailableVariations( el ){
			this.cleanState();

			this.$state.attName  = el.attr( 'data-attribute_name' ).replace( 'attribute_', '' );
			this.$state.attValue = el.find( 'option:selected' ).val();

			if ( ! this.$state.attValue || 0 === this.$state.attValue ) {
				return;
			}

			this.$state.variations = el.closest( 'td.mpc-product-variation' ).find( '.row-variation-data' ).data( 'variation_data' );
			if ( ! this.$state.variations || 0 === this.$state.variations.length ) {
				return;
			}

			Object.keys( this.$state.variations ).forEach(
				variationID => this.updateAvailableVariations( variationID )
			);

			this.filterAvailableVariations( el );
		}
		cleanState(){
			this.$state = { // variation data with current variation state.
				attName:    null, // current selected attribute name.
				attValue:   null, // current selected attribute value.
				variations: {}, // variation data.
				available:  {} // all available variation attribute options.
			};
		}
		updateAvailableVariations( variationID ){
			const attributes = this.$state.variations[ variationID ].attributes;

			// check if this variation matches current selected attribute value.
			const chkAttValue = attributes[ this.$state.attName ];
			const hasAttValue = ! chkAttValue || 0 === chkAttValue.length || chkAttValue === this.$state.attValue;
			if ( ! hasAttValue ) {
				return;
			}

			Object.keys( attributes ).forEach(
				attName =>
				this.updateState(
					attName,
					attributes[ attName ]
				)
			);
		}
		updateState( attName, attValue ){
			if ( attName === this.$state.attName ) {
				return;
			}

			if ( ! this.$state.available[ attName ] ) {
				this.$state.available[ attName ] = [];
			}

			this.$state.available[ attName ].push( attValue );
		}
		filterAvailableVariations( el ){
			if ( ! this.$state.available || 0 === this.$state.available.length ) {
				return;
			}

			Object.keys( this.$state.available ).forEach(
				attName =>
				this.filterAvailableVariation(
					attName,
					el
				)
			);
		}
		filterAvailableVariation( attName, el ){
			const dropDown = el.closest( 'td.mpc-product-variation' ).find( 'select.' + attName );
			if ( ! dropDown || 0 === dropDown.length ) {
				return;
			}

			const hasValue = dropDown.find( 'option:selected' ).val();

			dropDown.find( 'option' ).each(
				( _, cel ) =>
				{
					const attValue = $( cel ).val();
					if ( ! hasValue && attValue && attValue.length > 0 && -1 === this.$state.available[ attName ].indexOf( attValue ) ) {
						$( cel ).hide();
					} else {
						$( cel ).show();
					}
				}
			);
		}
		clearVariations( e ){
			e.preventDefault();
			
			const el = $( e.currentTarget ).closest( 'tr.cart_item' )

			el.find( 'select' ).each(
				( _, cel ) => this.clearVariation( $( cel ) ) // child element.
			);

			el.find( '.mpc-var-desc' ).empty();
			el.find( 'a.reset_variations' ).remove();
			el.find( 'input[type="number"]' ).prop( 'disabled', false );
			
			$( document ).trigger( 'mpc-reset-variations', [ el ] );
		}
		clearVariation( el ){
			el.find( 'option' ).each(
				( _, cel ) => $( cel ).show()
			);
			el.find( 'option:first' ).prop( 'selected', true );
			el.trigger( 'change' );
		}
	}
	new MPCVariationHandler();
} )( jQuery, window, document );
