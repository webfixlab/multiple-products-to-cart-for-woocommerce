/**
 * Admin settings page functions
 *
 * @package    Wordpress
 * @subpackage Product Role Rules Premium
 * @since      8.0.0
 */

;(function($, window, document) {
    class mpcAdminEvents{
        constructor(){
			this.$popup = null;
            $(document).ready(() => {
                this.init();
            });
        }
        init(){
			this.settingsEvents();
			this.settingsFieldsEvents();
			this.shortCodeEvents();
		}
		settingsEvents(){
			const self = this;
			this.$popup = $(document.body).find('#mpcpop');
			$('.mpc-colorpicker').wpColorPicker(); // color picker.

			$(document.body).on('click', '#mpcpop', function(){
				$(this).hide();
			});
			$(document.body).on('click', 'span.mpcpop-close', function(){
				self.$popup.hide();
			});
			$(document.body).on('keyup', function(e){
				if(e.keyCode === 27) self.$popup.hide();
			});
			$(document.body).on('click', '.mpcex-disabled', function(e){
				e.preventDefault();
				const title = $(this).attr('title');
				self.$popup.find('.mpc-focus span').text(title);
				self.$popup.show();
			});
			$(document.body).on('click', '.mpcdp_submit_button', function(){
				self.sortTableColumns();
			});

			$(document.body).on('click', '#mpc-export', function(e){
				e.preventDefault();
				self.exportSettings($(this));
			});



			$(window).on('scroll', function(){
				const sidebarTabs = $(document.body).find('.mpcdp_sidebar_tabs');
				$('.mpcdp_sidebar_tabs.is-affixed .inner-wrapper-sticky').css('width', `${$('.mpcdp_settings_sidebar')[0].clientWidth}px`);

				var stickyOffset = sidebarTabs.offset().top;
				if($(window).scrollTop() > stickyOffset) sidebarTabs.addClass('is-affixed');
				else if(sidebarTabs.hasClass('is-affixed')) sidebarTabs.removeClass('is-affixed');
			});
			$(window).on('resize', function(){
				$('.mpcdp_sidebar_tabs.is-affixed .inner-wrapper-sticky').css('width', `${$('.mpcdp_settings_sidebar')[0].clientWidth}px`);
			});


			// if one of the section is empty, do something as it can't be sorted later.
			$('#active-mpc-columns, #inactive-mpc-columns').each(function(){
				if($(this).find('li').length === 0) $(this).addClass('empty-cols');
			});



			const activeColumns = $(document.body).find('#active-mpc-columns');
			if(activeColumns.length !== 0){
				$('#active-mpc-columns, #inactive-mpc-columns').sortable({
					connectWith: '.connectedSortable',
					remove:      function(event, ui){
						if(ui.item.hasClass('mpc-stone-col')) return false;

						var itemTo   = ui.item.closest('ul');
						var itemFrom = $(event.target);

						setColumnMinimumHeight(itemFrom, itemTo);
					}
				});
			}
		}
		settingsFieldsEvents(){
			const self = this;
			$(document.body).on('click', '.hurkanSwitch-switch-item', function(e){
				// check if it's a pro feature.
				var input = $(this).closest('.mpcdp_settings_option').find('input[type="checkbox"]');
				if(input.hasClass('mpcex-disabled') && !mpc_admin.has_pro){
					e.preventDefault();
					var title = input.attr('title');
					$('body').find('#mpcpop .mpc-focus span').text(title);
					$('body').find('#mpcpop').show();
				}else{
					self.toggleSwitch($(this));
				}
			});
			$(document.body).on('click', 'input[name="wmc_redirect"]', function(){
				var v = $(this).val();
	
				var section  = $(this).closest('.mpcdp_settings_option');
				var field_id = section.data('field-id');
	
				section.closest('.mpcdp_container').find('.mpcdp_settings_option').each(function(){
					if($(this).data('depends-on') === field_id && mpc_admin.has_pro){
						if(v === 'custom'){
							if(!$(this).is(':visible')) $(this).slideToggle('slow');
						}else{
							if($(this).is(':visible')) $(this).slideToggle('slow');
						}
					}
				});
			});
			$(document.body).on('keypress', '.number-input', function(e){
				var field = $(this);
				if(e.originalEvent.which < 48 || e.originalEvent.which > 57){
					e.preventDefault();
					field.addClass('mpc-notice');
				}
				setTimeout(function(){
					field.removeClass('mpc-notice');
				}, 1000);
			});
		}
		shortCodeEvents(){
			const self = this;
			$(document.body).on('click', '.mpcasc-reset', function(e){
				if(!confirm('Are you sure?')) e.preventDefault();
			});
			$(document.body).on('click', '.mpc-opt-sc-btn.delete', function(e){
				if(!confirm('Are you sure?')) e.preventDefault();
			});
			$(document.body).on('click', '.mpc-opt-sc-btn.copy', function(){
				self.copyText($(this));
			});
		}
		exportSettings(btn){
			if(!mpc_admin.has_pro || mpc_admin.has_pro !== '1') return;

			const noticeWrap = $(document).find('#export-success');
			noticeWrap.toggle( 'slow' );

			$.ajax({
				url:  mpc_admin.ajaxurl,
				type: 'POST',
				data: {
					action:  'mpc_export_settings',
					security: mpc_admin.export_nonce,
				},
				success: function(response){
					if(response.success){
						const link    = document.createElement('a');
						link.href     = response.data.file_url;
						link.download = 'mpc_export.json';
						link.click();

						noticeWrap.find('.mpcdp_option_label').text(mpc_admin.export_ok);
						setTimeout(function(){
							noticeWrap.toggle();
							noticeWrap.find('.mpcdp_option_label').text(mpc_admin.export_text);
						}, 3000);
					}else alert('Export failed!');
				},
				complete: function(){
					btn.prop('disabled', false).text('Export');
				}
			});
		}
		


		async copyText(item){
			const self = this;
			const element = item.closest('.mpc-shortcode, .mpc-shortcode-item').find('textarea');
			const text = element.val();
			if(!navigator.clipboard){
				self.fallbackCopyText(text);
			}else{
				navigator.clipboard.writeText(text).then(function(){
					// copied successfully.
				}, function(err){
					console.log('error nav copying', err);
				});
			}
			item.find('.dashicons').toggleClass('dashicons-admin-page dashicons-saved');
			setTimeout(function(){
				item.find('.dashicons').toggleClass('dashicons-admin-page dashicons-saved');
			}, 2000);
		}
		fallbackCopyText(text){
			// create new textarea method.
			const textArea = document.createElement('textarea');
			textArea.value = text;
			textArea.style.top = '0';
			textArea.style.left = '0';
			textArea.style.position = 'fixed';
			document.body.appendChild(textArea);
			textArea.focus();
			textArea.select();
			
			try{
				const copied = document.execCommand('copy');
			}catch(error){
				console.log('error', error);
			}

			document.body.removeChild(textArea);
		}
		toggleSwitch(btn){
			const wrap = btn.closest('.hurkanSwitch-switch-box');
			wrap.find('.hurkanSwitch-switch-item').each(function(){
				if($(this).hasClass('active'))$(this).removeClass('active');
				else $(this).addClass('active');
			});

			btn.closest('.mpcdp_settings_option').find('input[type="checkbox"]').trigger('click');
			this.toggleFollowUp(btn.closest('.mpcdp_settings_option'));
			wrap.toggleClass('switch-animated-off switch-animated-on', 1000);
		}
		toggleFollowUp(section){
			var field_id = section.data('field-id');
			section.closest('.mpcdp_container').find('.mpcdp_settings_option').each(function(){
				if($(this).data('depends-on') === field_id) $(this).slideToggle('slow');
			});
		}
		sortTableColumns(){
			let sequence = ''; // make sequence of columns ( available and sorted ones ).
			$('#active-mpc-columns').find('li').each(function(){
				if(sequence.length !== 0) sequence += ',';
				sequence += $(this).data('meta_key');
			});
			$('.mpc-sorted-cols').val(sequence);
		}
		setColumnMinimumHeight(from, to){ // set min height of sortable columns.
			from = typeof from === 'undefined' ? $('#') : from;
			fo   = typeof fo === 'undefined' ? $('#') : fo;

			const itemHeight = to.find('li')[0].offsetHeight;

			const fromMinHeight = from.find('li').length !== 0 ? (from.find('li').length + 1) * itemHeight : itemHeight;
			const toMinHeight   = to.find('li').length !== 0 ? (to.find('li').length + 1) * itemHeight : itemHeight;

			from.css({'min-height' : `${fromMinHeight}px`});
			to.css({'min-height' : `${toMinHeight}px`});
		}
    }

    new mpcAdminEvents();
})(jQuery, window, document);
