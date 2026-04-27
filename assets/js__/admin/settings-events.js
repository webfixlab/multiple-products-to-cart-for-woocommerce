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
			$( '.mpc-switch-state' ).on( 'click', e => this.toggleSwitch( e ) );
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

			this.setDynamicSortableArea();
		}
		redirectOptionsHandler( e ){
			const elm  = $( e.currentTarget );
			const wrap = elm.closest( '.mpcdp_settings_toggle' );
			wrap.find( '.mpc-followup' ).each( ( _, el ) => $( el ).hide() );

			const followup   = elm.attr( 'data-followup' );
			const followWrap = followup && followup.length > 0 ? wrap.find( `.mpc-followup-${followup}`) : null;
			if( followWrap && followWrap.length > 0 ){
				followWrap.slideToggle( 'slow' );
			}
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

			section.find( '.mpc-switch .mpc-switch-state' ).each( ( _, el ) => $( el ).toggleClass( 'active', 1000 ) );
		}
		makeMoreSpace( event, ui ) {
			if ( ui.item.hasClass( 'mpc-stone-col' ) ) {
				return false;
			}
			
			this.setDynamicSortableArea();
		}
		setDynamicSortableArea(){
			const activeCols   = $( '#active-mpc-columns' );
			const inActiveCols = $( '#inactive-mpc-columns' );

			const baseHeight =$( '#active-mpc-columns, #inactive-mpc-columns' ).find( 'li' )[0].offsetHeight;
			// const baseHeight = 'undefined' !== typeof activeCols.find( 'li' ) ? activeCols.find( 'li' )[0].offsetHeight : activeCols.find( 'li' )[0].offsetHeight;
			const maxHeight  = Math.max( ( activeCols.find( 'li' ).length + 1 ) * baseHeight, ( inActiveCols.find( 'li' ).length + 1 ) * baseHeight );

			activeCols.css( { 'min-height': `${maxHeight}px` } );
			inActiveCols.css( { 'min-height': `${maxHeight}px` } );
		}
		setSortedColumns() {
			const allActiveCols = $( '#active-mpc-columns li' ).map( ( _, el ) => $( el ).data( 'meta_key' ) ).get();
			$('.mpc-sorted-cols').val( allActiveCols.join( ',' ) );
		}
	}
	new MPCAdminEventsHandler();
} )( jQuery, window, document );
