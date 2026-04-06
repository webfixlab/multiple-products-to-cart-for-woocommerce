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
			$( document ).ready(
				() => this.initAdminEvents()
			);
		}
		initAdminEvents(){
			this.initEventTriggers();
			this.preparePage();
		}
		initEventTriggers(){
			$( 'body' ).on(
				'click',
				'.mpcdp_submit_button',
				( e ) => this.setSortedColumns()
			);
			$( 'input[name="wmc_redirect"]' ).on(
				'click',
				( e ) => this.redirectOptionsHandler( $( e.currentTarget ) )
			);
			$( '.number-input' ).on(
				'keypress',
				( e ) =>
				{
					var field = $( e.currentTarget );
					if ( e.originalEvent.which < 48 || e.originalEvent.which > 57 ) {
						e.preventDefault();
						field.addClass( 'mpc-notice' );
					}
					setTimeout(
						() => field.removeClass( 'mpc-notice' ),
						1000
					);
				}
			);
			$( '.hurkanSwitch-switch-item' ).on(
				'click',
				( e ) =>
				{
					var input = $( e.currentTarget ).closest( '.mpcdp_settings_option' ).find( 'input[type="checkbox"]' );
					if ( input.hasClass( 'mpcex-disabled' ) && ! mpca_obj.has_pro ) {
						e.preventDefault();
						var title = input.attr( 'title' );
						$( 'body' ).find( '#mpcpop .mpc-focus span' ).text( title );
						$( 'body' ).find( '#mpcpop' ).show();
					} else {
						this.toggleSwitch( $( e.currentTarget ) );
					}
				}
			);
		}
		preparePage(){
			// if one of the section is empty, do something as it can't be sorted later.
			$( "#active-mpc-columns, #inactive-mpc-columns" ).each(
				( _, el ) =>
				{
					if ( $( el ).find( 'li' ).length === 0 ) $( el ).addClass( 'empty-cols' );
				}
			);

			// column sortable.
			var has_colsort = $( 'body' ).find( '#active-mpc-columns' );

			if ( typeof has_colsort != 'undefined' && has_colsort.length > 0 ) {
				$( "#active-mpc-columns, #inactive-mpc-columns" ).sortable(
					{
						connectWith: '.connectedSortable',
						remove:
						( event, ui ) =>
						{
							if ( ui.item.hasClass( 'mpc-stone-col' ) ) return false;

							var itemTo = ui.item.closest( 'ul' );
							var itemFrom = $( event.target );
							this.setMinHeight(itemFrom, itemTo);
						}
					}
				);
			}
		}



		setSortedColumns() {
			var sequence = '';
			$( '#active-mpc-columns' ).find( 'li' ).each(
				( e ) =>
				{
					if ( sequence.length === 0 ) {
						sequence += $( e.currentTarget ).data( 'meta_key' );
					} else {
						sequence += ',' + $( e.currentTarget ).data( 'meta_key' );
					}
				}
			);
			$('.mpc-sorted-cols').val(sequence);
		}
		redirectOptionsHandler( el ){
			var v = el.val();
			var section = el.closest( '.mpcdp_settings_option' );
			var field_id = section.data( 'field-id' );

			section.closest( '.mpcdp_container' ).find( '.mpcdp_settings_option' ).each(
				( _, el ) =>
				{
					if ( $( el ).data( 'depends-on' ) === field_id && mpca_obj.has_pro ) {
						$( el ).slideToggle( 'slow', 'custom' === v );
					}
				}
			);
		}
		toggleSwitch( btn ) {
			var wrap = btn.closest( '.hurkanSwitch-switch-box' );
			wrap.find( '.hurkanSwitch-switch-item' ).each(
				( _, el ) => $( el ).toggleClass( 'active' )
			);

			btn.closest( '.mpcdp_settings_option' ).find( 'input[type="checkbox"]' ).trigger( 'click' );

			this.handleFollowupField( btn.closest( '.mpcdp_settings_option' ) );

			wrap.toggleClass( 'switch-animated-off switch-animated-on', 1000 );
		}
		handleFollowupField( section ) {
			var field_id = section.data( 'field-id' );
			section.closest( '.mpcdp_container' ).find( '.mpcdp_settings_option' ).each(
				( e ) =>
				{
					if ( $( e.currentTarget ).data( 'depends-on' ) === field_id ) {
						$( e.currentTarget ).slideToggle( 'slow' );
					}
				}
			);
		}
		setMinHeight( from, to ) {
			from = typeof from === 'undefined' ? $('#') : from;
			fo   = typeof fo === 'undefined' ? $('#') : fo;

			var height        = to.find('li')[0].offsetHeight;
			var fromMinHeight = from.find('li') ? (from.find('li').length + 1) * height : height;
			var toMinHeight   = to.find('li') ? (to.find('li').length + 1) * height : height;

			from.css( { 'min-height': fromMinHeight + 'px' } );
			to.css( { 'min-height': toMinHeight + 'px' } );
		}
	}
	new MPCAdminEventsHandler();
} )( jQuery, window, document );
