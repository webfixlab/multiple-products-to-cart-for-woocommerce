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
	// for fixed price variable product - handle it's price.
	function mpc_fixed_price_variable_product(row) {
		if (row.find('.mpc-range').text() == row.find('.mpc-single-price').text()) {
			row.find('.mpc-single-price').hide();
		}
		if (typeof row.find('.mpc-range ins').text() != 'undefined') {
			if (row.find('.mpc-range ins').text() == row.find('.mpc-single-price').text()) {
				row.find('.mpc-single-price').hide();
			}
		}
	}

	// show specific image or default woocommerce one.
	function mpc_variation_image(row, data) {
		var img = data['image'];
		if (img['full'].indexOf('woocommerce-placeholder') != -1) {
			row.find('.mpc-product-image .mpcpi-wrap img').attr('src', row.find('.mpc-product-image').data('pimg-thumb'));
			row.find('.mpc-product-image .mpcpi-wrap img').attr('data-fullimage', row.find('.mpc-product-image').data('pimg-full'));
		} else {
			row.find('.mpc-product-image .mpcpi-wrap img').attr('src', img['thumbnail']);
			row.find('.mpc-product-image .mpcpi-wrap img').attr('data-fullimage', img['full']);
		}
	}

	// get variation id.
	function mpc_get_variation_id(row, data) {
		var total_select = 0, actual_selected = 0, varid = 0;
		var row_data = {};

		row.find('select.mpc-var-att').each(function(){
			total_select++;
			const attName = $(this).attr('class').replace('mpc-var-att ', '');
			row_data[attName] = $(this).find('option').filter(':selected').attr('data-value');
		});

		for (var vid in data) {
			actual_selected = 0;
			for (var att in data[vid]['attributes']) {
				if (data[vid]['attributes'][att].length == 0) {
					actual_selected++;
				} else if (typeof row_data[att] != 'undefined') {
					if (data[vid]['attributes'][att] == row_data[att]) {
						actual_selected++;
					}
				}
			}
			if (actual_selected == total_select && total_select != 0) {
				varid = vid;
			}
		}
		return varid;
	}

	// shows specific price for the selected option.
	function mpc_row_price_handler(row, price) {
		if (typeof price == 'undefined') {
			row.find('.mpc-single-price span.total-price').text('');
		} else {
			row.find('.mpc-single-price').show();
			
			// price = price.toLocaleString(mpc_frontend.locale, {
			// 	minimumFractionDigits: mpc_frontend.dp,
			// 	maximumFractionDigits: mpc_frontend.dp,
			// 	useGrouping: true
			// });
			row.find('.mpc-single-price span.total-price').text(price);
			if (!row.find('.mpc-product-price').hasClass('mpc-single-active')) {
				row.find('.mpc-product-price').addClass('mpc-single-active');
			}
		}
		if (row.find('.mpc-single-price span.total-price').text().length == 0) {
			row.find('.mpc-single-price').hide();
			if (row.find('.mpc-product-price').hasClass('mpc-single-active')) {
				row.find('.mpc-product-price').removeClass('mpc-single-active');
			}
		}
	}

	function mpc_is_variable_product(row) {
		return row.hasClass('variable') ? true : false;
	}

	function mpc_get_row_quantity(row) {
		const qtyField = row.find('.mpc-product-quantity input[type="number"]');
		return qtyField.length !== 0 ? parseInt(qtyField.val()) : 1;
	}

	// update variation short description.
	function mpc_handle_variation_description(row, desc) {
		var add_desc = false;
		if (typeof desc != 'undefined' && desc.length > 0) add_desc = true;

		if (add_desc === false) {
			row.find('.mpc-var-desc').remove();
		} else {
			if (row.find('.mpc-var-desc') != 'undefined' && row.find('.mpc-var-desc').length > 0) {
				row.find('.mpc-var-desc').replaceWith('<p class="mpc-var-desc">' + desc + '</p>');
			} else {
				row.find('.mpc-product-variation').append('<p class="mpc-var-desc">' + desc + '</p>');
			}
		}
	}

	function row_in_stock(row) {
		var data = row.find('.row-variation-data').data('variation_data');
		var variation_id = mpc_get_variation_id(row, data);
		var instock = -1;
		if (variation_id != 0) {
			if (typeof data[variation_id]['stock_status'] != 'undefined') {
				if (data[variation_id]['stock_status'] == 'outofstock') {
					instock = 1;
				} else if (typeof data[variation_id]['stock'] != 'undefined' && data[variation_id]['stock'].length > 0) {
					instock = data[variation_id]['stock'];
				}
			}
		}
		return instock;
	}
	function row_stock_handler(row) {
		var instock = row_in_stock(row);
		row.find('.out-of-stock, .stock').remove();
		if (instock == -1 || instock == 1) {
			if (row.find('.mpc-product-select input').is(':checked')) {
				row.find('.mpc-product-select input').trigger('click');
			}
			if (typeof row.find('.mpce-single-add') != 'undefined') {
				row.find('.mpce-single-add').prop('disabled', true);
			}
			if (instock == 1 && row.find('.mpc-def-stock').length > 0) {
				row.find('.mpc-def-stock').html(mpc_frontend.outofstock_txt);
			}
			if (typeof row.find('input[type="number"]') != 'undefined') {
				row.find('input[type="number"]').val(0).trigger('change');
			}
			row.find('input[type="number"]').prop('disabled', true);
		} else {
			row.find('input[type="number"]').prop('disabled', false);
			if (typeof row.find('.mpce-single-add') != 'undefined') {
				row.find('.mpce-single-add').prop('disabled', false);
			}
			if (row.find('input[type="number"]').length) {
				if (row.find('input[type="number"]').val() == '0') {
					row.find('input[type="number"]').val(1).trigger('change');
				}
			}
			row.find('.mpc-def-stock').html('<span class="stock in-stock">' + instock + '</span>');
		}
		return instock == -1 || instock == 1 ? false : true;
	}

	// handle pricing per row.
	function mpc_dynamic_pricing_for_row(row) {
		var price = parseFloat(0).toFixed(mpc_frontend.dp);
		var quantity = mpc_get_row_quantity(row);

		if (mpc_is_variable_product(row)) {
			var data = row.find('.row-variation-data').data('variation_data');
			var variation_id = mpc_get_variation_id(row, data);
			if (variation_id != 0) {
				mpc_variation_image(row, data[variation_id]);
				mpc_row_price_handler(row, data[variation_id]['price']);
				mpc_fixed_price_variable_product(row);

				mpc_handle_variation_description(row, data[variation_id]['desc']);

				if (mpc_if_all_options_selected(row) && typeof data[variation_id]['price'] != 'undefined') {
					price = parseFloat(data[variation_id]['price']);
				}

				if (typeof row.find('.mpc-product-sku') != 'undefined' && typeof data[variation_id]['sku'] != 'undefined') {
					row.find('.mpc-def-sku').hide();
					row.find('.mpc-var-sku').html(data[variation_id]['sku']);
				}
				row.attr('data-variation_id', variation_id);
				row.attr('data-price', data[variation_id]['price']);
			} else {
				mpc_handle_variation_description(row, '');
				row.attr('data-variation_id', 0);
				row.attr('data-price', 0);
				row.find('.mpc-product-image .mpcpi-wrap img').attr('src', row.find('.mpc-product-image').data('pimg-thumb'));
				row.find('.mpc-product-image .mpcpi-wrap img').attr('data-fullimage', row.find('.mpc-product-image').data('pimg-full'));
				mpc_row_price_handler(row, '');

				if (typeof row.find('.mpc-product-sku') != 'undefined') {
					row.find('.mpc-def-sku').show();
					row.find('.mpc-var-sku').html('');
				}
				if (typeof row.find('.mpc-stock') != 'undefined') {
					row.find('.mpc-def-stock').show();
					row.find('.mpc-var-stock').html('');
				}
			}
		} else {
			var type = row.data('type');
			if (type !== 'grouped') {
				price = row.data('price');
			}
		}

		// if checkbox isn't checked, set price to 0.
		var checkbox = row.find('.mpc-product-select input');
		if (typeof checkbox !== 'undefined' && checkbox.length > 0 && !checkbox.is(':checked')) {
			price = parseFloat(0);
		}

		return {
			'price': parseFloat(price),
			'quantity': quantity
		};
	}

	// format price to WC standard.
	function priceFormat(price){
        let number = parseFloat(price);
        number = number.toFixed(mpc_frontend.dp);
        let htmlPrice = number.toString().replace('.', mpc_frontend.ds);

        if (mpc_frontend.ts.length > 0) {
            const parts = htmlPrice.split(mpc_frontend.ds);
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, mpc_frontend.ts);
            htmlPrice = parts.join(mpc_frontend.ds);
        }
		return htmlPrice;
	}

	// dynamic total price calculation.
	function mpc_dynamic_product_pricing() {
		$('body').find('form.mpc-cart').each(function () {
			var table = $(this);
			var total = 0.0;
			var checked = 0;
			table.find('.cart_item').each(function () {
				var value = mpc_dynamic_pricing_for_row($(this));
				if(value?.price && !isNaN(value.price)){
					total = total + value.price * value.quantity;
				}
				if ($(this).find('.mpc-product-select input[type="checkbox"]').is(':checked')) {
					checked++;
				}
			});
			table.find('.mpc-total span.total-price').text(priceFormat(total));

			var wrap = table.closest('.mpc-container');
			if (checked == 0) {
				wrap.find('.mpc-floating-total').removeClass('active');
			} else {
				wrap.find('.mpc-floating-total').addClass('active');
			}
			wrap.find('.mpc-floating-total span.total-price').text(total);
		});
	}

	// check if all options are selected for a variable product.
	function mpc_if_all_options_selected(row) {
		var total = selected = 0;
		row.find('select.mpc-var-att').each(function () {
			total++;
			if ($(this).val().length > 0) selected++;
		});
		return total == selected && total != 0 ? true : false;
	}

	function mpc_render_gallery(item) {
		var row = item.closest('tr.cart_item');
		var gallery = row.find('.gallery-items').data('gallery');
		if (typeof gallery != 'undefined') {
			var html = '';
			var found = false;
			$.each(gallery,function (k, v) {
				var cls = '';
				if (v.thumb == item.attr('src') && found == false) {
					cls = 'mpcgi-selected';
					found = true;
				}

				html += '<img class="' + cls + '" src="' + v.thumb + '" data-fullimage="' + v.full + '">';
			});

			if (typeof $('#mpcpop .mpc-gallery') != 'undefined' && $('#mpcpop .mpc-gallery').length > 0) {
				$('#mpcpop .mpc-gallery').replaceWith('<div class="mpc-gallery">' + html + '</div>');
			} else {
				$('#mpcpop').append('<div class="mpc-gallery">' + html + '</div>');
			}
		} else {
			if (typeof $('.mpc-gallery') != 'undefined' && $('.mpc-gallery').length > 0) {
				$('.mpc-gallery').remove();
			}
		}

	}
	// load image popup.
	function mpc_image_popup_loader(item) {
		var link = item.attr('data-fullimage');
		if (typeof link != 'undefined' && link.length > 0){}
		else link = item.attr('data-fullimage');

		var mpop = $('#mpcpop');
		mpop.find('img').attr('src', link);

		if (typeof image_src != 'undefined' && image_src != '') mpop.find('img').attr('src', image_src);

		mpc_render_gallery(item);
		mpop.show();
	}

	/**
	 * Dynamic select all status handler
	 * Checks table status. If everythig checked, auto check the select all checkbox. Anything middle will not auto check it.
	 */
	function mpc_init_select_all(wrap) {
		var total = selected = 0;
		wrap.find('form .mpc-product-select input').each(function () {
			total++;
			if ($(this).is(":checked")) selected++;
		});

		if (selected == 0 && total > 0 && wrap.find('.mpc-check-all').is(':checked')) {
			wrap.find('.mpc-check-all').prop('checked', false);
			wrap.find('.mpc-check-all').attr('data-state', 'not');
		}

		if (total == selected && selected > 0) {
			// check select all checkbox.
			wrap.find('.mpc-check-all').prop('checked', true);
			wrap.find('.mpc-check-all').attr('data-state', 'checked');
		}
	}

	function renderStickyHead(table) {
		const wrap = table.closest('.mpc-container');
		const vpw = window.innerWidth || document.documentElement.clientWidth; // viewPort width.
		
		let min = table.find('tbody tr:first-child td:first-child').offset().left;
		min = vpw < 768 ? 0 : min;

		wrap.find('.mpc-fixed-header').remove();
		if(vpw > 767){
			var html = '';
			table.find('thead th').each(function () {
				var th = $(this);
				html += `<th style="width:${th[0].offsetWidth}px;">${th.text()}</th>`;
			});
			html = `<table style="width:${table[0].offsetWidth}px;"><thead><tr>${html}</tr></thead></table>`;
			table.after(`<div class="mpc-fixed-header" style="left:${min}px;display:none;">${html}</div>`);
		}
		
		let width = vpw < 768 ? '100%' : `${table[0].offsetWidth}px`;
		wrap.find('.total-row').css({ 'width': `${width}` }); // fixed total section.
		wrap.find('.mpc-table-header').css({ 'left': `${min}px`, 'width': width }); // filter section.
	}

	/**
	 * One time section after table load
	 * Handle dynamic all select checkbox status at page load
	 */
	$('body').find('.mpc-container').each(function () {
		mpc_init_select_all($(this));
	});

	// on document load, calculate price.
	mpc_dynamic_product_pricing(); // table.

	// variation option clear button handler.
	function variation_clear_button(row) {
		// check if all select boxes are empty. If yes, remove clear button.
		var has_value = false;

		row.find('select').each(function () {
			if ($(this).val().length > 0) {
				has_value = true;
			}
		});

		if (has_value == false) {
			row.find('.clear-button').html('');
		} else {
			row.find('.clear-button').html('<a class="reset_variations" href="#">' + mpc_frontend.reset_var + '</a>');
		}
	}

	// on variation option change event.
	$('body').on('change', 'table.mpc-wrap select.mpc-var-att', function () {
		var row = $(this).closest('tr.cart_item');
		if (row_stock_handler(row)) {
			if (mpc_if_all_options_selected(row)) {
				var chk = row.find('input[type="checkbox"]');
				chk.prop('checked', true);
			}
			mpc_dynamic_product_pricing(); // row.
		}
		variation_clear_button(row);
	});
	
	function quantity_handler(row) {
		var qty = parseInt(row.find('input[type="number"]').val());

		// filter only integer.
		if (qty < 0) {
			row.find('input[type="number"]').val(0);
		}

		if (mpc_is_variable_product(row)) {
			// variable product.
			var instock = row_in_stock(row);
			if (instock != -1 && instock != 1) {
				// has stock quantity.
				instock = instock.replace(/[A-Za-z]/g, '');
				instock = instock.replace(' ', '');

				if (instock.length > 0) {
					var stock = parseInt(instock);
					if (qty > stock) {
						row.find('input[type="number"]').val(stock);
					}
				}
			}
		} else {
			// simple product.
			var stock = row.attr('stock');

			if (typeof stock != 'undefined' && stock.length > 0) {
				stock = parseInt(stock);

				if (qty > stock) {
					row.find('input[type="number"]').val(stock);
				}
			}
		}
	}
	// on quantity change event, calculate total price.
	$('body').on('change paste keyup cut select', '.mpc-product-quantity input[type="number"]', function () {
		quantity_handler($(this).closest('tr.cart_item'));
		mpc_dynamic_product_pricing();
	});

	// on add to cart checkbox checked event, calculate total price.
	$('body').on('click', 'input[type="checkbox"]', function () {
		mpc_dynamic_product_pricing();
	});

	// on click image, show image popup.
	$('body').on('click', '.mpc-product-image img', function () {
		mpc_image_popup_loader($(this));
	});
	$('body').on('click', '.mpc-product-image .moregallery', function () {
		mpc_image_popup_loader($(this).closest('.gallery-item').find('img'));
	});
	// select all handler.
	function handle_row_select(row, do_select) {
		var type = row.data('type');

		var input = row.find('input[type="checkbox"]');
		if (typeof input === 'undefined' || input.length === 0) {
			return;
		}

		var input_checked = input.is(':checked');

		if (type === 'variable' && do_select && !mpc_if_all_options_selected(row)) {
			return;
		}

		if ((input_checked && do_select) || (!input_checked && !do_select)) {
			return;
		}

		input.trigger('click');
	}
	$('body').on('click', '.mpc-check-all', function () {
		var wrap = $(this).closest('.mpc-container');
		var do_select = wrap.find('.mpc-check-all').attr('data-state');

		if (typeof do_select == 'undefined' || do_select === 'not') {
			do_select = true;
		} else {
			do_select = false;
		}

		wrap.find('tr.cart_item').each(function () {
			handle_row_select($(this), do_select);
		});

		do_select = do_select === false ? 'not' : 'checked';

		wrap.find('.mpc-check-all').attr('data-state', do_select);
	});

	// table: sticky header.
	function prepareStickyTable() {
		$('body').find('table.mpc-wrap').each(function () {
			renderStickyHead($(this));
		});
	}
	prepareStickyTable();

	function setStickyTop(wrap) {
		let top = 0;
		const adminBar = $(document).find('#wpadminbar');
		if (typeof adminBar !== undefined && adminBar.length > 0) {
			if (adminBar.css('position') === 'fixed') {
				top += adminBar.height();
			}
		}

		const elementorSticky = $(document).find('.elementor-sticky.elementor-sticky--active');
		if (typeof elementorSticky !== undefined && elementorSticky.length > 0) {
			const device = $(document).find('body').data('elementor-device-mode');
			elementorSticky.each(function () {
				if (!$(this).is(':hidden') || (typeof device !== undefined && !$(this).hasClass('elementor-hidden-' + device))) {
					top += $(this).height();
				}
			});
		}

		const fixedColumns = wrap.find('.mpc-fixed-header');
		if (typeof fixedColumns !== undefined && !fixedColumns.is(':hidden')) {
			fixedColumns.css({ 'top': `${top}px` });
			const fixedColsHeight = fixedColumns.height();
			if (fixedColsHeight) {
				top += fixedColsHeight;
			}
		}

		const fixedFilters = wrap.find('.mpc-table-header.mpc-fixed-filter');
		if (typeof fixedFilters !== undefined) {
			fixedFilters.css({ 'top': `${top}px` });
		}
	}

	var screenH = $(window).height();
	var screenW = window.screen.width;

	var oldScrolls = {};
	function tableScroll(currentScroll) {
		var currentScroll = $(window).scrollTop();
		var cs = currentScroll; // current scroll offset.

		var tk = 0; // table key.
		$('body').find('table.mpc-wrap').each( function () {
			var table = $(this);
			var wrap = table.closest('.mpc-container');

			setStickyTop(wrap);

			var head = table.offset().top + 50;
			var tail = table.find('tbody tr:last-child').offset().top;

			// table head.
			let products = table.find('tbody tr');
			let tableStart = products[1] ? $(products[1]).offset().top : 0;
			let tableEnd = $(products[products.length - 1]).offset().top + $(products[products.length - 1])[0].offsetHeight;
			if ((cs + screenH) > tableStart && (cs + screenH) < tableEnd) {
				wrap.find('.total-row').removeClass('mpc-fixed-total-m').addClass('mpc-fixed-total-m');
			} else {
				wrap.find('.total-row').removeClass('mpc-fixed-total-m');
			}

			// fixed header.
			if (screenW > 500) {
				if (cs > head && cs < tail) {
					if (table.find('thead').length) {
						table.closest('form').find('.mpc-fixed-header').show();
					}
				}
				if (cs < head || cs > tail) {
					if (table.find('thead').length) {
						table.closest('form').find('.mpc-fixed-header').hide();
					}
				}
			}

			// filter section.
			if (currentScroll < oldScrolls[tk] && currentScroll > head && currentScroll < tail) {
				var height = wrap.find('.mpc-table-header')[0].offsetHeight + 20;
				if (wrap.find('.mpc-all-select').length) {
					height += 32;
				}

				if (!wrap.find('.mpc-table-header').hasClass('mpc-fixed-filter')) {
					wrap.css('margin-top', `${height}px`);
				}

				wrap.find('.mpc-table-header').removeClass('mpc-fixed-filter').addClass('mpc-fixed-filter');
			} else {
				wrap.find('.mpc-table-header').removeClass('mpc-fixed-filter');
				wrap.css('margin-top', '20px');
			}
			oldScrolls[tk] = currentScroll;
			tk++;
		});
	}
	$(window).on('scroll', function () {
		tableScroll();
	});

	function prepareFreeHead() {
		$('body').find('.mpc-container').each(function () {
			var wrap = $(this);
			var elemCount = 0;
			wrap.find('.mpc-table-header > div').each(function () {
				elemCount++;
			});
			if (elemCount < 3) {
				wrap.find('.mpc-table-header').removeClass('mpc-free-head').addClass('mpc-free-head');
			}
		});
	}
	if (screenW < 500) {
		prepareFreeHead();
	}
	$(window).on('resize', function () {
		prepareStickyTable();
		tableScroll();
	});
})( jQuery );
