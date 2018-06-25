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

add_filter( 'jpeg_quality', function( $arg ) { return 100; } );

add_filter( 'wp_image_editors', 'sunshine_force_imagick' );
function sunshine_force_imagick( $editors ) {
	if ( extension_loaded( 'imagick' ) ) {
		$editors = array( 'WP_Image_Editor_Imagick' );
	}
	return $editors;
}

if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine' ) || ( isset( $_POST['currentTab'] ) && isset( $_POST['action'] ) && $_POST['action'] == 'update' ) ) {
	include_once( SUNSHINE_PATH.'classes/sf-class-settings.php' );
	$sunshine_options = new SF_Settings_API( $id = 'sunshine', $title = 'Sunshine Settings', $menu = 'admin.php', __FILE__ );
	$sunshine_options->load_options( SUNSHINE_PATH.'/sunshine-options.php' );
}

if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine' ) {
	flush_rewrite_rules();
}

add_action( 'admin_init', 'sunshine_install_redirect' );
function sunshine_install_redirect() {
	if ( get_option( 'sunshine_install_redirect', false ) ) {
		delete_option( 'sunshine_install_redirect' );
		wp_redirect( admin_url( '/admin.php?page=sunshine_about&sunshine_install' ) );
		exit;
	}
}
function sunshine_manual_update() {
	echo '<div id="message" class="updated"><p>' .__('Update completed', 'sunshine') . '</p></div>';
}

add_action( 'admin_enqueue_scripts', 'sunshine_admin_cssjs' );
function sunshine_admin_cssjs() {
	global $post_type;

	wp_register_style( 'sunshine-admin-css', plugins_url( 'assets/css/admin.css', dirname( __FILE__ ) ), '', SUNSHINE_VERSION );
	wp_enqueue_style( 'sunshine-admin-css' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery.ui.theme', plugins_url( 'assets/jqueryui/smoothness/jquery-ui-1.9.2.custom.css', dirname( __FILE__ ) ) );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine' ) {
		wp_enqueue_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js', array( 'jquery' ) );
		wp_enqueue_style( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css' );
	}
}


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
				if ( isset( $_GET['sunshine_updated'] ) ) {
					$message = __( 'Thank you for updating to the latest version!', 'sunshine' );
				} else {
					$message = __( 'Thank you for installing!', 'sunshine' );
				}

				printf( __( '<strong>%s</strong> Sunshine %s is the most comprehensive client proofing and photo cart plugin for WordPress. We hope you enjoy greater selling success!', 'sunshine' ), $message, $sunshine->version );
			?>
		</p>
	</div>

	<div class="wrap about-wrap sunshine-about-wrap">

		<?php if ( !$sunshine->is_pro() ) { ?>
		<div id="sunshine-pro">
			<h2>Go PRO for only $149!</h2>
			<p>Get every single Sunshine add-on and access to premium support for one low price of $149</p>
			<p>Oh, and we offer a 30 day money back guarantee to sweeten the deal... because we know you won’t need it.</p>
			<p><a href="https://www.sunshinephotocart.com/pro/?utm_source=plugin&utm_medium=link&utm_campaign=about" class="sunshine-button">Learn More</a></p>
		</div>
		<?php } ?>
		<div class="fb-page sunshine-fb-page" data-href="https://www.facebook.com/sunshinephotocart" data-width="300" data-height="400" data-small-header="true" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="false" data-show-posts="true"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/sunshinephotocart"><a href="https://www.facebook.com/sunshinephotocart">Sunshine Photo Cart</a></blockquote></div></div>

		<?php if ( isset( $_GET['sunshine_install'] ) ) { ?>
			<div id="sunshine-get-started">
				<h2>Getting started is easy!</h2>
				<style>.embed-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; } .embed-container iframe, .embed-container object, .embed-container embed { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style><div class='embed-container'><iframe width="420" height="285" src="https://www.youtube.com/embed/t1IRNUASJSA?modestbranding=1&rel=0&showinfo=0" frameborder="0" allowfullscreen></iframe></div>
				<ol>
					<li><a href="http://www.facebook.com/sunshinephotocart" target="_blank">Follow us on Facebook</a> or <a href="http://eepurl.com/bzxukv" target="_blank">join our email newsletter</a> to stay updated with new features, important bug fixes, promos and more!</li>
					<li><a href="<?php echo admin_url('admin.php?page=sunshine_addons'); ?>">Check out the add-ons</a> to get more advanced functionality</li>
					<li><a href="<?php echo admin_url('admin.php?page=sunshine'); ?>">Configure your settings</a></li>
					<li><a href="<?php echo admin_url('edit.php?post_type=sunshine-product'); ?>">Create your products</a></li>
					<li><a href="<?php echo admin_url('edit.php?post_type=sunshine-gallery'); ?>">Create a gallery</a></li>
					<li>Invite your clients/users to view your galleries</li>
				</ol>
				<p><strong>Get more in depth help and how-to articles by going through the <a href="https://www.sunshinephotocart.com/docs/?utm_source=plugin&utm_medium=link&utm_campaign=docs">documentation</a></strong></p>
			</div>
		<?php } ?>

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
			<h2>What's Improved Recently</h2>
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

Home Page:                <?php echo site_url() . "\n"; ?>
Gallery URL:              <?php echo get_permalink( $sunshine->options['page'] ) . "\n"; ?>
Admin:                 	  <?php echo admin_url() . "\n"; ?>

WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
WordPress Memory Limit:   <?php echo ( sunshine_let_to_num( WP_MEMORY_LIMIT )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
ImageMagick:              <?php echo ( extension_loaded( 'imagick' ) ) ? 'Yes' : 'No'; echo  "\n"; ?>

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

IMAGE SIZES:

<?php
global $_wp_additional_image_sizes;
foreach ( $_wp_additional_image_sizes as $name => $image_size ) {
	$crop = ( $image_size['crop'] ) ? 'cropped' : 'not cropped';
?>
<?php echo $name . ': ' . $image_size['width'] . 'x' . $image_size['height'] . ' (' .$crop . ')'; ?>

<?php } ?>

### End System Info ###
</textarea>

	</div>
	<p>Our support team may ask you to manually run the update process. <a href="admin.php?page=sunshine_system_info&amp;sunshine_force_update=1">Click here to do so</a></p>
<?php

}

function sunshine_addons() {
	global $sunshine;
	//if ( get_option( 'sunshine_pro_license_active') == 'valid' ) return;

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
			<p><a href="https://www.sunshinephotocart.com/pro/?utm_source=plugin&utm_medium=link&utm_campaign=admin-promo" class="sunshine-button">Learn More</a></p>
		</div>

		<?php
		$addons = get_option( 'sunshine_addons' );
		if ( $addons ) {
			$addons = (object) json_decode( $addons );
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

add_filter( 'cron_schedules', 'sunshine_cron_add_weekly' );
function sunshine_cron_add_weekly( $schedules ) {
	// add a 'weekly' schedule to the existing set
	$schedules['weekly'] = array(
		'interval' => 604800,
		'display' => __('Once Weekly')
	);
	return $schedules;
}

add_action( 'admin_init', 'sunshine_addon_check_cron' );
function sunshine_addon_check_cron() {
	if (! wp_next_scheduled ( 'sunshine_addon_check' )) {
		wp_schedule_event(time(), 'weekly', 'sunshine_addon_check');
	}
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine_addons' ) {
		$plugins = get_option( 'sunshine_addons' );
		if ( empty( $plugins ) ) {
			sunshine_get_addons();
		}
	}
}

add_action( 'sunshine_addon_check', 'sunshine_get_addons' );
add_action( 'sunshine_install', 'sunshine_get_addons' );
function sunshine_get_addons() {
	global $sunshine;

	if ( $sunshine->is_pro() ) return;

	$url = SUNSHINE_STORE_URL . '/?sunshine_addons_feed';

	$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );
	if ( ! is_wp_error( $feed ) ) {
		if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$addons = wp_remote_retrieve_body( $feed );
			update_option( 'sunshine_addons', $addons );
		}
	}
}

add_action( 'admin_init', 'sunshine_help' );
function sunshine_help() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine_help' ) {
		wp_redirect( 'https://www.sunshinephotocart.com/support/?utm_source=plugin&utm_medium=link&utm_campaign=help' );
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
			'https://www.sunshinephotocart.com?utm_source=plugin&utm_medium=link&utm_campaign=rate',
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
	global $sunshine;
	if ( is_admin() && $query->is_main_query() && $query->get( 'post_type' ) == 'attachment' && !empty( $sunshine->options['show_media_library'] ) ) {
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
add_filter( 'ajax_query_attachments_args', 'sunshine_wp_ajax_query_attachments', 1 );
function sunshine_wp_ajax_query_attachments( $query ) {
	global $sunshine;
	$this_post = get_post( $_POST['post_id'] );
	if ( is_admin() && $query['post_type'] == 'attachment' && $this_post->post_type != 'sunshine-gallery' && $sunshine->options['show_media_library'] != 1 ) {
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

add_action( 'admin_bar_menu', 'sunshine_admin_bar_view_client_galleries', 768 );
function sunshine_admin_bar_view_client_galleries() {
  	global $wp_admin_bar, $sunshine;
  	if ( is_admin() ) {
    	$wp_admin_bar->add_node( array(
      		'id' => 'sunshine-client-galleries',
      		'title' => __( 'View Client Galleries', 'sunshine' ),
      		'href' => get_permalink( $sunshine->options['page'] ),
			'parent' => 'site-name'
    	) );
  }
}

/**********************
Move images from pre 2.4
***********************/
add_action( 'admin_enqueue_scripts', 'sunshine_update_image_location_ajaxq' );
function sunshine_update_image_location_ajaxq( $hook ) {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine_update_image_location' ) {
    	wp_enqueue_script( 'ajaxq', SUNSHINE_URL . 'assets/js/ajaxq.js' );
	}
}

add_action( 'admin_menu', 'sunshine_register_update_image_location_page' );
function sunshine_register_update_image_location_page() {
    add_submenu_page(
        null,
        'Update Image Location',
        'Update Image Location',
        'manage_options',
        'sunshine_update_image_location',
        'sunshine_update_image_location_page'
    );
}

function sunshine_update_image_location_page() {

	$start = ( isset( $_GET['start'] ) ) ? $_GET['start'] : 0;
	$args = array(
		'post_type' => 'sunshine-gallery',
		'posts_per_page' => 1,
		'post_status' => 'any',
		'offset' => $start,
		'meta_query' => array(
			array(
				'key' => '24_update',
		     	'compare' => 'NOT EXISTS',
		     	'value' => ''
			)
		)
	);
	$galleries = get_posts( $args );
	$gallery_count = count( $galleries );

	if ( $gallery_count > 0 ) {
		$image_count = 0;
		$image_ids = array();
		foreach ( $galleries as $gallery ) {
			$args = array(
				'post_type' => 'attachment',
				'posts_per_page' => -1,
				'post_status' =>'any',
				'post_parent' => $gallery->ID,
				'meta_query' => array(
					array(
						'key' => '24_update',
				     	'compare' => 'NOT EXISTS',
				     	'value' => ''
					)
				)
			);
			$images = get_posts( $args );
			$image_count = count( $images );
			foreach ( $images as $image ) {
				$image_ids[] = $image->ID;
			}
		}
	} else {
		delete_option( 'sunshine_update_image_location' );
	}
	$start++;
	$redirect_url = 'admin.php?page=sunshine_update_image_location&start=' . $start;
	?>

	<div class="wrap sunshine">
		<h2>Sunshine Upgrade Process</h2>
		<?php if ( $gallery_count > 0 ) { ?>
		<h3>Processing "<?php echo $galleries[0]->post_title; ?>"</h3>
		<p>If you have a lot of images, this could take a little while. <strong>Please do not leave this page until it is done.</strong></p>
		<div id="progress-bar" style="background: #000; height: 30px; position: relative;">
			<div id="percentage" style="height: 30px; background-color: green; width: 0%;"></div>
			<div id="processed" style="position: absolute; top: 0; left: 0; width: 100%; color: #FFF; text-align: center; font-size: 18px; height: 30px; line-height: 30px;">
				<span id="processed-count">0</span> of <span id="processed-total"><?php echo $image_count; ?></span>
			</div>
		</div>
		<ul id="errors"></ul>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var images = [<?php echo join( ',', $image_ids ); ?>];
			var processed = 0;
			var total = <?php echo $image_count; ?>;
			var percent = 0;
			var redirect_url = '<?php echo admin_url( $redirect_url ); ?>';
			if ( total == 0 ) {
				window.location.href = redirect_url;
			}
			function sunshine_update_image(image_id) {
				var data = {
					'action': 'sunshine_update_image_location',
					'image_id': image_id,
					'gallery_id': <?php echo $galleries[0]->ID; ?>
				};
				$.postq('imageupdatelocation', ajaxurl, data, function(response) {
					var obj = $.parseJSON( response );
					if ( obj.result == 'error' ) {
						$( '#errors' ).append( '<li><strong>' + obj.error + '</li>' );
						return;
					}
					processed++;
					if ( processed >= total ) {
						window.location.href = redirect_url;
					}
					$('#processed-count').html(processed);
					percent = Math.round( (processed / total) * 100);
					$('#percentage').css('width', percent+'%');
				});
			}
			for (i = 0; i < total; i++) {
			    sunshine_update_image( images[i] );
			}
		});
		</script>
		<?php } else { ?>
			<p id="done" style="font-size: 18px; color: green;"><?php _e( 'Your galleries have been updated!', 'sunshine' ); ?></p>
		<?php } ?>
	</div>

<?php
}

add_action( 'wp_ajax_sunshine_update_image_location', 'sunshine_update_image_location_ajax' );
function sunshine_update_image_location_ajax() {
	global $sunshine;
	if ( !isset( $_POST['image_id'] ) || !isset( $_POST['gallery_id'] ) ) { return; }
	$image_id = $_POST['image_id'];
	$gallery_id = $_POST['gallery_id'];
	$upload_dir = wp_upload_dir();
	$meta = wp_get_attachment_metadata( $image_id );

	$file_name = basename( $meta['file'] );

	if ( !isset( $meta['file'] ) || !file_exists( $upload_dir['basedir'] . '/' . $meta['file'] ) ) { // Meta data simply doesn't exist, let's just consider this updated and move on
		update_post_meta( $image_id, '24_update', 'yes' );
		echo json_encode( array( 'result' => 'success', 'message' => __( 'Image could not be found, just moving on', 'sunshine' ) ) );
		exit;
	}

	// New folder path
	$new_path = $upload_dir['basedir'] . '/sunshine/' . $gallery_id;
	if ( !is_dir( $new_path ) ) {
		$mkdir = mkdir( $new_path );
		if ( !$mkdir ) {
			echo json_encode( array( 'result' => 'error', 'message' => __( 'Could not make new folder for gallery', 'sunshine' ) ) );
			exit;
		}
	}
	// Move main image
	$rename = rename( $upload_dir['basedir'] . '/' . $meta['file'], $new_path . '/' . $file_name );
	if ( !$rename ) {
		echo json_encode( array( 'result' => 'error', 'message' => sprintf( __( 'Could not move full size image: %s', 'sunshine' ), $meta['file'] ) ) );
		exit;
	}

	// Move all sizes
	$folder = str_replace( basename( $meta['file'] ), '', $meta['file'] );
	foreach ( $meta['sizes'] as $size ) {
		if ( file_exists( $upload_dir['basedir'] . '/' . $folder . $size['file'] ) ) {
			$rename = rename( $upload_dir['basedir'] . '/' . $folder . $size['file'], $new_path . '/' . $size['file'] );
			if ( !$rename ) {
				echo json_encode( array( 'result' => 'error', 'message' => sprintf( __( 'Could not move %s', 'sunshine' ), $size['file'] ) ) );
				exit;
			}
		}
	}

	// Update meta data
	$new_meta = $meta;
	$new_meta['file'] = 'sunshine/' . $gallery_id . '/' . $file_name;
	wp_update_attachment_metadata( $image_id, $new_meta );
	update_attached_file( $image_id, $new_path . '/' . $file_name );
	update_post_meta( $image_id, '24_update', 'yes' );

	echo json_encode( array( 'result' => 'success', 'message' => sprintf( __( 'Successfully moved %s', 'sunshine' ), $file_name ) ) );

	exit;
}


add_action( 'admin_notices', 'sunshine_order_admin_debug' );
function sunshine_order_admin_debug() {
	if ( isset( $_GET['post'] ) && isset( $_GET['sunshine_debug'] ) ) {
		echo '<div class="notice error">';
		$meta = get_post_meta( $_GET['post'] );
		foreach ( $meta as $key => $values ) {
			echo '<strong>' . $key . '</strong><br />';
			foreach ( $values as $value ) {
				$value = maybe_unserialize( $value );
				sunshine_dump_var( $value );
			}
		}
		echo '</div>';
	}
}

/* Customizations for Order Status admin */
add_action( 'admin_head', 'sunshine_order_status_admin_customizations' );
function sunshine_order_status_admin_customizations() {
	$screen = get_current_screen();
	if ( $screen->id == 'edit-sunshine-order-status' && isset( $_GET['tag_ID'] ) ) {
		if ( isset( $_GET['tag_ID'] ) ) {
			$current_status = get_term( $_GET['tag_ID'] );
		}
	?>
	<script>
	jQuery( document ).ready( function($) {
		$( '.inline-edit-row label:nth-child(2)' ).remove();
		<?php
		if ( isset( $_GET['tag_ID'] ) ) {
			$core_statuses = sunshine_core_order_statuses();
			if ( in_array( $current_status->slug, $core_statuses ) ) {
		?>
			$( '#delete-link' ).remove();
			$( '.form-table tr:nth-child(2) p.description' ).html( '<?php echo esc_attr( __( 'The slug for core order statuses is not editable as it could break Sunshine functionality', 'sunshine' ) ); ?>' );
			$( '.form-field input[name="slug"]' ).attr( 'disabled', 'disabled' );
		<?php } } ?>
	});
	</script>
	<?php
	}
}

add_filter( 'sunshine-order-status_row_actions', 'sunshine_order_status_row_actions', 10, 2 );
function sunshine_order_status_row_actions( $actions, $term ) {
	$core_statuses = sunshine_core_order_statuses();
	if ( in_array( $term->slug, $core_statuses ) ) {
		unset( $actions['delete'] );
	}
	return $actions;
}


/* GDPR */
function sunshine_personal_data_exporter( $email_address, $page = 1 ) {

	$number = 500; // Limit us to avoid timing out
	$page = (int) $page;

	$export_items = array();

	$orders = get_posts(
	  	array(
		  	'post_type' => 'sunshine-order',
		  	'number' => $number,
		  	'paged' => $page
	  	)
	);

	foreach ( (array) $orders as $order ) {

		$item_id = "sunshine-order-{$order->ID}";
		$group_id = 'sunshine-order';
		$group_label = __( 'Orders', 'sunshine' );

		$order_data = sunshine_get_order_data( $order->ID );

		// Plugins can add as many items in the item data array as they want
		$data = array(
		  	array(
			  	'name'  => __( 'Order ID' ),
			  	'value' => $order->ID
		  	),
		  	array(
			  	'name'  => __( 'Email' ),
			  	'value' => $order_data['email']
		  	),
		  	array(
			  	'name'  => __( 'Phone' ),
			  	'value' => $order_data['phone']
		  	),
		  	array(
			  	'name'  => __( 'Billing First Name' ),
			  	'value' => $order_data['first_name']
		  	),
		  	array(
			  	'name'  => __( 'Billing Last Name' ),
			  	'value' => $order_data['last_name']
		  	),
		  	array(
			  	'name'  => __( 'Billing Address' ),
			  	'value' => $order_data['address']
		  	),
		  	array(
			  	'name'  => __( 'Billing Address 2' ),
			  	'value' => $order_data['address2']
		  	),
		  	array(
			  	'name'  => __( 'Billing City' ),
			  	'value' => $order_data['city']
		  	),
		  	array(
			  	'name'  => __( 'Billing State' ),
			  	'value' => $order_data['state']
		  	),
		  	array(
			  	'name'  => __( 'Billing Country' ),
			  	'value' => $order_data['country']
		  	),
		  	array(
			  	'name'  => __( 'Shipping First Name' ),
			  	'value' => $order_data['shipping_first_name']
		  	),
		  	array(
			  	'name'  => __( 'Shipping Last Name' ),
			  	'value' => $order_data['shipping_last_name']
		  	),
		  	array(
			  	'name'  => __( 'Shipping Address' ),
			  	'value' => $order_data['shipping_address']
		  	),
		  	array(
			  	'name'  => __( 'Shipping Address 2' ),
			  	'value' => $order_data['shipping_address2']
		  	),
		  	array(
			  	'name'  => __( 'Shipping City' ),
			  	'value' => $order_data['shipping_city']
		  	),
		  	array(
			  	'name'  => __( 'Shipping State' ),
			  	'value' => $order_data['shipping_state']
		  	),
		  	array(
			  	'name'  => __( 'Shipping Country' ),
			  	'value' => $order_data['shipping_country']
		  	),
		  	array(
			  	'name'  => __( 'Notes' ),
			  	'value' => $order_data['notes']
			)
		);

	  	$export_items[] = array(
	    	'group_id'    => $group_id,
	    	'group_label' => $group_label,
	    	'item_id'     => $item_id,
	    	'data'        => $data,
	  	);
	}

  	// Tell core if we have more comments to work on still
  	$done = count( $orders ) < $number;

  	return array(
    	'data' => $export_items,
    	'done' => $done,
  	);
}

function register_sunshine_personal_data_exporter( $exporters ) {
  	$exporters[] = array(
    	'exporter_friendly_name' => __( 'Comment Location Plugin' ),
    	'callback'               => 'sunshine_personal_data_exporter',
    );
  	return $exporters;
}

add_filter( 'wp_privacy_personal_data_exporters', 'register_sunshine_personal_data_exporter', 10 );
