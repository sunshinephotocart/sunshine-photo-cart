<?php
class SunshinePaymentOffline extends SunshinePaymentMethods {

	function __construct() {

		global $sunshine;
		if ( $sunshine->options['offline_active'] ) {
			$name = ( $sunshine->options['offline_name'] ) ? $sunshine->options['offline_name'] : __( 'Offline','sunshine' );
			$desc = ( $sunshine->options['offline_desc'] ) ? $sunshine->options['offline_desc'] : __( 'Send payment in outside of website','sunshine' );
			SunshinePaymentMethods::add_payment_method( 'offline', $name, $desc, 20 );
		}

		self::process_payment();
	}

	function process_payment() {
		global $current_user, $post, $sunshine;

		if ( isset( $_POST['sunshine_checkout'] ) && is_page( $sunshine->options['page_checkout'] ) && $_POST['sunshine_checkout'] == 1 && empty( $sunshine->errors ) && $sunshine->cart->total > 0 && $_POST['payment_method'] == 'offline' ) {

			// Order details
			$order['user_id'] = $current_user->ID;
			$order['shipping_method'] = $sunshine->cart->shipping_method['id'];
			$order['shipping_cost'] = $sunshine->cart->shipping_method['cost'];
			$order['credits'] = $sunshine->cart->useable_credits;
			$order['discount_total'] = $sunshine->cart->discount_total;
			$order['discount_items'] = $sunshine->cart->discount_items;
			$order['tax'] = $sunshine->cart->tax;
			$order['subtotal'] = $sunshine->cart->subtotal;
			$order['total'] = $sunshine->cart->total;
			$order['payment_method'] = 'offline';
			$order['status'] = 'pending';

			// Contact Info
			$order['email'] = SunshineUser::get_user_meta( 'email' );
			$order['phone'] = SunshineUser::get_user_meta( 'phone' );

			// Billing Info
			$order['first_name'] = SunshineUser::get_user_meta( 'first_name' );
			$order['last_name'] = SunshineUser::get_user_meta( 'last_name' );
			$order['address'] = SunshineUser::get_user_meta( 'address' );
			$order['address2'] = SunshineUser::get_user_meta( 'address2' );
			$order['city'] = SunshineUser::get_user_meta( 'city' );
			$order['state'] = SunshineUser::get_user_meta( 'state' );
			$order['zip'] = SunshineUser::get_user_meta( 'zip' );
			$order['country'] = SunshineUser::get_user_meta( 'country' );

			// Shipping Info
			$order['shipping_first_name'] = SunshineUser::get_user_meta( 'shipping_first_name' );
			$order['shipping_last_name'] = SunshineUser::get_user_meta( 'shipping_last_name' );
			$order['shipping_address'] = SunshineUser::get_user_meta( 'shipping_address' );
			$order['shipping_address2'] = SunshineUser::get_user_meta( 'shipping_address2' );
			$order['shipping_city'] = SunshineUser::get_user_meta( 'shipping_city' );
			$order['shipping_state'] = SunshineUser::get_user_meta( 'shipping_state' );
			$order['shipping_zip'] = SunshineUser::get_user_meta( 'shipping_zip' );
			$order['shipping_country'] = SunshineUser::get_user_meta( 'shipping_country' );

			$order_id = SunshineOrder::add_order( $order );
			wp_redirect( get_permalink( $order_id ) );
			exit;

		}
	}

}

add_action( 'wp', 'sunshine_init_offline', 20 );
function sunshine_init_offline() {
	global $sunshine;
	if ( $sunshine->options['offline_active'] )
		SunshinePaymentOffline::instance();
}

add_filter( 'sunshine_order_status_description', 'sunshine_offline_order_status', 1, 3 );
function sunshine_offline_order_status( $description, $status, $order_id ) {
	global $sunshine;
	$order_data = maybe_unserialize( get_post_meta( $order_id, '_sunshine_order_data', true ) );
	if ( strtolower( $order_data['payment_method'] ) == 'offline' && $status->slug == 'pending' && $sunshine->options['offline_instructions'] ) {
		$description .= '<br /><br />'.nl2br( $sunshine->options['offline_instructions'] );
	}
	return $description;
}

add_filter( 'sunshine_options_payment_methods', 'sunshine_offline_options', 20 );
function sunshine_offline_options( $options ) {
	$options[] = array( 'name' => __( 'Offline','sunshine' ), 'type' => 'title', 'desc' => __( 'Offline payments can be anything you want, most likely for accepting checks','sunshine' ) );
	$options[] = array(
		'name' => __( 'Enable offline payments','sunshine' ),
		'id'   => 'offline_active',
		'type' => 'checkbox',
		'options' => array( 1 => '1' )
	);
	$options[] = array(
		'name' => __( 'Name','sunshine' ),
		'id'   => 'offline_name',
		'type' => 'text',
		'tip' => __( 'Name that users will see on the checkout page, defaults to "Offline"','sunshine' )
	);
	$options[] = array(
		'name' => __( 'Description','sunshine' ),
		'id'   => 'offline_desc',
		'type' => 'text',
		'tip' => __( 'Description that users will see on the checkout page','sunshine' )
	);
	$options[] = array(
		'name' => __( 'Instructions','sunshine' ),
		'tip'  => __( 'Use this to instruct customers how to submit payment (if check, mailing address would be a good idea)','sunshine' ),
		'id'   => 'offline_instructions',
		'type' => 'textarea'
	);
	return $options;
}

add_filter( 'sunshine_email_receipt', 'sunshine_offline_receipt', 10, 2 );
function sunshine_offline_receipt( $message, $order_id ) {
	global $sunshine;
	$order_data = maybe_unserialize( get_post_meta( $order_id, '_sunshine_order_data', true ) );
	if ( is_array( $order_data ) && $order_data['payment_method'] == 'offline' ) {
		$message .= '<p>'.nl2br( $sunshine->options['offline_instructions'] ).'</p>';
	}
	return $message;
}
?>