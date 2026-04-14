/**
 * Admin JS events handler
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCAdminEventsHandler{
		constructor(){
			$( document ).ready( () => this.initEventTriggers() );
		}
		initEventTriggers(){
			$( 'input[name="wmc_redirect"]' ).on( 'click', e => this.redirectOptionsHandler( e ) );
			$( '.hurkanSwitch-switch-item' ).on( 'click', e => this.toggleSwitch( e ) );
			$( '.mpcdp_submit_button' ).on( 'click', () => this.setSortedColumns() ); // use form submit event here !!!
			this.initColumnSorting();
		}
		initColumnSorting(){
			const columnSorting = $( '#active-mpc-columns, #inactive-mpc-columns' );
			if( !columnSorting || 0 === columnSorting.length ){
				return;
			}
			columnSorting.sortable( {
				connectWith: '.connectedSortable',
				remove: ( event, ui ) => this.makeMoreSpace( event, ui )
			} );
		}
		redirectOptionsHandler( e ){
			const elm     = $( e.currentTarget );
			const section = elm.closest( '.mpcdp_settings_option' );
			section.closest( '.mpcdp_container' ).find( '.mpcdp_settings_option' ).each( ( _, dep ) => {
				if ( $( dep ).data( 'depends-on' ) === section.data( 'field-id' ) && mpc_admin.has_pro ) {
					$( dep ).slideToggle( 'slow', 'custom' === elm.val() );
				}
			} );
		}
		toggleSwitch( e ) {
			const section  = $( e.currentTarget ).closest( '.mpcdp_settings_option' );
			const checkBox = section.find( 'input[type="checkbox"]' );
			if ( checkBox.hasClass( 'mpcex-disabled' ) && ! mpc_admin.has_pro ) {
				e.preventDefault();
				$( '#mpcpop .mpc-focus span' ).text( checkBox.attr( 'title' ) );
				$( '#mpcpop' ).show();
				return;
			}

			checkBox.trigger( 'click' );
			this.handleFollowupField( section );

			const switchBox = section.find( '.hurkanSwitch-switch-box' );
			switchBox.find( '.hurkanSwitch-switch-item' ).each( ( _, el ) => $( el ).toggleClass( 'active' ) );
			switchBox.toggleClass( 'switch-animated-off switch-animated-on', 1000 );
		}
		handleFollowupField( section ) {
			var field_id = section.data( 'field-id' );
			section.closest( '.mpcdp_container' ).find( '.mpcdp_settings_option' ).each( ( e ) => {
				if ( $( e.currentTarget ).data( 'depends-on' ) === field_id ) {
					$( e.currentTarget ).slideToggle( 'slow' );
				}
			} );
		}
		makeMoreSpace( event, ui ) {
			if ( ui.item.hasClass( 'mpc-stone-col' ) ) {
				return;
			}
			
			const wrapTo   = ui.item.closest( 'ul' );
			const wrapFrom = $( event.target );

			const heightBase    = wrapTo.find( 'li' )[0].offsetHeight;
			const fromMinHeight = ( wrapFrom.find( 'li' ).length + 1 ) * heightBase;
			const toMinHeight   = ( wrapTo.find( 'li' ).length + 1 ) * heightBase;

			wrapFrom.css( { 'min-height': fromMinHeight + 'px' } );
			wrapTo.css( { 'min-height': toMinHeight + 'px' } );
		}
		setSortedColumns() {
			const allActiveCols = $( '#active-mpc-columns li' ).map( ( _, el ) => $( el ).data( 'meta_key' ) ).get();
			$('.mpc-sorted-cols').val( allActiveCols.join( ',' ) );
		}
	}
	new MPCAdminEventsHandler();
} )( jQuery, window, document );
