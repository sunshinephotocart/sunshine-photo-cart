<?php
/**
 * Plugin Name: Sunshine Photo Cart
 * Plugin URI: https://www.sunshinephotocart.com
 * Description: Client Gallery Photo Cart & Proofing Plugin for WordPress
 * Author: Sunshine Photo Cart
 * Author URI: https://www.sunshinephotocart.com
 * Version: 2.0
 * Text Domain: sunshine
 * Domain Path: languages
 *
 * Sunshine Photo Cart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Sunshine Photo Cart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sunshine Photo Cart. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SUNSHINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SUNSHINE_URL', plugin_dir_url( __FILE__ ) );
define( 'SUNSHINE_VERSION', '2.0' );
define( 'SUNSHINE_STORE_URL', 'https://www.sunshinephotocart.com' );

include_once( 'classes/singleton.class.php' );
include_once( 'classes/session.class.php' );
include_once( 'classes/sunshine.class.php' );
include_once( 'classes/frontend.class.php' );
include_once( 'classes/shipping.class.php' );
include_once( 'classes/user.class.php' );
include_once( 'classes/cart.class.php' );
include_once( 'classes/order.class.php' );
include_once( 'classes/email.class.php' );
include_once( 'classes/countries.class.php' );
include_once( 'classes/paymentmethods.class.php' );
include_once( 'classes/license.class.php' );

include_once( 'sunshine-functions.php' );
include_once( 'sunshine-template-functions.php' );
include_once( 'sunshine-widgets.php' );
include_once( 'sunshine-shortcodes.php' );

/* Get Features */
$addons = array_filter( glob( SUNSHINE_PATH.'addons/*' ), 'is_dir' );
foreach ( $addons as $addon ) {
	include $addon.'/index.php';
}

$sunshine = new Sunshine();

register_activation_hook( __FILE__, array( $sunshine,'install' ) );
register_deactivation_hook( __FILE__, array( $sunshine,'deactivate_license' ) );

/**
 * Main initialization of Sunshine
 *
 * @since 1.0
 * @return void
 */
add_action( 'init', 'sunshine_init', 5 );
function sunshine_init() {
	global $sunshine;

	add_rewrite_endpoint( $sunshine->options['endpoint_gallery'], EP_PERMALINK | EP_PAGES );
	add_rewrite_endpoint( $sunshine->options['endpoint_image'], EP_PERMALINK | EP_PAGES );
	add_rewrite_endpoint( $sunshine->options['endpoint_order'], EP_PERMALINK | EP_PAGES );

	SunshineUser::instance();
	SunshineCountries::instance();

	load_plugin_textdomain( 'sunshine', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	$functions = SUNSHINE_PATH.'themes/'.$sunshine->options['theme'].'/functions.php';
	if ( file_exists( $functions ) )
		include_once( $functions );

	if( is_admin() ) {
		include_once( 'admin/sunshine-admin.php' );
	} else {
		SunshineSession::instance();
		SunshinePaymentMethods::instance();
		SunshineEmail::instance();
		SunshineFrontend::instance();
	}

}

/**
 * Update Sunshine
 *
 * @since 1.0
 * @return void
 */
add_action( 'admin_init', 'sunshine_update_check' );
function sunshine_update_check() {
	global $sunshine;
	if ( $sunshine->version == '' ) return;
	if ( version_compare( $sunshine->version, SUNSHINE_VERSION, '<' ) || $sunshine->version == 0 || isset( $_GET['sunshine_force_update'] ) ) {
		$sunshine->update();
		add_action( 'admin_notices', 'sunshine_manual_update' );
	}
}

add_action( 'init', 'sunshine_pro_license', 0 );
function sunshine_pro_license() {
	if( class_exists( 'Sunshine_License' ) && is_admin() ) {
		$sunshine_pro_license = new Sunshine_License( 'sunshine-pro', 'Sunshine Photo Cart Pro', '2.0', 'Sunshine Photo Cart' );
	}
}

?>