<?php
class SunshineSession extends SunshineSingleton {

	protected function __construct() {
		if ( !session_id() ) session_start();
	}

	public function __get( $key ) {
		if( !empty( $_SESSION['sunshine_session'][$key] ) ) {
			return $_SESSION['sunshine_session'][$key];
		}
				
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

/**
 * Original source: WooCommerce
 *
 * Set a cookie - wrapper for setcookie using WP constants
 *
 * @param  string  $name   Name of the cookie being set
 * @param  string  $value  Value of the cookie
 * @param  integer $expire Expiry of the cookie
 * @param  string  $secure Whether the cookie should be served only over https
 */

function sunshine_setcookie( $name, $value, $expire = 0, $secure = false, $from = '' ) {
	if ( ! headers_sent() ) {
		setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		headers_sent( $file, $line );
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
	}
}
