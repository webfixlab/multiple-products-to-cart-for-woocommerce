/**
 * Table loader functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcTableTemplate{
        constructor(){
            this.$wrap = null;
            this.$data = null;

            this.$product = null;
            this.$cols    = null;
            this.$hasVariable = false;
        }
        loadTable(tableWrap, response){
            this.$wrap = tableWrap;
            this.$data = response;

            let html = `<form class="mpc-cart" method="post" enctype="multipart/form-data" data-current_page="1">
                ${this.tableHeader()}
                ${this.renderTable()}
                ${this.tableFooter()}
            </form>`;

            this.$wrap.append(html);

            // let text = mpcHooks.applyFilters('mpcLoadTable', 'loaded', wrap);
            console.log('response', response);
        }


        tableHeader(){
            return `<div class="mpc-table-header">
                ${this.tableFilters()}
                ${this.tableAllCheckbox()}
            </div>`;
        }
        tableFilters(){
            const options = mpcHooks.applyFilters('mpcFilters', {
                'menu_order': 'Default sorting',
                'price-ASC':  'Price: low to high',
                'price-DESC': 'Price: hight to low'
            });

            if(mpc_frontend.settings?.title_filter === true){
                options['title-ASC']  = 'Title: a to z';
                options['title-DESC'] = 'Title: z to a';
            }
            
            let html = '';
            for(const key in options){
                html += `<option value="${key}">${options[key]}</option>`;
            }

            return `<div class="mpc-sort">
                <select name="mpc_orderby" class="mpc-orderby" title="Table order by">
                ${html}
                </select>
                <input type="hidden" name="paged" value="1" />
            </div>`;
        }
        tableAllCheckbox(){
            return `<div class="mpc-all-select">
                <label>Select all</label>
                <input type="checkbox" class="mpc-check-all">
            </div>`;
        }
        renderTable(){
            return `<table class="mpc-wrap" cellspacing="0">
                <thead><tr>${this.tableHeaderColumns()}</tr></thead>
                <tbody>
                    ${this.allProductRows()}
                </tbody>
            </table>`;
        }
        tableHeaderColumns(){
            const cols = this.$data?.atts?.columns ?? mpc_frontend.settings.default_cols;
            if(cols.length === 0) return;

            this.$cols = cols;

            const labels = {
                'wmc_ct_image' :     'Image',
                'wmc_ct_product' :   'Product',
                'wmc_ct_variation' : 'Variation',
                'wmc_ct_price' :     'Price',
                'wmc_ct_quantity' :  'Quantity',
                'wmc_ct_buy' :       'Buy',
            };
            const pro_cols = ['wmc_ct_stock', 'wmc_ct_category', 'wmc_ct_rating', 'wmc_ct_tag', 'wmc_ct_sku'];

            // check if any variable products exists inside of the table.
            for(const i in this.$data.products){
                if(this.$data.products[i].type === 'variable'){
                    this.$hasVariable = true;
                    break;
                }
            }

            let html = '';
            for(const i in cols){
                const col = cols[i];
                if(mpc_frontend.has_pro === false && pro_cols.indexOf(col) !== -1) continue;
                if(col.indexOf('variation') !== -1 && !this.$hasVariable) continue;

                html += `<th for="${col}" class="mpc-product-${col.replace('wmc_ct_')}">${labels[col]}</th>`;
            }
            return html;
        }
        allProductRows(){
            if(!this.$data.products) return;

            let html = '';
            for(const id in this.$data.products){
                this.$product = this.$data.products[id];
                html += mpcHooks.applyFilters( 'mpcTableRow', this.productRow(), this.$product, this.$cols);
            }
            return html;
        }
        productRow(){
            return `<tr class="cart_item" data-id="${this.$product.id}">
                ${this.getRowItems()}
            </tr>`;
        }
        getRowItems(){
            let html = '';
            for(const i in this.$cols){
                const col = this.$cols[i];
                if(col === 'wmc_ct_image') html += this.productImage();
                else if(col === 'wmc_ct_product') html += this.productDetails();
                else if(col === 'wmc_ct_variation') html += this.productVariations();
                else if(col === 'wmc_ct_price') html += this.productPrice();
                else if(col === 'wmc_ct_quantity') html += this.productQuantity();
                else if(col === 'wmc_ct_buy') html += this.productBuy();
                else html += mpcHooks.applyFilters('mpcRowItem', '', col, this.$product);
            }
            return html;
        }



        productImage(){
            const thumb = this.$product?.image?.thumb ?? mpc_frontend.wc_default_img.thumb;
            return `<td for="image" class="mpc-product-image">
                <div class="mpcpi-wrap">
                    ${this.productOnSale()}
                    <img src="${thumb}" class="mpc-product-image attachment-thumbnail size-thumbnail" alt="${this.$product.title}">
                </div>
            </td>`;
        }
        productOnSale(){
            if(!mpc_frontend.settings?.show_sale) return;
            return `<span class="wfl-sale">sale</span>`;
        }
        productDetails(){
            return `<td for="title" class="mpc-product-name">
                <div class="mpc-product-title">
                    ${this.productTitle()}
                    ${this.productDescription()}
                </div>
            </td>`;
        }
        productTitle(){
            return `<a href="${this.$product.url}">${this.$product.title}</a>`;
        }
        productDescription(){
            if(!this.$product.desc) return '';
            return `<div class="woocommerce-product-details__short-description">
                <p>${this.$product.desc}</p>
            </div>`;
        }
        productPrice(){
            return `<td for="price" class="mpc-product-price">
                <div class="mpc-single-price">
                    ${this.productPriceRange()}
                </div>
                <div class="mpc-range">
                    ${this.$product.price}
                </div>
            </td>`;
        }
        productPriceRange(){
            if(this.$product.type !== 'variable') return;
            return `<span class="woocommerce-Price-amount amount">
                <bdi>
                    <span class="total-price">0</span>
                    <span class="woocommerce-Price-currencySymbol">${mpc_frontend.currency}</span>
                </bdi>
            </span>`;
        }
        productQuantity(){
            return `<td for="quantity" class="mpc-product-quantity">
                ${this.productQuantityField()}
            </td>`;
        }
        productQuantityField(){
            if(this.$product.type === 'grouped') return '';

            let stock = this.$product.stock_status === 'instock' && this.$product.stock ? parseInt(this.$product.stock) : '';
            stock     = this.$product.sold_individually ? 1 : stock;
            
            let default_qty = mpc_frontend.settings.default_qty ?? 0;
            default_qty     = stock && default_qty > stock ? stock : default_qty;

            const min = 1;
            const max = stock.length === 0 ? '' : stock;

            return `<div class="quantity">
                <input
                    type="number"
                    name="quantity${this.$product.id}"
                    value="${default_qty}"
                    class="input-text qty text"
                    step="1"
                    min="${min}"
                    max="${max}"
                    title="Quantity"
                    size="4"
                    inputmode="numeric"
            </div>`;
        }
        productBuy(){
            const ids     = this.$data?.atts?.selected ?? [];
            const checked = ids.indexOf(this.$product.id) !== -1 ? 'checked' : '';
            return `<td for="buy" class="mpc-product-select">
                <span class="mpc-mobile-only">
                    ${mpc_frontend.labels.buy}
                </span>
                <input
                    type="checkbox"
                    name="product_ids[]"
                    value="${this.$product.id}"
                    ${checked}>
            </td>`;
        }
        productVariations(){
            if(!this.$hasVariable) return '';
            return `<td for="variation" class="mpc-product-variation">
                ${this.emptyVariations()}
                ${this.variationAttributes()}
                ${this.clearVariations()}
            </td>`;
        }
        emptyVariations(){
            if(this.$product.type === 'variable') return '';
            return `<span>${mpc_frontend.labels.empty_variation}</span>`;
        }
        variationAttributes(){
            if(!this.$product.atts) return '';

            let html = '';
            for(const att in this.$product.atts){
                let attName = mpc_frontend.labels.variation_prefix;
                attName = attName.length === 0 ? this.$product.att_names[att] : '&nbsp;' + attName;
                html += `<div class="variation-group">
                    <select class="${att}" name="attribute_${att}">
                        <option value="">${attName}</option>
                        ${this.variationAttributeOptions(att)}
                    </select>
                </div>`;
            }
            return html;
        }
        variationAttributeOptions(att){
            let html = '';
            for(const i in this.$product.atts[att]){
                html += `<option value="${this.$product.atts[att][i].slug}" ${this.isVariationSelected(att, i)}>${this.$product.atts[att][i].name}</option>`;
            }
            return html;
        }
        isVariationSelected(att, i){
            return this.$product?.atts[att][i].slug === this.$product?.default_atts[att] ? 'selected' : '';
        }
        clearVariations(){
            const total    = this.$product.atts ? Object.keys(this.$product.atts).length : 0;
            const def_atts = this.$product.default_atts ? Object.keys(this.$product.default_atts).length : 0;
            return total === 0 || total !== def_atts ? '' : `<div class="clear-button"><a class="reset_variations" href="#" aria-label="Clear options">Clear</a></div>`;
        }



        tableFooter(){
            return `<div class="mpc-table-footer">
                <div class="total-row">
                    ${this.tableTotal()}
                    ${this.tableAddToCart()}
                </div>
                ${this.tablePagination()}
            </div>
            `;
        }
        tableTotal(){
            return `<div class="mpc-total-wrap">
                ${this.tableTotalPrice()}
                ${this.tableResetButton()}
            </div>`;
        }
        tableTotalPrice(){
            return `<div class="mpc-table-total">
                <span class="total-label">${mpc_frontend.labels.total}</span>
                <span class="mpc-total">
                    <span class="woocommerce-Price-amount amount">
                        <bdi>
                            <span class="total-price">${this.formatPrice(0)}</span>
                            <span class="woocommerce-Price-currencySymbol">${mpc_frontend.currency}</span>
                        </bdi>
                    </span>
                </span>
            </div>`;
        }
        tableResetButton(){
            if(!mpc_frontend.settings.reset_btn) return '';
            return `<div class="mpc-reset-table">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32.00 32.00">
                    <path d="M27.1 14.313V5.396L24.158 8.34c-2.33-2.325-5.033-3.503-8.11-3.503C9.902 4.837 4.901 9.847 4.899 16c.001 6.152 5.003 11.158 11.15 11.16 4.276 0 9.369-2.227 10.836-8.478l.028-.122h-3.23l-.022.068c-1.078 3.242-4.138 5.421-7.613 5.421a8 8 0 0 1-5.691-2.359A7.993 7.993 0 0 1 8 16.001c0-4.438 3.611-8.049 8.05-8.049 2.069 0 3.638.58 5.924 2.573l-3.792 3.789H27.1z"></path>
                </svg>
            </div>`;
        }
        tableAddToCart(){
            return `<div class="mpc-add-to-cart">
                <input
                    type="submit"
                    class="mpc-add-to-cart single_add_to_cart_button button alt wc-forward"
                    name="proceed"
                    value="${mpc_frontend.labels.add_to_cart}" />
            </div>`;
        }
        tablePagination(){
            return `<div class="mpc-pagination-wrap">
                ${this.tablePaginationPages()}
                ${this.tablePaginationInfo()}
            </div>`;
        }
        tablePaginationPages(){
            const pages = this.getPaginationPageNumbers();
            if(!pages) return '';

            let html    = '';
            let prev    = 0; // previous page number.
            for(const i in pages){
                html += prev > 0 && (pages[i] - prev) > 1 ? '<span class="mpc-divider">...</span>' : '';
                html += `<span class="${pages[i] === 1 ? 'current' : ''}">${pages[i]}</span>`;
                prev = pages[i];
            }
            return `<div class="mpc-pagenumbers"">
                ${html}
            </div>`;
        }
        getPaginationPageNumbers(){
            const total = this.$data.max_num_pages ?? 1;
            if(total <= 1) return [];

            const current  = 1;
            const surround = 1;
            let pages = Array.from(new Set( // Find unique numbers.
                [1, total].concat( // Merge all page numbers.
                    Array.from({ length: (Math.min(total, current + surround) - Math.max(1, current - surround) + 1) }, (_, i) => i + Math.max(1, current - surround))
                )
            ));

            pages.sort((a, b) => a - b);
            return pages;
        }
        tablePaginationInfo(){
            if(!mpc_frontend.settings.pagination_info) return '';
            if(!this.$data?.atts?.pagination) return '';

            const limit = this.$data?.atts?.limit ?? 10;
            if(this.$data.found_posts < limit) return '';
            const range = `1 - ${Math.min(limit, this.$data.found_posts)}`;
            return `<div class="mpc-product-range">
                <p>
                    ${mpc_frontend.labels.pagination_text.replace('%1$s', mpc_frontend.labels.pagination_prefix).replace('%2$s', range).replace('%3$s', this.$data.found_posts)}
                </p>
            </div>`;
        }



        formatPrice(price){
            return price.toLocaleString( mpc_frontend.locale, {
                minimumFractionDigits: mpc_frontend.dp,
                maximumFractionDigits: mpc_frontend.dp,
                useGrouping: true
            });
        }
    }

    window.mpcTableTemplate = new mpcTableTemplate();
})(jQuery, window, document);
