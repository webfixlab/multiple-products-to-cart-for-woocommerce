/**
 * Admin JavaScript
 *
 * @package Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since 1.0
 */

(function ($) {
	function show_followup(section){
		var field_id = section.data( 'field-id' );

		section.closest( '.mpcdp_container' ).find( '.mpcdp_settings_option' ).each(
			function () {
				if ( $( this ).data( 'depends-on' ) == field_id ) {
					$( this ).slideToggle( 'slow' );
				}
			}
		);
	}
	function toggle_button( btn ){
		var wrap = btn.closest( '.hurkanSwitch-switch-box' );

		wrap.find( '.hurkanSwitch-switch-item' ).each(
			function () {
				if ( $( this ).hasClass( 'active' ) ) {
					$( this ).removeClass( 'active' );
				} else {
					$( this ).addClass( 'active' );
				}
			}
		);

		btn.closest( '.mpcdp_settings_option' ).find( 'input[type="checkbox"]' ).trigger( 'click' );
		show_followup( btn.closest( '.mpcdp_settings_option' ) );
		wrap.toggleClass( 'switch-animated-off switch-animated-on', 1000 );
	}
	$( document ).ready(
		function () {
			// color picker.
			$( '.mpc-colorpicker' ).wpColorPicker();

			// set min height of sortable columns.
			function setMinHeight( from, to ){
				from = typeof from === 'undefined' ? $( '#' ) : from;
				fo   = typeof fo === 'undefined' ? $( '#' ) : fo;

				var height = to.find( 'li' )[0].offsetHeight;

				var fromMinHeight = from.find( 'li' ) ? ( from.find( 'li' ).length + 1 ) * height : height;
				var toMinHeight   = to.find( 'li' ) ? ( to.find( 'li' ).length + 1 ) * height : height;

				from.css( { 'min-height' : fromMinHeight + 'px' } );
				to.css( { 'min-height' : toMinHeight + 'px' } );
			}

			// column sortable.
			var has_colsort = $( 'body' ).find( '#active-mpc-columns' );
			if ( typeof has_colsort != 'undefined' && has_colsort.length > 0 ) {
				$( "#active-mpc-columns, #inactive-mpc-columns" ).sortable(
					{
						connectWith: '.connectedSortable',
						remove: function ( event, ui ) {
							if ( ui.item.hasClass( 'mpc-stone-col' ) ) {
								return false;
							}

							var itemTo   = ui.item.closest( 'ul' );
							var itemFrom = $( event.target );

							setMinHeight( itemFrom, itemTo );
						}
					}
				);
			}

			function column_sorting(){
				// make sequence of columns ( available and sorted ones ).
				var sequence = '';
				$( '#active-mpc-columns' ).find( 'li' ).each(
					function () {
						if ( sequence.length == 0 ) {
							sequence += $( this ).data( 'meta_key' );
						} else {
							sequence += ',' + $( this ).data( 'meta_key' );
						}
					}
				);
				$( '.mpc-sorted-cols' ).val( sequence );
			}

			// if one of the section is empty, do something as it can't be sorted later.
			$( "#active-mpc-columns, #inactive-mpc-columns" ).each(
				function () {
					if ( $( this ).find( 'li' ).length === 0 ) {
						$( this ).addClass( 'empty-cols' );
					}
				}
			);

			$( 'body' ).on(
				'click',
				'.mpcdp_submit_button',
				function (e) {
					column_sorting();
				}
			);

			function copy_text(element) {
				element.select();
				document.execCommand( 'copy' );
				window.getSelection().removeAllRanges();
			}
			$( '.mpc-opt-sc-btn.copy' ).on(
				'click',
				function () {
					copy_text( $( this ).closest( '.mpc-shortcode, .mpc-shortcode-item' ).find( 'textarea' ) );

					$( this ).find( '.dashicons' ).toggleClass( 'dashicons-admin-page dashicons-saved' );
					setTimeout(
						function () {
							$( this ).find( '.dashicons' ).toggleClass( 'dashicons-admin-page dashicons-saved' );
						},
						2000
					);
				}
			);

			$( 'body' ).on(
				'click',
				'.mpcex-disabled',
				function (e) {
					e.preventDefault();
					var title = $( this ).attr( 'title' );
					$( 'body' ).find( '#mpcpop .mpc-focus span' ).text( title );
					$( 'body' ).find( '#mpcpop' ).show();
				}
			);

			// for closing popup.
			$( '#mpcpop' ).on(
				'click',
				function () {
					$( 'body' ).find( '#mpcpop' ).hide();
				}
			);

			$( 'body' ).on(
				'click',
				'span.mpcpop-close',
				function () {
					$( 'body' ).find( '#mpcpop' ).hide();
				}
			);

			$( document ).on(
				'keyup',
				function ( e ) {
					if ( e.keyCode == 27 ) { // esc key.
						$( 'body' ).find( '#mpcpop' ).hide();
					}
				}
			);

			$( window ).on(
				'scroll',
				function () {
					$( '.mpcdp_sidebar_tabs.is-affixed .inner-wrapper-sticky' ).css( 'width', $( '.mpcdp_settings_sidebar' )[0].clientWidth + 'px' );

					var stickyOffset = $( '.mpcdp_sidebar_tabs' ).offset().top;

					if ( $( window ).scrollTop() > stickyOffset ) {
						$( '.mpcdp_sidebar_tabs' ).addClass( 'is-affixed' );
					} else {
						if ( $( '.mpcdp_sidebar_tabs' ).hasClass( 'is-affixed' ) ) {
							$( '.mpcdp_sidebar_tabs' ).removeClass( 'is-affixed' );
						}
					}
				}
			);

			$( window ).on(
				'resize',
				function () {
					$( '.mpcdp_sidebar_tabs.is-affixed .inner-wrapper-sticky' ).css( 'width', $( '.mpcdp_settings_sidebar' )[0].clientWidth + 'px' );
				}
			);

			$( '.hurkanSwitch-switch-item' ).on(
				'click',
				function (e) {
					// check if it's a pro feature.
					var input = $( this ).closest( '.mpcdp_settings_option' ).find( 'input[type="checkbox"]' );

					if ( input.hasClass( 'mpcex-disabled' ) && ! mpca_obj.has_pro ) {
						e.preventDefault();

						var title = input.attr( 'title' );
						$( 'body' ).find( '#mpcpop .mpc-focus span' ).text( title );
						$( 'body' ).find( '#mpcpop' ).show();
					} else {
						toggle_button( $( this ) );
					}
				}
			);

			$( 'input[name="wmc_redirect"]' ).on(
				'click',
				function () {
					var v = $( this ).val();

					var section  = $( this ).closest( '.mpcdp_settings_option' );
					var field_id = section.data( 'field-id' );

					section.closest( '.mpcdp_container' ).find( '.mpcdp_settings_option' ).each(
						function () {
							if ( $( this ).data( 'depends-on' ) == field_id && mpca_obj.has_pro ) {
								if ( v == 'custom' ) {
									if ( ! $( this ).is( ':visible' ) ) {
										$( this ).slideToggle( 'slow' );
									}
								} else {
									if ( $( this ).is( ':visible' ) ) {
										$( this ).slideToggle( 'slow' );
									}
								}
							}
						}
					);
				}
			);

			$( '.number-input' ).on(
				'keypress',
				function (e) {
					var field = $( this );

					if ( e.originalEvent.which < 48 || e.originalEvent.which > 57 ) {
						e.preventDefault();
						field.addClass( 'mpc-notice' );
					}

					setTimeout(
						function () {
							field.removeClass( 'mpc-notice' );
						},
						1000
					);
				}
			);

			function update_sc_input(wrap) {
				var val    = '';
				var values = [];

				wrap.find( '.choices__inner .choices__list .choices__item' ).each(
					function () {
						var itemValue = $( this ).attr( 'data-value' );
						if (values.indexOf( itemValue ) === -1) { // Ensure uniqueness.
							values.push( itemValue );
							if (val.length === 0) {
								val = itemValue;
							} else {
								val += ',' + itemValue;
							}
						}
					}
				);

				wrap.find( '.choicesdp-field' ).val( val );
			}

			$( 'body' ).find( '.mpc-sc-itembox' ).each(
				function () {
					var id = $( this ).attr( 'id' );

					if ( ! id || id.length === 0 ) {
						return; // true/none = continue, false = break.
					}

					var single = '', plural = '';
					if (id == 'cats') {
						single = 'category';
						plural = 'categories';
					} else {
						single = 'product';
						plural = 'products';
					}

					var params = {
						removeItemButton:      true,
						placeholder:           true,
						placeholderValue:      'Choose options',
						shouldSort:            false,
						itemSelectText:        'Select ' + id,
						duplicateItemsAllowed: false,
						searchEnabled:         false,
					};

					if ( id === 'ids' || id === 'selected' || id === 'skip_products' || id === 'cats' ) {
						params['searchEnabled']     = true;
						params['searchFields']      = ['label', 'value'];
						params['itemSelectText']    = 'Select ' + plural;
						params['noChoicesText']     = 'Type the ' + single + ' name';
						params['searchResultLimit'] = 50;
					}

					var type = new Choices( document.querySelector( '#' + id ), params );

					var debounceTimeout;

					$( '#' + id ).on(
						'search',
						function (event) {
							clearTimeout( debounceTimeout );
							type.setChoices( [{ value: '', label: 'Loading...' }], 'value', 'label', true );

							debounceTimeout = setTimeout(
								function () {
									var data = new FormData();
									data.append( 'action', 'mpc_admin_search_box' );
									data.append( 'search', event.detail.value );
									data.append( 'type_name', id );
									data.append( 'nonce', mpca_obj.nonce );

									$.ajax(
										{
											url: mpca_obj.ajaxurl,
											method: 'POST',
											data: data,
											dataType: 'json',
											processData: false,
											contentType: false,
											success: function (response) {
												type.clearChoices();
												type.setChoices( response, 'id', 'name', false );

												if (response.length === 0) {
													type.setChoices( [{ value: '', label: 'No ' + single + ' found.' }], 'value', 'label', true );
												}
											},
											error: function (jqXHR, textStatus, errorThrown) {
												type.setChoices( [{ value: '', label: 'Error fetching data.' }], 'value', 'label', true );
											}
										}
									);
								},
								1000
							);
						}
					);

					type.passedElement.element.addEventListener(
						'addItem',
						function (event) {
							update_sc_input( $( this ).closest( '.choicesdp' ) );
						}
					);

					type.passedElement.element.addEventListener(
						'removeItem',
						function (event) {
							update_sc_input( $( this ).closest( '.choicesdp' ) );
						}
					);
				}
			);

			$( '.mpcasc-reset' ).on(
				'click',
				function (e) {
					if ( ! confirm( 'Are you sure?' ) ) {
						e.preventDefault();
					}
				}
			);

			$( '.mpc-opt-sc-btn.delete' ).on(
				'click',
				function (e) {
					if ( ! confirm( 'Are you sure?' ) ) {
						e.preventDefault();
					}
				}
			);

			$( document ).on(
				'click',
				'#mpc-export',
				function (e) {
					e.preventDefault();
					const btn        = $( this );
					const noticeWrap = $( document ).find( '#export-success' );

					if (mpca_obj.has_pro && mpca_obj.has_pro === "1" ) {
						noticeWrap.toggle( 'slow' );
						$.ajax(
							{
								url: mpca_obj.ajaxurl,
								type: 'POST',
								data: {
									action: 'mpc_export_settings',
									security: mpca_obj.export_nonce,
								},
								success: function (response) {
									if (response.success) {
										const link    = document.createElement( 'a' );
										link.href     = response.data.file_url;
										link.download = 'mpc_export.json';
										link.click();

										noticeWrap.find( '.mpcdp_option_label' ).text( mpca_obj.export_ok );
										setTimeout(
											function () {
												noticeWrap.toggle();
												noticeWrap.find( '.mpcdp_option_label' ).text( mpca_obj.export_text );
											},
											3000
										);
									} else {
										alert( 'Export failed!' );
									}
								},
								complete: function () {
									btn.prop( 'disabled', false ).text( 'Export' );
								},
							}
						);
					}
				}
			);
		}
	);
}( jQuery ));
