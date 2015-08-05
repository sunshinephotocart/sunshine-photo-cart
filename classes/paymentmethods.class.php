<?php
class SunshinePaymentMethods extends SunshineSingleton {

	public static $payment_methods = array();

	static function add_payment_method( $key, $name, $description, $order ) {
		self::$payment_methods[$order] = array(
			'key' => $key,
			'name' => $name,
			'description' => $description
		);
		ksort( self::$payment_methods );
	}

	static function method_name( $key ) {
		foreach ( self::$payment_methods as $method ) {
			if ( $method['key'] == $key )
				return $method['name'];
		}
	}

}
?>