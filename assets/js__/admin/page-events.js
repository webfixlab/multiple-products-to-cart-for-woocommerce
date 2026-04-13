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
			$( document ).ready( () => this.initEventTriggers() );
		}
		initEventTriggers(){
            $( '.mpc-colorpicker' ).wpColorPicker();

            $( '.mpcex-disabled' ).on( 'click', e => this.showProPopup( e ) );
            $( '#mpcpop, span.mpcpop-close' ).on( 'click', e => this.hideProPopup( e ) );
            $( document ).on( 'keyup', e => this.hideProPopup( e ) );

            $( window ).on( 'scroll resize', () => this.screenChangeEventHandler() );
        }
        showProPopup( e ){
            e.preventDefault();
            $( '#mpcpop .mpc-focus span' ).text( $( e.currentTarget ).attr( 'title' ) );
            $( '#mpcpop' ).show();
        }
        hideProPopup( e ){
            if( 27 !== e.keyCode ){
                return;
            }
            $( 'body' ).find( '#mpcpop' ).hide()
        }
        screenChangeEventHandler(){
            const sidebar   = $( '.mpcdp_settings_sidebar' )[0];
            const stickyTab = $( '.mpcdp_sidebar_tabs.is-affixed .inner-wrapper-sticky' );
            if( ! sidebar || ! stickyTab || 0 === sidebar.length || 0 === stickyTab.length ){
                return;
            }
            stickyTab.css( 'width', `${sidebar.clientWidth}px` );
        }
	}
	new MPCAdminTable();
} )( jQuery, window, document );
