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
            window.mpcHooks.addAction( 'mpc_table_loaded', ( response, wrap ) => this.tableEvents( wrap ) );

            $( 'body' ).on( 'click', '.mpc-check-all', ( e ) => this.allCheckEventHandler( $( e.currentTarget ) ) );
            $( 'body' ).on( 'change paste keyup cut select', '.mpc-product-quantity input[type="number"]', ( e ) => this.qtyChangeEventHandler( $( e.currentTarget ) ) );
            $( 'body' ).on( 'change', 'table.mpc-wrap select.mpc-var-att', ( e ) => this.variationAttchangeEventHandler( $( e.currentTarget ) ) );
            $( '.mpc-container' ).on( 'click', 'a.reset_variations', ( e ) => this.clearVariations( e ) );
            $( 'body' ).on( 'click', 'table.mpc-wrap input[type="checkbox"]', ( e ) => this.productCheckEventHandler( $( e.currentTarget ) ) );

            $( 'body' ).on( 'click', '.mpc-reset', () => window.location.reload() );

            $( 'body' ).find( '.mpc-container' ).each( ( _, el ) => this.tableEvents( $( el ) ) );
        }
        tableEvents( wrap ){
            this.initTablesState( wrap );
            this.updateAllCheck( wrap );
            // set table total price.
        }
        initTablesState( wrap ){
            const table = wrap.find( 'table.mpc-wrap' );
            table.attr( 'data-table_id', this.tableCounter );
            table.find( 'tr.cart_item' ).each( ( _, row ) => this.initTableRowState( $( row ) ) );
            this.tableCounter++;
        }
        initTableRowState( row ){
            if( 'grouped' === row.attr( 'data-type' ) ){
                return;
            }
            
            const productData = {
                type: row.attr( 'data-type' )
            };
            const qty = row.find( '.mpc-product-quantity input[type="number"]' );
            if( qty && qty.length > 0 ){
                productData['qty'] = parseInt( qty.val() );
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
                productData['variation'] = variation;
            }else{
                const simplePrice = row.attr( 'data-price' );
                productData['price'] = simplePrice ? parseFloat( simplePrice ) : 0;
                productData['stock'] = this.sanitizeStock( row.attr( 'stock' ), row.attr( 'stock_status' ) );
            }

            window.mpcTables.updateProductState( {
                tableId:   this.tableCounter,
                productId: parseInt( row.attr( 'data-id' ) )
            }, productData );

            // apply this product data too !!!
            // like, disable qty field if product is outofstock.
            // validate stock - limit quantity.
        }
        getCurrentVariation( row ){
            const variations = row.find( '.row-variation-data' ).data( 'variation_data' );
            const variation = Object.values( variations ).find( variation => {
                let hasNoIssue = true; // if any attribute value is missed.
                for( const [attName, attVal] of Object.entries( variation.attributes ) ){
                    const attNameSanitized = attName.replace( 'attribute_', '' );
                    const foundAttVal      = row.find( `select.${attNameSanitized} option:selected` ).attr( 'data-value' ); // could you just :selected.

                    if( !foundAttVal || 0 === foundAttVal.length || attVal && attVal.length > 0 && attVal !== foundAttVal ){
                        hasNoIssue = false;
                    }
                }
                return hasNoIssue;
            });
            return variation || null;
        }
        sanitizeStock( stock, stockStatus ){
            return 'outofstock' === stockStatus ? 0 : (
                ! stock || 0 === stock.length ? -1 : parseInt( stock )
            ); // -1 = unlimited, 0 = out of stock
        }
        updateAllCheck( wrap ){
            const allCheck = wrap.find( '.mpc-check-all' );
            if( ! allCheck || 0 === allCheck.length ){
                return;
            }
            const total   = wrap.find( 'table.mpc-wrap input[type="checkbox"]' ).length;
            const checked = wrap.find( 'table.mpc-wrap input[type="checkbox"]:checked' ).length;
            allCheck.prop( 'checked', total === 0 || total === checked );
        }

        allCheckEventHandler( el ){
            const allChecked  = el.is( ':checked' );

            const wrap        = el.closest( '.mpc-container' );
            const allCheckBox = wrap.find( 'table.mpc-wrap input[type="checkbox"]' );
            if( !allCheckBox || 0 === allCheckBox.length ){
                return;
            }

            const target = {
                tableId:   parseInt( wrap.find( 'table.mpc-wrap' ).attr( 'data-table_id' ) ),
                productId: 0
            };
            allCheckBox.each( ( _, cb ) => {
                target.productId = parseInt( $( cb ).closest( 'tr.cart_item' ).attr( 'data-id' ) );
                
                const checkBox     = $( cb );
                const hasNoIssue   = this.hasNoRowIssue( checkBox.closest( 'tr.cart_item' ) );
                const shouldChange = ( hasNoIssue && allChecked ) !== checkBox.is( ':checked' );
                window.mpcTables.updateProductMeta( target, 'checked', shouldChange );
                // all checked != checkbox -> update checked status but what if it were
                if( shouldChange ){
                    checkBox.trigger( 'click' );
                }
            } );
        }
        hasNoRowIssue( row ){
            if( 'variable' !== row.attr( 'data-type' ) ){
                return true;
            }
            const total    = row.find( 'select.mpc-var-att' );
            const hasValue = total.filter( function(){
                const value = $( this ).find( 'option:selected' ).val();
                return value && value.length > 0;
            } ).length;
            return total.length > 0 && total.length !== hasValue ? false : true;
        }

        qtyChangeEventHandler( qtyField ){
            const target = window.mpcTables.identifyTable( qtyField );
            window.mpcTables.updateProductMeta( target, 'qty', parseInt( qtyField.val() ) );

            this.validateStock( qtyField, target );
            this.setTableTotal( qtyField, target );
        }   
        validateStock( field, target ){
            const qty = window.mpcTables.getValidStockQuantity( field, target );

            const row      = field.closest( 'tr.cart_item' );
            const qtyField = row.find( '.mpc-product-quantity input[type="number"]' );
            const checkBox = row.find( '.mpc-product-select input[type="checkbox"]' );
            if( qtyField && qtyField.length > 0 ){
                qtyField.val( qty );
                qtyField.prop( 'disabled', 0 === qty );
            }

            // contingency checking.
            if( checkBox && checkBox.length > 0 ){
                window.mpcTables.updateProductMeta( target, 'checked', 0 !== qty );
                checkBox.prop( 'checked', 0 !== qty );
                checkBox.prop( 'disabled', 0 === qty );
            }
        }
        setTableTotal( field, target ){
            // I could use wc_price here.
            const tableTotal = field.closest( '.mpc-container' ).find( '.mpc-total span.total-price' );
            if( ! tableTotal || 0 === tableTotal.length ){
                return;
            }

            const total = window.mpcTables.getTableTotal( target );
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
            const target = window.mpcTables.identifyTable( attDropDown );
            const row    = attDropDown.closest( 'tr.cart_item' );
    
            const variation = this.getCurrentVariation( row );
            window.mpcTables.updateProductMeta( target, 'variation', variation ? variation : {} );
            window.mpcTables.updateProductMeta( target, 'price', variation && variation.price ? parseFloat( variation.price ) : 0 );
            
            if( variation ){
                window.mpcTables.updateProductMeta( target, 'stock', this.sanitizeStock( variation.stock, variation.stock_status ) );
            }

            this.clearVariationButton( row );
            this.validateStock( attDropDown, target );

            this.updateVariationImage( row, variation );
            this.updateVariationDesc( row, variation );
            this.updateVariationPrice( row, variation );

            window.mpcHooks.doAction( 'mpc_variation_changed', row, variation );

            this.setTableTotal( attDropDown, target );
        }
        clearVariationButton( row ) {
            const allAtts  = row.find( 'select.mpc-var-att' );
            const clearBtn = row.find( '.clear-button' );
            if( ! clearBtn || 0 === clearBtn.length ){
                row.find( '.mpc-product-variation' ).append( `<div class="clear-button"><a class="reset_variations" href="#">${mpc_frontend.reset_var}</a></div>` );
            }
            clearBtn.toggle( allAtts.length > 0 && allAtts.toArray().every( el => el.value !== '' ) ); // if all have values.
        }
        updateVariationImage( row, variation ){
            const colImage = row.find( '.mpc-product-image .mpcpi-wrap img' );
            if( ! colImage || 0 === colImage.length || ! variation || 0 === variation.length ){
                return;
            }
            const full  = variation.image.full ? variation.image.full : '';
            const thumb = variation.image.thumb ? variation.image.thumb : '';
            if( ! full || ! thumb ){
                return;
            }
            colImage.attr( 'src', thumb );
            colImage.attr( 'data-fullimage', full );
        }
        updateVariationDesc( row, variation ){
            const desc     = variation && variation.desc ? variation.desc : '';
            const descWrap = row.find( '.mpc-var-desc' );
            if( descWrap && descWrap.length > 0 ){
                descWrap.remove();
            }
            if( desc && desc.length > 0 ){
                row.find( '.mpc-product-variation' ).append( `<p class="mpc-var-desc">${desc}</p>` );
            }
        }
        updateVariationPrice( row, variation ){
            const priceWrap = row.find( '.mpc-product-price .mpc-single-price' );
            if( ! priceWrap || 0 === priceWrap.length ){
                return;
            }
            const price = variation && variation.price ? variation.price : '';

            priceWrap.find( 'span.total-price' ).text( 'number' === typeof price ? this.priceFormat( price ) : '' );
            priceWrap.toggle( 'number' === typeof price );
            row.attr( 'data-price', price );
        }
        clearVariations( e ){
            e.preventDefault();

            const clearBtn = $( e.currentTarget );
            const target = window.mpcTables.identifyTable( clearBtn );

            const section = clearBtn.closest( '.mpc-product-variation' );
            section.find( 'select.mpc-var-att' ).each( ( _, el ) => $( el ).val( '' ) );

            window.mpcTables.updateProductMeta( target, 'variation', {} );
            window.mpcTables.updateProductMeta( target, 'price', '' );
            window.mpcTables.updateProductMeta( target, 'stock', '' );
            window.mpcTables.updateProductMeta( target, 'checked', false );

            section.find( '.mpc-var-desc' ).empty();
            section.find( 'a.reset_variations' ).hide();

            const checkBox = clearBtn.closest( 'tr.cart_item' ).find( '.mpc-product-select input[type="checkbox"]' );
            if( checkBox && checkBox.length > 0 ){
                checkBox.prop( 'checked', false );
            }
        }

        productCheckEventHandler( checkBox ){
            const target = window.mpcTables.identifyTable( checkBox );
            window.mpcTables.updateProductMeta( target, 'checked', checkBox.is( ':checked' ) );
            this.setTableTotal( checkBox, target );
        }
	}
	new MPCFrontProductEvents();
} )( jQuery, window, document );
