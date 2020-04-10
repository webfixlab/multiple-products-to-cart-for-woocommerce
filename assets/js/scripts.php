<script type="text/javascript">
	(function($) {
		function mpc_is_selects_ok( ids ){
			var flag = false;
			var total_select = 0;
			if( ids.length > 0 ){
				ids.forEach(function(item, index){
					var c = 0; // total valid select
					var t = 0; // total select
					$( 'select[name$="' + item + '"]' ).each(function(){
						t++;
						if( $(this).val() ){
							c++;
						}
					});
					if( t == c ){
						flag = true;
					}
				});
			}
			return flag;
		}
	    $('input[name="proceed"]').click(function(e){
	    	var flag = 0;
	    	var cs = 0; // checked select
			var ids = [];
		    $( 'input[name="product_ids[]"]').each(function(){
		    	var id = parseInt( $(this).val() );
	    		if( this.checked ){
	    			if( !ids.includes( id ) ){
	    				ids.push( id );
	    			}
	    		}
		    });
	    	if( ids.length > 0 ){
	    		ids.forEach(function(item, index){
	    			var c = 0; // total valid select
	    			var t = 0; // total select - checked select
	    			if( $( 'select[name$="' + item + '"]' ).length > 0 ){
	    				cs++;
	    				$( 'select[name$="' + item + '"]' ).each(function(){
	    					t++;
	    					if( $(this).val() ){
	    						c++;
	    					}
	    				});
	    				if( t == c && t != 0 ){
	    					flag++;
	    				}
	    			}
	    		});
	    	}
	    	// console.log( "CS " + cs + ", Flag = " + flag );
	    	if( cs != flag ){
		    	e.preventDefault();
	    		$( 'div.woo-notices' ).html( '<p class="woo-err"><?php echo __( "Please choose the correct options.", "mpc" ); ?></p>' );
	    		$('html, body').animate({
    		        scrollTop: $('.woo-notices').offset().top - 60
    		    }, 'slow');
	    	}
	    });
		$( ".product-image img" ).each( function(){
			var full = $(this).data("fullimage");
			$(this).on( "click", function(){
				console.log( full );
				$( "#mpcpop" ).html("");
				$( "#mpcpop" ).show();
				var content = '<img src="' + full + '">'
				$( "#mpcpop" ).html( content );
			});
		});
		$("#mpcpop").on( "click", function(){
			$( "#mpcpop" ).hide();
		});
	})(jQuery);
</script>

