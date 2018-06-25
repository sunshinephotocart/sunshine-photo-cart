<?php
add_filter( 'sunshine_add_shipping_methods', 'sunshine_init_flat_rate', 5 );
function sunshine_init_flat_rate( $methods ) {
	global $sunshine;
	if ( isset( $sunshine->options['flat_rate_active'] ) && $sunshine->options['flat_rate_active'] != '' ) {
		$methods['flat_rate'] = array(
			'id' => 'flat_rate',
			'title' => $sunshine->options['flat_rate_name'],
			'taxable' => ( !empty( $sunshine->options['flat_rate_taxable'] ) ) ? $sunshine->options['flat_rate_taxable'] : 0,
			'cost' => ( $sunshine->options['flat_rate_cost'] > 0 ) ? $sunshine->options['flat_rate_cost'] : 0
		);
	}
	return $methods;
}

add_filter( 'sunshine_shipping_method_cost', 'sunshine_flat_rate_extra_shipping', 10, 2 );
function sunshine_flat_rate_extra_shipping( $cost, $method ) {
	global $sunshine;
	if ( $method == 'flat_rate' && !empty( $sunshine->cart->shipping_extra ) ) {
		$cost += $sunshine->cart->shipping_extra;
	}
	return $cost;
}


add_filter( 'sunshine_options_shipping_methods', 'sunshine_flat_rate_options', 10 );
function sunshine_flat_rate_options( $options ) {
	$options[] = array( 'name' => 'Flat Rate', 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __( 'Enable Flat Rate Shipping','sunshine' ),
		'id'   => 'flat_rate_active',
		'type' => 'checkbox',
		'options' => array( 1 )
	);
	$options[] = array(
		'name' => __( 'Name','sunshine' ),
		'id'   => 'flat_rate_name',
		'type' => 'text'
	);
	$options[] = array(
		'name' => __( 'Flat Rate Shipping Cost','sunshine' ).' ('.sunshine_currency_symbol().')',
		'id'   => 'flat_rate_cost',
		'type' => 'text',
		'css' => 'width: 50px;'
	);
	$options[] = array(
		'name' => __( 'Taxable','sunshine' ),
		'id'   => 'flat_rate_taxable',
		'type' => 'checkbox',
		'options' => array( 1 )
	);

	return $options;
}
