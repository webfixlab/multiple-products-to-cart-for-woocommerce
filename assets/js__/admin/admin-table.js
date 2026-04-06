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
			$( document ).ready(
				() => this.initEventTriggers()
			);
		}
		initEventTriggers(){
			$( '.mpc-opt-sc-btn.copy' ).on(
				'click',
				( e ) =>
				{
					// copy to clip board shortcode.
					this.copyToClipBoard( $( e.currentTarget ).closest( '.mpc-shortcode, .mpc-shortcode-item' ).find( 'textarea' ) );

					$( e.currentTarget ).find( '.dashicons' ).toggleClass( 'dashicons-admin-page dashicons-saved' );
					setTimeout(
						() => $( e.currentTarget ).find( '.dashicons' ).toggleClass( 'dashicons-admin-page dashicons-saved' ),
						2000
					);
				}
			);
			$( '.mpcasc-reset, .mpc-opt-sc-btn.delete' ).on(
				'click',
				( e ) => 
				{
					if ( ! confirm( 'Are you sure?' ) ) {
						e.preventDefault();
					}
				}
			);
			$( 'body' ).find( '.mpc-sc-itembox' ).each(
				( _, el ) => this.initChoiceJSItem( $( el ) )
			);
		}
		copyToClipBoard( elm ) {
			elm.select();
			document.execCommand( 'copy' );
			window.getSelection().removeAllRanges();
		}
		initChoiceJSItem( el ){
			const key = el.attr( 'id' );
			if ( ! key || 0 === key.length ) {
				return;
			}

			const choiceItem = new Choices(
				document.querySelector( `# ${key}` ),
				this.getQueryArgs( key )
			);

			let debounceTimeout;
			$( `# ${key}` ).on(
				'search',
				( event ) =>
				{
					clearTimeout( debounceTimeout );
					choiceItem.setChoices( [ { value: '', label: 'Loading...' } ], 'value', 'label', true );
					debounceTimeout = setTimeout(
						() =>
						{
							var data = new FormData();
							data.append( 'action', 'mpc_admin_search_box' );
							data.append( 'search', event.detail.value );
							data.append( 'type_name', key );
							data.append( 'nonce', mpca_obj.nonce );

							this.sendSearchRequest( data, choiceItem, key );
						},
						1000
					);
				}
			);
			choiceItem.passedElement.element.addEventListener(
				'addItem',
				( e ) => this.setFieldValue(
					$( e.currentTarget ).closest( '.choicesdp' ),
					choiceItem.getValue( true )
				)
			);
			choiceItem.passedElement.element.addEventListener(
				'removeItem',
				( e ) => this.setFieldValue(
					$( e.currentTarget ).closest( '.choicesdp' ),
					choiceItem.getValue( true )
				)
			);
		}
		getQueryArgs( key ){
			const single = 'cats' === key ? 'category' : 'product';
			const plural = 'cats' === key ? 'categories' : 'products';

			const isSearchable = -1 !== [ 'ids', 'selected', 'skip_products', 'cats' ].indexOf( key );

			const args = {
				removeItemButton:      true,
				placeholder:           true,
				placeholderValue:      'Choose options',
				shouldSort:            false,
				itemSelectText:        'Select ' + isSearchable ? plural : key,
				duplicateItemsAllowed: false,
				searchEnabled:         isSearchable,
			};

			if ( isSearchable ) {
				args['searchFields'] = ['label', 'value'];
				args['noChoicesText'] = `Type the ${single} name`;
				args['searchResultLimit'] = 50;
			}

			return args;
		}
		sendSearchRequest( data, choiceItem, key ){
			const single = 'cats' === key ? 'category' : 'product';
			$.ajax(
				{
					url: mpca_obj.ajaxurl,
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
				}
			);
		}
		setFieldValue( wrap, value ) {
			const field = wrap.find( '.choicesdp-field' );
			if ( 'undefined' === typeof value ) {
				field.val( '' );
				return;
			}

			field.val( 'string' === typeof value ? value : value.join( ',' ) );
		}
	}
	new MPCAdminTable();
} )( jQuery, window, document );
