<?php
/*
When a user enters a Pro license, this uses TGM Plugin Activation class
to allow users to one-click install/activate any Sunshine Photo Cart
add-on. It also grabs their license key from our server for each add-on making
the process super simple. No need to manage tons of license keys if you want
every add-on!
*/

require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

add_action( 'admin_init', 'sunshine_addon_check_pro_page' );
function sunshine_addon_check_pro_page() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine_addon_manager' ) {
		sunshine_get_pro_addons();
	}
}

add_action( 'sunshine_addon_check', 'sunshine_get_pro_addons', 20 );
add_action( 'sunshine_pro_license_activate', 'sunshine_get_pro_addons' );
function sunshine_get_pro_addons( $force = false ) {
	global $sunshine;

	if ( $sunshine->is_pro() || $force ) {
		$url = SUNSHINE_STORE_URL.'/?sunshine_addons_feed&pro=1';
		$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$addons = json_decode( wp_remote_retrieve_body( $feed ) );
				foreach ($addons as $addon) {
					if ( empty( $addon->file ) ) continue;
					$plugins[] = array(
						'name'               => $addon->title,
						'slug'               => 'sunshine-'.$addon->slug,
						'source'             => $addon->file,
						'required'           => false,
						'external_url'       => $addon->url
					);
				}
				update_option( 'sunshine_addons_pro', $plugins );
			}
		}
	}

}

add_action( 'tgmpa_register', 'sunshine_addon_manager', 9999 );
function sunshine_addon_manager() {
	global $sunshine;

	if ( !$sunshine->is_pro() ) return;

	$plugins = get_option( 'sunshine_addons_pro' );

	if ( !is_array( $plugins ) ) {
		$plugins = array();
	};

    $config = array(
        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
        'menu'         => 'sunshine_addon_manager', 	// Menu slug.
		'parent_slug'  => 'sunshine_admin',
		'capability'   => 'sunshine_manage_options',
        'has_notices'  => false,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => true,                   // Automatically activate plugins after installation or not.
        'message'      => '<div class="notice notice-info"><p>' . __( 'Please only install the add-ons you need. Hint: You <em>absolutely</em> do not need all of them', 'sunshine' ) . '</p></div>',                      // Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => __( 'Sunshine Pro Add-on Manager', 'sunshine' ),
            'menu_title'                      => __( 'Add-on Manager', 'sunshine' ),
            'installing'                      => __( 'Installing Add-on: %s', 'sunshine' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'sunshine' ),
            'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins' ),
            'return'                          => __( 'Return to Sunshine Pro Add-on Manager', 'sunshine' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'sunshine' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'sunshine' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa( $plugins, $config );

}

function sunshine_addon_manager_get_license( $shortname ) {
	global $sunshine;

	if ( !$sunshine->options['sunshine_pro_license_key'] ) return;

	// Get license data from sunshine website
	$url = SUNSHINE_STORE_URL.'/?sunshine_get_license&plugin='.$shortname.'&pro_license='.$sunshine->options['sunshine_pro_license_key'];
	error_log( $url );
	$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );
	$license = '';

	if ( ! is_wp_error( $feed ) ) {
		if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$license = wp_remote_retrieve_body( $feed );
		}
	}

	return $license;

}

function sunshine_addon_manager_activate_license( $name, $shortname ) {

	$shortname = str_replace( array( '.php', '-' ) , array( '', '_' ), basename( $shortname ) );

	$license = sunshine_addon_manager_get_license( $shortname );

	if ( !$license ) return;

	// Data to send to the API
	$api_params = array(
		'edd_action' => 'activate_license',
		'license'    => $license,
		'item_name'  => urlencode( $name ),
		'url'        => home_url()
	);

	// Call the API
	$response = wp_remote_post(
		SUNSHINE_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		)
	);

	// Make sure there are no errors
	if ( is_wp_error( $response ) ) {
		return;
	}

	// Tell WordPress to look for updates
	set_site_transient( 'update_plugins', null );

	// Decode license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	update_option( $shortname . '_license_active', $license_data->license );
	set_transient( $shortname . '_license_expiration', $license_data->expires, WEEK_IN_SECONDS );

	do_action( $shortname . '_license_activate', $license_data->success );

	if( ! (bool) $license_data->success ) {
		set_transient( $shortname . '_license_error', $license_data, 1000 );
	} else {
		delete_transient( $shortname . '_license_error' );
	}

	// Put license key into global Sunshine options
	$options = maybe_unserialize( get_option( 'sunshine_options' ) );
	$options[ $shortname . '_license_key' ] = $license;
	update_option( 'sunshine_options', $options );

}

function sunshine_addon_manager_deactivate_license( $name, $shortname ) {

	$shortname = str_replace( array( '.php', '-' ) , array( '', '_' ), basename( $shortname ) );

	$license = sunshine_addon_manager_get_license( $shortname );

	// Data to send to the API
	$api_params = array(
		'edd_action' => 'deactivate_license',
		'license'    => $license,
		'item_name'  => urlencode( $name ),
		'url'        => home_url()
	);

	// Call the API
	$response = wp_remote_post(
		SUNSHINE_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		)
	);

	// Make sure there are no errors
	if ( is_wp_error( $response ) ) {
		return;
	}

	// Decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	delete_option( $shortname . '_license_active' );

	// Put empty license key into global Sunshine options
	$options = maybe_unserialize( get_option( 'sunshine_options' ) );
	$options[ $shortname . '_license_key' ] = '';
	update_option( 'sunshine_options', $options );

}

function sunshine_child_plugin_notice() {
?>
	<div class="error"><p><?php _e( 'Sorry, all Sunshine add-ons require that the main Sunshine Photo Cart plugin first be active','sunshine' ); ?></p></div>
<?php
}

?>
