/**
 * Frontend JavaScript
 *
 * @uses       mpc_frontend
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      1.0
 */

(function($) {
	function mpc_loading_animation( way, elem ){
		var wrap = elem.closest( '.mpc-container' );

		// way = load or close.
		if ( way == 'load' ) {
			wrap.find( 'table' ).before( '<span class="mpc-loader"><img src="' + mpc_frontend.imgassets + 'loader.gif"></span>' );
		} else if ( way == 'close' ) {
			$( 'body' ).find( '.mpc-loader' ).remove();
		}
	}

	// for fixed price variable product - handle it's price.
	function mpc_fixed_price_variable_product( row ){
		if ( row.find( '.mpc-range' ).text() == row.find( '.mpc-single-price' ).text() ) {
			row.find( '.mpc-single-price' ).hide();
		}
		if ( typeof row.find( '.mpc-range ins' ).text() != 'undefined' ) {
			if ( row.find( '.mpc-range ins' ).text() == row.find( '.mpc-single-price' ).text() ) {
				row.find( '.mpc-single-price' ).hide();
			}
		}
	}

	// show specific image or default woocommerce one.
	function mpc_variation_image( row, data ){
		var img = data['image'];
		if ( img['full'].indexOf( 'woocommerce-placeholder' ) != -1 ) {
			row.find( '.mpc-product-image .mpcpi-wrap img' ).attr( 'src', row.find( '.mpc-product-image' ).data( 'pimg-thumb' ) );
			row.find( '.mpc-product-image .mpcpi-wrap img' ).attr( 'data-fullimage', row.find( '.mpc-product-image' ).data( 'pimg-full' ) );
		} else {
			row.find( '.mpc-product-image .mpcpi-wrap img' ).attr( 'src', img['thumbnail'] );
			row.find( '.mpc-product-image .mpcpi-wrap img' ).attr( 'data-fullimage', img['full'] );
		}
	}

	// get variation id.
	function mpc_get_variation_id( row, data ){
		var total_select = 0, actual_selected = 0, varid = 0;
		var row_data     = {};

		// collect current data.
		row.find( 'select' ).each(
			function(){
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

	// shows specific price for the selected option.
	function mpc_row_price_handler( row, price ){
		if ( typeof price == 'undefined' ) {
			row.find( '.mpc-single-price span.total-price' ).text( '' );
		} else {
			row.find( '.mpc-single-price' ).show();
			row.find( '.mpc-single-price span.total-price' ).text( price );
			if ( ! row.find( '.mpc-product-price' ).hasClass( 'mpc-single-active' ) ) {
				row.find( '.mpc-product-price' ).addClass( 'mpc-single-active' );
			}
		}
		if ( row.find( '.mpc-single-price span.total-price' ).text().length == 0 ) {
			row.find( '.mpc-single-price' ).hide();
			if ( row.find( '.mpc-product-price' ).hasClass( 'mpc-single-active' ) ) {
				row.find( '.mpc-product-price' ).removeClass( 'mpc-single-active' );
			}
		}
	}

	function mpc_is_variable_product( row ){
		// is variable - set to false.
		var f = false;

		// get all classes.
		var cls = row.prop( 'className' );
		if( typeof cls == 'undefined' || cls.length == 0 ){
			return false;
		}

		var csslist = cls.split( ' ' );

		csslist.forEach( function(css){
			if ( css.indexOf( 'variable' ) != -1 ) {
				f = true;
			}
		});

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

	// update variation short description.
	function mpc_handle_variation_description( row, desc ){

		// wheather to add variation description or not.
		var add_desc = false;

		if ( typeof desc != 'undefined' && desc.length > 0 ) {
			// update flag | add description.
			add_desc = true;
		}

		if ( add_desc === false ) {
			// remove description.
			row.find( '.mpc-var-desc' ).remove();

		} else {
			// add description.
			if ( row.find( '.mpc-var-desc' ) != 'undefined' && row.find( '.mpc-var-desc' ).length > 0 ) {
				// update.
				row.find( '.mpc-var-desc' ).replaceWith( '<p class="mpc-var-desc">' + desc + '</p>' );
			} else {
				// add.
				row.find( '.mpc-product-variation' ).append( '<p class="mpc-var-desc">' + desc + '</p>' );
			}
		}
	}

	function row_in_stock( row ){
		var data         = row.find( '.row-variation-data' ).data( 'variation_data' );
		var variation_id = mpc_get_variation_id( row, data );
		
		// flag for final in stock row or not.
		var instock = -1;

		if ( variation_id != 0 ) {
			if( typeof data[variation_id]['stock_status'] != 'undefined' ){
				if( data[variation_id]['stock_status'] == 'outofstock' ){
					instock = 1;
				}else if( typeof data[variation_id]['stock'] != 'undefined' && data[variation_id]['stock'].length > 0 ){
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

		if( instock == -1 || instock == 1 ){
			// disable checkbox + single add to cart
			if ( row.find( '.mpc-product-select input' ).is( ':checked' ) ) {
				row.find( '.mpc-product-select input' ).trigger( 'click' );
			}

			if ( typeof row.find( '.mpce-single-add' ) != 'undefined' ) {
				row.find( '.mpce-single-add' ).prop( 'disabled', true );
			}

			if( instock == 1 && row.find( '.mpc-stock' ).length > 0 ){
				// out of stock
				row.find( '.mpc-stock' ).html( mpc_frontend.outofstock_txt );
			}

			// quantity set to 0 and disable quantity field.
			if( typeof row.find( 'input[type="number"]' ) != 'undefined' ){
				row.find( 'input[type="number"]' ).val( 0 ).trigger( 'change' );
			}
			row.find( 'input[type="number"]' ).prop( 'disabled', true );
			
		}else{
			row.find( 'input[type="number"]' ).prop( 'disabled', false );

			// in stock, show stock and enable checkbox + single add
			row.find( '.mpc-def-stock' ).hide();

			if ( typeof row.find( '.mpce-single-add' ) != 'undefined' ) {
				row.find( '.mpce-single-add' ).prop( 'disabled', false );
			}

			if( row.find( 'input[type="number"]' ).length ){
				if( row.find( 'input[type="number"]' ).val() == '0' ){
					row.find( 'input[type="number"]' ).val( 1 ).trigger( 'change' );
				}
			}

			row.find( '.mpc-stock' ).html( instock );
		}

		if( instock == -1 || instock == 1 ){
			return false;
		}else{
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
					price = parseFloat( data[variation_id]['price'].toString().replace( /[^\d\.\-]/g, "" ) );
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
			if( type !== 'grouped' ){
				price = row.data( 'price' );
			}
		}

		// if checkbox isn't checked, set price to 0.
		var checkbox = row.find( '.mpc-product-select input' );
		if( typeof checkbox !== 'undefined' && checkbox.length > 0 && ! checkbox.is( ':checked' ) ){
			price = parseFloat( 0 );
		}

		return {
			'price': parseFloat( price ),
			'quantity': quantity
		};
	}

	// dynamic total price calculation.
	function mpc_dynamic_product_pricing(){
		$( 'body' ).find( 'form.mpc-cart' ).each( function(){
			var table = $( this );
			var total = 0.0;

			// find if at least one checkbox is checked.
			var checked = 0;

			table.find( '.cart_item' ).each( function(){
				var value = mpc_dynamic_pricing_for_row( $( this ) );

				total = total + value['price'] * value['quantity'];

				if ( $( this ).find( '.mpc-product-select input[type="checkbox"]' ).is( ':checked' ) ) {
					checked++;
				}
			});

			table.find( '.mpc-total span.total-price' ).text( parseFloat( total ).toFixed( mpc_frontend.dp ) );

			// floating total.
			var wrap = table.closest( '.mpc-container' );
			if ( checked == 0 ) {
				wrap.find( '.mpc-floating-total' ).removeClass( 'active' );
			} else {
				wrap.find( '.mpc-floating-total' ).addClass( 'active' );
			}
			wrap.find( '.mpc-floating-total span.total-price' ).text( parseFloat( total ).toFixed( mpc_frontend.dp ) );
		});
	}

	// check if all options are selected for a variable product.
	function mpc_if_all_options_selected( row ){
		var total = selected = 0;
		
		row.find( 'select' ).each( function(){
			total++;
			if ( $( this ).val().length > 0 ) {
				selected++;
			}
		});

		if ( total == selected && total != 0 ) {
			return true;
		} else {
			return false;
		}
	}

	function mpc_render_gallery( item ){
		var row = item.closest( 'tr.cart_item' );

		var gallery = row.find( '.gallery-items' ).data( 'gallery' );
		if ( typeof gallery != 'undefined' ) {
			var html  = '';
			var found = false; // for active item | if product image is already in gallery.
			$.each( gallery, function( k, v ){
				var cls = '';
				if ( v.thumb == item.attr( 'src' ) && found == false ) {
					cls   = 'mpcgi-selected';
					found = true;
				}

				html += '<img class="' + cls + '" src="' + v.thumb + '" data-fullimage="' + v.full + '">';
			});

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
	// load image popup.
	function mpc_image_popup_loader( item ){
		var row  = item.closest( 'tr.cart_item' );
		var link = item.attr( 'data-fullimage' );

		if ( typeof link != 'undefined' && link.length > 0 ) {
		} else {
			link = item.attr( 'data-fullimage' );
		}

		var mpop = $( '#mpcpop' );
		mpop.find( 'img' ).attr( 'src', link );

		if ( typeof image_src != 'undefined' && image_src != '' ) {
			mpop.find( 'img' ).attr( 'src', image_src );
		}

		// handle gallery.
		mpc_render_gallery( item );

		mpop.show();
	}

	/**
	 * Dynamic select all status handler
	 * Checks table status. If everythig checked, auto check the select all checkbox. Anything middle will not auto check it.
	 */
	function mpc_init_select_all( wrap ){
		var total = selected = 0;
		wrap.find( 'form .mpc-product-select input' ).each( function(){
			total++;
			if ( $( this ).is( ":checked" ) ) {
				selected++;
			}
		});

		if ( selected == 0 && total > 0 && wrap.find( '.mpc-check-all' ).is( ':checked' ) ) {
			console.log( 'selected 0 and checked, uncheck' );
			wrap.find( '.mpc-check-all' ).prop( 'checked', false );
			wrap.find( '.mpc-check-all' ).attr( 'data-state', 'not' );
		}

		if ( total == selected && selected > 0 ) {
			console.log( 'total = selected and selected > 0. so checking' );
			// check select all checkbox.
			wrap.find( '.mpc-check-all' ).prop( 'checked', true );
			wrap.find( '.mpc-check-all' ).attr( 'data-state', 'checked' );
		}
	}

	function mpc_get_atts( wrap ){
		var att_data = wrap.find( '.mpc-table-query' ).data( 'atts' );

		var atts = {};
		if ( typeof att_data != 'undefined' && typeof att_data == 'object' ) {
			$.each( att_data, function( key, val ){
				atts[key] = val;
			});
		}

		$.each( mpc_frontend.key_fields, function( shortcode_attr, attr_key ){
			// Shortcode_attr | shortcode attribute key - attr_key | identifier | .something or #something
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
		});

		return atts;
	}

	function mpc_table_loader_response( wrapper, response ){
		var rp = JSON.parse( response );

		// if there is an error
		if( rp.status ){
			wrapper.find( '.mpc-all-select, .mpc-table-footer' ).hide();
		}else{
			wrapper.find( '.mpc-all-select, .mpc-table-footer' ).show();
		}

		if ( rp.mpc_fragments ) {
			$.each( rp.mpc_fragments, function( k, v ){
				if ( typeof wrapper.find( v.key ) == 'undefined' || wrapper.find( v.key ).length == 0 ) {
					// element doesn't exists - add that.
					if ( typeof v.parent != 'undefined' ) {
						if ( typeof v.adding_type != 'undefined' ) {
							if ( v.adding_type == 'prepend' ) {
								wrapper.find( v.parent ).prepend( v.val );
							}
						}
					}else{
						wrapper.find( v.key ).replaceWith( v.val );
					}
				} else {
					wrapper.find( v.key ).replaceWith( v.val );
				}
			});
		}

		// animate to table top.
		$( 'html, body' ).animate({
			scrollTop: $( wrapper ).offset().top - 80
		}, 'slow' );

		// calculate total price.
		mpc_dynamic_product_pricing(); // table.

		// select all handler.
		mpc_init_select_all( wrapper );
	}

	// AJAX table loader
	function ajax_table_loader( atts, page, wrapper ){
		$.ajax({
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
			success: function(response) {
				mpc_loading_animation('close', wrapper);

				// Empty response - return.
				if (response.length == 0) {
					wrapper.find('.mpc-pageloader').html('');
					return;
				}

				mpc_table_loader_response(wrapper, response);
			},
			error: function(errorThrown) {
				console.log(errorThrown);
			}
		});
	}
	// AJAX table loader pre processing.
	function mpc_table_loader_request(page, wrapper) {
		mpc_loading_animation('load', wrapper);

		var atts = mpc_get_atts(wrapper);

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

		$( 'html, body' ).animate({
			scrollTop: $( table ).offset().top - 60
		}, 'slow' );

		setTimeout( function(){
			$( 'body' ).find( '.mpc-popify' ).remove();
		}, 2000 );

		setTimeout( function(){
			table.find( '.woo-notices' ).remove();
		}, 5000 );
	}

	function mpc_get_add_to_cart_row_data( row, is_single ){
		var data = {};

		var chk      = qty = 0;
		var quantity = 1;

		// check if checkbox is checked for adding.
		if ( typeof row.find( 'input[type="checkbox"]' ) != 'undefined' && row.find( 'input[type="checkbox"]' ).length > 0 && ! is_single ) {
			chk++;

			if ( row.find( 'input[type="checkbox"]' ).is( ':checked' ) ) {
				chk++;
			}
		}

		// if quantity box exists.
		if ( typeof row.find( 'input[type="number"]' ) != 'undefined' && row.find( 'input[type="number"]' ).length > 0 ) {
			qty++;

			if ( row.find( 'input[type="number"]' ).val().length > 0 && parseInt( row.find( 'input[type="number"]' ).val() ) > 0 ) {
				qty++;
				quantity = parseInt( row.find( 'input[type="number"]' ).val() );
			}
		}

		if ( ( chk == 0 || chk == 2 ) && ( qty == 0 || qty == 2 ) ) {
			var id = varid = 0;

			// product id.
			id = parseInt( row.attr( 'data-id' ) );

			// if exists or has value, get variation id.
			if ( typeof row.attr( 'data-variation_id' ) != 'undefined' && row.attr( 'data-variation_id' ).length > 0 ) {
				varid = parseInt( row.attr( 'data-variation_id' ) );
			}

			data[ id ]             = {};
			data[ id ]['quantity'] = quantity;
			data[ id ]['type']     = row.attr( 'data-type' );

			if ( mpc_is_variable_product( row ) ) {
				has_variation = true;

				var tt = ss = 0;
				row.find( 'select' ).each( function(){
					tt++;

					if ( $( this ).val().length > 0 ) {
						ss++;
					}
				});

				if ( tt == ss && tt != 0 ) {
					data[ id ]['variation_id'] = varid;
					data[ id ]['attributes']   = {};

					row.find( 'select' ).each( function(){
						data[id]['attributes'][ $( this ).attr( 'data-attribute_name' ) ] = $( this ).find( 'option:selected' ).val();
					});
				} else {
					// mpc_notify( row.closest( '.mpc-container' ), 'error', mpc_frontend.missed_option );
					return false;
				}
			}
		} else {
			return false;
		}

		return data;
	}

	// validate form/table before sending or adding to cart request.
	function mpc_get_add_to_cart_request( wrap ){
		var data = {};

		wrap.find( 'tr.cart_item' ).each( function(){
			var t = mpc_get_add_to_cart_row_data( $( this ), false );
			if ( t == false ) {
				return; // continue, return false - break;.
			}

			$.each( t, function( id, d ){
				data[ id ] = d;
			});
		});

		if ( $.isEmptyObject( data ) ) {
			mpc_notify( wrap, 'error', mpc_frontend.blank_submit );
			return false;
		}
		return data;
	}

	// handle response after AJAX added to cart.
	function mpcajx_add_to_cart_response( table, response ){

		// $( '.cart_totals' ).replaceWith( html_str );
		$( document.body ).trigger( 'updated_cart_totals' );

		table.find( '.mpc-button a.mpc-loading' ).remove();
		table.find( '.mpc-button input[type="submit"]' ).show();

		if ( response.fragments ) {
			$.each( response.fragments, function( key, value ) {
				$( key ).replaceWith( value );
			});
		}

		$( 'body' ).find( '.mpc-cart-messege' ).remove();

		var popup  = '';
		var notice = '';

		if ( response.cart_message ) {
			popup  = '<div class="woocommerce-message" role="alert">' + response.cart_message + '</div>';
			notice = '<div class="woocommerce-message" role="alert">' + response.cart_message + '</div>';
		}

		if ( response.error_message ) {
			popup  += '<div class="woo-err woocommerce-error" role="alert">' + response.error_message + '</div>';
			notice += '<ul class="woocommerce-error" role="alert"><li>' + response.error_message + '</li></ul>';
		}

		// add popup.
		$( 'body' ).append( '<div class="mpc-popup mpc-popify mpc-cart-messege"><div class="woocommerce">' + popup + '</div></div>' );
 
		// add table notice.
		table.closest( '.mpc-container' ).prepend( '<div class="woocommerce-notices-wrapper mpc-cart-messege">' + notice + '</div>' );

		setTimeout( function(){
			$( 'body' ).find( '.mpc-popify' ).remove();
			table.find( 'input[type="checkbox"]' ).each( function(){
				if ( $( this ).is( ':checked' ) ) {
					$( this ).trigger( 'click' );
				}
			});
		}, 2000 );

		setTimeout( function(){
			table.find( '.mpc-cart-messege' ).remove();
		}, 7000 );

	}

	// Example: Dispatching an 'added_to_cart' event to update the mini-cart
	const updateMiniCart = () => {
		const event = new CustomEvent('wc-blocks_added_to_cart', {
			bubbles    : true,
			cancelable : true,
		});
		
		document.body.dispatchEvent(event);
	};

	function mpc_request_ajax_add_to_cart( wrap, data ){
		// remove loading animation.
		mpc_loading_animation( 'load', wrap );
		$.ajax({
			method : "POST",
			url    : mpc_frontend.ajaxurl,
			data   : {
				'action'         : 'mpc_ajax_add_to_cart',
				'mpca_cart_data' : data,
				'cart_nonce'     : mpc_frontend.cart_nonce
			},
			success:function(response) {
				// remove loading animation.
				mpc_loading_animation( 'close', wrap );
				mpcajx_add_to_cart_response( wrap, response );

				// Call this function whenever you need to trigger a mini-cart update
				updateMiniCart();
			},
			error: function(errorThrown){
				console.log( errorThrown );
			}
		});

	}

	// table add to cart method.
	function mpc_table_add_to_cart( item, e ){
		var wrap = item.closest( '.mpc-container' );
		var data = mpc_get_add_to_cart_request( wrap );

		if ( ! data || ( typeof data == 'object' && $.isEmptyObject( data ) ) ) {
			e.preventDefault();
			return '';
		}

		if ( mpc_frontend.redirect_url == 'ajax' ) {
			e.preventDefault();
			mpc_request_ajax_add_to_cart( wrap, data );
		} else {
			var d = JSON.stringify( data );
			wrap.find( 'input[name="mpc_cart_data"]' ).val( d );
		}
	}

	// floating add to cart button clicked event.
	$( '.mpc-floating-total .float-label' ).on( 'click', function(e){
		var wrap = $( this ).closest( '.mpc-container' );
		wrap.find( '.mpc-cart .mpc-add-to-cart' ).trigger( 'click' );
	});

	// table add to cart button clicked event.
	$( 'body' ).on( 'click', '.mpc-cart .mpc-add-to-cart', function(e){
		mpc_table_add_to_cart( $( this ), e );
	});

	// single add to cart.
	$( 'body' ).on( 'click', '.mpce-single-add', function(e){
		e.preventDefault();
		var row  = $( this ).closest( 'tr.cart_item' );
		var wrap = $( this ).closest( '.mpc-container' );

		var data = mpc_get_add_to_cart_row_data( row, true );
		if ( ! data || ( typeof data == 'object' && $.isEmptyObject( data ) ) ) {
			mpc_notify( wrap, 'error', mpc_frontend.blank_submit );
		}else{
			mpc_request_ajax_add_to_cart( wrap, data );
		}

	});

	// ajax search.
	var searchTimer;
	$('body').on('input', '.mpc-asearch input', function () {
		var input = $(this);
		var s = input.val();
		
		clearTimeout(searchTimer);

		searchTimer = setTimeout(function () {
			if( s.length === 0 || s.length >= 3 ){
				mpc_order_by_table(input);
			}
		}, 1000);
	});
	

	// ajax category filter.
	$( 'body' ).on( 'change', '.mpcp-cat-filter select', function(){
		mpc_order_by_table( $( this ) );
	});

	$( 'body' ).on( 'click', '.mpc-cart-messege', function(){
		if ( typeof $( 'body' ).find( '.mpc-cart-message' ) != 'undefined' ) {
			$( 'body' ).find( '.mpc-cart-messege' ).remove();
		}
	});

	/**
	 * One time section after table load
	 * Handle dynamic all select checkbox status at page load
	 */
	$( 'body' ).find( '.mpc-container' ).each( function(){
		mpc_init_select_all( $( this ) );
	});

	// on document load, calculate price.
	mpc_dynamic_product_pricing(); // table.

	/**
	 * User Events section
	 * On popup box clicked, hide it
	 */
	$( '#mpcpop' ).on( 'click', function(e){
		if ( e.target.tagName.toLowerCase() == 'img' ) {
			$( '#mpcpop .image-wrap img' ).attr( 'src', $( e.target ).attr( 'data-fullimage' ) );
			$( '#mpcpop .mpc-gallery img' ).removeClass( 'mpcgi-selected' );
			$( e.target ).addClass( 'mpcgi-selected' );
		} else {
			$( '#mpcpop' ).hide()
		}
	});

	// on close button of popup box clicked, hide it.
	$( 'body' ).on( 'click', 'span.mpcpop-close', function(){
		$( '#mpcpop' ).hide();
	});

	// variation option clear button handler.
	function variation_clear_button( row ){
		// check if all select boxes are empty. If yes, remove clear button.
		var has_value = false;

		row.find( 'select' ).each( function(){
			if ( $( this ).val().length > 0 ) {
				has_value = true;
			}
		});

		if ( has_value == false ) {
			row.find( '.clear-button' ).html( '' );
		} else {
			row.find( '.clear-button' ).html( '<a class="reset_variations" href="#">' + mpc_frontend.reset_var + '</a>' );
		}
	}

	// on variation option change event.
	$( 'body' ).on( 'change', 'table.mpc-wrap select', function(){
		var row = $( this ).closest( 'tr.cart_item' );

		// for stock handling purpose.
		if( row_stock_handler( row ) ) {
			
			// trigger automatic checkbox check if all options are selected (irrespective of their values).
			if ( mpc_if_all_options_selected( row ) ) {
				var chk = row.find( 'input[type="checkbox"]' );
				chk.prop( 'checked', true );
			}
			
			mpc_dynamic_product_pricing(); // row.
		}

		// clear variations button handler.
		variation_clear_button( row );
	});

	// on variation clear button click event.
	$( 'body' ).on( 'click', 'a.reset_variations', function(e){
		e.preventDefault();

		// remove all selected values of variation dropdowns.
		var row = $( this ).closest( '.mpc-product-variation' );
		row.find( 'select' ).each( function(){
			$( this ).val( '' );
			$( this ).trigger( 'change' );
		});

		row.find( '.mpc-var-desc' ).empty();
	});

	function quantity_handler( row ){
		var qty = parseInt( row.find( 'input[type="number"]' ).val() );

		// filter only integer.
		if( qty < 0 ){
			row.find( 'input[type="number"]' ).val( 0 );
		}

		if( mpc_is_variable_product( row ) ){
			// variable product.
			var instock = row_in_stock( row );
			if( instock != -1 && instock != 1 ){
				// has stock quantity.
				instock = instock.replace( /[A-Za-z]/g, '' );
				instock = instock.replace( ' ', '' );
				
				if( instock.length > 0 ){
					var stock = parseInt( instock );
					if( qty > stock ){
						row.find( 'input[type="number"]' ).val( stock );
					}
				}
			}
		}else{
			// simple product.
			var stock = row.attr( 'stock' );

			if( typeof stock != 'undefined' && stock.length > 0 ){
				stock = parseInt( stock );

				if( qty > stock ){
					row.find( 'input[type="number"]' ).val( stock );
				}
			}
		}
	}
	// on quantity change event, calculate total price.
	$( 'body' ).on(
		'change paste keyup cut select', '.mpc-product-quantity input[type="number"]',
		function(){
			quantity_handler( $(this).closest( 'tr.cart_item' ) );

			mpc_dynamic_product_pricing(); // row.
		}
	);

	// on add to cart checkbox checked event, calculate total price.
	$( 'body' ).on( 'click', 'input[type="checkbox"]', function(){
		mpc_dynamic_product_pricing(); // row.
	});

	// on click image, show image popup.
	$( 'body' ).on( 'click', '.mpc-product-image img', function(){
		mpc_image_popup_loader( $( this ) );
	});
	$( 'body' ).on( 'click', '.mpc-product-image .moregallery', function(){
		mpc_image_popup_loader( $( this ).closest( '.gallery-item' ).find( 'img' ) );
	});

	// ajax pagination loader.
	$( 'body' ).on( 'click', '.mpc-pagenumbers span', function(){
		if ( ! $( this ).hasClass( 'current' ) ) {
			mpc_pagination_loader( $( this ) );
		}
	});

	// table order by option change event.
	$( 'body' ).on( 'change', '.mpc-orderby', function(){
		mpc_order_by_table( $( this ) );
	});

	// select all handler.
	function handle_row_select( row, do_select ){
		var type = row.data( 'type' );

		var input = row.find( 'input[type="checkbox"]' );
		if( typeof input === 'undefined' || input.length === 0 ){
			return;
		}

		var input_checked = input.is( ':checked' );

		if( type === 'variable' && do_select && ! mpc_if_all_options_selected( row ) ){
			return;
		}

		if( ( input_checked && do_select ) || ( ! input_checked && ! do_select ) ){
			return;
		}

		input.trigger( 'click' );
	}
	$( 'body' ).on( 'click', '.mpc-check-all', function(){
		var wrap      = $( this ).closest( '.mpc-container' );
		var do_select = wrap.find( '.mpc-check-all' ).attr( 'data-state' );

		if ( typeof do_select == 'undefined' || do_select === 'not' ) {
			do_select = true;
		}else{
			do_select = false;
		}

		wrap.find( 'tr.cart_item' ).each(function(){
			handle_row_select( $(this), do_select );
		});
		
		do_select = do_select === false ? 'not' : 'checked';

		wrap.find( '.mpc-check-all' ).attr( 'data-state', do_select );
	});

	// reset form.
	$( 'body' ).on( 'click', '.mpc-reset', function(){
		window.location.reload();
	});

	// on ESC key pressed, hide popup box.
	$( document ).on( 'keyup', function( e ) {
		if ( e.keyCode == 27 ) {
			$( '#mpcpop' ).hide();
		}
	});

	// on category clicked, filter that category products instead of going to archive page
	function table_loader_by_tag( id, type, wrapper ){
		var atts = mpc_get_atts( wrapper );
		atts[type] = id;

		ajax_table_loader( atts, 1, wrapper );
	}
	$(document).on( 'click', '.mpc-product-category a, .mpc-product-tag a', function(e){
		e.preventDefault();
		var id      = parseInt( $(this).data( 'id' ) );
		var wrapper = $(this).closest( '.mpc-container' );

		var type   = $(this).closest( 'td' ).hasClass( 'mpc-product-category' ) ? 'cats' : 'tags';
		var select = 'cats' === type ? wrapper.find( '.mpc-cat-filter' ) : wrapper.find( '.mpc-tag-filter' );
		if( typeof select !== 'undefined' && select.length > 0 ){
			select.val( id ).trigger( 'change' );
			return;
		}

		table_loader_by_tag( id, type, wrapper );
	});

	$( 'body' ).on( 'change', '.mpcp-tag-filter select', function(){
		table_loader_by_tag( parseInt( $(this).find( 'option:selected' ).val() ), 'tags', $(this).closest( '.mpc-container' ) );
	});
})( jQuery );
