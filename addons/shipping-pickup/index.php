<?php
add_filter( 'sunshine_add_shipping_methods', 'sunshine_init_pickup', 5 );
function sunshine_init_pickup( $methods ) {
	global $sunshine;
	if ( isset( $sunshine->options['pickup_active'] ) && $sunshine->options['pickup_active'] != '' ) {
		$methods['pickup'] = array(
			'id' => 'pickup',
			'title' => $sunshine->options['pickup_name'],
			'taxable' => ( isset( $sunshine->options['pickup_taxable'] ) ) ? $sunshine->options['pickup_taxable'] : 0,
			'cost' => ( $sunshine->options['pickup_cost'] > 0 ) ? $sunshine->options['pickup_cost'] : 0
		);
	}
	return $methods;
}

add_filter( 'sunshine_options_shipping_methods', 'sunshine_pickup_options', 10 );
function sunshine_pickup_options( $options ) {
	$options[] = array( 'name' => __( 'Pickup','sunshine' ), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __( 'Enable Pickup Shipping','sunshine' ),
		'id'   => 'pickup_active',
		'type' => 'checkbox',
		'options' => array( 1 )
	);
	$options[] = array(
		'name' => __( 'Name','sunshine' ),
		'id'   => 'pickup_name',
		'type' => 'text'
	);
	$options[] = array(
		'name' => __( 'Pickup Shipping Cost','sunshine' ).' ('.sunshine_currency_symbol().')',
		'id'   => 'pickup_cost',
		'type' => 'text',
		'css' => 'width: 50px;'
	);
	$options[] = array(
		'name' => __( 'Taxable','sunshine' ),
		'id'   => 'pickup_taxable',
		'type' => 'checkbox',
		'options' => array( 1 )
	);

	return $options;
}
