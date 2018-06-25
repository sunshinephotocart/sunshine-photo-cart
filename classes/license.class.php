<?php
/**
 * License handler for Sunshine Photo Cart
 *
 * This class should simplify the process of adding license information
 * to new Sunshine add-ons.
 *
 * @version 1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Sunshine_License' ) ) :

/**
 * sunshine_License Class
 */
class Sunshine_License {
	private $file;
	private $license;
	private $item_name;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = SUNSHINE_STORE_URL;

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item_name
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 */
	function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null ) {
		global $sunshine;

		$this->file           = $_file;
		$this->item_name      = $_item_name;
		$this->item_shortname = str_replace( array( '.php', '-' ) , array( '', '_' ), basename( $_file ) );
		$this->version        = $_version;
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		if ( !empty( $sunshine->options[$this->item_shortname . '_license_key'] ) ) {
			$this->license = $sunshine->options[$this->item_shortname . '_license_key'];
		}

		// Setup hooks
		$this->includes();
		$this->hooks();
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if ( ! class_exists( 'SunshineUpdate' ) ) require_once 'update.class.php';
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Register settings
		add_filter( 'sunshine_options_licenses', array( $this, 'settings' ) );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ), 0 );

		// Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 1 );

		add_action( 'admin_notices', array( $this, 'notices' ) );

		add_action( 'admin_init', array( $this, 'check_license' ), 99 );

	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @return  void
	 */
	public function auto_updater() {

		if ( 'valid' !== get_option( $this->item_shortname . '_license_active' ) || $this->item_shortname == 'sunshine_pro' )
			return;

		// Setup the updater
		$sunshine_updater = new SunshineUpdate(
			$this->api_url,
			$this->file,
			array(
				'version'   => $this->version,
				'license'   => $this->license,
				'item_name' => $this->item_name,
				'author'    => $this->author
			)
		);
	}


	/**
	 * Add license field to settings
	 *
	 * @access  public
	 * @param array   $settings
	 * @return  array
	 */
	public function settings( $options ) {

		$id = $this->item_shortname . '_license_key';
		$status = get_option( $this->item_shortname . '_license_active' );
		$expiration = get_transient( $this->item_shortname . '_license_expiration' );
		$expiration_readable = '';
		if ( $expiration == 'lifetime' ) {
			$expiration_readable = __( 'You have a very special lifetime license, congrats!', 'sunshine' );
		} elseif ( $expiration && $expiration != 'lifetime' ) {
			$expiration_readable = date( get_option( 'date_format' ), strtotime( $expiration ) );
			$expiration_readable = sprintf( __( 'Your license expires on %s', 'sunshine' ), $expiration_readable );
		}
		$desc = '';
		if ( $status == 'invalid' && $this->license != '' ) {
			$desc = '<span style="color: #FF0000; font-weight: bold;">' . __( 'Invalid license key','sunshine' ) . '</span>';
		} elseif ( $status == 'expired' ) {
			$desc = '<span style="color: #FF0000; font-weight: bold;">' . sprintf( __( 'Your license expired on %s. <a href="https://www.sunshinephotocart.com/account" target="_blank">click here to renew</a>', 'sunshine' ), $expiration_readable ) . '</span>';
		} elseif ( $status == 'valid' ) {
			$desc = '<a href="' . wp_nonce_url('admin.php?page=sunshine&tab=licenses&deactivate='.$id, 'deactivate_sunshine_license', 'deactivate_'.$id ) . '" class="button">' . __( 'Deactivate this license','sunshine' ) . '</a>';
			if ( $expiration_readable ) {
				$desc .= ' ' . $expiration_readable;
			}
		}

		$options[] = array(
			'name'    => $this->item_name,
			'id'      => $id,
			'type' => 'text',
			'desc' => $desc
		);

		return $options;
	}


	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license() {

		if ( ! isset( $_POST['sunshine_options'] ) ) {
			return;
		}

		if ( ! isset( $_POST['sunshine_options'][ $this->item_shortname . '_license_key'] ) ) {
			return;
		}

		foreach( $_POST as $key => $value ) {
			if( false !== strpos( $key, 'license_key_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}

		if ( 'valid' === get_option( $this->item_shortname . '_license_active' ) ) {
			return;
		}

		$license = sanitize_text_field( $_POST['sunshine_options'][ $this->item_shortname . '_license_key'] );

		if( empty( $license ) ) {
			return;
		}

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
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

		update_option( $this->item_shortname . '_license_active', $license_data->license );
		set_transient( $this->item_shortname . '_license_expiration', $license_data->expires, WEEK_IN_SECONDS );

		do_action( $this->item_shortname . '_license_activate', $license_data->success );

		if( ! (bool) $license_data->success ) {
			set_transient( $this->item_shortname . '_license_error', $license_data, 1000 );
		} else {
			delete_transient( $this->item_shortname . '_license_error' );
		}
	}


	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license() {


		// Run on deactivate button press
		if ( isset( $_GET['deactivate'] ) && $_GET['deactivate'] == $this->item_shortname . '_license_key' && check_admin_referer( 'deactivate_sunshine_license', 'deactivate_' . $this->item_shortname . '_license_key' ) ) {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => SUNSHINE_STORE_URL
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
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

			delete_option( $this->item_shortname . '_license_active' );

			if( ! (bool) $license_data->success ) {
				set_transient( $this->item_shortname . '_license_error', $license_data, 1000 );
			} else {
				delete_transient( $this->item_shortname . '_license_error' );
			}

			wp_redirect( admin_url( 'admin.php?page=sunshine&tab=licenses' ) );
			exit;
		}
	}

	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function check_license() {
		global $sunshine;

		if ( $sunshine->is_pro() && $this->item_name != 'Sunshine Photo Cart Pro' ) {
			return;
		}

		$expiration = get_transient( $this->item_shortname . '_license_expiration' );

		if ( $expiration === FALSE ) {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
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

			update_option( $this->item_shortname . '_license_active', $license_data->license );
			set_transient( $this->item_shortname . '_license_expiration', $license_data->expires, WEEK_IN_SECONDS );

			if ( $license_data->license == 'expired' ) {
				$sunshine->notices->add_notice( $this->item_shortname . '_expired', sprintf( __('Your license for %s has expired - you are no longer eligible for further updates but you may continue to use it. <a href="https://www.sunshinephotocart.com/account" target="_blank">Go here to renew</a>', 'sunshine' ), $this->item_name ), 'notice-error', true );
			}
		}


	}



	/**
	 * Admin notices for errors
	 *
	 * @access  public
	 * @return  void
	 */
	public function notices() {

		if( ! isset( $_GET['page'] ) || 'sunshine' !== $_GET['page'] ) {
			return;
		}

		if( ! isset( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
			return;
		}

		$license_error = get_transient( $this->item_shortname . '_license_error' );

		if( false === $license_error ) {
			return;
		}

		if( ! empty( $license_error->error ) ) {

			switch( $license_error->error ) {

			case 'item_name_mismatch' :

				$message = __( 'This license does not belong to the product you have entered it for.', 'sunshine' );
				break;

			case 'no_activations_left' :

				$message = __( 'This license does not have any activations left', 'sunshine' );
				break;

			case 'expired' :

				$message = __( 'This license key is expired. Please renew it.', 'sunshine' );
				break;

			default :

				$message = sprintf( __( 'There was a problem activating your license key for %s, please try again or contact support. Error code: %s', 'sunshine' ), urldecode( $license_error->item_name ), $license_error->error );
				break;

			}

		}

		if( ! empty( $message ) ) {

			echo '<div class="error">';
			echo '<p>' . $message . '</p>';
			echo '</div>';

		}

		delete_transient( $this->item_shortname . '_license_error' );

	}
}

endif; // end class_exists check
