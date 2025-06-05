/**
 * Table frontend main functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcTableEvents{
        constructor(){
            $(document).ready(() => {
                this.globalEvents();
                this.headerEvents();
                this.tableRowEvents();
            });
        }



        globalEvents(){
            const self = this;
            $(document.body).on('click', '#mpcpop', function(e){
                if(e.target.tagName.toLowerCase() !== 'img'){
                    self.imagePopup('', 'hide');
                }
            });
            $(document.body).on('click', 'span.mpcpop-close', function(){
                self.imagePopup('', 'hide');
            });
        }
        headerEvents(){
            const self = this;
            $(document.body).on('click', '.mpc-check-all', function(){
                self.selectAllProducts($(this));
            });
        }
        tableRowEvents(){
            const self = this;
            $(document.body).on( 'click', 'img.mpc-product-image', function(){
                self.showProductImage($(this));
            });
            $(document.body).on('change paste keyup cut select', '.mpc-product-quantity input[type="number"]', function(){
                self.checkProductStock($(this), '');
                self.updateTotalPrice($(this));
            });
            $(document.body).on('click', 'input[type="checkbox"]', function(){
                self.updateTotalPrice($(this));
            });
            $(document.body).on('change', '.mpc-product-variation select', function(){
                self.variationAttributeMarker($(this));

                const row       = $(this).closest('tr.cart_item');
                const variation = self.getVariation(row);

                self.checkProductStock(row, variation);

                self.setVariationImage(row, variation);
                self.setVariationPrice(row, variation);
                self.setVariationDescription(row, variation);
                self.addClearVariations(row);

                const ability = !variation || (variation && variation.stock_status === 'outofstock') || (variation && !variation.price) ? false : true;
                self.disableRow(row, ability);
            });
            $(document.body).on('click', '.mpc-product-variation a.reset_variations', function(e){
                e.preventDefault();
                self.clearAllVariations($(this));
                self.disableRow($(this).closest('tr.cart_item'), true);
            });
        }



        // global events.
        imagePopup(url, action){
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

        // table header events.
        selectAllProducts(item){
            const self       = this;
            const ifCheckAll = item.is(':checked') ? true : false;
            const table      = item.closest('.mpc-container').find('table.mpc-wrap');
            table.find('tr').each(function(){
                const checkBox = $(this).find('input[type="checkbox"]');
                if(!checkBox) return false;
                const itemChecked = checkBox.is(':checked');

                if(!ifCheckAll && itemChecked) checkBox.trigger('click');
                else if(ifCheckAll && !itemChecked && !self.hasRowDisputs($(this))) checkBox.trigger('click');
            });
        }

        // table row events.
        showProductImage(item){
            const url = item.attr('data-fullimage');
            if(!url) return;
            this.imagePopup(url, 'show');
        }
        updateTotalPrice(item){
            const self      = this;
            const tableWrap = item.closest('.mpc-container');
            const table     = tableWrap.find('table.mpc-wrap');

            let total = 0;
            table.find('tr').each(function(){
                total += self.getRowTotalPrice($(this));
            });

            total = this.formatPrice(total);
            tableWrap.find('.mpc-total .total-price').text(total);
            tableWrap.find('.mpc-floating-total span.total-price').text(total);
        }
        checkProductStock(item, hasVariation){
            const row       = item.hasClass('.cart_item') ? item : item.closest('tr.cart_item');
            const variation = hasVariation ? hasVariation : this.getVariation(row);
            if(row.attr('data-type') !== 'variable' || !variation || !variation.stock) return;

            const qtyField = row.find('.mpc-product-quantity input[type="number"]');
            const qty      = qtyField.length !== 0 ? parseInt(qtyField.val()) : 1;

            const stock = variation['stock'].replace(/[A-Za-z]/g, '').replace(' ', '');
            if(!stock || stock === '0') return;

            if(stock && qty > parseInt(stock)){
                qtyField.val(parseInt(stock));
            }
        }



        // variation related functions.
        setVariationImage(row, variation){
            if(!variation) return;

            const imgWrap = row.find('.mpc-product-image');
            const img     = variation.image ?? false;
            if(!imgWrap || !img) return;

            if(img.thumbnail){
                imgWrap.find('.mpcpi-wrap img').attr('src', img.thumbnail);
            }
            if(img.full){
                imgWrap.find('.mpcpi-wrap img').attr('data-fullimage', img.full);
            }
        }
        setVariationPrice(row, variation){
            const priceWrap = row.find('.mpc-single-price');
            if(!priceWrap) return;

            priceWrap.hide();
            if(!variation) return;
            
            const price = variation.price;
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
        setVariationDescription(row, variation){
            if(!variation || !variation.desc){
                return;
            }

            if(!mpc_frontend.settings.variation_desc){ // if setting disabled.
                return;
            }
            
            const titleWrap = row.find('td.mpc-product-name');
            if(!titleWrap){
                return;
            }

            const descWrap = row.find('.woocommerce-product-details__short-description');
            descWrap.length !== 0 ? descWrap.text(variation.desc) : titleWrap.append(`<p class="woocommerce-product-details__short-description">${variation.desc}</p>`);
        }
        addClearVariations(row){
            const total  = row.find('select').length;
            let selected = 0;
            row.find('select').each(function(){
                if($(this).find('option:selected').val()) selected++;
            });
            if(total === 0) return;

            if(row.find('.clear-button').length){
                row.find('.clear-button').html(`<a class="reset_variations" href="#">Clear</a>`);
            }else{
                row.find('.mpc-product-variation').append(`<div class="clear-button"><a class="reset_variations" href="#">Clear</a></div>`);
            }
        }
        clearAllVariations(item){
            const row = item.closest('tr.cart_item');
            row.find('select').each(function(){
                $(this).val('').trigger('change');
            });
            row.find('a.reset_variations').remove();
            row.find('.woocommerce-product-details__short-description').text('');
            this.disableRow(row, true);
        }
        disableRow(row, ability){
            const qtyField = row.find('.mpc-product-quantity input[type="number"]');
            const checkBox = row.find('.mpc-product-select input[type="checkbox"]');

            // check variation attribute situations.
            ability = row.data('type') === 'variable' && this.hasRowDisputs(row) ? false : ability;

            if(ability){
                if(qtyField.length !== 0) qtyField.prop('disabled', false);
                if(checkBox.length !== 0){
                    checkBox.prop('disabled', false);
                    checkBox.trigger('click');
                }
            }else{
                if(qtyField.length !== 0){
                    qtyField.val(0);
                    qtyField.prop('disabled', true);
                }
                if(checkBox.length !== 0){
                    if(checkBox.is(':checked')) checkBox.trigger('click');
                    checkBox.prop('disabled', true);
                }
            }
        }
        variationAttributeMarker(att){
            const marker = 'mpc-att-marker';
            att.find('option:selected').val().length !== 0 && !att.hasClass(marker) ? att.addClass(marker) : att.removeClass(marker);
        }

        // helper functions.
        getRowTotalPrice(row){
            const qtyField  = row.find('.mpc-product-quantity input[type="number"]');
            const checkBox  = row.find('.mpc-product-select input[type="checkbox"]');
            if(checkBox && !checkBox.is(':checked')) return 0;

            const variation = this.getVariation(row);
            const price     = variation ? parseFloat(variation['price']) : parseFloat(row.data('price'));
            const qty       = qtyField && qtyField.length !== 0 ? parseInt(qtyField.val()) : 1;
            // console.log(price, qty);
            // console.log(variation, typeof variation);
            return price && qty ? price * qty : 0;
        }
        getVariation(row){
            if(row.attr('data-type') !== 'variable') return '';

            const data = row.find('.row-variation-data').data('variation_data');
            if(!data) return '';

            const atts = {};
            let total  = 0, hasValue = 0;
            row.find('select').each(function(){
                const att = $(this).attr('name').replace('attribute_', '').toLowerCase();
                atts[att] = $(this).find('option:selected').val();

                total++;
                if(atts[att]) hasValue++;
            });

            if(total > 0 && total !== hasValue) return ''; // partial selection shouldn't yield any result.

            let variation = false;
            for(const id in data){
                if(!data[id].atts) return false;

                let total = 0, matched = 0;
                for(const att in data[id].atts){
                    total++;
                    const value = data[id].atts[att];
                    if(!value || atts[att].toLowerCase() === value.toLowerCase()){
                        matched++;
                    }
                }

                if(total > 0 && total === matched) variation = data[id];
            }
            return variation;
        }
        autoCheckProduct(row, ability){
            const checkBox = row.find('input[type="checkbox"]');
            if(checkBox.length === 0) return;

            if((!ability && checkBox.is(':checked')) || (ability && !checkBox.is(':checked'))) checkBox.trigger('click');
        }
        hasRowDisputs(row){
            let total = 0, selected = 0;
            row.find('select').each(function(){
                total++;
                if($(this).find('option:selected').val()) selected++;
            });
            return total === 0 || (total > 0 && total === selected) ? false : true;
        }
        formatPrice(price){
            return price.toLocaleString(mpc_frontend.locale, {
                minimumFractionDigits: mpc_frontend.dp,
                maximumFractionDigits: mpc_frontend.dp,
                useGrouping:           true
            });
        }
    }

    new mpcTableEvents();
})(jQuery, window, document);