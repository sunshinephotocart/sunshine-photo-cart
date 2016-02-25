<?php
class SunshinePaymentPaypal extends SunshinePaymentMethods {

	function __construct() {

		global $sunshine;

		$name = ( $sunshine->options['paypal_name'] ) ? $sunshine->options['paypal_name'] : 'PayPal';
		$desc = ( $sunshine->options['paypal_desc'] ) ? $sunshine->options['paypal_desc'] : __( 'Submit payment via PayPal account or use a credit card','sunshine' );
		SunshinePaymentMethods::add_payment_method( 'paypal', $name, $desc, 10 );

		self::paypal_redirect();
		self::process_payment();

	}

	function paypal_redirect() {
		global $current_user, $sunshine;
		if ( isset( $_POST['sunshine_checkout'] ) && is_page( $sunshine->options['page_checkout'] ) && $_POST['sunshine_checkout'] == 1 && empty( $sunshine->errors ) && $sunshine->cart->total > 0 && $_POST['payment_method'] == 'paypal' ) {
			
			$paypal_args = array();
			$order = array();
			$order['user_id'] = $current_user->ID;
			$order['shipping_method'] = $sunshine->cart->shipping_method['id'];
			$order['shipping_cost'] = $sunshine->cart->shipping_method['cost'];
			$order['credits'] = $sunshine->cart->useable_credits;
			$order['discount_total'] = $sunshine->cart->discount_total;
			$order['discount_items'] = $sunshine->cart->discount_items;
			$order['tax'] = $sunshine->cart->tax;
			$order['subtotal'] = $sunshine->cart->subtotal;
			$order['total'] = $sunshine->cart->total;
			$order['payment_method'] = 'paypal';
			$order['status'] = 'pending';

			// Billing info is address stored in user's profile
			$order['first_name'] = SunshineUser::get_user_meta( 'first_name' );
			$order['last_name'] = SunshineUser::get_user_meta( 'last_name' );
			$order['address'] = SunshineUser::get_user_meta( 'address' );
			$order['address2'] = SunshineUser::get_user_meta( 'address2' );
			$order['city'] = SunshineUser::get_user_meta( 'city' );
			$order['state'] = SunshineUser::get_user_meta( 'state' );
			$order['zip'] = SunshineUser::get_user_meta( 'zip' );
			$order['country'] = SunshineUser::get_user_meta( 'country' );
			$order['phone'] = SunshineUser::get_user_meta( 'phone' );
			$order['email'] = SunshineUser::get_user_meta( 'email' );

			// Shipping Info
			$order['shipping_first_name'] = SunshineUser::get_user_meta( 'shipping_first_name' );
			$order['shipping_last_name'] = SunshineUser::get_user_meta( 'shipping_last_name' );
			$order['shipping_address'] = SunshineUser::get_user_meta( 'shipping_address' );
			$order['shipping_city'] = SunshineUser::get_user_meta( 'shipping_city' );
			$order['shipping_state'] = SunshineUser::get_user_meta( 'shipping_state' );
			$order['shipping_zip'] = SunshineUser::get_user_meta( 'shipping_zip' );
			$order['shipping_country'] = SunshineUser::get_user_meta( 'shipping_country' );
			
			$order_id = SunshineOrder::add_order( $order, false );
			add_post_meta( $order_id, 'paypal', 'yes' );
			
			$paypal_args['custom'] = $order_id;

			$paypal_url = ( $sunshine->options['paypal_test_mode'] ) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
?>
	<html>
		<head>
			<title><?php _e( 'Redirecting to PayPal','sunshine' ); ?>...</title>
			<style type="text/css">
			body, html { margin: 0; padding: 50px; background: #FFF; }
			h1 { color: #000; text-align: center; font-family: Arial; font-size: 24px; }
			</style>
		</head>
		<body>
			<h1><?php _e( 'Redirecting to PayPal','sunshine' ); ?>...</h1>
		<form method="post" action="<?php echo $paypal_url; ?>" id="paypal" style="display: none;">

			<?php
			$i = 1;
			// Cart info
			$cart = $sunshine->cart->get_cart();
			$i = 1;
			foreach ( $cart as $item ) {
				$name_key = 'item_name_' . $i;
				$quantity_key = 'quantity_' . $i;
				$amount_key = 'amount_' . $i;
				$product = get_post($item['product_id']);
				$cat = wp_get_post_terms($item['product_id'], 'sunshine-product-category');
				$name = apply_filters('sunshine_cart_item_category', (isset($cat[0]->name)) ? $cat[0]->name : '', $item) . ' - ' .  apply_filters('sunshine_cart_item_name', $product->post_title, $item);
				if ( $item['image_id'] > 0 ) {
					$image = get_post( $item['image_id'] );
					$name = $image->post_title . ' - ' . $name;
				}
				$paypal_args[ $name_key ] = $name;
				$paypal_args[ $quantity_key ] = $item['qty'];
				$paypal_args[ $amount_key ] = $item['price'];
				$i++;
			}
			if ( $sunshine->cart->shipping_method['cost'] > 0 ) {
				$paypal_args['item_name_' . $i ] = sprintf( __( 'Shipping via %s', 'sunshine' ), $sunshine->cart->shipping_method['title'] );
				$paypal_args['quantity_' . $i ] = 1;
				$paypal_args['amount_' . $i ] = number_format( $sunshine->cart->shipping_method['cost'], 2 );
			}
			$paypal_args['tax_cart'] = number_format( $sunshine->cart->tax, 2 );
			$paypal_args['discount_amount_cart'] = number_format( $sunshine->cart->discount_total, 2 );
			/*
			$paypal_args['item_name_1'] = __( 'Order from ','sunshine' ).get_bloginfo( 'name' );
			$paypal_args['quantity_1'] = 1;
			$paypal_args['amount_1'] = number_format( $sunshine->cart->total, 2 );
			*/

			// Business Info
			$paypal_args['business'] = $sunshine->options['paypal_email'];
			$paypal_args['cmd'] = '_cart';
			$paypal_args['upload'] = '1';
			$paypal_args['charset'] = 'utf-8';
			$paypal_args['currency_code'] = $sunshine->options['currency'];
			$paypal_args['return'] = add_query_arg( 'goto_recent_order', '1', get_permalink( $sunshine->options['page'] ) );
			$paypal_args['cancel_return'] = wp_nonce_url( add_query_arg( 'order_id', $order_id, sunshine_url( 'checkout' ) ), 'paypal_cancel', 'paypal_cancel' );
			$paypal_args['notify_url'] = trailingslashit( get_bloginfo( 'url' ) ).'?sunshine_paypal_ipn=paypal_standard_ipn';
			$paypal_args['address_override'] = 1;
			if ( $sunshine->cart->shipping_method['id'] == 'pickup' || $sunshine->cart->shipping_method['id'] == 'download' ) // Only ask for address if not pickup
				$paypal_args['no_shipping'] = 1;
			else
				$paypal_args['no_shipping'] = 2;

			// Prefill user info
			$paypal_args['first_name'] = SunshineUser::get_user_meta( 'first_name' );
			$paypal_args['last_name'] = SunshineUser::get_user_meta( 'last_name' );
			$paypal_args['address1'] = SunshineUser::get_user_meta( 'shipping_address' );
			$paypal_args['address2'] = SunshineUser::get_user_meta( 'shipping_address2' );
			$paypal_args['city'] = SunshineUser::get_user_meta( 'shipping_city' );
			$paypal_args['state'] = SunshineUser::get_user_meta( 'shipping_state' );
			$paypal_args['zip'] = SunshineUser::get_user_meta( 'shipping_zip' );
			$paypal_args['country'] = SunshineUser::get_user_meta( 'shipping_country' );;
			$paypal_args['email'] = SunshineUser::get_user_meta( 'email' );
			$phone = preg_replace( "/[^0-9,.]/", "", SunshineUser::get_user_meta( 'phone' ) );
			$paypal_args['night_phone_a'] = substr( $phone, 0, 3 );
			$paypal_args['night_phone_b'] = substr( $phone, 3, 3 );
			$paypal_args['night_phone_c'] = substr( $phone, 6, 4 );

			// Pass user ID for order processing
			//$paypal_args['custom'] = $current_user->ID;
			
			foreach ( $paypal_args as $key => $value ) {
				$paypal_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
			}
			echo implode( "\r\n", $paypal_args_array );
?>

			<input type="submit" value="<?php _e( 'Submit payment via PayPal','sunshine' ); ?>" style="border: none; background: #FFF; color: #FFF; box-shadow: none; text-shadow: none;" />
		</form>
		<script>
			document.getElementById("paypal").submit();
		</script>
		</body>
		</html>
<?php
			die();
		}
	}

	function process_payment() {
		global $sunshine;

		if ( isset( $_GET['sunshine_paypal_ipn'] ) && $_GET['sunshine_paypal_ipn'] == 'paypal_standard_ipn' && isset( $_POST ) ) {

			$raw_post_data = file_get_contents( 'php://input' );
			$raw_post_array = explode( '&', $raw_post_data );
			$myPost = array();
			foreach ( $raw_post_array as $keyval ) {
			  $keyval = explode ( '=', $keyval );
			  if ( count($keyval) == 2 )
			     $myPost[ $keyval[ 0 ] ] = urldecode( $keyval[ 1 ] );
			}
			// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
			$req = 'cmd=_notify-validate';
			if( function_exists( 'get_magic_quotes_gpc' ) ) {
			   $get_magic_quotes_exists = true;
			} 
			foreach ( $myPost as $key => $value ) {        
			   if ( $get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1 ) { 
			        $value = urlencode(stripslashes($value)); 
			   } else {
			        $value = urlencode($value);
			   }
			   $req .= "&$key=$value";
			}

			$paypal_url = ( $sunshine->options['paypal_test_mode'] ) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

			$response = wp_remote_post( $paypal_url, array(
			    'method'      => 'POST',
			    'timeout'     => 45,
			    'redirection' => 5,
			    'httpversion' => '1.0',
			    'blocking'    => true,
			    'headers'     => array(),
			    'body'        => $req,
			    'cookies'     => array()
			    )
			);

			if ( is_wp_error( $response ) ) {
				exit;
			} else {
				$res = wp_remote_retrieve_body( $response );
			}
			
			if (strcmp ($res, "VERIFIED") != 0) {
				exit;
			}
				
			$order_id = intval( $_POST['custom'] );
			wp_set_post_terms( $order_id, 'processing', 'sunshine-order-status' );
			add_post_meta( $order_id, 'txn_id', $myPost['txn_id'] );
			add_post_meta( $order_id, 'payment_fee', $myPost['payment_fee'] );
			add_post_meta( $order_id, 'ipn_track_id', $myPost['ipn_track_id'] );
			add_post_meta( $order_id, 'verify_sign', $myPost['verify_sign'] );
			add_post_meta( $order_id, 'payer_id', $myPost['payer_id'] );
			add_post_meta( $order_id, 'mode', ( $sunshine->options['paypal_test_mode'] ) ? 'test' : 'live' );
			
			SunshineOrder::notify( $order_id );
			
			exit;
		}
	}

}

add_action( 'wp', 'sunshine_init_paypal', 20 );
function sunshine_init_paypal() {
	global $sunshine;
	if ( $sunshine->options['paypal_active'] )
		SunshinePaymentPaypal::instance();
}

add_filter( 'sunshine_options_payment_methods', 'sunshine_paypal_options', 10 );
function sunshine_paypal_options( $options ) {
	$options[] = array( 'name' => 'PayPal', 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __( 'Enable payments via PayPal','sunshine' ),
		'id'   => 'paypal_active',
		'type' => 'checkbox',
		'options' => array( 1 ),
		'desc' => sprintf( __( 'Please make sure you have enabled your <a href="%s" target="_blank">PayPal IPN settings</a>. The URL does not matter, just as long as it is enabled.', 'sunshine' ), 'https://www.paypal.com/us/cgi-bin/webscr?cmd=_profile-ipn-notify-edit' )
	);
	$options[] = array(
		'name' => __( 'Name','sunshine' ),
		'id'   => 'paypal_name',
		'type' => 'text',
		'tip' => __( 'Name that users will see on the checkout page, defaults to "PayPal"','sunshine' )
	);
	$options[] = array(
		'name' => __( 'Description','sunshine' ),
		'id'   => 'paypal_desc',
		'type' => 'text',
		'tip' => __( 'Description that users will see on the checkout page','sunshine' )
	);
	$options[] = array(
		'name' => __( 'PayPal Email','sunshine' ),
		'id'   => 'paypal_email',
		'type' => 'text'
	);
	$options[] = array(
		'name' => __( 'Enable test mode (Sandbox)','sunshine' ),
		'id'   => 'paypal_test_mode',
		'tip'  => __( 'More for developers, this lets you accept test transactions via PayPal. Requires developer account and being logged into the developer account.','sunshine' ),
		'type' => 'checkbox',
		'options' => array( 1 => '1' )
	);
	return $options;
}

/* Redirect user to order receipt page when coming back from PayPal */
add_action( 'wp', 'sunshine_paypal_redirect_to_order' );
function sunshine_paypal_redirect_to_order() {
	global $current_user, $sunshine;
	if ( isset( $_GET['goto_recent_order'] ) && is_user_logged_in() ) {
		
		$args = array(
			'post_type' => 'sunshine-order',
			'meta_key' => '_sunshine_customer_id',
			'meta_value' => $current_user->ID
		);
		$orders = get_posts( $args );
		foreach ( $orders as $order ) {
			wp_redirect( add_query_arg( 'complete_order', 1, get_permalink( $order->ID ) ) );
			exit;
		}
		
	}
}


add_action( 'wp', 'sunshine_paypal_clear_cart', 999 );
function sunshine_paypal_clear_cart() {
	global $sunshine, $wp_query;
	if ( !empty( SunshineFrontend::$current_order ) && isset( $_GET['complete_order'] ) ) {
		$sunshine->cart->empty_cart();
		wp_redirect( get_permalink( SunshineFrontend::$current_order->ID ) );
		exit;
	}
}

add_action( 'wp', 'sunshine_paypal_cancel_order' );
function sunshine_paypal_cancel_order() {
	global $sunshine;
	if ( isset( $_GET['paypal_cancel'] ) && wp_verify_nonce( $_GET['paypal_cancel'], 'paypal_cancel' ) && isset( $_GET['order_id'] ) ) {
		wp_set_post_terms( intval( $_GET['order_id'] ), 'cancelled', 'sunshine-order-status' );
	}
}

add_action('add_meta_boxes', 'sunshine_paypal_order_meta_box');
function sunshine_paypal_order_meta_box() {
    global $post;
	$is_paypal = get_post_meta( $post->ID, 'paypal', true );
	if ( !$is_paypal ) return;
   	add_meta_box(
       'paypal_payment_info',
       'PayPal Payment Info',
       'sunshine_paypal_order_meta_box_inner',
       'sunshine-order',
       'side',
       'default'
   	);
}

function sunshine_paypal_order_meta_box_inner() {
    global $post;
	$txn_id = get_post_meta( $post->ID, 'txn_id', true );
	$payment_fee = get_post_meta( $post->ID, 'payment_fee', true );
	$ipn_track_id = get_post_meta( $post->ID, 'ipn_track_id', true );
	$verify_sign = get_post_meta( $post->ID, 'verify_sign', true );
	$payer_id = get_post_meta( $post->ID, 'payer_id', true );
	$mode = get_post_meta( $post->ID, 'mode', true );
	$paypal_url = ( $mode == 'test' ) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
	
    ?>
    <p><strong>PayPal Transaction ID</strong>: <a href="<?php echo $paypal_url; ?>?cmd=_view-a-trans&id=<?php echo $txn_id; ?>" target="_blank"><?php echo $txn_id; ?></a></p>
    <p><strong>Transaction Fee</strong>: <?php sunshine_money_format( $payment_fee ); ?></p>
	<?php
}

// CRON JOB TO CANCEL PENDING PAYPAL ORDERS AFTER A WEEK
add_action( 'sunshine_install', 'sunshine_paypal_cron' );
add_action( 'sunshine_update', 'sunshine_paypal_cron' );
function sunshine_paypal_cron() {
	if ( !wp_next_scheduled( 'sunshine_paypal_cleanup' ) ) {
		wp_schedule_event( time(), 'daily', 'sunshine_paypal_cleanup' );
	}
}

add_action( 'sunshine_paypal_cleanup', 'sunshine_paypal_cleanup' );
function sunshine_paypal_cleanup() {
	$args = array(
		'post_type' => 'sunshine-order',
		'nopaging' => true,
		'meta_key' => 'paypal',
		'meta_value' => 'yes',
		'tax_query' => array(
			array(
				'taxonomy' => 'sunshine-order-status',
				'field' => 'slug',
				'terms' => 'pending'
			)
		),
		'date_query' => array(
			'before' => '-1 week'
		)
	);
	$orders = get_posts( $args );
	foreach ( $orders as $order ) {
		wp_set_post_terms( $order->ID, 'cancelled', 'sunshine-order-status' );
	}
}
?>