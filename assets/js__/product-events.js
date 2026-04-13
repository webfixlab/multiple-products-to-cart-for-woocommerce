/**
 * Frontend table product events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCFrontProductEvents{
		constructor(){
            this.tableCounter = 1;
            this.state = {}; // all table states with product data.
			$( document ).ready( () => this.initEvents() );
		}
		initEvents(){
            // trigger new custom events from this free version with arguments and use it on pro.
            $( document ).on( 'mpc_ajax_table_loaded', ( wrap, response ) => this.tableEvents( wrap ) );

            $( 'body' ).on( 'click', '.mpc-check-all', ( e ) => this.allCheckEventHandler( $( e.currentTarget ) ) );
            $( 'body' ).on( 'change paste keyup cut select', '.mpc-product-quantity input[type="number"]', ( e ) => this.variationAttchangeEventHandler( $( e.currentTarget ) ) );
            $( 'body' ).on( 'change', 'table.mpc-wrap select.mpc-var-att', ( e ) => this.variationAttChanged( $( e.currentTarget ) ) );
            $( 'body' ).on( 'click', 'table.mpc-wrap input[type="checkbox"]', ( e ) => this.productCheckEventHandler( $( e.currentTarget ) ) );

            $( 'body' ).find( '.mpc-container' ).each( ( _, el ) => this.tableEvents( $( el ) ) );
            // this.mpc_dynamic_product_pricing(); // table.
        }
        tableEvents( wrap ){
            this.initTablesState( wrap );
            this.mpc_dynamic_product_pricing(); // table.
            this.updateAllCheck( wrap );
        }
        initTablesState( wrap ){
            const table = wrap.find( 'table.mpc-wrap' );
            table.attr( 'data-table_id', this.tableCounter );
            table.find( 'tr.cart_item' ).each( ( _, row ) => this.initTableRowState( $( row ) ) );
            this.tableCounter++;
        }
        initTableRowState( tableId, row ){
            const productData = {
                type: row.attr( 'data-type' )
            };
            const qty = row.find( '.mpc-product-quantity input[type="number"]' );
            if( qty && qty.length > 0 ){
                productData['qty'] = parseInt( qty );
            }

            const checkBox = row.find( 'input[type="checkbox"]' );
            if( checkBox && checkBox.length > 0 ){
                productData['checked'] = checkBox.is( ':checked' );
            }

            // get price - for default attribute value or just get simple product price.
            if( 'variable' === productData.type ){
                const variation = this.getCurrentVariation( row );
                productData['price'] = variation && variation.price ? parseFloat( variation.price) : 0;
                productData['stock'] = variation && variation.stock_status ? this.sanitizeStock( variation.stock, variation.stock_status ) : -1; // -1 = unlimited.
            }else{
                const simplePrice = row.attr( 'data-price' );
                productData['price'] = simplePrice ? parseFloat( simplePrice ) : 0;
                productData['stock'] = this.sanitizeStock( row.attr( 'stock' ), row.attr( 'stock_status' ) );
            }
            
            if( ! this.state[ tableId ] ){
                this.state[ tableId ] = [];
            }
            const productId = parseInt( row.attr( 'data-id' ) );
            this.state[ tableId ][ productId ] = productData;
            
            // apply this product data too !!!
            // like, disable qty field if product is outofstock.
            // validate stock - limit quantity.
        }
        getCurrentVariation( row ){
            const variations = row.find( '.row-variation-data' ).data( 'variation_data' );
            const variation = Object.values( variations ).find( variation => {
                let hasNoIssue = true; // if any attribute value is missed.
                for( const [attName, attVal] of Object.entries( variation.attributes ) ){
                    const foundAttVal = row.find( `select.${attName} option:selected` ).attr( 'data-value' ); // could you just :selected.
                    if( attVal && attVal.length > 0 && attVal !== foundAttVal ){
                        hasNoIssue = false;
                    }
                }
                return hasNoIssue;
            });
            return variation || null
        }
        sanitizeStock( stock, stockStatus ){
            return 'outofstock' === stockStatus ? 0 : (
                ! stock || 0 === stock.length ? -1 : parseInt( stock )
            ); // -1 = unlimited, 0 = out of stock
        }
        qtyChangeEventHandler( qtyField ){
            const target = this.identifyTarget( field );
            this.updateState( target, 'qty', parseInt( qtyField.val() ) );
            this.validateStock( qtyField );
            this.setTableTotal( qtyField, target );
        }
        identifyTarget( target ){
            return {
                tableId:   parseInt( target.closest( 'table.mpc-wrap' ).attr( 'data-table_id' ) ),
                productId: parseInt( target.closest( 'tr.cart_item' ).attr( 'data-id' ) )
            };
        }
        updateState( target, key, value ){
            this.state[ target.tableId ][ target.productId ][ key ] = value;
        }    
        validateStock( field ){
            const target = this.identifyTarget( field );
            const stock  = this.state[ target.tableId ][ target.productId ]['stock'];
            let qty      = this.state[ target.tableId ][ target.productId ]['qty'];

            // validate quantity.
            qty = stock && 0 === stock ? 0 : (
                stock && qty > stock ? stock : qty
            );
            this.state[ target.tableId ][ target.productId ]['qty'] = validQty;

            const qtyField = field.closest( 'tr.cart_item' ).find( '.mpc-product-quantity input[type="number"]' );
            if( qtyField && qtyField.length > 0 ){
                qtyField.val( validQty );
            }
        }
        setTableTotal( field, target ){
            // I could use wc_price here.
            const tableTotal = field.closest( '.mpc-container' ).find( '.mpc-total span.total-price' );
            if( ! tableTotal || 0 === tableTotal.length ){
                return;
            }

            const tableData = this.state[ target.tableId ];
            const total = tableData && tableData.length > 0 ? Object.values( tableData ).reduce( ( sum, item ) => {
                const price = item.checked ? item.price : 0;
                return sum + price;
            }, 0 ) : 0;
            tableTotal.text( this.priceFormat( total ) );
        }
        priceFormat( price ){
            return parseFloat( price ).toLocaleString( mpc_frontend.locale, {
                minimumFractionDigits: mpc_frontend.dp,
                maximumFractionDigits: mpc_frontend.dp,
                useGrouping: true
            } );
        }
        variationAttchangeEventHandler( attDropDown ){
            const target = this.identifyTarget( field );

            const variation = this.getCurrentVariation( row );
            this.updateState( target, 'price', variation && variation.price ? parseFloat( variation.price ) : 0 );
            if( variation && variation.stock_status ){
                this.updateState( target, 'stock', this.sanitizeStock( variation.stock, variation.stock_status ) );
            }
            
            this.validateStock( attDropDown );
            // handle image.
            // description.

            this.setTableTotal( attDropDown, target );
        }
        productCheckEventHandler( checkBox ){
            const target = this.identifyTarget( field );
            this.updateState( target, 'checked', checkBox.is( ':checked' ) );
            this.setTableTotal( checkBox, target );
        }
        





        allCheckEventHandler( el ){
            const allChecked  = el.is( ':checked' );
            const allCheckBox = el.closest( '.mpc-container' ).find( 'table.mpc-wrap input[type="checkbox"]' );
            if( !allCheckBox || 0 === allCheckBox.length ){
                return;
            }
            allCheckBox.each( ( _, cb ) => this.checkProduct( $( cb ), allChecked ) );
        }
        checkProduct( checkBox, allChecked ){
            const hasNoIssue = this.hasNoRowIssue( checkBox.closest( 'tr.cart_item' ) );
            // all checked != checkbox -> update checked status but what if it were
            if( ( hasNoIssue && allChecked ) !== checkBox.is( ':checked' ) ){
                checkBox.prop( 'checked', hasNoIssue && allChecked );
                checkBox.trigger( 'click' );
            }
        }
        hasNoRowIssue( row ){
            if( 'variable' !== row.attr( 'data-type' ) ){
                return true;
            }
            const total    = row.find( 'select.mpc-var-att' ).length;
            const hasValue = row.find( 'select.mpc-var-att option:selected' ).length;
            return total > 0 && total !== hasValue ? false : true;
        }
        updateAllCheck( wrap ){
            const allCheck = wrap.find( '.mpc-check-all' );
            if( !allCheck || 0 === allCheck.length ){
                return;
            }
            const total    = wrap.find( 'table.mpc-wrap input[type="checkbox"]' ).length;
            const checked  = wrap.find( 'table.mpc-wrap input[type="checkbox"]:checked' ).length;
            allCheck.prop( 'checked', total === 0 || total === checked );
        }
        


        mpc_dynamic_product_pricing() {
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
        mpc_dynamic_pricing_for_row(row) {
            var price = parseFloat(0).toFixed(mpc_frontend.dp);
            var quantity = mpc_get_row_quantity(row);

            if ( row.hasClass( 'variable' ) ) {
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
        mpc_get_row_quantity(row) {
            const qtyField = row.find('.mpc-product-quantity input[type="number"]');
            return qtyField.length !== 0 ? parseInt(qtyField.val()) : 1;
        }
        mpc_get_variation_id(row, data) {
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
        mpc_variation_image(row, data) {
            var img = data['image'];
            if (img['full'].indexOf('woocommerce-placeholder') != -1) {
                row.find('.mpc-product-image .mpcpi-wrap img').attr('src', row.find('.mpc-product-image').data('pimg-thumb'));
                row.find('.mpc-product-image .mpcpi-wrap img').attr('data-fullimage', row.find('.mpc-product-image').data('pimg-full'));
            } else {
                row.find('.mpc-product-image .mpcpi-wrap img').attr('src', img['thumbnail']);
                row.find('.mpc-product-image .mpcpi-wrap img').attr('data-fullimage', img['full']);
            }
        }
        mpc_row_price_handler(row, price) {
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
        mpc_fixed_price_variable_product(row) {
            if (row.find('.mpc-range').text() == row.find('.mpc-single-price').text()) {
                row.find('.mpc-single-price').hide();
            }
            if (typeof row.find('.mpc-range ins').text() != 'undefined') {
                if (row.find('.mpc-range ins').text() == row.find('.mpc-single-price').text()) {
                    row.find('.mpc-single-price').hide();
                }
            }
        }
        mpc_handle_variation_description(row, desc) {
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


        
        qtyChanged( qtyField ) {
            // row stock handler.
            if ( row.hasClass( 'variable' ) ) {
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
            this.mpc_dynamic_product_pricing();
        }
        row_stock_handler(row) {
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
        row_in_stock(row) {
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
        variationAttChanged( el ){
            const row = el.closest( 'tr.cart_item' );
            if (row_stock_handler(row)) {
                if (mpc_if_all_options_selected(row)) {
                    var chk = row.find('input[type="checkbox"]');
                    chk.prop('checked', true);
                }
                mpc_dynamic_product_pricing(); // row.
            }
            clearVariationText( row );
        }
        clearVariationText( row ) {
            const allAtts   = row.find( 'select.mpc-var-att' );
            const allFilled = allAtts.length > 0 && allAtts.toArray().every( el => el.value !== '' ); // if all have values.
            row.find( '.clear-button' ).html( allFilled ? `<a class="reset_variations" href="#">${mpc_frontend.reset_var}</a>` : '' );
        }
	}
	new MPCFrontProductEvents();
} )( jQuery, window, document );
