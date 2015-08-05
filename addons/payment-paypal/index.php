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
			$paypal_args['item_name_1'] = __( 'Order from ','sunshine' ).get_bloginfo( 'name' );
			$paypal_args['quantity_1'] = 1;
			$paypal_args['amount_1'] = number_format( $sunshine->cart->total, 2 );

			// Business Info
			$paypal_args['business'] = $sunshine->options['paypal_email'];
			$paypal_args['cmd'] = '_cart';
			$paypal_args['upload'] = '1';
			$paypal_args['charset'] = 'utf-8';
			$paypal_args['currency_code'] = $sunshine->options['currency'];
			$paypal_args['return'] = add_query_arg( 'goto_recent_order', '1', get_permalink( $sunshine->options['page'] ) );
			$paypal_args['cancel_return'] = sunshine_url( 'checkout' );
			$paypal_args['notify_url'] = trailingslashit( get_bloginfo( 'url' ) ).'?paypal_notify=paypal_standard_ipn';
			$paypal_args['address_override'] = 1;
			if ( $sunshine->cart->shipping_method['id'] == 'pickup' ) // Only ask for address if not pickup
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
			$paypal_args['custom'] = $current_user->ID;

			foreach ( $paypal_args as $key => $value ) {
				$paypal_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
			}
			echo implode( '', $paypal_args_array );
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

		if ( isset( $_GET['paypal_notify'] ) && $_GET['paypal_notify'] == 'paypal_standard_ipn' && isset( $_POST ) ) {

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

			$ch = curl_init( $paypal_url );
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

			if( !($res = curl_exec($ch)) ) {
			    curl_close($ch);
			    exit;
			}
			curl_close($ch);
			
			if (strcmp ($res, "VERIFIED") != 0) {
				exit;
			}
				
				
			// Get user info from custom field and set cart for the user
			$user_id = intval( $_POST['custom'] );
			wp_set_current_user( $user_id );
			$sunshine->cart->set_cart();

			if ( $sunshine->cart->content ) {
				// Order details
				$order['user_id'] = $user_id;
				$order['shipping_method'] = $sunshine->cart->shipping_method['id'];
				$order['shipping_cost'] = $sunshine->cart->shipping_method['cost'];
				$order['credits'] = $sunshine->cart->useable_credits;
				$order['discount_total'] = $sunshine->cart->discount_total;
				$order['discount_items'] = $sunshine->cart->discount_items;
				$order['tax'] = $sunshine->cart->tax;
				$order['subtotal'] = $sunshine->cart->subtotal;
				$order['total'] = $sunshine->cart->total;
				$order['payment_method'] = 'paypal';
				$order['status'] = 'new';

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
				$order['shipping_first_name'] = $_POST['first_name'];
				$order['shipping_last_name'] = $_POST['last_name'];
				$order['shipping_address'] = $_POST['address_street'];
				$order['shipping_city'] = $_POST['address_city'];
				$order['shipping_state'] = $_POST['address_state'];
				$order['shipping_zip'] = $_POST['address_zip'];
				$order['shipping_country'] = $_POST['address_country_code'];

				$order_id = SunshineOrder::add_order( $order );
				//wp_redirect(get_permalink($order_id));
			}
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
		'options' => array( 1 )
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
	global $current_user;
	if ( isset( $_GET['goto_recent_order'] ) && is_user_logged_in() ) {
		
		$args = array(
			'post_type' => 'sunshine-order',
			'meta_key' => '_sunshine_customer_id',
			'meta_value' => $current_user->ID
		);
		$orders = get_posts( $args );
		foreach ( $orders as $order ) {
			wp_redirect( get_permalink( $order->ID ) );
			exit;
		}
		
	}
}

?>