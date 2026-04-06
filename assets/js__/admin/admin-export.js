/**
 * Admin export settings and tables
 *
 * @package    Wordpress
 * @subpackage Multiple Products to Cart for Woocommerce
 * @since      9.0.0
 */

( function ( $, window, document ) {
	class MPCExportSettings{
		constructor(){
            this.$noticeWrap = null; // notice wrapper.
            this.$expBtn     = null; // export button.
			$( document ).ready(
				() => this.initAdminEvents()
			);
		}
		initAdminEvents(){
			$( document ).on(
                'click',
                '#mpc-export',
                ( e ) =>
                {
                    e.preventDefault();

                    this.$expBtn = $( e.currentTarget );
                    this.$expBtn.prop( 'disabled', true );

                    this.exportHandler();
                }
            );
		}
        exportHandler(){
            this.$noticeWrap = $( document ).find( '#export-success' );
            this.$noticeWrap.toggle( 'slow', mpc_export.has_pro && '1' === mpc_export.has_pro );

            this.requestExport();
        }
        requestExport(){
            $.ajax(
                {
                    url: mpc_export.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mpc_export_settings',
                        security: mpc_export.export_nonce,
                    },
                    success: ( res ) => this.responseHandler( res ),
                    complete: () => this.$expBtn.prop( 'disabled', false ),
                }
            );
        }
        responseHandler( response ){
            if ( response.success ) {
                const link    = document.createElement( 'a' );
                link.href     = response.data.file_url;
                link.download = 'mpc_export.json';

                link.click();

                this.noticeHandler();
            } else {
                alert( mpc_export.failed );
            }
        }
        exportButton( isDisabled ){
            this.$expBtn.prop( 'disabled', isDisabled );
            // if( ! isDisabled ) {
            //     this.$expBtn.text( 'Export' );
            // }
        }
        noticeHandler(){
            this.$noticeWrap.find( '.mpcdp_option_label' ).text( mpc_export.export_ok );
            setTimeout(
                () =>
                {
                    this.$noticeWrap.toggle();
                    this.$noticeWrap.find( '.mpcdp_option_label' ).text( mpc_export.export_text );
                },
                3000
            );
        }
	}
	new MPCExportSettings();
} )( jQuery, window, document );
