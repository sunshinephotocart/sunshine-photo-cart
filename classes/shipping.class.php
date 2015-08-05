<?php
class SunshineShipping {

	public $methods = array();

	function __construct() {
		$this->methods = apply_filters( 'sunshine_add_shipping_methods', $this->methods );
	}

	function get_shipping_methods() {
		return apply_filters( 'sunshine_shipping_methods', $this->methods );
	}

	function get_shipping_method_cost( $method ) {
		global $sunshine;
		return $sunshine->options[$method.'_cost'];
	}

	function clear() {
		$this->methods = array();
	}

}
?>