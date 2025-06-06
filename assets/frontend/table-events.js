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
            this.$row       = null;
            this.$fields    = null;
            this.$variation = null;

            $(document).ready(() => {
                this.globalEvents();
                this.headerEvents();
                this.tableRowEvents();
                this.tableFooterEvents();

                this.initTables();
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
                self.setRowData($(this));
                self.checkProductStock();
                self.updateTotalPrice($(this));
            });
            $(document.body).on('click', 'input[type="checkbox"]', function(){
                self.updateTotalPrice($(this));
            });
            $(document.body).on('change', '.mpc-product-variation select', function(){
                self.variationAttributeMarker($(this));

                self.setRowData($(this));

                self.setVariationImage();
                self.setVariationPrice();
                self.setVariationDescription();
                self.addClearVariations();
                self.disableRow();

                self.checkProductStock();
            });
            $(document.body).on('click', '.mpc-product-variation a.reset_variations', function(e){
                e.preventDefault();
                self.resetTableRow($(this).closest('tr.cart_item'));
            });
        }
        tableFooterEvents(){
            const self = this;
            $(document.body).on('click', '.mpc-reset-table', function(){
                self.resetTable($(this));
            });
        }
        


        // global events.
        initTables(){
            const self = this;
            $(document.body).find('.mpc-container table.mpc-wrap').each(function(){
                let totalRow = 0, checkedRow = 0;
                $(this).find('tbody tr').each(function(){
                    self.setRowData($(this));
                    self.initTableRow();
                    
                    totalRow++;
                    if(!self.hasRowDisputs($(this))) checkedRow++;
                });

                const checkAllBox = $(this).find('.mpc-select-all input[type="checkbox"]');
                if(totalRow > 0 && totalRow === checkedRow && checkAllBox.length !== 0 && !checkAllBox.is(':checked')) checkAllBox.trigger('click');
            });
        }
        initTableRow(){
            if(!this.hasRowDisputs(this.$row) && this.$fields.checkBox.length !== 0 && !this.$fields.checkBox.is(':checked')) this.$fields.checkBox.trigger('click');
            
            const variationId = this.$variation ? this.$variation.id : 0;
            this.$row.attr('data-variation_id', variationId);
            
            this.$row.find('select').each(function(){ // triggering change since it's document load time.
                if($(this).find('option:selected').val().length !== 0) $(this).trigger('change');
            });
        }
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
        checkProductStock(){
            if(this.$row.attr('data-type') !== 'variable' || !this.$variation || !this.$variation.stock) return;

            const qty   = this.$fields.qtyField.length !== 0 ? parseInt(this.$fields.qtyField.val()) : 1;
            const stock = this.$variation['stock'].replace(/[A-Za-z]/g, '').replace(' ', '');
            if(!stock || stock === '0') return;

            if(stock && qty > parseInt(stock)){
                this.$fields.qtyField.val(parseInt(stock));
            }
        }
        resetTable(item){
            const self = this;
            const wrap = item.closest('.mpc-container');
            wrap.find('table.mpc-wrap tbody tr').each(function(){
                self.resetTableRow($(this));
            });

            const filterDropdown = wrap.find('select[name="mpc_orderby"]');
            if(filterDropdown.length !== 0 && filterDropdown.val() !== 'menu_order') filterDropdown.val('menu_order').trigger('change');

            const checkAll = wrap.find('.mpc-all-select input[type="checkbox"]');
            if(checkAll.length !== 0 && checkAll.is(':checked')) checkAll.trigger('click');
        }



        // variation related functions.
        setVariationImage(){
            if(!this.$variation) return;

            const imgWrap = this.$row.find('.mpc-product-image');
            const img     = this.$variation.image ?? false;
            if(!imgWrap || !img) return;

            if(img.thumbnail){
                imgWrap.find('.mpcpi-wrap img').attr('src', img.thumbnail);
            }
            if(img.full){
                imgWrap.find('.mpcpi-wrap img').attr('data-fullimage', img.full);
            }
        }
        setVariationPrice(){
            const priceWrap = this.$row.find('.mpc-single-price');
            if(!priceWrap) return;

            priceWrap.hide();
            if(!this.$variation) return;
            
            const price = this.$variation.price;
            priceWrap.find('span.total-price').text(this.formatPrice(price));
            typeof price !== 'undefined' ? priceWrap.show() : priceWrap.hide();

            // handle same variation price.
            const singlePrice = priceWrap.text().trim().replace(/[^0-9.$]/g, '');
            let rangePrice    = this.$row.find('.mpc-range').text().trim();
            rangePrice        = this.$row.find('.mpc-range ins').length > 0 ? this.$row.find('.mpc-range ins').text().trim() : rangePrice;
            if(singlePrice === rangePrice){ // fixed price variable product.
                priceWrap.hide();
            }
        }
        setVariationDescription(){
            if(!this.$variation || !this.$variation.desc){
                return;
            }

            if(!mpc_frontend.settings.variation_desc){ // if setting disabled.
                return;
            }
            
            const titleWrap = this.$row.find('td.mpc-product-name');
            if(!titleWrap){
                return;
            }

            const descWrap = this.$row.find('.woocommerce-product-details__short-description');
            descWrap.length !== 0 ? descWrap.text(this.$variation.desc) : titleWrap.append(`<p class="woocommerce-product-details__short-description">${this.$variation.desc}</p>`);
        }
        addClearVariations(){
            const total  = this.$row.find('select').length;
            let selected = 0;
            this.$row.find('select').each(function(){
                if($(this).find('option:selected').val()) selected++;
            });
            if(total === 0 || (total > 0 && total !== selected)){
                if(this.$row.find('.clear-button').length !== 0) this.$row.find('.clear-button').empty();
                return;
            }

            if(this.$row.find('.clear-button').length !== 0){
                this.$row.find('.clear-button').html(`<a class="reset_variations" href="#">Clear</a>`);
            }else{
                this.$row.find('.mpc-product-variation').append(`<div class="clear-button"><a class="reset_variations" href="#">Clear</a></div>`);
            }
        }
        disableRow(){
            let ability = true;
            const hasDisput = this.hasRowDisputs(this.$row);
            if(!hasDisput){
                if(!this.$variation) ability = false;
                if(this.$variation && (this.$variation.stock_status === 'outofstock' || !this.$variation.price)) ability = false;
            }

            if(ability){
                if(this.$fields.qtyField.length !== 0) this.$fields.qtyField.prop('disabled', false);
                if(this.$fields.checkBox.length !== 0){
                    this.$fields.checkBox.prop('disabled', false);
                    if(!hasDisput && !this.$fields.checkBox.is(':checked')) this.$fields.checkBox.trigger('click');
                }
            }else{
                if(this.$fields.qtyField.length !== 0){
                    this.$fields.qtyField.val(0);
                    this.$fields.qtyField.prop('disabled', true);
                }
                if(this.$fields.checkBox.length !== 0){
                    if(this.$fields.checkBox.is(':checked')) this.$fields.checkBox.trigger('click');
                    this.$fields.checkBox.prop('disabled', true);
                }
            }
        }
        variationAttributeMarker(attOpt){
            const marker = 'mpc-att-marker';
            if(attOpt.find('option:selected').val().length !== 0){
                if(!attOpt.hasClass(marker)) attOpt.addClass(marker);
            }else attOpt.removeClass(marker);
        }

        // helper functions.
        getRowTotalPrice(row){
            const qtyField  = row.find('.mpc-product-quantity input[type="number"]');
            const checkBox  = row.find('.mpc-product-select input[type="checkbox"]');
            if(checkBox && !checkBox.is(':checked')) return 0;

            const variation = this.getVariation(row);
            const price     = variation ? parseFloat(variation['price']) : parseFloat(row.data('price'));
            const qty       = qtyField && qtyField.length !== 0 ? parseInt(qtyField.val()) : 1;
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
        hasRowDisputs(row){
            let total = 0, selected = 0;
            row.find('select').each(function(){
                total++;
                if($(this).find('option:selected').val()) selected++;
            });
            return total > 0 && total === selected ? false : true;
        }
        resetTableRow(row){
            row.find('select').each(function(){
                $(this).val('').trigger('change');
            });

            const qtyField = row.find('.mpc-product-quantity input[type="number"]');
            const checkBox = row.find('.mpc-product-select input[type="checkbox"]');

            if(qtyField.length !== 0 && parseInt(qtyField.val()) > mpc_frontend.settings.default_qty) qtyField.val(mpc_frontend.settings.default_qty);
            if(checkBox.length !== 0 && checkBox.is(':checked')) checkBox.trigger('click');

            if(row.find('a.reset_variations').length !== 0) row.find('a.reset_variations').remove();
            if(row.find('.woocommerce-product-details__short-description').length !== 0) row.find('.woocommerce-product-details__short-description').text('');
        }



        setRowData(item){
            this.$row       = item.closest('tr.cart_item');
            this.$variation = this.getVariation(this.$row);

            this.$fields = {
                'qtyField' : this.$row.find('.mpc-product-quantity input[type="number"]'),
                'checkBox' : this.$row.find('.mpc-product-select input[type="checkbox"]')
            };
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