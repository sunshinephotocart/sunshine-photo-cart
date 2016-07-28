<?php
add_filter( 'sunshine_add_shipping_methods', 'sunshine_init_pickup', 5 );
function sunshine_init_pickup( $methods ) {
	global $sunshine;
	if ( isset( $sunshine->options['pickup_active'] ) && $sunshine->options['pickup_active'] != '' ) {
		$methods['pickup'] = array(
			'id' => 'pickup',
			'title' => $sunshine->options['pickup_name'],
			'taxable' => ( !empty( $sunshine->options['pickup_taxable'] ) ) ? $sunshine->options['pickup_taxable'] : 0,
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
		'name' => __( 'Pickup Instructions','sunshine' ),
		'id'   => 'pickup_instructions',
		'type' => 'textarea',
	);
	$options[] = array(
		'name' => __( 'Taxable','sunshine' ),
		'id'   => 'pickup_taxable',
		'type' => 'checkbox',
		'options' => array( 1 )
	);

	return $options;
}

add_action( 'sunshine_checkout_end_form', 'sunshine_pickup_hide_shipping_fields' );
function sunshine_pickup_hide_shipping_fields() {
	global $sunshine;
	$shipping_methods = $sunshine->shipping->get_shipping_methods();
	if ( count( $shipping_methods ) == 1 && array_key_exists( 'pickup', $shipping_methods ) ) {
	?>
		<script>
		jQuery(document).ready(function(){
			jQuery( '#sunshine-checkout-step-shipping, #sunshine-billing-toggle' ).hide();
		});
		</script>
	<?php	
	} else {
	?>
		<script>
		jQuery(document).ready(function(){
			jQuery('input[name="shipping_method"]').change(function(){
				var shipping_method_pickup_check = jQuery('input[name="shipping_method"]:checked').val();
				if ( shipping_method_pickup_check == 'pickup' ) {
					jQuery('#sunshine-checkout-step-shipping, #sunshine-billing-toggle').hide();
					//jQuery('#sunshine-checkout').append( '<input type="hidden" name="billing_as_shipping" value="1" id="sunshine-pickup-shipping-billing" />');
					jQuery('#sunshine-billing-toggle input').attr('checked', false).trigger('change');
					jQuery('#sunshine-billing-toggle').hide();
				} else {
					jQuery('#sunshine-checkout-step-shipping, #sunshine-billing-toggle').show();
					jQuery('#sunshine-pickup-shipping-billing').remove();
				}
			});
			var init_shipping_method_pickup_check = jQuery( 'input[name="shipping_method"]:checked' ).val();
			if ( init_shipping_method_pickup_check == 'pickup' ) {
				jQuery('#sunshine-checkout-step-shipping').hide();
				//jQuery('#sunshine-checkout').append( '<input type="hidden" name="billing_as_shipping" value="1" id="sunshine-pickup-shipping-billing" />');
				jQuery('#sunshine-billing-toggle input').attr('checked', false).trigger('change');
				jQuery('#sunshine-billing-toggle').hide();
			}
			
		});
		</script>
	<?php
	}
}

add_filter( 'sunshine_order_data', 'sunshine_pickup_remove_shipping_from_order_data' );
function sunshine_pickup_remove_shipping_from_order_data( $data ) {
	if ( $data['shipping_method'] == 'pickup' ) {
		foreach ( $data as $key => $value ) {
			if ( $key == 'shipping_method' ) continue;
			if ( strpos( $key, 'shipping_' ) !== false ) {
				unset( $data[ $key ] );
			}
		}
	}
	return $data;
}

add_action( 'sunshine_order_notes', 'sunshine_pickup_order_notes' );
function sunshine_pickup_order_notes( $order_id ) {
	global $sunshine;
	$order_data = sunshine_get_order_data( $order_id );
	if ( $order_data['shipping_method'] == 'pickup' && !empty( $sunshine->options['pickup_instructions'] ) ) {
	?>
	<p id="sunshine-pickup-instructions"> 
		<?php echo $sunshine->options['pickup_instructions']; ?>
	</p>
	<?php
	}
}

add_action( 'sunshine_before_order_receipt_items', 'sunshine_pickup_order_email_notes', 10, 2 );
function sunshine_pickup_order_email_notes( $html, $order_id ) {
	global $sunshine;
	$order_data = sunshine_get_order_data( $order_id );
	if ( $order_data['shipping_method'] == 'pickup' && !empty( $sunshine->options['pickup_instructions'] ) ) {
		$html .= '<p>' . $sunshine->options['pickup_instructions'] . '</p>';
	}
	return $html;
}