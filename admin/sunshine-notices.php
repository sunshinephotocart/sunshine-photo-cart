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
	
	public function add_notice( $id, $notice, $class = 'notice-success', $dismissible, $retry, $force_recreate = false ) {
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
			sunshine_log( $this->notices );
			foreach ( $this->notices as $notice_id => $notice ) {
				if ( $notice['status'] == 'active' || ( $notice['status'] == 'dismissed' && ( $notice['time'] + $notice['retry'] ) < current_time( 'timestamp' ) ) ) {
					$dismissible = ( $notice['dismissible'] ) ? 'is-dismissible' : '';
					$notices .= '<div data-notice-id="' . $notice_id . '" class="sunshine-notice notice ' . $dismissible . ' ' . $notice['class'] . '"><p>' . $notice['notice'] . '</p></div>';
					if ( $notice['status'] == 'dismissed' ) {
						$notice['time'] = current_time( 'timestamp' );
						$notice['status'] = 'active';
						update_option( $this->option_prefix . $notice_id, $notice );
					}
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
					console.log( notice_id );
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
	
	
}


add_action( 'admin_init', 'sunshine_admin_notices', 100 );
function sunshine_admin_notices() {
	global $sunshine;
	$sunshine->notices = new SunshineNotices();

	if ( get_option( 'permalink_structure' ) == '' ) {
		$sunshine->notices->add_notice( 'permalink_structure', sprintf( __( 'Sunshine does not work using the Default Permalink settings. <a href="%s">Please choose another option</a> (we recommend "Post name").','sunshine' ),'options-permalink.php' ), 'notice-error', false );
	}
	if ( get_option( 'page_on_front' ) == $sunshine->options['page'] ) {
		$sunshine->notices->add_notice( 'page_on_front', sprintf( __( 'Sunshine cannot be the front page of your WordPress installation. <a href="%s" target="_blank">Learn more on how to resolve this issue</a>.','sunshine' ),'https://www.sunshinephotocart.com/docs/sunshine-cannot-be-your-front-page/' ), 'notice-error', false );
	}
	
	$review_text = '<p>' . __( 'You having been using Sunshine Photo Cart for a while now and that\'s awesome! Could you please do Sunshine a big favor and give it a 5-star rating on WordPress?  Reviews from users like you really help Sunshine to grow and continue to improve.', 'sunshine') . '<br /><br />
    				' . __( 'Thank You', 'sunshine' ) . '<br />
    				Derek, Lead Developer</p>
    				<p><a href="https://wordpress.org/support/view/plugin-reviews/sunshine-photo-cart?filter=5#postform" target="_blank" class="button-primary notice-dismiss-button">' . __( 'Sure thing!', 'sunshine' ) . '</a> &nbsp; 
					<a href="#" class="button notice-dismiss-button">' . __( 'Maybe later', 'sunshine' ) . '</a>  &nbsp; 
					<a href="#" class="button notice-dismiss-button">' . __( 'I already did', 'sunshine' ) . '</a></p>';
	$sunshine->notices->add_notice( 'review', $review_text, 'notice-info', true, 30 * DAY_IN_SECONDS );

	$survey_text = '<p>' . __( 'Would you like to earn $10 credit towards an add-on or a license renewal? Then go take the Sunshine survey! It will help us learn more about how you use Sunshine and what we can do to make it better.', 'sunshine') . '<br /><br />
    				' . __( 'Thank You', 'sunshine' ) . '<br />
    				Derek, Lead Developer</p>
    				<p><a href="https://www.sunshinephotocart.com/survey" target="_blank" class="button-primary notice-dismiss-button">' . __( 'Sure thing!', 'sunshine' ) . '</a> &nbsp; 
					<a href="#" class="button notice-dismiss-button">' . __( 'Maybe later', 'sunshine' ) . '</a>  &nbsp; 
					<a href="#" class="button notice-dismiss-button">' . __( 'I already did', 'sunshine' ) . '</a></p>';
	$sunshine->notices->add_notice( 'survey', $survey_text, 'notice-info', true, 60 * DAY_IN_SECONDS );

}

?>