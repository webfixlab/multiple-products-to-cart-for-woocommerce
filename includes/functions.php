<?php
/**
WooCommerce Settings Tab
**/
add_filter( 'woocommerce_get_sections_products', 'wmc_add_admin_section' );
function wmc_add_admin_section( $sections ) {
	$sections['wmc'] = __( 'Multiple Products to Cart', 'mpc' );
	return $sections;
}
/**
* Add settings Options
*/
add_filter( 'woocommerce_get_settings_products', 'wmc_admin_options', 10, 2 );
function wmc_admin_options( $settings, $current_section ) {
	/**
	* Check the current section is what we want
	**/
	if ( $current_section == 'wmc' ) {
		$wmc_settings = array();
		// Add Title to the Settings
		$wmc_settings[] = array(
			'name' 		=> __( 'Multiple Products to Cart Settings.', 'mpc' ),
			'type' 		=> 'title',
			'desc' 		=> __( "For quick support, contact us ", "mpc" ) . '<a href="https://webfixlab.com/#contact-us" target="_blank">' . __( "here.", "mpc" ) . '</a>',
			'id' 		=> 'wmc'
		);
	    // Add second text field option
		$wmc_settings[] = array(
			'name'     	=> __( 'Table Header Background Color', 'mpc' ),
			'desc_tip' 	=> __( 'Click on the right text field to change button color.', 'mpc' ),
			'id'       	=> 'wmc_thead_back_color',
			'type'     	=> 'color',
			'css'       => 'max-width: 365px;',
			'default'   => '#000000'
		);
		// Add second text field option
		$wmc_settings[] = array(
			'name'     	=> __( 'Cart Button Text', 'mpc' ),
			'desc_tip' 	=> __( 'You can change the cart button text.', 'mpc' ),
			'id'       	=> 'wmc_button_text',
			'type'     	=> 'text',
			'default'   => 'Add to Cart'
		);
		// Add second text field option
		$wmc_settings[] = array(
			'name'     	=> __( 'Cart Button Color', 'mpc' ),
			'desc_tip' 	=> __( 'Click on the right text field to change button color.', 'mpc' ),
			'id'       	=> 'wmc_button_color',
			'type'     	=> 'color',
			'css'       => 'max-width: 365px;',
			'default'   => '#000000'
		);
		$wmc_settings[] = array(
			'type' 		=> 'sectionend',
			'id' 		=> 'wmc'
		);
		return $wmc_settings;
		/**
		* If not, return the standard settings
		**/
    }
    else{
	    return $settings;
    }
}
/**
* Get all products for our $atts
*
* @return product objects based on the attributes supplied.
*/
if( !function_exists( 'mpc_extract_atts' ) ) {
	function mpc_extract_atts( $atts ){
		$atts = shortcode_atts(
			array(
				'limit'     	=> 50,
				'orderby'   	=> '',
				'order'     	=> '',
				'ids'        	=> '',
				'cats'       	=> '',
				'type'      	=> 'all',
			),
			$atts, 'woo-multi-cart'
		);
	    // common
	    if( $atts['limit'] > 50 || $atts['limit'] < 0 ){
	    	$atts['limit'] = 50;
	    }
		$args = array(
			'post_type'         => 'product',
			'post_status'       => 'publish',
			'posts_per_page'    => $atts['limit'],
			'meta_key' 			=> '_stock_status',
			'meta_value'		=> 'instock',
		);
	    if( in_array( $atts['orderby'], array( 'title', 'date' ) ) ){
	    	$args['orderby'] = ( $atts['orderby'] != '' ? $atts['orderby'] : 'date' );
	    }
	    if( isset( $atts['order'] ) ){
	    	$args['order'] = ( $atts['order'] != '' ? strtoupper( $atts['order'] ) : 'DESC' );
	    }
	  	// validate before processing
	    if( $atts['ids'] != '' && $atts['cats'] == '' && $atts['type'] == 'all' ){
	  		$ids = explode( ',', str_replace( ' ', '', $atts['ids'] ) );
	    	if( $atts['orderby'] != '' || $atts['order'] != '' ){
	    		$args['post__in'] = $ids;
	    		$all_posts = array();
	    		$posts = new WP_Query( $args );
	    		while ( $posts->have_posts() ) {
		  		    $posts->the_post();
		  		    array_push( $all_posts, get_the_ID() );
	    		}
	    		wp_reset_postdata();
	    		return $all_posts;
	    	}else{
	    		$validated = array();
	    		if( isset( $ids ) && count( $ids ) > 0 ){
	    			foreach( $ids as $id ){
	    				$post = get_post( $id );
	    				if( isset( $post ) ){
	    					array_push( $validated, $id );
	    				}
	    			}
	    		}
	      	return $validated;
	    	}
	    }
	    elseif( $atts['ids'] != '' && $atts['cats'] != '' && $atts['type'] == 'all' ){
	  		$ids = explode( ',', str_replace( ' ', '', $atts['ids'] ) );
	    	$termids = explode(',', str_replace( ' ', '', $atts['cats'] ) );
	    	$args['post__in'] = $ids;
		    $args['tax_query'] = array(
		        array(
					'taxonomy'  => 'product_cat',
					'field'     => 'term_id',
					// 'field' 	=> 'slug',
					// 'terms' 	=> 'white-wines'
					'terms'     => $termids,
		        )
		    );
		    $all_posts = array();
		    $posts = new WP_Query( $args );
		    while ( $posts->have_posts() ) {
		        $posts->the_post();
		        array_push( $all_posts, get_the_ID() );
		    }
		    wp_reset_postdata();
		    return $all_posts;
	    }
	    elseif( $atts['ids'] == '' && $atts['cats'] != '' && $atts['type'] == 'all' ){
	    	//validate
	    	$termids = explode(',', str_replace( ' ', '', $atts['cats'] ) );
	    	$validated = array();
	    	if( isset( $termids ) && count( $termids ) > 0 ){
	    		foreach( $termids as $id ){
	    			$term = get_term( $id );
	    			if( isset( $term ) ){
	    				array_push( $validated, $id );
	    			}
	    		}
	    	}
		    $args['tax_query'] = array(
		        array(
					'taxonomy' => 'product_cat',
					'field' 	 => 'term_id',
					// 'field' 	=> 'slug',
					// 'terms' 	=> 'white-wines'
					'terms' 	 => $validated,
		        )
		    );
	    	//show posts from each category sequentially
	    	$all_posts = array();
	    	$posts = new WP_Query( $args );
	    	while ( $posts->have_posts() ) {
		  	    $posts->the_post();
		  	    array_push( $all_posts, get_the_ID() );
	    	}
	    	wp_reset_postdata();
	    	//rearrange posts
	    	$sorted = array();
	    	if( isset( $all_posts ) && count( $all_posts ) > 0 ){
	    		foreach( $validated as $termid ){
	    			foreach( $all_posts as $id ){
	    				$terms = get_the_terms( $id, 'product_cat' );
	    				if( isset( $terms ) ){
	    					foreach( $terms as $term ){
	    						if( $term->term_id == $termid && !in_array( $id, $sorted ) ){
	    							array_push( $sorted, $id );
	    						}
	    					}
	    				}
	    			}
	    		}
	    	}
	    	return $sorted;
	    }
	    elseif( $atts['ids'] == '' && $atts['cats'] == '' && $atts['type'] != 'all' ){
	    	$ids = array();
	    	if( in_array( $atts['type'], array( 'simple', 'variable' ) ) ){
	    		$posts = new WP_Query( $args );
		  	    while( $posts->have_posts() ){
		  	    	$posts->the_post();
			        $id = get_the_ID();
			        $_product = wc_get_product( $id );
			        if( isset( $_product ) && $_product->is_type( $atts['type'] ) ){
			        	array_push( $ids, $id );
			        }
		  	    }
	    	}
	    	wp_reset_postdata();
	    	return $ids;
	    }
	    elseif( $atts['ids'] == '' && $atts['cats'] != '' && $atts['type'] != 'all' ){
	    	$termids = explode(',', str_replace( ' ', '', $atts['cats'] ) );
	    	$validated = array();
	    	if( isset( $termids ) && count( $termids ) > 0 ){
	    		foreach( $termids as $id ){
	    			$term = get_term( $id );
	    			if( isset( $term ) ){
	    				array_push( $validated, $id );
	    			}
	    		}
	    	}
	    	$args['tax_query'] = array(
		  	    array(
					'taxonomy' 		=> 'product_cat',
					'field' 	  	=> 'term_id',
					// 'field' 	=> 'slug',
					// 'terms' 	=> 'white-wines'
					'terms' 	  	=> $validated,
		  	    )
	    	);
	    	$all_posts = array();
	    	$posts = new WP_Query( $args );
	    	while ( $posts->have_posts() ) {
		  	    $posts->the_post();
		  	    $_product = wc_get_product( get_the_ID() );
		  	    if( isset( $_product ) && $_product->is_type( $atts['type'] ) ){
		  	    	array_push( $all_posts, get_the_ID() );
		  	    }
	    	}
	    	wp_reset_postdata();
	    	$sorted = array();
	    	if( isset( $all_posts ) && count( $all_posts ) > 0 ){
	    		foreach( $validated as $termid ){
	    			foreach( $all_posts as $id ){
	    				$terms = get_the_terms( $id, 'product_cat' );
	    				if( isset( $terms ) ){
	    					foreach( $terms as $term ){
	    						if( $term->term_id == $termid && !in_array( $id, $sorted ) ){
	    							array_push( $sorted, $id );
	    						}
	    					}
	    				}
	    			}
	    		}
	    	}
	    	return $sorted;
	    }
	    elseif( $atts['ids'] != '' && $atts['cats'] != '' && $atts['type'] != 'all' ){
			$ids = explode( ',', str_replace( ' ', '', $atts['ids'] ) );
	    	$termids = explode(',', str_replace( ' ', '', $atts['cats'] ) );
	    	$args['post__in'] = $ids;
		    $args['tax_query'] = array(
	        array(
				'taxonomy' 	=> 'product_cat',
				'field' 	=> 'term_id',
				// 'field' 	=> 'slug',
				// 'terms' 	=> 'white-wines'
				'terms' 	=> $termids,
	        )
		    );
		    $all_posts = array();
		    $posts = new WP_Query( $args );
		    while ( $posts->have_posts() ) {
		        $posts->the_post();
		        $_product = wc_get_product( get_the_ID() );
		        if( isset( $_product ) && $_product->is_type( $atts['type'] ) ){
		        	array_push( $all_posts, get_the_ID() );
		        }
		    }
		    wp_reset_postdata();
		    return $all_posts;
	    }else{
	    	$ids = array();
	    	$posts = new WP_Query( $args );
	    	while ( $posts->have_posts() ) {
		  	    $posts->the_post();
		  	    array_push( $ids, get_the_ID() );
	    	}
	    	wp_reset_postdata();
	    	return $ids;
	    }
	}
}
/**
* Check first if this file has override in parent/child theme
* Finally, locate the template
*/
function mpc_locate_template( $default = '' ){
	$path = get_stylesheet_directory() . '/templates/listing-list.php';
	if( !file_exists( $path ) ){
		$path = $default;
	}
	return $path;
}
/**
 * Add "productslist" shortcode.
 */
if( !function_exists('mpc_multicart_shortcode') ) {
	function mpc_multicart_shortcode( $atts ) {
		ob_start();
		// include( WMC_DIR . '/templates/listing-list.php');
		if( mpc_is_plugin_active( 'woocommerce/woocommerce.php' ) ){
			$ids = mpc_extract_atts( $atts );
			include( mpc_locate_template( WMC_DIR . '/templates/listing-list.php' ) );
		}else{
			include( mpc_locate_template( WMC_DIR . '/templates/error.php' ) );
		}
		$sc_content = ob_get_contents();
		ob_get_clean();
		return do_shortcode( $sc_content );
	}
	add_shortcode( 'woo-multi-cart', 'mpc_multicart_shortcode' );
}
function mpc_go_to_designated_page( $url = '' ){
	if( $url != '' ){
		wp_safe_redirect( $url );
	}else{
		wp_safe_redirect( wc_get_cart_url() );
	}
	exit;
}
/**
* Provided the $at_key as the variations, get the variation id
* Partial variation matched also covered.
**/
function mpc_get_variation_id( $at_key, $pid ){
	$variation_id = 0;
	$args = array(
	    'post_type'     => 'product_variation',
	    'post_status'   => array( 'private', 'publish' ),
	    'numberposts'   => -1,
	    'orderby'       => 'menu_order',
	    'order'         => 'asc',
	    'post_parent'   => $pid // get parent post-ID
	);
	$vs = get_posts( $args );
	if( !empty( $at_key ) ){
	    foreach ( $vs as $v ) {
			//checking for multiple variations
			$flag = 110;
			$count = 0;
			foreach( $at_key as $key => $value ){
				$saved_value = get_post_meta( $v->ID, $key, TRUE );
				if( isset($saved_value) && $saved_value == $value ){
					$count++;
				}
			}
			if( count( $at_key ) == $count ){
				$flag = 100;
			}
			if( $flag == 100 ){
				$variation_id = $v->ID;
			}
			else{
				if( $count > 0 && $count < count( $at_key ) ){
					foreach( $at_key as $key => $value ){
						$saved_value = get_post_meta( $v->ID, $key, TRUE );
						if( isset($saved_value) && $saved_value == $value ){
							$variation_id = $v->ID;
						}
					}
				}
			}
	    }

	}
	return $variation_id;
}
/**
* Add all checked products with variations to cart.
*/
function woocommerce_maybe_add_multiple_products_to_cart() {
	if ( ! class_exists( 'WC_Form_Handler' ) ){
		return;
	}
	if( isset( $_REQUEST['add_more_to_cart'] ) && intval( $_REQUEST['add_more_to_cart'] ) == 1 ){
		$product_ids = filter_input( INPUT_POST, 'product_ids', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );
		if ( count( $product_ids ) == 0 ) {
			if ( function_exists( 'wc_add_notice' ) ){
				wc_add_notice( __( 'Please select one or more products.', 'woocommerce' ), 'error' );
			}
			wp_safe_redirect( home_url( $_SERVER['REQUEST_URI']) );
			exit();
		}
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );
		$success = 0;
		$titles = array();
		$count  = 0;
		foreach( $product_ids as $pid ){
			$quantity = ( isset( $_REQUEST[ 'quantity' . $pid ] ) ? intval( $_REQUEST[ 'quantity' . $pid ] ) : 1);
			$_product = wc_get_product( $pid );
			if( $_product->is_type( 'simple' ) ){
				if ( false !== WC()->cart->add_to_cart( $pid, $quantity ) ) $success++;
			}elseif( $_product->is_type( 'variable' ) ){
				$saved = false;
				$att_key_val = '';
				$at_key = array();
				$variations = array();
				foreach ( $_product->get_attributes() as $attribute ) {
					$key = 'attribute_' . sanitize_title( $attribute['name'] );
					if ( isset( $_POST[ $key . $pid ] ) ){
						$value = html_entity_decode( wc_clean( wp_unslash( $_POST[ $key . $pid ] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) );
						$at_key[ $key ] = $value;
						$variations[ $key ] = $value;
					}
				}
				$variation_id = mpc_get_variation_id( $at_key, $pid );
				// t
				// echo '<pre>'; print_r( $at_key ); echo '</pre>';
				// echo 'Variation id = ' . $variation_id .' / for product ( id = ' . $pid . ')';
				// echo '<pre>'; print_r( array() ); echo $_product->get_children()[0] . '</pre>';
				// wp_die();
				// t
				if( $variation_id != 0 && !empty( $variations ) ){
					if ( false !== WC()->cart->add_to_cart( $pid, $quantity, $variation_id, $variations ) ) {
						$success++; $saved = true;
					}
				}
				// t
				if( $variation_id == 0 ){
					$variation_id = $_product->get_children()[0];
					if ( false !== WC()->cart->add_to_cart( $pid, $quantity, $variation_id, $variations ) ) {
						$success++; $saved = true;
					}
				}
				// t
				if( $saved == false ){
					mpc_go_to_designated_page();
				}
			}
			$titles[] = apply_filters( 'woocommerce_add_to_cart_qty_html', ( $quantity > 1 ? absint( $quantity ) . ' &times; ' : '' ), $pid ) . apply_filters( 'woocommerce_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'mpc' ), strip_tags( get_the_title( $pid ) ) ), $pid );
		}
		if( count( $product_ids ) == $success && $success != 0 ){
			$titles = array_filter( $titles );
			$added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'mpc' ), wc_format_list_of_items( $titles ) );
			$message = sprintf( '<a href="%s" tabindex="1" class="button wc-forward">%s</a> %s', esc_url( wc_get_page_permalink( 'cart' ) ), __( 'View cart', 'mpc' ), esc_html( $added_text ) );
			wc_add_notice( $message );
			// wc_add_to_cart_message( $product_ids, true );
			mpc_go_to_designated_page();
		}
	}
}
add_action( 'wp_loaded', 'woocommerce_maybe_add_multiple_products_to_cart', 15 );
/* Check if there is any variation type product in the $posts */
if( !function_exists( 'mpc_check_if_variation_exists' ) ) {
	function mpc_check_if_variation_exists( $ids ) {
		$flag = false;
		if( isset( $ids ) && count( $ids ) > 0 ){
			foreach( $ids as $id ){
				$_product = wc_get_product( $id );
				if( $_product->is_type( 'grouped' ) ){
					continue;
				}
				if( $_product->is_type( 'variable' ) ){
					$flag = true;
				}
			}
		}
		return $flag;
	}
}
