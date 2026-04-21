/**
 * Admin JS events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCAdminTable{
		constructor(){
			$( document ).ready( () => this.initEventTriggers() );
		}
		initEventTriggers(){
			$( '.mpc-opt-sc-btn.copy' ).on( 'click', e => this.copyToClipBoard( e ) );
			$( '.mpcasc-reset, .mpc-opt-sc-btn.delete' ).on( 'click', e => this.resetEventHandler( e ) );
			$( 'body' ).find( '.mpc-sc-itembox' ).each( ( _, el ) => this.initChoiceJSItem( $( el ) ) );
		}
		copyToClipBoard( e ) {
			const copyBtn   = $( e.currentTarget );
			const shortcode = copyBtn.closest( '.mpc-shortcode, .mpc-shortcode-item' ).find( 'textarea' );
			shortcode.select();
			document.execCommand( 'copy' );
			window.getSelection().removeAllRanges();

			// animate dashicon.
			copyBtn.find( '.dashicons' ).toggleClass( 'dashicons-admin-page dashicons-saved' );
			setTimeout( () => copyBtn.find( '.dashicons' ).toggleClass( 'dashicons-admin-page dashicons-saved' ), 2000 );
		}
		resetEventHandler( e ){
			if ( ! confirm( 'Are you sure?' ) ) {
				e.preventDefault();
			}
		}
		initChoiceJSItem( el ){
			const key = el.attr( 'id' );
			if ( ! key || 0 === key.length ) {
				return;
			}

			const choiceItem = new Choices(
				document.querySelector( `#${key}` ),
				this.getQueryArgs( key )
			);

			let debounceTimeout;
			$( `#${key}` ).on( 'search', ( event ) => {
				clearTimeout( debounceTimeout );
				choiceItem.setChoices( [ { value: '', label: 'Loading...' } ], 'value', 'label', true );
				debounceTimeout = setTimeout( () => {
					var data = new FormData();
					data.append( 'action', 'mpc_admin_search_box' );
					data.append( 'search', event.detail.value );
					data.append( 'type_name', key );
					data.append( 'nonce', mpc_admin.nonce );

					this.sendSearchRequest( data, choiceItem, key );
				}, 1000 );
			} );
			choiceItem.passedElement.element.addEventListener( 'addItem', ( e ) => this.setChoiceFieldValue(
				$( e.currentTarget ).closest( '.choicesdp' ),
				choiceItem.getValue( true )
			) );
			choiceItem.passedElement.element.addEventListener( 'removeItem', ( e ) => this.setChoiceFieldValue(
				$( e.currentTarget ).closest( '.choicesdp' ),
				choiceItem.getValue( true )
			) );
		}
		getQueryArgs( key ){
			const single = 'cats' === key ? 'category' : 'product';
			const plural = 'cats' === key ? 'categories' : 'products';

			const isSearchField = -1 !== [ 'ids', 'selected', 'skip_products', 'cats' ].indexOf( key );

			const args = {
				removeItemButton:      true,
				placeholder:           true,
				placeholderValue:      'Choose options',
				shouldSort:            false,
				itemSelectText:        'Select ' + isSearchField ? plural : key,
				duplicateItemsAllowed: false,
				searchEnabled:         isSearchField,
			};

			if ( isSearchField ) {
				args['searchFields']      = ['label', 'value'];
				args['noChoicesText']     = `Type the ${single} name`;
				args['searchResultLimit'] = 50;
			}

			return args;
		}
		sendSearchRequest( data, choiceItem, key ){
			const single = 'cats' === key ? 'category' : 'product';
			$.ajax( {
				url: mpc_admin.ajaxurl,
				method: 'POST',
				data: data,
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function ( response ) {
					choiceItem.clearChoices();
					choiceItem.setChoices( response, 'id', 'name', false );
					if ( 0 === response.length ) {
						choiceItem.setChoices( [ { value: '', label: 'No ' + single + ' found.' } ], 'value', 'label', true );
					}
				},
				error: function ( jqXHR, textStatus, errorThrown ) {
					choiceItem.setChoices( [ { value: '', label: 'Error fetching data.' } ], 'value', 'label', true );
				}
			} );
		}
		setChoiceFieldValue( wrap, value ) {
			const field = wrap.find( '.choicesdp-field' );
			if ( value && value.length > 0 ) {
				field.val( 'string' === typeof value ? value : value.join( ',' ) );
			}
		}
	}
	new MPCAdminTable();
} )( jQuery, window, document );
