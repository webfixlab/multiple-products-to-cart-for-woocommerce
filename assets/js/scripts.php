<script type="text/javascript">
	(function($) {		

	    $('input[name="proceed"]').click(function(e){
	    	var flag = false;
	    	// Check if any one of the checkbox is selected
	    	// foreach element check again
	    		//quantity is not zero, 0
	    		//if it has variations, they are selected/has option value

			var ids = [];
		    $( 'input[name="product_ids[]"]').each(function(){
		    	var id = parseInt( $(this).val() );
	    		if( this.checked ){
	    			if( !ids.includes( id ) ){
	    				ids.push( id );
	    			}
	    		}
		    });

    		// console.log( "Found " + ids.length + " id(s)." );
	    	if( ids.length > 0 ){
	    		ids.forEach(function(item, index){
	    			// console.log( item );

	    			// $('td[name=tcol1]') // matches exactly 'tcol1'
	    			// $('td[name^=tcol]') // matches those that begin with 'tcol'
	    			// $('td[name$=tcol]') // matches those that end with 'tcol'
	    			// $('td[name*=tcol]') // matches those that contain 'tcol'
	    			var c = 0;
	    			var t = 0;
	    			$( 'select[name$="' + item + '"]' ).each(function(){
	    				t++;
	    				if( $(this).val() ){
	    					c++;
	    					// console.log( $(this).val() );
	    				}
	    			});
	    			if( t == c ){
	    				flag = true;
	    			}

	    			// var q = parseInt( $( 'input[name="quantity' + item + '"]' ).val() );
	    			// if( q > 0 ){
	    			// 	flag++;
	    			// }
	    		});
	    	}

	    	if( flag == false ){
		    	e.preventDefault();
	    		$( 'div.woo-notices' ).html( '<p class="woo-err">Please choose the correct options.</p>' );
	    		$('html, body').animate({
    		        scrollTop: $('.woo-notices').offset().top - 60
    		    }, 'slow');
	    	}
	    });
	})(jQuery);
</script>