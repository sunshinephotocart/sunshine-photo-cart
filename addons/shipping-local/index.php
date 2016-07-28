<?php
add_filter( 'sunshine_add_shipping_methods', 'sunshine_init_local', 5 );
function sunshine_init_local( $methods ) {
	global $sunshine;
	if ( isset( $sunshine->options['local_active'] ) && $sunshine->options['local_active'] != '' ) {
		$methods['local'] = array(
			'id' => 'local',
			'title' => $sunshine->options['local_name'],
			'taxable' => ( !empty( $sunshine->options['local_taxable'] ) ) ? $sunshine->options['local_taxable'] : 0,
			'cost' => ( $sunshine->options['local_cost'] > 0 ) ? $sunshine->options['local_cost'] : 0
		);
	}
	return $methods;
}

add_filter( 'sunshine_options_shipping_methods', 'sunshine_local_options', 10 );
function sunshine_local_options( $options ) {
	$options[] = array( 'name' => 'Local Delivery', 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __( 'Enable Local Delivery Shipping','sunshine' ),
		'id'   => 'local_active',
		'type' => 'checkbox',
		'options' => array( 1 )
	);
	$options[] = array(
		'name' => __( 'Name','sunshine' ),
		'id'   => 'local_name',
		'type' => 'text'
	);
	$options[] = array(
		'name' => __( 'Local Delivery Shipping Cost','sunshine' ).' ('.sunshine_currency_symbol().')',
		'id'   => 'local_cost',
		'type' => 'text',
		'css' => 'width: 50px;'
	);
	$options[] = array(
		'name' => __( 'Allowed zip/post codes','sunshine' ),
		'id'   => 'local_zipcodes',
		'type' => 'textarea',
		'tip'  => __( 'What zip/post codes is this allowed for? Separate each zip/post code with a comma.','sunshine' )
	);
	$options[] = array(
		'name' => __( 'Taxable','sunshine' ),
		'id'   => 'local_taxable',
		'type' => 'checkbox',
		'options' => array( 1 )
	);

	return $options;
}

add_action( 'sunshine_checkout_validation', 'sunshine_local_checkout_validation' );
function sunshine_local_checkout_validation() {
	global $sunshine;
	if ( isset( $_POST['shipping_method'] ) && $sunshine->options['local_zipcodes'] && SunshineUser::get_user_meta( 'shipping_method' ) != 'download' && $_POST['shipping_method'] == 'local' ) {
		$zipcodes = array_map( 'trim', explode( ',',$sunshine->options['local_zipcodes'] ) );
		if ( !in_array( SunshineUser::get_user_meta( 'shipping_zip' ), $zipcodes ) )
			$sunshine->add_error( sprintf( __( 'Cannot choose %s shipping, not within allowed area','sunshine' ), strtolower( $sunshine->options['local_name'] ) ) );
	}
}