/**
 * Frontend JavaScript
 *
 * @uses       mpc_frontend
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      1.0
 */

(function ($) {
	function mpc_loading_animation( way, elem ){
		var wrap = elem.closest( '.mpc-container' );

		// way = load or close.
		if ( way == 'load' ) {
			wrap.find( 'table' ).before( '<span class="mpc-loader"><img src="' + mpc_frontend.imgassets + 'loader.gif"></span>' );
		} else if ( way == 'close' ) {
			$( 'body' ).find( '.mpc-loader' ).remove();
		}
	}

	// get variation id.
	function mpc_get_variation_id( row, data ){
		var total_select = 0, actual_selected = 0, varid = 0;
		var row_data     = {};

		// collect current data.
		row.find( 'select' ).each(
			function () {
				total_select++;
				row_data[ $( this ).attr( 'class' ) ] = $( this ).find( 'option' ).filter( ':selected' ).attr( 'data-value' );
			}
		);

		// get variation id from data.
		for ( var vid in data ) {
			actual_selected = 0;
			for ( var att in data[vid]['attributes'] ) {
				if ( data[vid]['attributes'][att].length == 0 ) {
					actual_selected++;
				} else if ( typeof row_data[att] != 'undefined' ) {
					if ( data[vid]['attributes'][att] == row_data[att] ) {
						actual_selected++;
					}
				}
			}
			if ( actual_selected == total_select && total_select != 0 ) {
				varid = vid;
			}
		}
		return varid;
	}

	function mpc_is_variable_product( row ){
		// is variable - set to false.
		var f = false;

		// get all classes.
		var cls = row.prop( 'className' );
		if ( typeof cls == 'undefined' || cls.length == 0 ) {
			return false;
		}

		var csslist = cls.split( ' ' );

		csslist.forEach(
			function (css) {
				if ( css.indexOf( 'variable' ) != -1 ) {
						f = true;
				}
			}
		);

		return f;
	}

	// get quantity.
	function mpc_get_row_quantity( row ){
		var quantity = 1;

		var val = row.find( '.mpc-product-quantity input[type="number"]' ).val();

		if ( typeof val != 'undefined' ) {
			if ( val.length > 0 ) {
				var quantity = parseFloat( val );
			}
		} else {
			if ( typeof mpc_frontend.dqty != 'undefined' ) {
				if ( mpc_frontend.dqty.length > 0 ) {
					quantity = mpc_frontend.dqty;
				}
			}
		}

		return quantity;
	}

	function row_in_stock( row ){
		var data         = row.find( '.row-variation-data' ).data( 'variation_data' );
		var variation_id = mpc_get_variation_id( row, data );

		// flag for final in stock row or not.
		var instock = -1;

		if ( variation_id != 0 ) {
			if ( typeof data[variation_id]['stock_status'] != 'undefined' ) {
				if ( data[variation_id]['stock_status'] == 'outofstock' ) {
					instock = 1;
				} else if ( typeof data[variation_id]['stock'] != 'undefined' && data[variation_id]['stock'].length > 0 ) {
					instock = data[variation_id]['stock'];
				}
			}
		}

		return instock;
	}
	function row_stock_handler( row ){
		/**
		 * Get variation id | variation data
		 * Check if stock out | disable checkbox
		 * Possibly show outofstock error notice
		 */
		var instock = row_in_stock( row );

		row.find( '.out-of-stock, .stock' ).remove();

		if ( instock == -1 || instock == 1 ) {
			// disable checkbox + single add to cart.
			if ( row.find( '.mpc-product-select input' ).is( ':checked' ) ) {
				row.find( '.mpc-product-select input' ).trigger( 'click' );
			}

			if ( typeof row.find( '.mpce-single-add' ) != 'undefined' ) {
				row.find( '.mpce-single-add' ).prop( 'disabled', true );
			}

			if ( instock == 1 && row.find( '.mpc-def-stock' ).length > 0 ) {
				// out of stock.
				row.find( '.mpc-def-stock' ).html( mpc_frontend.outofstock_txt );
			}

			// quantity set to 0 and disable quantity field.
			if ( typeof row.find( 'input[type="number"]' ) != 'undefined' ) {
				row.find( 'input[type="number"]' ).val( 0 ).trigger( 'change' );
			}
			row.find( 'input[type="number"]' ).prop( 'disabled', true );

		} else {
			row.find( 'input[type="number"]' ).prop( 'disabled', false );

			// in stock, show stock and enable checkbox + single add.
			if ( typeof row.find( '.mpce-single-add' ) != 'undefined' ) {
				row.find( '.mpce-single-add' ).prop( 'disabled', false );
			}

			if ( row.find( 'input[type="number"]' ).length ) {
				if ( row.find( 'input[type="number"]' ).val() == '0' ) {
					row.find( 'input[type="number"]' ).val( 1 ).trigger( 'change' );
				}
			}

			row.find( '.mpc-def-stock' ).html( '<p class="stock in-stock">' + instock + '</p>' );
		}

		if ( instock == -1 || instock == 1 ) {
			return false;
		} else {
			return true;
		}
	}

	// handle pricing per row.
	function mpc_dynamic_pricing_for_row( row ){
		// initialize price and quantity.
		var price    = parseFloat( 0 ).toFixed( mpc_frontend.dp );
		var quantity = mpc_get_row_quantity( row );

		if ( mpc_is_variable_product( row ) ) {
			var data         = row.find( '.row-variation-data' ).data( 'variation_data' );
			var variation_id = mpc_get_variation_id( row, data );

			if ( variation_id != 0 ) {
				mpc_variation_image( row, data[variation_id] );
				mpc_row_price_handler( row, data[variation_id]['price'] );
				mpc_fixed_price_variable_product( row );

				mpc_handle_variation_description( row, data[variation_id]['desc'] );

				if ( mpc_if_all_options_selected( row ) && typeof data[variation_id]['price'] != 'undefined' ) {
					price = parseFloat( data[variation_id]['price'] );
				}

				// handle sku.
				if ( typeof row.find( '.mpc-product-sku' ) != 'undefined' && typeof data[variation_id]['sku'] != 'undefined' ) {
					row.find( '.mpc-def-sku' ).hide();
					row.find( '.mpc-var-sku' ).html( data[variation_id]['sku'] );
				}

				row.attr( 'data-variation_id', variation_id );
			} else {
				mpc_handle_variation_description( row, '' );

				row.attr( 'data-variation_id', 0 );
				// switch to default product image.
				row.find( '.mpc-product-image .mpcpi-wrap img' ).attr( 'src', row.find( '.mpc-product-image' ).data( 'pimg-thumb' ) );
				row.find( '.mpc-product-image .mpcpi-wrap img' ).attr( 'data-fullimage', row.find( '.mpc-product-image' ).data( 'pimg-full' ) );
				mpc_row_price_handler( row, '' );

				// sku.
				if ( typeof row.find( '.mpc-product-sku' ) != 'undefined' ) {
					row.find( '.mpc-def-sku' ).show();
					row.find( '.mpc-var-sku' ).html( '' );
				}

				// sku.
				if ( typeof row.find( '.mpc-stock' ) != 'undefined' ) {
					row.find( '.mpc-def-stock' ).show();
					row.find( '.mpc-var-stock' ).html( '' );
				}
			}
		} else {
			var type = row.data( 'type' );
			if ( type !== 'grouped' ) {
				price = row.data( 'price' );
			}
		}

		// if checkbox isn't checked, set price to 0.
		var checkbox = row.find( '.mpc-product-select input' );
		if ( typeof checkbox !== 'undefined' && checkbox.length > 0 && ! checkbox.is( ':checked' ) ) {
			price = parseFloat( 0 );
		}

		return {
			'price': parseFloat( price ),
			'quantity': quantity
		};
	}

	function mpc_render_gallery( item ){
		var row = item.closest( 'tr.cart_item' );

		var gallery = row.find( '.gallery-items' ).data( 'gallery' );
		if ( typeof gallery != 'undefined' ) {
			var html  = '';
			var found = false; // for active item | if product image is already in gallery.
			$.each(
				gallery,
				function ( k, v ) {
					var cls = '';
					if ( v.thumb == item.attr( 'src' ) && found == false ) {
						cls   = 'mpcgi-selected';
						found = true;
					}

					html += '<img class="' + cls + '" src="' + v.thumb + '" data-fullimage="' + v.full + '">';
				}
			);

			if ( typeof $( '#mpcpop .mpc-gallery' ) != 'undefined' && $( '#mpcpop .mpc-gallery' ).length > 0 ) {
				$( '#mpcpop .mpc-gallery' ).replaceWith( '<div class="mpc-gallery">' + html + '</div>' );
			} else {
				$( '#mpcpop' ).append( '<div class="mpc-gallery">' + html + '</div>' );
			}
		} else {
			if ( typeof $( '.mpc-gallery' ) != 'undefined' && $( '.mpc-gallery' ).length > 0 ) {
				$( '.mpc-gallery' ).remove();
			}
		}

	}

	/**
	 * Dynamic select all status handler
	 * Checks table status. If everythig checked, auto check the select all checkbox. Anything middle will not auto check it.
	 */
	function mpc_init_select_all( wrap ){
		var total = selected = 0;
		wrap.find( 'form .mpc-product-select input' ).each(
			function () {
				total++;
				if ( $( this ).is( ":checked" ) ) {
						selected++;
				}
			}
		);

		if ( selected == 0 && total > 0 && wrap.find( '.mpc-check-all' ).is( ':checked' ) ) {
			wrap.find( '.mpc-check-all' ).prop( 'checked', false );
			wrap.find( '.mpc-check-all' ).attr( 'data-state', 'not' );
		}

		if ( total == selected && selected > 0 ) {
			// check select all checkbox.
			wrap.find( '.mpc-check-all' ).prop( 'checked', true );
			wrap.find( '.mpc-check-all' ).attr( 'data-state', 'checked' );
		}
	}

	function mpc_get_atts( wrap ){
		var att_data = wrap.find( '.mpc-table-query' ).data( 'atts' );

		var atts = {};
		if ( typeof att_data != 'undefined' && typeof att_data == 'object' ) {
			$.each(
				att_data,
				function ( key, val ) {
					atts[key] = val;
				}
			);
		}

		$.each(
			mpc_frontend.key_fields,
			function ( shortcode_attr, attr_key ) {
				// Shortcode_attr | shortcode attribute key - attr_key | identifier | .something or #something.
				if ( typeof wrap.find( attr_key ) != 'undefined' && typeof wrap.find( attr_key ).val() != 'undefined' ) {
					var attr_value = wrap.find( attr_key ).val();

					if ( attr_value.length > 0 ) {
						if ( attr_value.indexOf( 'ASC' ) != -1 ) {
							attr_value = attr_value.replace( '-ASC', '' );
							atts.order = 'ASC';
						} else if ( attr_value.indexOf( 'DESC' ) != -1 ) {
							attr_value = attr_value.replace( '-DESC', '' );
							atts.order = 'DESC';
						}

						if ( attr_key.indexOf( 'mpcp-cat-filter' ) != -1 && typeof atts[ shortcode_attr ] == 'undefined' ) {
							atts.origin = 'dropdown_filter';
						}

						if ( attr_value != 'menu_order' ) {
							atts[ shortcode_attr ] = attr_value;
						}
					}
				}
			}
		);

		return atts;
	}

	function renderStickyHead(table){
		var min  = 99999;
		var html = '';
		table.find( 'thead th' ).each(
			function () {
				var th = $( this );
				if ( th.offset().left < min ) {
					min = th.offset().left;
				}

				html += `<th style="width:${th[0].offsetWidth}px;">${th.text()}</th>`;
			}
		);
		var width = table[0].offsetWidth;
		let wrap  = table.closest( '.mpc-container' );
		html      = `<table style="width:${width}px;"><thead><tr>${html}</tr></thead></table>`;
		html      = `<div class="mpc-fixed-header" style="left:${min}px;display:none;">${html}</div>`;
		wrap.find( '.mpc-fixed-header' ).remove();
		table.after( html );

		wrap.find( '.total-row' ).css( {'width': `${width}px`} );

		var header       = wrap.find( '.mpc-table-header' );
		width            = width < 401 ? '100%' : `${width}px`;
		var headerHeight = header[0].offsetHeight;
		headerHeight     = headerHeight > 100 ? 55 : headerHeight;
		header.css( {'left': `${min}px`, 'width' : width, 'min-height' : `${headerHeight}px`} );
	}
	function mpc_table_loader_response( wrapper, response ){
		var rp = JSON.parse( response );

		// if there is an error.
		if ( rp.status ) {
			wrapper.find( '.mpc-all-select, .mpc-table-footer' ).hide();
		} else {
			wrapper.find( '.mpc-all-select, .mpc-table-footer' ).show();
		}

		if ( rp.mpc_fragments ) {
			$.each(
				rp.mpc_fragments,
				function ( k, v ) {
					if ( typeof wrapper.find( v.key ) == 'undefined' || wrapper.find( v.key ).length == 0 ) {
						// element doesn't exists - add that.
						if ( typeof v.parent != 'undefined' ) {
							if ( typeof v.adding_type != 'undefined' ) {
								if ( v.adding_type == 'prepend' ) {
									wrapper.find( v.parent ).prepend( v.val );
								}
							}
						} else {
							wrapper.find( v.key ).replaceWith( v.val );
						}
					} else {
						wrapper.find( v.key ).replaceWith( v.val );
					}
				}
			);
		}

		wrapper.find( '.mpc-fixed-header' ).remove();
		renderStickyHead( wrapper.find( 'table' ) );

		// animate to table top.
		$( 'html, body' ).animate(
			{
				scrollTop: $( wrapper ).offset().top - 80
			},
			'slow'
		);

		// calculate total price.
		mpc_dynamic_product_pricing(); // table.

		// select all handler.
		mpc_init_select_all( wrapper );
	}

	// AJAX table loader.
	function ajax_table_loader( atts, page, wrapper ){
		$.ajax(
			{
				method: "POST",
				url: mpc_frontend.ajaxurl,
				data: {
					'action': 'mpc_ajax_table_loader',
					'page': page,
					'atts': atts,
					'table_nonce': mpc_frontend.table_nonce
				},
				async: 'false',
				dataType: 'html',
				success: function (response) {
					mpc_loading_animation( 'close', wrapper );

					// Empty response - return.
					if (response.length == 0) {
						wrapper.find( '.mpc-pageloader' ).html( '' );
						return;
					}

					mpc_table_loader_response( wrapper, response );
				},
				error: function (errorThrown) {
					console.log( errorThrown );
				}
			}
		);
	}
	// AJAX table loader pre processing.
	function mpc_table_loader_request(page, wrapper) {
		mpc_loading_animation( 'load', wrapper );

		var atts = mpc_get_atts( wrapper );

		ajax_table_loader( atts, page, wrapper );
	}

	// mpc pagination loader.
	function mpc_pagination_loader( elm ){
		var wrap = elm.closest( '.mpc-container' );
		var page = parseInt( elm.text() );

		mpc_table_loader_request( page, wrap );
	}

	// mpc order by table.
	function mpc_order_by_table( elm ){
		var wrapper = elm.closest( '.mpc-container' );

		// check if ordering dropdown option has value.
		mpc_table_loader_request( 1, wrapper );
	}

	// show notification - message for user.
	function mpc_notify( table, type, msg ){
		var html = '';
		if ( type == 'error' ) {
			html = '<p class="woo-err woocommerce-error">' + msg + '</p>';
		}

		if ( table.find( '.woo-notices' ).length > 0 ) {
			table.find( '.woo-notices' ).html( html );
		} else {
			table.prepend( '<div class="woo-notices mpc-notice">' + html + '</div>' );
		}

		$( 'html, body' ).animate(
			{
				scrollTop: $( table ).offset().top - 60
			},
			'slow'
		);

		setTimeout(
			function () {
				$( 'body' ).find( '.mpc-popify' ).remove();
			},
			2000
		);

		setTimeout(
			function () {
				table.find( '.woo-notices' ).remove();
			},
			5000
		);
	}

	// single add to cart.
	$( 'body' ).on(
		'click',
		'.mpce-single-add',
		function (e) {
			e.preventDefault();
			var row  = $( this ).closest( 'tr.cart_item' );
			var wrap = $( this ).closest( '.mpc-container' );

			var data = mpc_get_add_to_cart_row_data( row, true );
			if ( ! data || ( typeof data == 'object' && $.isEmptyObject( data ) ) ) {
				mpc_notify( wrap, 'error', mpc_frontend.blank_submit );
			} else {
				mpc_request_ajax_add_to_cart( wrap, data );
			}

		}
	);

	// ajax search.
	var searchTimer;
	$( 'body' ).on(
		'input',
		'.mpc-asearch input',
		function () {
			var input = $( this );
			var s     = input.val();

			if ( s.length ) {
				input.closest( '.mpc-asearch' ).find( '.mpc-close-search' ).show();
			} else {
				input.closest( '.mpc-asearch' ).find( '.mpc-close-search' ).hide();
			}

			clearTimeout( searchTimer );

			searchTimer = setTimeout(
				function () {
					if ( s.length === 0 || s.length >= 3 ) {
						mpc_order_by_table( input );
					}
				},
				1000
			);
		}
	);
	$( 'body' ).on(
		'click',
		'.mpc-close-search',
		function () {
			$( this ).closest( '.mpc-asearch' ).find( 'input' ).val( '' ).trigger( 'change' );
			$( this ).hide();
		}
	);

	// ajax category filter.
	$( 'body' ).on(
		'change',
		'.mpcp-cat-filter select',
		function () {
			mpc_order_by_table( $( this ) );
		}
	);

	$( 'body' ).on(
		'click',
		'.mpc-cart-messege',
		function () {
			if ( typeof $( 'body' ).find( '.mpc-cart-message' ) != 'undefined' ) {
				$( 'body' ).find( '.mpc-cart-messege' ).remove();
			}
		}
	);

	/**
	 * User Events section
	 * On popup box clicked, hide it
	 */
	$( '#mpcpop' ).on(
		'click',
		function (e) {
			if ( e.target.tagName.toLowerCase() == 'img' ) {
				$( '#mpcpop .image-wrap img' ).attr( 'src', $( e.target ).attr( 'data-fullimage' ) );
				$( '#mpcpop .mpc-gallery img' ).removeClass( 'mpcgi-selected' );
				$( e.target ).addClass( 'mpcgi-selected' );
			} else {
				$( '#mpcpop' ).hide()
			}
		}
	);








	


	$( 'body' ).on(
		'click',
		'.mpc-product-image .moregallery',
		function () {
			mpc_image_popup_loader( $( this ).closest( '.gallery-item' ).find( 'img' ) );
		}
	);

	// ajax pagination loader.
	$( 'body' ).on(
		'click',
		'.mpc-pagenumbers span',
		function () {
			if ( ! $( this ).hasClass( 'current' ) ) {
				mpc_pagination_loader( $( this ) );
			}
		}
	);

	// table order by option change event.
	$( 'body' ).on(
		'change',
		'.mpc-orderby',
		function () {
			mpc_order_by_table( $( this ) );
		}
	);

	// select all handler.
	function handle_row_select( row, do_select ){
		var type = row.data( 'type' );

		var input = row.find( 'input[type="checkbox"]' );
		if ( typeof input === 'undefined' || input.length === 0 ) {
			return;
		}

		var input_checked = input.is( ':checked' );

		if ( type === 'variable' && do_select && ! mpc_if_all_options_selected( row ) ) {
			return;
		}

		if ( ( input_checked && do_select ) || ( ! input_checked && ! do_select ) ) {
			return;
		}

		input.trigger( 'click' );
	}

	// reset form.
	$( 'body' ).on(
		'click',
		'.mpc-reset-table',
		function () {
			window.location.reload();
		}
	);

	// on category clicked, filter that category products instead of going to archive page.
	function table_loader_by_tag( id, type, wrapper ){
		var atts   = mpc_get_atts( wrapper );
		atts[type] = id;

		ajax_table_loader( atts, 1, wrapper );
	}
	$( document ).on(
		'click',
		'.mpc-product-category a, .mpc-product-tag a, .mpc-subheader-info a',
		function (e) {
			e.preventDefault();
			var id      = parseInt( $( this ).data( 'id' ) );
			var wrapper = $( this ).closest( '.mpc-container' );

			var type   = $( this ).closest( 'td' ).hasClass( 'mpc-product-category' ) ? 'cats' : $( this ).closest( 'td' ).hasClass( 'mpc-product-tag' ) ? 'tags' : '';
			var select = 'cats' === type ? wrapper.find( '.mpc-cat-filter' ) : wrapper.find( '.mpc-tag-filter' );
			if ( typeof select !== 'undefined' && select.length > 0 ) {
				select.val( id ).trigger( 'change' );
				return;
			}

			table_loader_by_tag( id, type, wrapper );
		}
	);

	$( 'body' ).on(
		'change',
		'.mpcp-tag-filter select',
		function () {
			table_loader_by_tag( parseInt( $( this ).find( 'option:selected' ).val() ), 'tags', $( this ).closest( '.mpc-container' ) );
		}
	);
})( jQuery );
