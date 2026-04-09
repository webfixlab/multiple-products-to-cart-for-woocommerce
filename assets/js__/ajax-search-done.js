/**
 * Frontend table loader events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCFrontTableLoader{
		constructor(){
            this.$tableState = {}; // current table state.
			$( document ).ready(
				() => this.initEvents()
			);
		}
		initEvents(){
            // ajax category filter.
            $('body').on('change', '.mpcp-cat-filter select', function () {
                mpc_order_by_table($(this));
            });
            $('body').on('change', '.mpcp-tag-filter select', function () {
                table_loader_by_tag(parseInt($(this).find('option:selected').val()), 'tags', $(this).closest('.mpc-container'));
            });
            // on category clicked, filter that category products instead of going to archive page.
            $(document).on('click', '.mpc-product-category a, .mpc-product-tag a, .mpc-subheader-info a', function (e) {
                e.preventDefault();
                var id = parseInt($(this).data('id'));
                var wrapper = $(this).closest('.mpc-container');

                var type = $(this).closest('td').hasClass('mpc-product-category') ? 'cats' : $(this).closest('td').hasClass('mpc-product-tag') ? 'tags' : '';
                var select = 'cats' === type ? wrapper.find('.mpc-cat-filter') : wrapper.find('.mpc-tag-filter');
                if (typeof select !== 'undefined' && select.length > 0) {
                    select.val(id).trigger('change');
                    return;
                }
                table_loader_by_tag(id, type, wrapper);
            });
            // ajax search.
            var searchTimer;
            $('body').on('input', '.mpc-asearch input', function () {
                var input = $(this);
                var s = input.val();

                if (s.length) {
                    input.closest('.mpc-asearch').find('.mpc-close-search').show();
                } else {
                    input.closest('.mpc-asearch').find('.mpc-close-search').hide();
                }

                clearTimeout(searchTimer);

                searchTimer = setTimeout(function () {
                    if (s.length === 0 || s.length >= 3) {
                        mpc_order_by_table(input);
                    }
                }, 1000);
            });
            $('body').on('click', '.mpc-close-search', function () {
                $(this).closest('.mpc-asearch').find('input').val('').trigger('change');
                $(this).hide();
            });
        }
        table_loader_by_tag(id, type, wrapper) {
            var atts = mpc_get_atts(wrapper);
            atts[type] = id;

            ajax_table_loader(atts, 1, wrapper);
        }
	}
	new MPCFrontTableLoader();
} )( jQuery, window, document );
