<?php
require_once ( 'sunshine-tracking.php' );
require_once ( 'sunshine-menu.php' );
require_once ( 'sunshine-dashboard.php' );
require_once ( 'sunshine-galleries.php' );
require_once ( 'sunshine-image-processor.php' );
require_once ( 'sunshine-products.php' );
require_once ( 'sunshine-bulk-add-products.php' );
require_once ( 'sunshine-orders.php' );
require_once ( 'sunshine-discounts.php' );
require_once ( 'sunshine-users.php' );


if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine' ) || ( isset( $_POST['currentTab'] ) && isset( $_POST['action'] ) && $_POST['action'] == 'update' ) ) {
	include_once( SUNSHINE_PATH.'classes/sf-class-settings.php' );
	$sunshine_options = new SF_Settings_API( $id = 'sunshine', $title = 'Sunshine Settings', $menu = 'admin.php', __FILE__ );
	$sunshine_options->load_options( SUNSHINE_PATH.'/sunshine-options.php' );
}

if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine' )
	flush_rewrite_rules();

add_action( 'admin_notices', 'sunshine_notices' );
function sunshine_notices() {
	global $sunshine;

	if ( isset( $_GET['sunshine_nag_users_can_register'] ) )
		SunshineUser::update_user_meta( 'sunshine_nag_users_can_register', 1 );
	if ( isset( $_GET['sunshine_nag_sunshine_not_front_page'] ) )
		SunshineUser::update_user_meta( 'sunshine_nag_sunshine_not_front_page', 1 );

	if ( get_option( 'permalink_structure' ) == '' ) {
		echo '<div class="error"><p>'.sprintf( __( 'Sunshine does not work using the Default Permalink settings. <a href="%s">Please choose another option</a> (we recommend "Post name").','sunshine' ),'options-permalink.php' ).'</p></div>';
	}
	if ( get_option( 'users_can_register' ) != 1 && !SunshineUser::get_user_meta( 'sunshine_nag_users_can_register' ) ) {
		echo '<div class="error"><p>'.sprintf( __( 'Sunshine requires that you enable user registration. <a href="%s">Update your general settings</a> and 	enable the "Anyone can register" option for "Membership".','sunshine' ),'options-general.php' ).' <a href="?sunshine_nag_users_can_register=1" style="float: right;">'.__( 'Dismiss','sunshine' ).'</a></p></div>';
	}
	if ( get_option( 'page_on_front' ) == $sunshine->options['page'] && !SunshineUser::get_user_meta( 'sunshine_nag_sunshine_not_front_page' ) )
		echo '<div class="error"><p>'.sprintf( __( 'Sunshine cannot be the front page of your WordPress installation. <a href="%s" target="_blank">Learn more on how to resolve this issue</a>.','sunshine' ),'http://www.sunshinephotocart.com/docs/sunshine-cannot-be-your-front-page/' ).' <a href="?sunshine_nag_sunshine_not_front_page=1" style="float: right;">'.__( 'Dismiss','sunshine' ).'</a></p></div>';
}

function sunshine_manual_update() {
	echo '<div id="message" class="updated"><p>' .__('Update completed', 'sunshine') . '</p></div>';
}

function sunshine_admin_cssjs() {
	global $post_type;
	wp_register_style( 'sunshine-admin-css', plugins_url( 'assets/css/admin.css', dirname( __FILE__ ) ) );
	wp_enqueue_style( 'sunshine-admin-css' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery.ui.theme', plugins_url( 'assets/jqueryui/smoothness/jquery-ui-1.9.2.custom.css', dirname( __FILE__ ) ) );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_init', 'sunshine_admin_cssjs' );

function sunshine_system_info() {
	global $sunshine;
?>
<div class="wrap sunshine">
		<div class="icon32 icon32-sunshine-system-info" id="icon-sunshine"><br/></div>
		<h2>System Information</h2>
		<p>Use the information below when submitting tickets or questions via <a href="http://www.sunshinephotocart.com/support" target="_blank">Sunshine Support</a>.</p>

<textarea readonly="readonly" style="font-family: 'courier new', monospace; margin: 10px 0 0 0; width: 900px; height: 400px;" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).">

	### Begin System Info ###

	Multi-site:               <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

	SITE_URL:                 <?php echo site_url() . "\n"; ?>
	HOME_URL:                 <?php echo home_url() . "\n"; ?>

	WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

	PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
	MySQL Version:            <?php echo mysql_get_server_info() . "\n"; ?>
	Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

	PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
	PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>

	WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

	Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
	Page On Front:            <?php echo get_option( 'page_on_front' ) . "\n" ?>
	Page For Posts:           <?php echo get_option( 'page_for_posts' ) . "\n" ?>

	Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
	Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
	Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
	Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
	Use Cookies:              <?php echo ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ); ?><?php echo "\n"; ?>
	Use Only Cookies:         <?php echo ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ); ?><?php echo "\n"; ?>

	UPLOAD_MAX_FILESIZE:      <?php if( function_exists( 'phpversion' ) ) echo ( sunshine_let_to_num( ini_get( 'upload_max_filesize' ) )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
	POST_MAX_SIZE:            <?php if( function_exists( 'phpversion' ) ) echo ( sunshine_let_to_num( ini_get( 'post_max_size' ) )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
	WordPress Memory Limit:   <?php echo ( sunshine_let_to_num( WP_MEMORY_LIMIT )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
	WP_DEBUG:                 <?php echo ( WP_DEBUG ) ? __( 'On', 'sunshine' ) : __( 'Off', 'sunshine' ); ?><?php echo "\n"; ?>
	DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
	FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? __( 'Your server supports fsockopen.', 'sunshine' ) : __( 'Your server does not support fsockopen.', 'sunshine' ); ?><?php echo "\n"; ?>

	ACTIVE PLUGINS:

<?php
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach ( $plugins as $plugin_path => $plugin ):

		//If the plugin isn't active, don't show it.
		if ( !in_array( $plugin_path, $active_plugins ) )
			continue;
?>
	<?php echo $plugin['Name']; ?>: <?php echo $plugin['Version']; ?>

<?php endforeach; ?>

	CURRENT THEME:

	<?php
	if ( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		echo $theme_data['Name'] . ': ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		echo $theme_data->Name . ': ' . $theme_data->Version;
	}
?>


	SUNSHINE SETTINGS:
	<?php foreach( $sunshine->options as $key => $value ): ?>
	<?php echo $key.': '.$value; ?>
	<?php endforeach; ?>

	### End System Info ###
</textarea>

	</div>
	<p>Our support team may ask you to manually run the update process. <a href="admin.php?page=sunshine_system_info&amp;sunshine_force_update=1">Click here to do so</a></p>
<?php

}

function sunshine_addons() {
	global $sunshine;
	if ( get_option( 'sunshine_photo_cart_pro_license_active') == 'valid' ) return;
	
?>
	<div class="wrap sunshine" id="sunshine-addons">
		<h2>Add-Ons for Sunshine Photo Cart</h2>

		<?php
		if ( false === ( $addons = get_transient( 'sunshine_addons' ) ) ) {

			$url = SUNSHINE_STORE_URL . '/?sunshine_addons_feed';

			$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
					$addons = wp_remote_retrieve_body( $feed );
					set_transient( 'sunshine_addons', $addons, 3600 );
				}
			} else {
				$addons = '<div class="error"><p>' . __( 'There was an error retrieving the add-ons list from the server. Please try again later.', 'sunshine' ) . '</div>';
			}
		}
		
		echo '<ul id="sunshine-addons">';
		$addons = json_decode( $addons );
		foreach ( $addons as $addon ) {
			echo '<li><h3><a href="' . $addon->link . '">' . $addon->title . '</a></h3><p>' . $addon->excerpt . '</p></li>';
		}
		echo '</ul>';
		
		?>
		
	</div>
<?php

}

function sunshine_support() {
		global $sunshine;
	?>
		<div class="wrap sunshine" id="sunshine-support">
			<h2>Sunshine Photo Cart Support</h2>
		
			<ul>
				<li><strong><a href="https://www.sunshinephotocart.com/docs" target="_blank">Documentation</a></strong> - Search the docs first! You're question may already be answered</li>
				<li><strong><a href="https://wordpress.org/support/plugin/sunshine-photo-cart" target="_blank">Community Forums</a></strong> - Submit a question and a member of the Sunshine community may be able to answer it for you</li>
				<li><strong><a href="https://www.sunshinephotocart.com/support-ticket/" target="_blank">Submit a support ticket</a></strong> - Sunshine Pro and Sunshine Priority Support users can get 1-on-1, in-depth support. We'll dig in and work tirelessly to find the solution to any problem you send over.</li>
			</ul>
			
		</div>
	<?php
}

add_action( 'save_post', 'sunshine_flush_rewrite_page_save' );
function sunshine_flush_rewrite_page_save( $post_id ) {
	global $sunshine;
	if ( $post_id == $sunshine->options['page'] ) {
		flush_rewrite_rules();
	}
}

add_filter( 'admin_footer_text', 'sunshine_admin_footer_text' );
function sunshine_admin_footer_text( $footer_text ) {
	global $typenow;

	if ( $typenow == 'sunshine-gallery' || $typenow == 'sunshine-product' || $typenow == 'sunshine-order' || $typenow == 'sunshine-product' || isset( $_GET['page'] ) && strpos( $_GET['page'], 'sunshine' ) !== false ) {
		$rate_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">Sunshine Photo Cart</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'sunshine' ),
			'https://www.sunshinephotocart.com',
			'https://wordpress.org/support/view/plugin-reviews/sunshine-photo-cart?filter=5#postform'
		);

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $rate_text . '</span>';
	}

	return $footer_text;

}


/**********************
Clean up Media Library
***********************/
add_action( 'pre_get_posts', 'sunshine_clean_media_library' );
function sunshine_clean_media_library( $query ) {
	$screen = get_current_screen();
	if ( is_admin() && is_main_query() && $query->get( 'post_type' ) == 'attachment' && $screen->id == 'upload' ) {
		$galleries = get_posts( 'post_type=sunshine-gallery&nopaging=true&post_status=any' );
		$gallery_ids = array();
		foreach ( $galleries as $gallery ) {
			$gallery_ids[] = $gallery->ID;
		}
		$query->set( 'post_parent__not_in', $gallery_ids );
	}
}

add_action( 'admin_head', 'sunshine_uploaded_to_page_default' );
function sunshine_uploaded_to_page_default() {
	global $parent_file;
	if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && isset( $_GET['post'] ) && $parent_file == 'edit.php?post_type=sunshine-gallery' ) {
?>
	<script>
	jQuery(document).ready(function($) {
		$('#wpcontent').ajaxStop(function() {
				$('.media-modal .media-frame .attachment-filters [value="uploaded"]').attr( 'selected', true ).parent().trigger('change');
		});
	});
	</script>
<?php
	}
}

?>