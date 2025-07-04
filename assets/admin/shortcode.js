/**
 * Admin settings page functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcAdminShortcode{
        constructor(){
            $(document).ready(() => {
                const self = this;
                $(document.body).find('.mpc-sc-itembox').each(function(){
                    self.initChoiceJSItem($(this));
                });
            });
        }
		initChoiceJSItem(item){
            const self = this;
			const id   = item.attr('id');
			if(id.length === 0) return; // true/none = continue, false = break.

            const single = id === 'cats' ? 'category' : 'product';
            const plural = id === 'cats' ? 'categories' : 'products';

			var params = {
				removeItemButton:      true,
				placeholder:           true,
				placeholderValue:      'Choose options',
				shouldSort:            false,
				itemSelectText:        `Select ${id}`,
				duplicateItemsAllowed: false,
				searchEnabled:         false,
			};

			if(id === 'ids' || id === 'selected' || id === 'skip_products' || id === 'cats'){
				params['searchEnabled']     = true;
				params['searchFields']      = ['label', 'value'];
				params['itemSelectText']    = `Select ${plural}`;
				params['noChoicesText']     = `Type the ${single} name`;
				params['searchResultLimit'] = 50;
			}

			var type = new Choices(document.querySelector('#' + id), params); // `#${id}`

			var debounceTimeout;
			$('#' + id).on('search', function(event){
				clearTimeout(debounceTimeout);
				type.setChoices([{ value: '', label: 'Loading...' }], 'value', 'label', true);

				debounceTimeout = setTimeout(function(){
					var data = new FormData();
					data.append('action', 'mpc_admin_search_box');
					data.append('search', event.detail.value);
					data.append('type_name', id);
					data.append('nonce', mpc_admin.nonce);

					$.ajax({
						url:         mpc_admin.ajaxurl,
						method:      'POST',
						data:        data,
						dataType:    'json',
						processData: false,
						contentType: false,
						success:     function(response){
							type.clearChoices();
							type.setChoices(response, 'id', 'name', false);
							if(response.length === 0) type.setChoices([{ value: '', label: `No ${single} found.` }], 'value', 'label', true);
						},
						error: function(jqXHR, textStatus, errorThrown){
							type.setChoices([{ value: '', label: 'Error fetching data.' }], 'value', 'label', true);
						}
					});
				}, 1000);
			});

			type.passedElement.element.addEventListener('addItem', function(event){
				self.UpdateFieldValue($(this).closest('.choicesdp'), type.getValue(true));
			});

			type.passedElement.element.addEventListener('removeItem', function(event){
				self.UpdateFieldValue($(this).closest('.choicesdp'), type.getValue(true));
			});
		}
        UpdateFieldValue(wrap, values) {
			values = typeof values === 'undefined' ? '' : typeof values === 'string' ? values : values.join(',');
			wrap.find('.choicesdp-field').val(values);
		}
    }

    new mpcAdminShortcode();
})(jQuery, window, document);
