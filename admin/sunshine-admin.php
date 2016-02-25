<?php
//require_once ( 'sunshine-tracking.php' );
require_once ( 'sunshine-menu.php' );
require_once ( 'sunshine-notices.php' );
require_once ( 'sunshine-dashboard.php' );
require_once ( 'sunshine-galleries.php' );
require_once ( 'sunshine-image-processor.php' );
require_once ( 'sunshine-products.php' );
require_once ( 'sunshine-bulk-add-products.php' );
require_once ( 'sunshine-orders.php' );
require_once ( 'sunshine-users.php' );

if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine' ) || ( isset( $_POST['currentTab'] ) && isset( $_POST['action'] ) && $_POST['action'] == 'update' ) ) {
	include_once( SUNSHINE_PATH.'classes/sf-class-settings.php' );
	$sunshine_options = new SF_Settings_API( $id = 'sunshine', $title = 'Sunshine Settings', $menu = 'admin.php', __FILE__ );
	$sunshine_options->load_options( SUNSHINE_PATH.'/sunshine-options.php' );
}

if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine' ) {
	flush_rewrite_rules();
}

function sunshine_manual_update() {
	echo '<div id="message" class="updated"><p>' .__('Update completed', 'sunshine') . '</p></div>';
}

function sunshine_admin_cssjs() {
	global $post_type;
	wp_register_style( 'sunshine-admin-css', plugins_url( 'assets/css/admin.css', dirname( __FILE__ ) ), '', SUNSHINE_VERSION );
	wp_enqueue_style( 'sunshine-admin-css' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery.ui.theme', plugins_url( 'assets/jqueryui/smoothness/jquery-ui-1.9.2.custom.css', dirname( __FILE__ ) ) );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_init', 'sunshine_admin_cssjs' );

function sunshine_about() {
	global $sunshine;
	?>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4&appId=228213277229357";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	
	<div id="sunshine-header">
		<h1><?php printf( __( 'Welcome to Sunshine Photo Cart %s', 'sunshine' ), $sunshine->version ); ?></h1>
		<p>
			<?php
				if ( isset( $_GET['sunshine_installed'] ) ) {
					$message = __( 'Thanks, all done!', 'sunshine' );
				} elseif ( isset( $_GET['sunshine_updated'] ) ) {
					$message = __( 'Thank you for updating to the latest version!', 'sunshine' );
				} else {
					$message = __( 'Thanks for installing!', 'sunshine' );
				}

				printf( __( '<strong>%s</strong> Sunshine %s is the most comprehensive client proofing and photo cart plugin for WordPress. We hope you enjoy greater selling success!', 'sunshine' ), $message, $sunshine->version );
			?>
		</p>
	</div>
	
	<div class="wrap about-wrap sunshine-about-wrap">

		<div class="fb-page sunshine-fb-page" data-href="https://www.facebook.com/sunshinephotocart" data-width="300" data-height="400" data-small-header="true" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="false" data-show-posts="true"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/sunshinephotocart"><a href="https://www.facebook.com/sunshinephotocart">Sunshine Photo Cart</a></blockquote></div></div>

		<p class="sunshine-actions">
			<a href="<?php echo admin_url('admin.php?page=sunshine'); ?>" class="button button-primary"><?php _e( 'Settings', 'sunshine' ); ?></a>
			<a href="<?php echo esc_url( 'https://www.sunshinephotocart.com/docs' ); ?>" class="docs button button-primary" target="_blank"><?php _e( 'Docs', 'sunshine' ); ?></a>
			<?php if ( get_option( 'sunshine_pro_license_active') != 'valid' ) { ?>
			<a href="<?php echo esc_url( 'https://www.sunshinephotocart.com/pro' ); ?>" class="button" target="_blank"><?php _e( 'Go Pro!', 'sunshine' ); ?></a>
			<?php } ?>
		</p>

		<div class="sunshine-changelog">
			<?php 
			$readme = file_get_contents( SUNSHINE_PATH . '/readme.txt' ); 
			$readme_pieces = explode( '== Changelog ==', $readme );
			$changelog = nl2br( htmlspecialchars( trim( $readme_pieces[1] ) ) ); 
			$changelog = str_replace( array( ' =', '= ' ), array( '</h3>', '<h3>' ), $changelog );
			if (($nth = nth_strpos($changelog, '<h3>', 7, true)) !== false) { 
			    $changelog = substr($changelog, 0, $nth); 
			} 
			?>
			<h2>What's New</h2>
			<div class="changelog"><?php echo $changelog; ?></div>					
		</div>
		
	</div>
	<?php
}

function nth_strpos($str, $substr, $n, $stri = false) 
{ 
    if ($stri) { 
        $str = strtolower($str); 
        $substr = strtolower($substr); 
    } 
    $ct = 0; 
    $pos = 0; 
    while (($pos = strpos($str, $substr, $pos)) !== false) { 
        if (++$ct == $n) { 
            return $pos; 
        } 
        $pos++; 
    } 
    return false; 
}

function sunshine_system_info() {
	global $sunshine;
?>
<div class="wrap sunshine">
		<h2>System Information</h2>
		<p>Use the information below when submitting tickets or questions via <a href="http://www.sunshinephotocart.com/support" target="_blank">Sunshine Support</a>.</p>

<textarea readonly="readonly" style="font-family: 'courier new', monospace; margin: 10px 0 0 0; width: 900px; height: 400px;" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).">

### Begin System Info ###

Multi-site:               <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

Home Page:                <?php echo site_url() . "\n"; ?>
Gallery URL:              <?php echo get_permalink( $sunshine->options['page'] ) . "\n"; ?>
Admin:                 	  <?php echo admin_url() . "\n"; ?>

WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo mysql_get_server_info() . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>

Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:            <?php echo get_option( 'page_on_front' ) . "\n" ?>
Page For Posts:           <?php echo get_option( 'page_for_posts' ) . "\n" ?>

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
	if ( get_option( 'sunshine_pro_license_active') == 'valid' ) return;
	
?>
	<div id="sunshine-header">
		<h1><?php _e( 'Add-ons for Sunshine Photo Cart', 'sunshine' ); ?></h1>
		<p>
			<?php _e( 'Go beyond the basics, Sunshine’s add-ons let you maximize your profits to help you build a more profitable client photo sales process.', 'sunshine' ); ?>
		</p>
	</div>

	<div class="wrap sunshine-wrap" id="sunshine-addons-wrap">
	
		<?php sunshine_promos(); ?>
		
	</div>
<?php
}

//add_action( 'sunshine_options_header', 'sunshine_promos' );

function sunshine_promos() {
	global $sunshine;
	if ( $sunshine->is_pro() ) return; 
?>
	<div id="sunshine-promos">
	
		<div id="sunshine-pro">
			<h2>Go PRO for only $149!</h2>
			<p>Get every single Sunshine add-on and access to premium support for one low price of $149</p>
			<p>Oh, and we offer a 30 day money back guarantee to sweeten the deal... because we know you won’t need it.</p>
			<p><a href="https://www.sunshinephotocart.com/pro" class="sunshine-button">Learn More</a></p>
		</div>

		<?php	
		$addons = get_transient( 'sunshine_addons' );
		if ( $addons ) {
			$addons = json_decode( $addons );
			echo '<ul id="sunshine-addons">';
			foreach ( $addons as $addon ) {
				if ( is_plugin_active( 'sunshine-' . $addon->slug . '/' . $addon->slug . '.php' ) ) {
					$action = '<p><span class="dashicons dashicons-yes"></span> Installed!</p>';
					$class = 'installed';
				} else {
					$action = '<p><span class="price">' . $addon->price . '</span> <a href="' . $addon->url . '?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" target="_blank">' . __( 'Learn more & buy &raquo;', 'sunshine' ) . '</a></p>';
					$class = 'available';
				}
				echo '<li class="' . $class . '"><h3><a href="' . $addon->url . '?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" target="_blank">' . $addon->title . '</a></h3><p>' . $addon->excerpt . '</p>' . $action . '</li>';
			}
			echo '</ul>';
		}
		?>
		<p id="sunshine-disclaimer">All licenses are subject to annual renewal at 50% of the initial price</p>
	</div>
<?php
}

add_action( 'admin_init', 'sunshine_get_addons' );
function sunshine_get_addons() {
	if ( false === ( $addons = get_transient( 'sunshine_addons' ) ) ) {

		$url = SUNSHINE_STORE_URL . '/?sunshine_addons_feed';

		$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$addons = wp_remote_retrieve_body( $feed );
				set_transient( 'sunshine_addons', $addons, WEEK_IN_SECONDS );
			}
		} 
	}
}

add_action( 'admin_init', 'sunshine_docs' );
function sunshine_docs() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine_docs' ) {
		wp_redirect( 'https://www.sunshinephotocart.com/docs/' );
		exit;
	}
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
// For main Media Gallery page
add_action( 'pre_get_posts', 'sunshine_clean_media_library' );
function sunshine_clean_media_library( $query ) {
	if ( is_admin() && $query->is_main_query() && $query->get( 'post_type' ) == 'attachment' ) {
		if ( ! function_exists( 'get_current_screen' ) ) { 
			return;
		}
		$screen = get_current_screen();
		if ( $screen->id != 'upload' ) {
			return;
		}
		$args = array(
			'post_type' => 'sunshine-gallery',
			'nopaging' => true,
			'post_status' => 'any'
		);
		$galleries = get_posts( $args );
		$gallery_ids = array();
		foreach ( $galleries as $gallery ) {
			$gallery_ids[] = $gallery->ID;
		}
		$query->set( 'post_parent__not_in', $gallery_ids );
	}
}

// For popup Media Gallery, this is done via AJAX call and has different filter
add_filter( 'ajax_query_attachments_args', 'my_wp_ajax_query_attachments', 1 );
function my_wp_ajax_query_attachments( $query ) {
	$this_post = get_post( $_POST['post_id'] );
	if ( is_admin() && $query['post_type'] == 'attachment' && $this_post->post_type != 'sunshine-gallery' ) {
		$args = array(
			'post_type' => 'sunshine-gallery',
			'nopaging' => true,
			'post_status' => 'any'
		);
		$galleries = get_posts( $args );
		$gallery_ids = array();
		foreach ( $galleries as $gallery ) {
			$gallery_ids[] = $gallery->ID;
		}
		$query['post_parent__not_in'] = $gallery_ids;
	}
	return $query;
}
?>