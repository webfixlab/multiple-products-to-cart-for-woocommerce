<?php

/**

* Client feedback - rating

*/

function mpc_client_feedback(){

  $notice_interval = 7; // Show notice on this date interval

  if( isset( $_GET['mpc_notify'] ) ){

    $task = $_GET['mpc_notify'];

    if( $task == 'done' ){

      update_option( 'mpc_client_feedback', "done" );

      // never show this notice again

    }elseif( $task == 'cancel' ){

      // show this notice in a week again

      update_option( 'mpc_client_feedback', date("Y-m-d") );

    }

  }else{

    $mcf = get_option( 'mpc_client_feedback' );

    if( isset( $mcf ) && $mcf != '' ){

      if( $mcf != 'done' ){

        $c = date_create( date( "Y-m-d" ) );

        $d = date_create( "$mcf" );

        $dif = date_diff( $c, $d );

        $b = (int) $dif->format( '%d' );

        if( $b >= $notice_interval ){

          // show notice to rate us

          add_action( 'admin_notices', 'mpc_client_feedback_notice' );

        }

      }

    }else{

      add_option( 'mpc_client_feedback', date( "Y-m-d" ) );

    }

  }

}

/**

* display what you want to show in the notice

*/

function mpc_client_feedback_notice(){

    ?>

    <div id = "eswc_notice_container" class="notice notice-info is-dismissible">

			<div>

				<p>

					Excellent! You've been using <strong><a href="https://wordpress.org/support/plugin/multiple-products-to-cart-for-woocommerce/reviews/?rate=5#new-post">Multiple Products to Cart for WooCommerce</a></strong> for a while. We are a small team and it will inspire us to continue developing this plugin if you kindly rate it on <strong><a href="https://wordpress.org/support/plugin/multiple-products-to-cart-for-woocommerce/reviews/?rate=5#new-post">WordPress.org</a></strong>?

				</p>

				<p>

					<a href="https://wordpress.org/support/plugin/multiple-products-to-cart-for-woocommerce/reviews/?rate=5#new-post" class="button-primary">Continue Developing</a>

					&nbsp;&nbsp;&nbsp;

					<a href="<?php echo home_url($_SERVER['REQUEST_URI']) . '?mpc_notify=done'; ?>" class="button">Already Did</a>

					&nbsp;&nbsp;&nbsp;

					<a href="<?php echo home_url($_SERVER['REQUEST_URI']) . '?mpc_notify=cancel'; ?>" class="button">Cancel</a>

				</p>

			</div>

		</div>

    <?php

}

?>