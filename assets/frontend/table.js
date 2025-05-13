/**
 * Table frontend main functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcTable{
        constructor(){
            this.$rowData = {};
            
            $(document).ready(() => {
                this.init();
            });
        }
        init(){
            const self = this;

            $('body').on('click', '#mpcpop', function(e){
                if(e.target.tagName.toLowerCase() !== 'img'){
                    self.renderImagePopup('', 'hide');
                }
            });
            $('body').on('click', 'span.mpcpop-close', function(){
                self.renderImagePopup('', 'hide');
            });

            $(document).find('.mpc-container').each(function(){
                const table = $(this).find('table.mpc-wrap');
                self.tableEvents(table); // table wrapper events and triggers.

                $(this).find('.mpc-check-all').on('click', function(){
                    self.selectAllProducts(table);
                });
            });



            $('body').on('click', '.mpc-product-variation a.reset_variations', function(e){
                e.preventDefault();
                const row = $(this).closest('tr.cart_item');
                self.clearAllVariations(row);
                row.find('a.reset_variations').remove();

                const checkBox = row.find('input[type="checkbox"]');
                if(checkBox) checkBox.trigger('click');
            });
        }


        
        tableEvents(table){
            const self = this;
            table.find('tr').each(function(){
                self.rowEvents($(this)); // table events and triggers.
            });
        }
        rowEvents(row){
            const self = this;
            row.find('.mpc-product-image img').on('click', function(){ // table row events and triggers.
                self.showProductImage($(this));
            });
            row.find('.mpc-product-quantity input[type="number"]').on('change paste keyup cut select', function(){
                self.commonRowEvents(row);
            });
            row.find('select').on('change', function(){
                self.commonRowEvents(row);
            });
            row.find('input[type="checkbox"]').on('click', function(){
                self.updateTotalPrice(row);
            });
        }
        commonRowEvents(row){
            this.getRowData(row);

            this.checkProductStock(row);
            this.autoCheckProduct(row);
            
            this.setVariationImage(row);
            this.setVariationPrice(row);
            this.setVariationDescription(row);
            this.addClearVariations(row);

            this.updateTotalPrice(row);
        }



        selectAllProducts(table){
            table.find('tr').each(function(){
                let checkBox = $(this).find('input[type="checkbox"]');
                if(checkBox){
                    checkBox.trigger('click');
                }
            });
        }



        getRowData(row){
            this.getVariationAtts(row);

            const qtyField          = row.find('.mpc-product-quantity input[type="number"]');
            this.$rowData.qty       = qtyField.val() ? parseInt(qtyField.val()) : 0;
            this.$rowData.variation = this.getVariation(row);
        }
        getVariation(row){
            if(row.attr('data-type') !== 'variable') return '';

            const data = row.find( '.row-variation-data' ).data( 'variation_data' );
            if(!data) return '';

            let atts  = this.$rowData.atts;
            if(this.$rowData.atts_total !== this.$rowData.atts_selected) return ''; // partial selection shouldn't yield any result.

            let variation = '';
            for(let id in data){
                let selected = 0;
                for(let att in data[id]['atts']){
                    if(data[id]['atts'][att].length === 0){
                        selected++;
                    }else if(typeof atts[att] !== 'undefined'){
                        if(data[id]['atts'][att].toLowerCase() === atts[att].toLowerCase()){
                            selected++;
                        }
                    }
                }
                if(selected === this.$rowData.atts_total && this.$rowData.atts_total !== 0){
                    variation = data[id];
                }
            }
            return variation;
        }
        getVariationAtts(row){
            let total = 0, selected = 0;
            let atts  = {};
            row.find('select').each(function(){
                total++;
                if($(this).find('option:selected').val().length > 0){
                    selected++;
                    atts[$(this).attr('class')] = $(this).find('option:selected').val();
                }
            });

            this.$rowData.atts          = atts;
            this.$rowData.atts_total    = total;
            this.$rowData.atts_selected = selected;
        }



        // Row events
        showProductImage(imgColumn){
            const url = imgColumn.attr('data-fullimage');
            this.renderImagePopup(url, 'show');
        }
        renderImagePopup(url, action){
            const popup = $('#mpcpop');
            if(!popup) return;

            if(action === 'hide'){
                popup.hide();
                return;
            }

            if(!url) return;

            popup.find('img').attr('src', url);
            popup.show();
        }

        checkProductStock(row){
            if(!this.$rowData.qtyField) return;

            const qtyField = row.find('.mpc-product-quantity input[type="number"]');

            let stock = '';
            if(row.attr('data-type') === 'variable'){
                if(!this.$rowData.variation || !this.$rowData.variation['stock']) return;
                stock = this.$rowData.variation['stock'].replace(/[A-Za-z]/g, '').replace(' ', '');
            }else{
                stock = row.attr('stock') ?? '';
            }
            if(!stock || stock === '0') return;

            if(stock && this.$rowData.qty > parseInt(stock)){
                qtyField.val(parseInt(stock));
            }
        }

        autoCheckProduct(row){
            if(this.hasRowDisputs(row)) return;

            const checkBox = row.find('input[type="checkbox"]');
            if(!checkBox) return;
            checkBox.prop('checked', true);
        }
        hasRowDisputs(row){
            let total = 0, selected = 0;
            row.find('select').each(function(){
                total++;
                if($(this).find('option:selected').val()){
                    selected++;
                }
            });

            let disputs = false;
            if(total !== selected) disputs = true;

            return disputs;
        }



        setVariationImage(row){
            if(!this.$rowData.variation) return;

            const imgWrap = row.find('.mpc-product-image');
            const img     = this.$rowData.variation.image ?? false;
            if(!imgWrap || !img) return;

            if(img.thumbnail){
                imgWrap.find('.mpcpi-wrap img').attr('src', img.thumbnail);
            }
            if(img.full){
                imgWrap.find('.mpcpi-wrap img').attr('data-fullimage', img.full);
            }
        }
        setVariationPrice(row){
            const priceWrap = row.find('.mpc-single-price');
            if(!priceWrap) return;

            priceWrap.hide();
            if(!this.$rowData.variation) return;
            
            const price = this.$rowData.variation.price;
            priceWrap.find('span.total-price').text(this.formatPrice(price));
            typeof price !== 'undefined' ? priceWrap.show() : priceWrap.hide();

            // handle same variation price.
            const singlePrice = priceWrap.text().trim().replace(/[^0-9.$]/g, '');
            let rangePrice    = row.find('.mpc-range').text().trim();
            rangePrice        = row.find('.mpc-range ins').length > 0 ? row.find('.mpc-range ins').text().trim() : rangePrice;
            if(singlePrice === rangePrice){ // fixed price variable product.
                priceWrap.hide();
            }
        }
        setVariationDescription(row){
            if(!this.$rowData.variation || !this.$rowData.variation.desc){
                return;
            }

            if(!mpc_frontend.settings.variation_desc){ // if setting is disabled.
                return;
            }
            
            const titleWrap = row.find('td.mpc-product-name');
            if(!titleWrap){
                return;
            }
            
            const descWrap = row.find('.woocommerce-product-details__short-description');
            descWrap ? descWrap.text(this.$rowData.variation.desc) : titleWrap.append(`<p class="woocommerce-product-details__short-description">${this.$rowData.variation.desc}</p>`);
        }
        addClearVariations(row){
            if(this.$rowData.atts_total === 0){
                row.find('.clear-button').html('');
            }
            row.find('.clear-button').html(`<a class="reset_variations" href="#">${mpc_frontend.reset_var}</a>`);
        }
        clearAllVariations(row){
            row.find('select').each(function(){
                $(this).val('');
                $(this).trigger('change');
            });
        }



        updateTotalPrice(row){
            const self      = this;
            const tableWrap = row.closest('.mpc-container');
            let total       = 0;
            tableWrap.find('tbody tr').each(function(){
                total += self.getRowTotalPrice($(this));
            });

            total = this.formatPrice(total);

            const totalWrap = tableWrap.find('.mpc-total');
            totalWrap.find('.total-price').text(total);
            tableWrap.find('.mpc-floating-total span.total-price').text(total);
        }
        getRowTotalPrice(row){
            const qtyField  = row.find('.mpc-product-quantity input[type="number"]');
            const checkBox  = row.find('.mpc-product-select input[type="checkbox"]');
            if(checkBox && !checkBox.is(':checked')) return 0;

            const variation = this.getVariation(row);
            const price     = variation ? parseFloat(variation['price']) : parseFloat(row.data('price'));
            const qty       = qtyField ? parseInt(qtyField.val()) : 1;
            return price * qty;
        }
        


        formatPrice(price){
            return price.toLocaleString(mpc_frontend.locale, {
                minimumFractionDigits: mpc_frontend.dp,
                maximumFractionDigits: mpc_frontend.dp,
                useGrouping:           true
            });
        }
    }

    new mpcTable();
})(jQuery, window, document);