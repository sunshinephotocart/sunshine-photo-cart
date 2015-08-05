<?php
class SunshineSession extends SunshineSingleton {

	protected function __construct() {
		if ( !session_id() ) session_start();
	}

	public function __get( $key ) {
		if( isset( $_SESSION['sunshine_session'][$key] ) )
			return $_SESSION['sunshine_session'][$key];

		return null;
	}

	public function __set( $key, $value ) {
		$_SESSION['sunshine_session'][$key] = $value;
		return $value;
	}

	public function __isset( $key ) {
		return isset( $_SESSION['sunshine_session'][$key] );
	}

	public function __unset( $key ) {
		unset( $_SESSION['sunshine_session'][$key] );
	}

}