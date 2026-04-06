/**
 * Frontend table events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCFrontTable{
		constructor(){
			$( document ).ready(
				() => this.initEventTriggers()
			);
		}
		initEventTriggers(){
            // trigger new custom events from this free version with arguments and use it on pro.
            $( document ).on( 'mpc_table_loader', ( wrap ) => this.onTableLoaded( wrap ) );



            $( document ).on( 'mpc_loader', ( action, wrap ) => this.tableLoader( action, wrap ) );
            


            // on ESC key pressed, hide popup box.
            $(document).on('keyup', function (e) {
                if (e.keyCode == 27) {
                    $('#mpcpop').hide();
                }
            });
            /**
             * User Events section
             * On popup box clicked, hide it
             */
            $('#mpcpop').on('click',function (e) {
                if (e.target.tagName.toLowerCase() == 'img') {
                    $('#mpcpop .image-wrap img').attr('src', $(e.target).attr('data-fullimage'));
                    $('#mpcpop .mpc-gallery img').removeClass('mpcgi-selected');
                    $(e.target).addClass('mpcgi-selected');
                } else {
                    $('#mpcpop').hide()
                }
            });

            // on close button of popup box clicked, hide it.
            $('body').on('click', 'span.mpcpop-close', function () {
                $('#mpcpop').hide();
            });

            $(document).on('click', '.mpc-to-top', function () {
                var btn = $(this);
                $('html, body').animate({
                    scrollTop: btn.closest('form').offset().top - 80
                }, 'slow');
            });
        }
        tableLoader( way, elem ) {
            var wrap = elem.closest('.mpc-container');
            if (way == 'load') {
                wrap.find('table').before('<span class="mpc-loader"><img src="' + mpc_frontend.imgassets + 'loader.gif"></span>');
            } else if (way == 'close') {
                $('body').find('.mpc-loader').remove();
            }
        }
        onTableLoaded( wrap ){
            wrap.find('.mpc-fixed-header').remove();
            renderStickyHead(wrap.find('table'));

            $('html, body').animate({
                scrollTop: $(wrap).offset().top - 80
            }, 'slow');
            mpc_dynamic_product_pricing(); // table.
            mpc_init_select_all(wrap);
        }
	}
	new MPCFrontTable();
} )( jQuery, window, document );
