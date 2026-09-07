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
			$( document ).ready( () => this.initEvents() );
		}
		initEvents(){
            // trigger new custom events from this free version with arguments and use it on pro.
            window.mpcHooks.addAction( 'mpc_table_loaded', ( response, wrap ) => this.getTableData( wrap ) );
            window.mpcHooks.addAction( 'mpc_calculate_total', ( tableId ) => this.setTableTotal( tableId ) );
            window.mpcHooks.addAction( 'mpc_all_products_check', ( tableId ) => this.applyAutoAllCheck( tableId ) );

            // row events.
            $( 'body' ).on( 'change paste keyup cut select', '.mpc-product-quantity input[type="number"]', ( e ) => this.navigateRowEvent( e, 'qty' ) );
            $( 'body' ).on( 'change', 'table.mpc-wrap select.mpc-var-att', ( e ) => this.navigateRowEvent( e, 'att' ) );
            $( 'body' ).on( 'click', 'table.mpc-wrap .mpc-clear-variations', ( e ) => this.navigateRowEvent( e, 'clear' ) );
            $( 'body' ).on( 'click', 'table.mpc-wrap input[type="checkbox"]', ( e ) => this.navigateRowEvent( e, 'check' ) );

            // table events.
            $( 'body' ).on( 'click', 'input[type="checkbox"].mpc-check-all', ( e ) => this.forceAllCheck( e ) );
            $( 'body' ).on( 'click', '.mpc-reset', () => window.location.reload() );

            $( 'body' ).find( '.mpc-container' ).each( ( _, el ) => this.getTableData( $( el ) ) );
        }

        getTableData( wrap ){
            wrap.find( 'table.mpc-wrap' ).attr( 'data-table_id', this.tableCounter );
            wrap.find( 'table.mpc-wrap tr.cart_item' ).each( ( _, row ) => this.getRowData( $( row ) ) );

            window.mpcHooks.doAction( 'mpc_calculate_total', this.tableCounter );
            window.mpcHooks.doAction( 'mpc_all_products_check', this.tableCounter );
            this.tableCounter++;
        }
        getRowData( row ){
            let data = {
                product_id: parseInt( row.attr( 'data-id' ) ),
                type:       row.attr( 'data-type' ),
                qty_state:  '', // can be either '+' or '-', if qty increase or decrease.
                variation: 'variable' === row.attr( 'data-type' ) ? this.getCurrentVariation( row ) : {},
            };

            const qtyField = row.find( '.mpc-product-quantity input[type="number"]' );
            const checkBox = row.find( 'input[type="checkbox"]' );
            const price    = row.attr( 'data-price' );

            data.qty     = qtyField && qtyField.length > 0 && qtyField.val().length > 0 ? parseInt( qtyField.val() ) : 1;
            data.checked = checkBox && checkBox.length > 0 ? checkBox.is( ':checked' ) : true;

            data.price = data.variation && 'undefined' !== typeof data.variation.price ? parseFloat( data.variation.price ) : ( 'undefined' !== typeof price ? parseFloat( price ) : 0 );
            data.stock = data.variation && 'undefined' !== typeof data.variation.stock_status ? this.sanitizeStock( data.variation.stock, data.variation.stock_status ) : this.sanitizeStock( row.attr( 'stock' ), row.attr( 'stock_status' ) );

            data.checked = 0 === data.stock || '0' === data.price || 0 === data.price.length ? false : data.checked;

            data = window.mpcHooks.applyFilters( 'mpc_init_row_data', data, row, this.tableCounter );
            if( ! $.isEmptyObject( data ) ){
                window.mpcTables.updateProduct( {
                    tableId:   this.tableCounter,
                    productId: data.product_id,
                }, data );
            }
        }
        getCurrentVariation( row ){
            const variations = row.find( '.row-variation-data' ).data( 'variation_data' );
            let variation    = ! $.isEmptyObject( variations ) ? Object.values( variations ).find( variation => {
                let hasNoIssue = true; // if any attribute value is missed.

                const newAtts = {};
                for( const [attName, attVal] of Object.entries( variation.attributes ) ){
                    const attNameSanitized = attName.replace( 'attribute_', '' );
                    const foundAttVal      = row.find( `select.${attNameSanitized} option:selected` ).attr( 'data-value' ); // could you just :selected.

                    newAtts[ `attribute_${attName}` ] = foundAttVal;

                    if( !foundAttVal || 0 === foundAttVal.length || attVal && attVal.length > 0 && attVal !== foundAttVal ){
                        hasNoIssue = false;
                    }
                }

                variation['attributes__'] = newAtts;

                return hasNoIssue;
            } ) : null;

            return window.mpcHooks.applyFilters( 'mpc_get_variation', variation, row );
        }
        sanitizeStock( stock, stockStatus ){
            return 'outofstock' === stockStatus ? 0 : (
                ! stock || 0 === stock.length ? -1 : parseInt( stock )
            ); // -1 = unlimited, 0 = out of stock.
        }

        navigateRowEvent( e, type ){
            const elm        = $( e.currentTarget );
            const table_id   = parseInt( elm.closest( 'table.mpc-wrap' ).attr( 'data-table_id' ) );
            const product_id = parseInt( elm.closest( 'tr.cart_item' ).attr( 'data-id' ) );

            let data = window.mpcTables.getRowData( table_id, product_id );
            data     = window.mpcHooks.applyFilters( 'mpc_get_row_data', data, elm, type );

            data = 'qty' === type ? this.applyQtyChange( elm, data ) : data;
            data = 'att' === type ? this.applyAttChange( elm, data ) : data;
            data = 'check' === type ? this.applyCheckChange( elm, data ) : data;
            data = 'clear' === type ? this.clearRowVariation( elm, data ) : data;

            data = this.applyEventValidation( data, elm, type );
            data = window.mpcHooks.applyFilters( 'mpc_field_changed', data, elm, type );
            window.mpcTables.updateProduct( { table_id: table_id, product_id: product_id }, data );

            window.mpcHooks.doAction( 'mpc_calculate_total', table_id );
            window.mpcHooks.doAction( 'mpc_all_products_check', table_id );

            if( 'clear' === type ){
                e.preventDefault();
                // elm.hide( 'slow' ).remove();
                elm.fadeOut( 1000, () => elm.remove() );
            }
        }
        applyQtyChange( field, data ){
            let qty        = 'undefined' !== field.val() && field.val().length > 0 ? parseInt( field.val() ) : 0;
            qty            = Math.max( 0, qty );
            data.qty_state = data.qty > qty ? '-' : ( data.qty < qty ? '+' : '' );
            data.qty       = 0 === qty && '-' === data.qty_state ? 0 : qty;
            
            data = this.applyStock( data, 'qty', field );
            return data;
        }
        applyStock( data, type, field ){
            const qtyField = field.closest( 'tr.cart_item' ).find( '.mpc-product-quantity input[type="number"]' );
            const fieldVal = qtyField && qtyField.length > 0 ? parseInt( qtyField.val() ) : 1;

            data.qty = 0 === data.qty && 0 !== data.stock ? fieldVal : data.qty;
            data.qty = -1 === data.stock ? data.qty : Math.min( data.qty, data.stock );

            return data;
        }
        applyAttChange( attDropDown, data ){
            const row       = attDropDown.closest( 'tr.cart_item' );
            const variation = this.getCurrentVariation( row );

            data.variation = variation ? variation : {};
            data.price     = variation && variation.price ? parseFloat( variation.price ) : 0;
            data.stock     = variation ? this.sanitizeStock( variation.stock, variation.stock_status ) : -1;
            
            data = this.applyStock( data, 'att', attDropDown );
            this.applyRowVariation( row, data );

            return data;
        }
        applyRowVariation( row, data ){
            if( 'undefined' === typeof data.variation ){
                return;
            }

            const totalAtts = row.find( 'select.mpc-var-att' );
            const attFilled = totalAtts.filter( function(){
                return $( this ).find( 'option:selected' ).val().length > 0;
            } );
            const clearBtn  = row.find( '.mpc-clear-variations' );
            const colImage  = row.find( '.mpc-product-image .mpcpi-wrap img' );
            const descWrap  = row.find( '.mpc-product-title .mpc-var-desc' );
            const priceWrap = row.find( '.mpc-product-price .mpc-single-price' );

            // clear variation button.
            if( ( ! clearBtn || 0 === clearBtn.length ) && attFilled.length > 0 ){
                row.find( '.mpc-product-variation' ).append( `<a class="mpc-clear-variations" href="#">${mpc_frontend.reset_var}</a>` );
            }else if( clearBtn && clearBtn.length > 0 && 0 === attFilled.length  ){
                clearBtn.remove();
            }

            // variation image.
            if( colImage && colImage.length > 0 && 'undefined' !== typeof data.variation.image ){
                colImage.attr( 'src', data.variation.image.thumb );
                colImage.attr( 'data-fullimage', data.variation.image.full );
            }

            // variation description.
            const desc = 'undefined' !== typeof data.variation.desc ? data.variation.desc : '';
            if( ( ! descWrap || 0 === descWrap.length ) && desc.length > 0 ){
                row.find( '.mpc-product-title' ).append( `<div class="mpc-var-desc">${desc}</div>` );
            }else if( descWrap && descWrap.length > 0 && ( ! desc || 0 === desc.length )  ){
                descWrap.remove();
            }

            // variation price.
            const price = 'undefined' !== typeof data.variation.price ? data.variation.price : '';
            priceWrap.find( 'span.total-price' ).text( 'number' === typeof price ? this.priceFormat( price ) : '' );
            priceWrap.toggle( 'number' === typeof price );
            row.attr( 'data-price', price );

            row.find( '.mpc-stock-out' ).remove();
            if( 0 === data.stock ){
                row.find( 'td.mpc-product-variation' ).prepend( `<span class="mpc-stock-out">${mpc_frontend.stock_out}</span>` );
            }
        }
        applyCheckChange( elm, data ){
            data.checked = elm.is( ':checked' );
            return data;
        }
        applyEventValidation( data, elm, type ){
            const row        = elm.closest( 'tr.cart_item' );
            const qtyField   = row.find( '.mpc-product-quantity input[type="number"]' );
            const checkBox   = row.find( '.mpc-product-buy input[type="checkbox"]' );

            if( qtyField && qtyField.length > 0 ){
                qtyField.prop( 'disabled', 0 === data.stock );
            }

            if( 'check' !== type && checkBox && checkBox.length > 0 ){
                let ok = data.qty > 0 && 0 !== data.stock;
                ok     = 'variable' === data.type && $.isEmptyObject( data.variation ) ? false : ok;

                checkBox.prop( 'disabled', 0 === data.stock );
                checkBox.prop( 'checked', ( '-' !== data.qty_state || 'qty' !== type ) && ! data.checked && ok ? true : ( data.checked && ok ) );
                data.checked = ( '-' !== data.qty_state || 'qty' !== type ) && ! data.checked && ok ? true : ( data.checked && ok );
            }

            return data;
        }
        clearRowVariation( elm, data ){
            const row        = elm.closest( 'tr.cart_item' );
            const qtyField   = row.find( '.mpc-product-quantity input[type="number"]' );
            const checkBox   = row.find( '.mpc-product-buy input[type="checkbox"]' );
            const attOptions = row.find( 'td.mpc-product-variation select' );
            const priceWrap  = row.find( '.mpc-product-price .mpc-single-price' );

            if( qtyField && qtyField.length > 0 ){
                qtyField.prop( 'disabled', false );
            }

            if( checkBox && checkBox.length > 0 ){
                checkBox.prop( 'disabled', false );
                checkBox.prop( 'checked', false );
            }

            attOptions.each( ( _, el ) => $( el ).val( '' ) );
            if( priceWrap && priceWrap.length > 0 ){
                priceWrap.hide();
            }

            row.find( '.mpc-stock-out' ).remove();

            data.checked   = false;
            data.variation = {};
            data.price     = 0;
            data.stock     = '';

            return data;
        }



        applyAutoAllCheck( tableId ){ // if all products are checked already, trigger checking "all check".
            const wrap = $( document.body ).find( '.mpc-container' ).filter( function(){
                return tableId.toString() === $( this ).find( 'table.mpc-wrap' ).attr( 'data-table_id' );
            } );

            const allCheck = wrap.find( '.mpc-check-all' );
            if( ! allCheck || 0 === allCheck.length ){
                return;
            }

            const data = window.mpcTables.getTableData( tableId );
            if( $.isEmptyObject( data ) ){
                return;
            }

            let counter = { total: 0, checked: 0 };
            Object.values( data ).forEach( ( item ) => {
                if( 0 !== item.stock ){
                    counter.total++;
                    counter.checked += item.checked ? 1 : 0;
                }
            } );
            counter = window.mpcHooks.applyFilters( 'mpc_checked_products', counter, data );
            allCheck.prop( 'checked', counter.total === counter.checked );
        }
        getRowArgs( data, row ){
            const args = {};
            args.checkbox = row.find( '.mpc-product-buy input[type="checkbox"]' );
            args.checkbox = 'undefined' === args.checkbox || 0 === args.checkbox.length ? null : args.checkbox;
            
            args.pid  = parseInt( row.attr( 'data-id' ) ); // product id.
            args.data = data[ args.pid ];
            args.data = 'undefined' === typeof args.data || 0 === args.data.length ? {} : args.data;

            return args;
        }
        forceAllCheck( e ){
            const elm     = $( e.currentTarget ); // select all checkbox.
            const table   = elm.closest( '.mpc-container' ).find( 'table.mpc-wrap' );
            const tableId = parseInt( table.attr( 'data-table_id' ) );
            let data      = window.mpcTables.getTableData( tableId );
            const getAll  = elm.is( ':checked' );

            table.find( 'tr.cart_item' ).each( ( _, row ) => {
                const args = this.getRowArgs( data, $( row ) );
                if( ! $.isEmptyObject( args.data ) && args.checkbox && -1 !== [ 'simple', 'variable' ].indexOf( args.data.type ) ){
                    const state = 'variable' === args.data.type && ! $.isEmptyObject( args.data.variation ) ? false : ( getAll && -1 !== args.data.stock && args.data.qty > 0 );
                    
                    args.checkbox.prop( 'checked', state );
                    data[ args.pid ].checked = state;
                }
            } );

            data = window.mpcHooks.applyFilters( 'mpc_force_all_check', data, getAll, table );
            window.mpcTables.updateTableData( tableId, data );

            window.mpcHooks.doAction( 'mpc_calculate_total', tableId, data );
        }
        setTableTotal( tableId ){
            const data = window.mpcTables.getTableData( tableId );
            if( $.isEmptyObject( data ) ){
                return total;
            }

            const wrap = $( document.body ).find( '.mpc-container' ).filter( function(){
                return tableId.toString() === $( this ).find( 'table.mpc-wrap' ).attr( 'data-table_id' );
            } );
            
            // I could use wc_price here.
            const tableTotal = wrap.find( '.mpc-total span.total-price' );
            if( ! tableTotal || 0 === tableTotal.length ){
                return;
            }

            let total = data && data.length > 0 ? Object.values( data ).reduce( ( sum, item ) => {
                return sum + ( item.checked ? ( item.qty * item.price ) : 0 );
            }, 0 ) : 0;
            total = window.mpcHooks.applyFilters( 'mpc_table_total', total, tableId );
            
            tableTotal.text( this.priceFormat( total ) );
        }

        priceFormat( price ){
            return parseFloat( price ).toLocaleString( mpc_frontend.locale, {
                minimumFractionDigits: mpc_frontend.dp,
                maximumFractionDigits: mpc_frontend.dp,
                useGrouping: true
            } );
        }
	}
	new MPCFrontProductEvents();
} )( jQuery, window, document );
