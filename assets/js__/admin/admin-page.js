/**
 * Admin JS events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCAdminTable{
		constructor(){
			$( document ).ready(
				() => this.initEventTriggers()
			);
		}
		initEventTriggers(){
            // initialize default WP color picker.
            $( '.mpc-colorpicker' ).wpColorPicker();

            $( 'body' ).on(
                'click',
                '.mpcex-disabled',
                ( e ) =>
                {
                    e.preventDefault();

                    $( 'body' ).find( '#mpcpop .mpc-focus span' ).text( $( e.currentTarget ).attr( 'title' ) );
                    $( 'body' ).find( '#mpcpop' ).show();
                }
            );

            // for closing popup.
            $( '#mpcpop, span.mpcpop-close' ).on(
                'click',
                () => $( 'body' ).find( '#mpcpop' ).hide()    
            );
            
            $( document ).on(
                'keyup',
                ( e ) =>
                {
                    if ( 27 === e.keyCode ) {
                        $( 'body' ).find( '#mpcpop' ).hide(); // esc key pressed.
                    }
                }
            );

            $( window ).on(
                'scroll',
                () =>
                {
                    $( '.mpcdp_sidebar_tabs.is-affixed .inner-wrapper-sticky' ).css(
                        'width',
                        $( '.mpcdp_settings_sidebar' )[0].clientWidth + 'px'
                    );

                    const stickyOffset = $( '.mpcdp_sidebar_tabs' ).offset().top;
                    $( '.mpcdp_sidebar_tabs' ).toggleClass( 'is-affixed', $( window ).scrollTop() > stickyOffset );
                }
            );

            $( window ).on(
                'resize',
                () => $( '.mpcdp_sidebar_tabs.is-affixed .inner-wrapper-sticky' ).css(
                    'width',
                    $( '.mpcdp_settings_sidebar' )[0].clientWidth + 'px'
                )
            );
        }
	}
	new MPCAdminTable();
} )( jQuery, window, document );
