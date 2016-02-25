<?php
/**
 * Plugin Name: Sunshine Photo Cart
 * Plugin URI: https://www.sunshinephotocart.com
 * Description: Client Gallery Photo Cart & Proofing Plugin for WordPress
 * Author: Sunshine Photo Cart
 * Author URI: https://www.sunshinephotocart.com
 * Version: 2.3
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
define( 'SUNSHINE_VERSION', '2.3' );
define( 'SUNSHINE_STORE_URL', 'http://www.sunshinephotocart.com' );

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

/**
 * Main initialization of Sunshine
 *
 * @since 1.0
 * @return void
 */
$sunshine = new Sunshine();

register_activation_hook( __FILE__, array( $sunshine, 'install' ) );

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
	if ( version_compare( $sunshine->version, SUNSHINE_VERSION, '<' ) || isset( $_GET['sunshine_force_update'] ) ) {
		$sunshine->update();
	}
}

add_action( 'init', 'sunshine_pro_license', 0 );
function sunshine_pro_license() {
	if( class_exists( 'Sunshine_License' ) && is_admin() ) {
		$sunshine_pro_license = new Sunshine_License( 'sunshine-pro', 'Sunshine Photo Cart Pro', '2.0', 'Sunshine Photo Cart' );
	}
}


/**
 * Freemius stuff
 *
 * @since 2.3
 */
// Create a helper function for easy SDK access.
function spc_fs() {
    global $spc_fs, $sunshine;

    if ( ! isset( $spc_fs ) ) {
        // Include Freemius SDK.
        require_once SUNSHINE_PATH . '/freemius/start.php';
		
        $spc_fs = fs_dynamic_init( array(
            'id'                => '200',
            'slug'              => 'sunshine-photo-cart',
            'public_key'        => 'pk_c522e4cb20d0f13fa11ef9b05e41e',
            'is_premium'        => ( $sunshine->is_pro() ) ? true : false,
            'has_addons'        => false,
            'has_paid_plans'    => false,
            'menu'              => array(
                'slug'       => 'sunshine_admin',
                'account'    => false,
                'support'    => ( $sunshine->is_pro() ) ? false : true,
                'contact'    => ( $sunshine->is_pro() ) ? true : false,
            ),
        ) );
    }

    return $spc_fs;
}

function spc_fs_custom_connect_message(
    $message,
    $user_first_name,
    $plugin_title,
    $user_login,
    $site_link,
    $freemius_link
) {
    return sprintf(
        __fs( 'hey-x' ) . '<br>' .
        __( 'To improve Sunshine Photo Cart and provide better support, Sunshine would like to connect your site to our new plugin analytics tool', 'sunshine-photo-cart' ),
        $user_first_name
    );
}

// Init Freemius.
if ( is_admin() ) {
	spc_fs();
	spc_fs()->add_filter('connect_message', 'spc_fs_custom_connect_message', 10, 6);
	spc_fs()->add_filter('connect_message_on_update', 'spc_fs_custom_connect_message', 10, 6);
}
?>