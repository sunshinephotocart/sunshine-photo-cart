<?php
class SunshineNotices {

	private $option_notices = 'sunshine_notices';
	private $option_prefix = 'sunshine_notice_';
	private $notice_ids = array();
	private $notices = array();

	function __construct() {
		$this->set_notices();
		add_filter( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'admin_head', array( $this, 'notices_head' ) );
		add_action( 'wp_ajax_sunshine_notice_dismiss', array( $this, 'dismiss_notice' ) );
	}

	function set_notices() {
		$notice_ids = get_option( $this->option_notices );
		if ( empty( $notice_ids ) ) return;
		foreach ( $notice_ids as $notice_id ) {
			$notice = get_option( $this->option_prefix . $notice_id );
			$this->notices[ $notice_id ] = $notice;
		}
		$this->notice_ids = $notice_ids;
	}

	public function add_notice( $id, $notice, $class = 'notice-success', $dismissible = true, $retry = '', $force_recreate = false ) {
		if ( isset( $this->notices[ $id ] ) && $this->notices[ $id ]['status'] == 'dismissed' && !$force_recreate ) return;
		$this->notices[ $id ] = array(
			'time' => current_time( 'timestamp' ),
			'notice' => $notice,
			'class' => $class,
			'retry' => $retry,
			'dismissible' => $dismissible,
			'status' => 'active'
		);
		if ( $dismissible ) {
			update_option( $this->option_prefix . $id, $this->notices[ $id ] );
			if ( !in_array( $id, $this->notice_ids ) ) {
				$this->notice_ids[] = $id;
				update_option( $this->option_notices, $this->notice_ids );
			}
		}
	}

	function has_notices() {
		return !empty( $this->notices );
	}

	function show_notices() {
		$notices = '';
		if ( $this->has_notices() ) {
			foreach ( $this->notices as $notice_id => $notice ) {
				//if ( $notice['status'] == 'active' || ( $notice['status'] == 'dismissed' && $notice['retry'] && ( $notice['time'] + $notice['retry'] ) < current_time( 'timestamp' ) ) ) {
				if ( $notice['status'] == 'active' ) {
					if ( $notice['class'] == 'custom' ) {
						echo $notice['notice'];
					} else {
						$dismissible = ( $notice['dismissible'] ) ? 'is-dismissible' : '';
						$notices .= '<div data-notice-id="' . $notice_id . '" class="sunshine-notice notice ' . $dismissible . ' ' . $notice['class'] . '"><p>' . $notice['notice'] . '</p></div>';
					}
					/*
					if ( $notice['status'] == 'dismissed' ) {
						$notice['time'] = current_time( 'timestamp' );
						$notice['status'] = 'active';
						update_option( $this->option_prefix . $notice_id, $notice );
					}
					*/
				}
			}
		}
		echo $notices;
	}

	function notices_head() {
		if ( $this->has_notices() ) {
		?>
			<script>
			jQuery( document ).on( 'click', '.sunshine-notice .notice-dismiss', function() {
				var notice_id = jQuery( this ).parent().data( 'notice-id' );
				if ( notice_id ) {
				    jQuery.ajax({
					  	type: 'POST',
				        url: ajaxurl,
				        data: {
				            action: 'sunshine_notice_dismiss',
							id: notice_id
				        }
				    })
				}
				return false;
			});
			jQuery( document ).on( 'click', '.sunshine-notice .notice-dismiss-button', function() {
				var notice_id = jQuery( this ).closest( '.sunshine-notice' ).data( 'notice-id' );
				if ( notice_id ) {
				    jQuery.ajax({
					  	type: 'POST',
				        url: ajaxurl,
				        data: {
				            action: 'sunshine_notice_dismiss',
							id: notice_id
				        }
				    })
				}
				jQuery( this ).closest( '.sunshine-notice' ).fadeOut();
			});
			</script>
		<?php
		}
	}


	function dismiss_notice() {
		$notice = get_option( $this->option_prefix . $_POST['id'] );
		$notice['status'] = 'dismissed';
		$notice['dismissed_time'] = current_time( 'timestamp' );
		update_option( $this->option_prefix . $_POST['id'], $notice );
		die();
	}

	public function delete_notice( $id ) {
		unset( $this->notices[ $id ] );
		return delete_option( $this->option_prefix . $id );
	}
}

add_action( 'admin_init', 'sunshine_admin_notices', 5 );
function sunshine_admin_notices() {
	global $sunshine;
	$sunshine->notices = new SunshineNotices();

	if ( get_option( 'permalink_structure' ) == '' ) {
		$sunshine->notices->add_notice( 'permalink_structure', sprintf( __( 'Sunshine does not work using the Default Permalink settings. <a href="%s">Please choose another option</a> (we recommend "Post name").','sunshine' ),'options-permalink.php' ), 'notice-error', false );
	}
	if ( get_option( 'page_on_front' ) == $sunshine->options['page'] ) {
		$sunshine->notices->add_notice( 'page_on_front', sprintf( __( 'Sunshine cannot be the front page of your WordPress installation. <a href="%s" target="_blank">Learn more on how to resolve this issue</a>.','sunshine' ),'https://www.sunshinephotocart.com/docs/sunshine-cannot-be-your-front-page/' ), 'notice-error', false );
	}
	if ( !get_option( 'users_can_register' ) ) {
		$sunshine->notices->add_notice( 'users_can_register', sprintf( __( 'For some Sunshine features to work, such as favorites, users are required to register. However, you do not have registration enabled. <a href="%s">Please consider updating this option</a>.','sunshine' ),'options-general.php' ), 'notice-error', false );
	}

	if ( class_exists( 'Jetpack_Photon' ) && Jetpack::is_module_active( 'photon' ) ) {
		$sunshine->notices->add_notice( 'jetpack_photon', sprintf( __( 'Sunshine Photo Cart is not compatible with the Photon module in Jetpack. This will cause your images to not show. In order for Sunshine to function properly you will need to disable the "Speed up images and photos" feature <a href="%s">here</a>.','sunshine' ), 'admin.php?page=jetpack#/settings' ), 'notice-error', false );
	}

	if ( defined( 'SUNSHINE_DISABLE_PROMOS' ) ) return;

	$install_time = get_option( 'sunshine_install_time' );

	if ( !$sunshine->is_pro() && ( ( current_time('timestamp') - $install_time ) >= DAY_IN_SECONDS * 3 ) ) {
		$support_text = '<p>' . sprintf( __( 'I hope Sunshine is working well for you so far! <strong>If you do run into <em>any</em> issues using Sunshine or integrating it into your theme</strong>, please check out our <a href="%s" target="_blank">support area</a> to look at our help articles or the <a href="%s" target="_blank">support forums</a> to ask a specific question. I want to make sure Sunshine is working well for you. In almost all cases Sunshine works great out of the box, but with the infinite number of plugin and theme combinations in WordPress a few tweaks might be necessary and I am here to help.', 'sunshine' ), 'https://www.sunshinephotocart.com/support', 'https://wordpress.org/support/plugin/sunshine-photo-cart' ) . '<br /><br />
	    				Derek, Sunshine Developer</p>';
		$sunshine->notices->add_notice( 'support', $support_text, 'notice-info', true );
	}

	if ( ( current_time('timestamp') - $install_time ) >= DAY_IN_SECONDS * 15 ) {
		$survey_text = '<p>' . __( 'Would you like to earn $10 credit towards an add-on or a license renewal? Then go take the Sunshine survey! It will help us learn more about how you use Sunshine and what we can do to make it better.', 'sunshine') . '<br /><br />
	    				' . __( 'Thank You', 'sunshine' ) . '<br />
	    				Derek, Sunshine Developer</p>
	    				<p><a href="https://www.sunshinephotocart.com/survey/?utm_source=plugin&utm_medium=notice&utm_content=survey&utm_campaign=survey" target="_blank" class="button-primary notice-dismiss-button">' . __( 'Sure thing!', 'sunshine' ) . '</a> &nbsp;
						<a href="#" class="button notice-dismiss-button">' . __( 'No thanks', 'sunshine' ) . '</a></p>';
		$sunshine->notices->add_notice( 'survey', $survey_text, 'notice-info', true );
	}

	if ( !$sunshine->is_pro() && ( ( current_time('timestamp') - $install_time ) >= DAY_IN_SECONDS * 30 ) ) {
		$all_plugins = get_plugins();
		$install_plugins = array();
		foreach ( $all_plugins as $plugin => $plugin_data ) {
			if ( strpos( $plugin, 'sunshine' ) !== false && $plugin != 'sunshine-photo-cart/sunshine-photo-cart.php' ) {
				$installed_addons[] = str_replace( 'Sunshine Photo Cart - ', '', $plugin_data['Name'] );
			}
		}
		$has_addons = '';
		if ( !empty( $installed_addons ) ) {
			$installed_addons_names = join( ', ', $installed_addons );
			$has_addons = '<p><em>' . sprintf( 'And yes, you can get credited for the add-ons you have purchased. Just submit a support ticket after upgrading.', $installed_addons_names ) . '</em></p>';
		}

		$discounts = array(
			'30' => 'MRvwpv1dKP47',
			'40' => 'ocYLWdhu22gT',
			'50' => 'j0P8PQnMqGK7',
		);

		$discount_percent = array_rand( $discounts );
		$discount_code = $discounts[ $discount_percent ];

		$discount_text = '<p>You having been using Sunshine Photo Cart for quite a while and that\'s great! However, it appears you are not enjoying the awesome features of having a Sunshine Pro bundle license (all the add-ons and 1-on-1 priority support).</p><p><strong>I am doing a one-time only offer of ' . $discount_percent . '% off a Sunshine Pro license!</strong> Sunshine <em>rarely</em> does any kind of discounts, especially this big. You can be sure this offer will not happen again any time soon.</p>
						' . $has_addons . '
	    				<p><a href="https://www.sunshinephotocart.com/checkout/?promo=1&edd_action=add_to_cart&download_id=44&discount=' . $discount_code . '&utm_source=plugin&utm_medium=notice&utm_content=' . $discount_percent .'percent&utm_campaign=PluginProUpgrade" target="_blank" class="button-primary notice-dismiss-button">Upgrade me please!</a> &nbsp;
						<a href="https://www.sunshinephotocart.com/pro/?promo=1&discount=' . $discount_code . '&utm_source=plugin&utm_medium=notice&utm_content=' . $discount_percent . 'percent&utm_campaign=PluginProUpgrade" class="button" target="_blank">Learn more about Pro</a>
						<a href="#" class="button notice-dismiss-button">No thanks, I\'m good as is</a></p>';
		$sunshine->notices->add_notice( 'superdiscount', $discount_text, 'notice-info', true );
	}

	if ( ( current_time('timestamp') - $install_time ) >= DAY_IN_SECONDS * 45 ) {
		$review_text = '<p>' . __( 'You having been using Sunshine Photo Cart for a bit and that\'s awesome! Could you please do Sunshine a big favor and give it a 5-star rating on WordPress?  Reviews from users like you really help Sunshine to grow and continue to improve.', 'sunshine') . '<br /><br />
	    				' . __( 'Thank You', 'sunshine' ) . '<br />
	    				Derek, Sunshine Developer</p>
	    				<p><a href="https://wordpress.org/support/view/plugin-reviews/sunshine-photo-cart?filter=5#postform" target="_blank" class="button-primary notice-dismiss-button">' . __( 'Sure thing!', 'sunshine' ) . '</a> &nbsp;
						<a href="#" class="button notice-dismiss-button">' . __( 'No thanks', 'sunshine' ) . '</a></p>';
		$sunshine->notices->add_notice( 'review', $review_text, 'notice-info', true );
	}

	if ( get_option( 'sunshine_update_image_location' ) == 'yes' && ( !isset( $_GET['page'] ) || $_GET['page'] != 'sunshine_update_image_location' ) ) {
		$sunshine->notices->add_notice( 'update_image_location', sprintf( __( '<p>Sunshine 2.4 is now saving all images in a dedicated folder at "wp-content/uploads/sunshine". As such, it needs to move all past uploaded image files. If you have a lot of galleries and images, this could take a little while to complete, but is necessary.</p><p><strong><a href="%s" class="button">Please click here to run the update process</a></strong></p>','sunshine' ), admin_url( '/admin.php?page=sunshine_update_image_location' ) ), 'notice-warning', false );
	}

}

?>
