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
            // this.$rowData = {};
            
            $(document.body).on('mpc_init_tables', () => {
                this.globalEvents();
                this.tableHeaderEvents();
                this.rowEvents();
                this.tableFooterEvents();
            });
        }
        globalEvents(){
            $('body').on('click', 'span.mpcpop-close', () => {
                this.imagePopup('', 'hide');
            });
            $('body').on('click', '#mpcpop', (e) => {
                if(e.target.tagName.toLowerCase() !== 'img'){
                    this.imagePopup('', 'hide');
                }
            });
        }
        tableHeaderEvents(){
            const self = this;
            $(document.body).on('click', '.mpc-check-all', function(){
                self.productAllCheck($(this));
            });
        }
        rowEvents(){
            const self = this;
            $(document.body).on( 'click', 'img.mpc-product-image', function(){
                self.showProductImage($(this));
            });
            $(document.body).on('change paste keyup cut select', '.mpc-product-quantity input[type="number"]', function(){
                // self.checkStock($(this));
                self.updateTotalPrice($(this));
            });
            $(document.body).on('click', 'input[type="checkbox"]', function(){
                self.updateTotalPrice($(this));
            });
            $(document.body).on('change', '.mpc-product-variation select', function(){
                const row       = $(this).closest('tr.cart_item');
                const data      = self.getProductData($(this));
                const variation = self.getProductVariation(row, data);
                self.setVariationStock(row, variation);
                self.setVariationImage(row, variation, data);
                self.setVariationPrice(row, variation);
                self.addClearVariations(row);
                self.disableRow(row, variation);

            });
            $(document.body).on('click', '.mpc-product-variation a.reset_variations', function(e){
                e.preventDefault();
                self.clearAllVariations($(this));
            });
        }
        tableFooterEvents(){}



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
        productAllCheck(item){
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
        // row - product specific events.
        showProductImage(item){
            const row       = item.closest('tr.cart_item');
            const data      = this.getProductData(item);
            if(data.type !== 'variable'){
                if(data?.image?.full) this.imagePopup(data.image.full, 'show');
                return;
            }

            const variation = this.getProductVariation(row, data);
            const full      = variation?.image?.full ? variation?.image?.full : (data?.image?.full ? data?.image?.full : mpc_frontend.wc_default_img.full);
            this.imagePopup(full, 'show');
        }
        updateTotalPrice(item){
            const self      = this;
            const tableWrap = item.closest('.mpc-container');
            const table     = tableWrap.find('table.mpc-wrap');
            let total   = 0;
            table.find('tr').each(function(){
                total += self.getRowTotalPrice($(this));
            });

            total = this.formatPrice(total);
            tableWrap.find('.mpc-total .total-price').text(total);
            tableWrap.find('.mpc-floating-total span.total-price').text(total);
        }
        // row - variable specific event handlers.
        setVariationImage(row, variation, data){
            const image = row.find('img.mpc-product-image');
            if(!image) return;

            const thumb = variation?.image?.thumb ? variation?.image?.thumb : (data?.image?.thumb ? data?.image?.thumb : mpc_frontend.wc_default_img.thumb);
            image.attr('src', thumb);
        }
        setVariationPrice(row, variation){
            const priceWrap = row.find('.mpc-single-price');
            if(!priceWrap) return;

            const price = variation?.price ?? '';
            if(!variation || !price){
                priceWrap.hide();
                return;
            }
            priceWrap.find('span.total-price').text(this.formatPrice(price));
            priceWrap.show();

            // handle same variation price.
            const singlePrice = priceWrap.text().trim().replace(/[^0-9.$]/g, '');
            let rangePrice    = row.find('.mpc-range').text().trim();
            rangePrice        = row.find('.mpc-range ins').length > 0 ? row.find('.mpc-range ins').text().trim() : rangePrice;
            if(singlePrice === rangePrice){ // fixed price variable product.
                priceWrap.hide();
            }
        }
        setVariationStock(row, variation){
            if(!variation) return;

            const qtyField = row.find('.mpc-product-quantity input[type="number"]');
            const stock    = variation.stock_status === 'instock' ? this.extractStock(variation.stock) : false;
            if(stock){
                const qty = parseInt(qtyField.val());
                if(qty > stock){
                    qtyField.val(stock);
                }
            }
        }
        clearAllVariations(item){
            const row = item.closest('tr.cart_item');
            row.find('select').each(function(){
                $(this).val('').trigger('change');
            });
            row.find('a.reset_variations').remove();
            if(row.find('input[type="checkbox"]')) row.find('input[type="checkbox"]').prop('checked', false);
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
        // table footer events.



        // helper functions.
        hasRowDisputs(row){
            let total = 0, selected = 0;
            row.find('select').each(function(){
                total++;
                if($(this).find('option:selected').val()) selected++;
            });
            return total === 0 || (total > 0 && total === selected) ? false : true;
        }
        getProductData(item){
            const id = parseInt(item.hasClass('cart_item') ? item.data('id') : item.closest('tr').data('id'));
            return mpc_frontend.products[id];
        }
        getProductVariation(row, data){
            if(!data?.children) return false;

            const atts = {};
            row.find('select').each(function(){
                const att = $(this).attr('name').replace('attribute_', '').toLowerCase();
                atts[att] = $(this).find('option:selected').val();
            });

            let variation = false;
            for(const id in data.children){
                if(!data.children[id].atts) return false;

                let total = 0, matched = 0;
                for(const att in data.children[id].atts){
                    total++;
                    const value = data.children[id].atts[att];
                    if(value.length === 0 || atts[att].toLowerCase() === value.toLowerCase()){
                        matched++;
                    }
                }
                if(total > 0 && total === matched) variation = data.children[id];
            }
            return variation;
        }
        extractStock(stock){
            stock = stock.replace(/[A-Za-z]/g, '').replace(' ', '');
            return stock ? parseInt(stock) : false;
        }
        getRowTotalPrice(row){
            const qtyField  = row.find('.mpc-product-quantity input[type="number"]');
            const checkBox  = row.find('.mpc-product-select input[type="checkbox"]');
            if(checkBox && !checkBox.is(':checked')) return 0;

            const qty  = qtyField ? parseInt(qtyField.val()) : 1;
            const data = this.getProductData(row);
            if(data.type !== 'variable') return data.price_ * qty;

            const variation = this.getProductVariation(row, data);
            return variation ? variation.price * qty : 0;
        }
        formatPrice(price){
            return price.toLocaleString(mpc_frontend.locale, {
                minimumFractionDigits: mpc_frontend.dp,
                maximumFractionDigits: mpc_frontend.dp,
                useGrouping:           true
            });
        }
        disableRow(row, variation){ // disable 
            const checkBox = row.find('input[type="checkbox"]');
            if(!checkBox) return;

            let disable = !variation ? true : false;
            if(variation && variation.stock_status === 'outofstock') disable = true;
            if(variation && !variation.price) disable = true;

            if(disable){
                if(checkBox){ // uncheck and disable checkbox.
                    if(checkBox.is(':checked')) checkBox.trigger('click');
                    checkBox.prop('disabled', true);
                }
            }else{
                checkBox.prop('disabled', false);
            }
        }









        autocheck(item){
            const row      = item.closest('tr.cart_item');
            const checkBox = row.find('input[type="checkbox"]');
            if(!checkBox) return;

            this.hasRowDisputs(row) ? checkBox.prop('checked', false) : checkBox.prop('checked', true);
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
    }

    new mpcTable();
})(jQuery, window, document);