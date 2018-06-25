<?php
add_action( 'init', 'sunshine_init_offline', 20 );
function sunshine_init_offline() {
	global $sunshine;

	if ( !isset( $sunshine->options['offline_active'] ) || $sunshine->options['offline_active'] != 1 ) return;

	$name = ( $sunshine->options['offline_name'] ) ? $sunshine->options['offline_name'] : __( 'Offline','sunshine' );
	$desc = ( $sunshine->options['offline_desc'] ) ? $sunshine->options['offline_desc'] : __( 'Send payment in outside of website','sunshine' );
	SunshinePaymentMethods::add_payment_method( 'offline', $name, $desc, 20 );

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
function sunshine_offline_receipt( $message, $order_data ) {
	global $sunshine;
	if ( is_array( $order_data ) && $order_data['payment_method'] == 'offline' ) {
		$message .= '<p>'.nl2br( $sunshine->options['offline_instructions'] ).'</p>';
	}
	return $message;
}
?>
