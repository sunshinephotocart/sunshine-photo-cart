<?php
/*
Plugin Name: Sunshine Photo Cart - Favorites
Plugin URI: http://www.sunshinephotocart.com/addon/favorites
Description: Add-on for Sunshine Photo Cart - Allows clients to mark their favorites and submit them to you
Version: 1.0
Author: Sunshine Photo Cart
Author URI: http://www.sunshinephotocart.com
*/

if ( !is_admin() )
	add_action( 'init', 'sunshine_favorites_init', 30 );

function sunshine_favorites_init() {
	global $sunshine;
	if ( is_user_logged_in() ) {
		sunshine_set_favorites();
	}
}

add_filter( 'sunshine_pages', 'sunshine_favorites_page' );
function sunshine_favorites_page( $pages ) {
	global $sunshine;
	$pages['favorites'] = $sunshine->options['page_favorites'];
	return $pages;
}

add_filter( 'body_class', 'sunshine_favorites_body_class' );
function sunshine_favorites_body_class( $classes ) {
	global $sunshine;
	if ( is_page( $sunshine->options['page_favorites'] ) ) {
		$classes[] = 'sunshine-favorites';
	}
	return $classes;
}

add_filter( 'sunshine_main_menu', 'sunshine_favorites_build_main_menu', '20', 1 );
function sunshine_favorites_build_main_menu( $menu ) {
	global $sunshine;
	if ( $sunshine->options['page_favorites'] && is_user_logged_in() && !$sunshine->options['disable_favorites'] ) {
		$count = '';
		if ( sunshine_favorite_count( false ) )
			$count = '<span class="sunshine-count sunshine-favorite-count">'.sunshine_favorite_count( false ).'</span>';
		$menu[25] = array(
			//'icon' => 'heart',
			'name' => __( 'Favorites','sunshine' ),
			'after_a' => $count,
			'url' => sunshine_url( 'favorites' ),
			'class' => 'sunshine-favorites'
		);
	}
	return $menu;
}

add_filter( 'sunshine_action_menu', 'sunshine_favorites_build_action_menu', 20, 1 );
function sunshine_favorites_build_action_menu( $menu ) {
	global $post, $wp_query, $sunshine;

	if ( $sunshine->options['disable_favorites'] ) {
		return $menu;
	}

	if ( !empty( SunshineFrontend::$current_image ) ) {
		if ( is_user_logged_in() ) {
			$menu[15] = array(
				'icon' => 'heart',
				'name' => __( 'Add to Favorites','sunshine' ),
				'url' => '#',
				'a_class' => 'add-to-favorites',
				'attr' => array(
					'data-image-id' => SunshineFrontend::$current_image->ID
				)
			);
			if ( sunshine_is_image_favorite( SunshineFrontend::$current_image->ID ) ) {
				$menu[15]['a_class'] .= ' sunshine-favorite';
				$menu[15]['name'] = __('Remove from Favorites','sunshine');
			}
		} else {
			$menu[15] = array(
				'icon' => 'heart',
				'name' => __( 'Add to Favorites','sunshine' ),
				'url' => wp_login_url( add_query_arg( 'sunshine_favorite', SunshineFrontend::$current_image->ID, sunshine_current_url( false ) ) ),
				'a_class' => 'add-to-favorites',
			);
		}
	}

	if ( is_page( $sunshine->options['page_favorites'] ) && !empty( $sunshine->favorites ) ) {
		$nonce = wp_create_nonce( 'sunshine_clear_favorites' );
		$menu[50] = array(
			'icon' => 'close',
			'name' => __( 'Remove All Favorites','sunshine' ),
			'url' => add_query_arg( array( 'clear_favorites' => 1, 'nonce' => $nonce ) ), get_permalink( $sunshine->options['page_favorites'] )
		);
		$nonce = wp_create_nonce( 'sunshine_submit_favorites' );
		$menu[60] = array(
			'icon' => 'envelope',
			'name' => __( 'Submit Favorites','sunshine' ),
			'url' => add_query_arg( array( 'submit_favorites' => 1, 'nonce' => $nonce ), get_permalink( $sunshine->options['page_favorites'] ) )
		);
	}

	return $menu;
}

add_filter( 'sunshine_image_menu', 'sunshine_favorites_build_image_menu', 20, 2 );
function sunshine_favorites_build_image_menu( $menu, $image ) {
	global $sunshine;

	if ( $sunshine->options['disable_favorites'] ) {
		return $menu;
	}

	if ( is_user_logged_in() ) {
		$menu[5] = array(
			'icon' => 'heart',
			'url' => get_permalink( $image->ID ),
			'a_class' => 'add-to-favorites',
			'attr' => array(
				'data-image-id' => $image->ID
			)
		);
		if ( sunshine_is_image_favorite( $image->ID ) ) {
			$menu[5]['a_class'] .= ' sunshine-favorite';
		}
	}
	else {
		$menu[5] = array(
			'icon' => 'heart',
			'name' => __( 'Add to Favorites','sunshine' ),
			'url' => wp_login_url( add_query_arg( 'sunshine_favorite', $image->ID, sunshine_current_url( false ) ) ),
			'a_class' => 'add-to-favorites',
		);
	}

	if ( is_page( $sunshine->options['page_favorites'] ) ) {
		$disable_products = get_post_meta( $image->post_parent, 'sunshine_gallery_disable_products', true );
		if ( !$disable_products && !$sunshine->options['proofing'] && !sunshine_is_gallery_expired( $image->post_parent ) ) {
			$menu[10] = array(
				'icon' => 'shopping-cart',
				'url' => get_permalink( $image->ID ),
				'class' => 'sunshine-purchase',
			);
		}
		$allow_comments = get_post_meta( $image->post_parent, 'sunshine_gallery_image_comments', true );
		if ( $allow_comments ) {
			$menu[30] = array(
				'icon' => 'comments',
				'url' => get_permalink( $image->ID ).'#respond',
				'class' => 'sunshine-comments',
			);
		}

	}

	/*
	if (is_page($sunshine->options['page_favorites'])) {
		$menu[60] = array(
			'name' => __('Add note','sunshine'),
			'url' => '#',
			'a_class' => 'add-note',
			'attr' => array(
				'image-id' => $image->ID
			)
		);
	}
	*/

	return $menu;

}

add_action( 'wp_head', 'sunshine_favorites_add_to_favorites_js' );
function sunshine_favorites_add_to_favorites_js() {
	global $sunshine;
	if ( is_sunshine() && !$sunshine->options['disable_favorites'] ) {
?>
	<script>
	function sunshine_add_image_to_favorites(e) {
		var image_id = jQuery(e).data('image-id');
		var this_link = jQuery(e);
		jQuery.ajax({
		  	type: 'POST',
		  	url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
		  	data: {
		  		action: 'sunshine_add_to_favorites',
				image_id: image_id
			},
		  	success: function(data, textStatus, XMLHttpRequest) {
				data = data.trim();
			 	if (data == 'ADD') {
		  			jQuery('#sunshine-image-'+image_id).addClass('sunshine-favorite');
		  			jQuery(e).addClass('sunshine-favorite');
					jQuery('span', e).html('<?php _e('Remove from favorites', 'sunshine'); ?>');
					if (!jQuery('.sunshine-main-menu .sunshine-favorites .sunshine-favorite-count').length)
						jQuery('.sunshine-main-menu .sunshine-favorites').append('<span class="sunshine-count sunshine-favorite-count">0</span>');
					jQuery('.sunshine-main-menu .sunshine-favorite-count').html(parseInt(jQuery('.sunshine-main-menu .sunshine-favorite-count').html())+1);
				} else if (data == 'DELETE') {
		  			jQuery('#sunshine-image-'+image_id).removeClass('sunshine-favorite');
		  			jQuery(e).removeClass('sunshine-favorite');
					jQuery('span', e).html('<?php _e('Add to favorites', 'sunshine'); ?>');
					jQuery('.sunshine-main-menu .sunshine-favorite-count').html(parseInt(jQuery('.sunshine-main-menu .sunshine-favorite-count').html())-1);
					if (parseInt(jQuery('.sunshine-main-menu .sunshine-favorite-count').html()) == 0)
						jQuery('.sunshine-main-menu .sunshine-favorite-count').remove();
					<?php if ( is_page( $sunshine->options['page_favorites'] ) ) { ?>
						jQuery('#sunshine-image-'+image_id).fadeOut();
					<?php } ?>
				}
		  	},
		  	error: function(MLHttpRequest, textStatus, errorThrown) {
				alert('<?php _e( 'Sorry, there was an error with your request' ); ?> '+errorThrown+MLHttpRequest+textStatus);
		  	}
		});
		return false;
	}

	jQuery(document).ready(function() {

		// Adding items to favorite
		jQuery('a.add-to-favorites[data-image-id]').click(function() {
			sunshine_add_image_to_favorites(this);
			return false;
		});

	});
	</script>
<?php
	}
}

add_filter( 'sunshine_image_class', 'sunshine_favorites_image_class', 99, 2 );
function sunshine_favorites_image_class( $image_id, $classes = array() ) {
	if ( sunshine_is_image_favorite( $image_id ) )
		$classes[] = 'sunshine-favorite';
	return $classes;
}

add_filter( 'sunshine_options_pages', 'sunshine_favorites_options_page' );
function sunshine_favorites_options_page( $options ) {
	$options[] = array(
		'name' => __( 'Favorites','sunshine' ),
		'id'   => 'page_favorites',
		'select2' => true,
		'type' => 'single_select_page'
	);
	return $options;
}

add_action( 'sunshine_install_options', 'sunshine_make_favorites_page' );
add_action( 'sunshine_update_options', 'sunshine_make_favorites_page' );
function sunshine_make_favorites_page( $options ) {
	if ( !$options['page_favorites'] ) {
		$options['page_favorites'] = wp_insert_post( array(
				'post_title' => __( 'Favorites','sunshine' ),
				'post_content' => '',
				'post_type' => 'page',
				'post_status' => 'publish',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_parent' => $options['page']
			) );
	}
	return $options;
}

add_filter( 'sunshine_options_galleries', 'sunshine_favorites_options' );
function sunshine_favorites_options( $options ) {
	$options[] = array(
		'name' => __( 'Disable Favorites', 'sunshine' ),
		'id'   => 'disable_favorites',
		'type' => 'checkbox',
	);

	return $options;
}

add_filter( 'sunshine_content', 'sunshine_favorites_content' );
function sunshine_favorites_content( $content ) {
	global $sunshine;
	if ( $sunshine->options['page_favorites'] > 0 && is_page( $sunshine->options['page_favorites'] ) ) {
		$content .= SunshineFrontend::get_template( 'favorites' );
	}
	return $content;
}


add_filter( 'sunshine_template', 'sunshine_favorites_template' );
function sunshine_favorites_template( $template ) {
	global $sunshine;
	$theme_path = SUNSHINE_PATH.'themes/'.$sunshine->options['theme'].'/';
	if ( is_page( $sunshine->options['page_favorites'] ) ) {
		if ( file_exists( get_stylesheet_directory().'/sunshine/favorites.php' ) )
			$template = get_stylesheet_directory().'/sunshine/favorites.php';
		else
			$template = $theme_path.'favorites.php';
	}
	return $template;
}

add_action( 'before_delete_post', 'sunshine_cleanup_favorites' );
function sunshine_cleanup_favorites( $post_id ) {
	global $wpdb, $post_type;
	if ( $post_type != 'sunshine-gallery' ) return;
	$args = array(
		'post_type' => 'attachment',
		'post_parent' => $post_id,
		'nopaging' => true
	);
	$images = get_posts( $args );
	foreach ( $images as $image )
		$image_ids[] = $image->ID;
	if ( !empty( $image_ids ) ) {
		$delete_ids = implode( $image_ids, ', ' );
		$query = "
			DELETE FROM $wpdb->usermeta
			WHERE meta_key = 'sunshine_favorite'
			AND meta_value in ($delete_ids)
		";
		$wpdb->query( $query );
	}
}

add_action( 'wp', 'sunshine_favorites_listener' );
function sunshine_favorites_listener() {
	global $sunshine;
	if ( is_user_logged_in() && isset( $_GET['sunshine_favorite'] ) ) {
		sunshine_add_favorite( $_GET['sunshine_favorite'] );
		$sunshine->add_message( __( 'Image added to favorites', 'sunshine' ) );
		wp_redirect( remove_query_arg( 'sunshine_favorite', sunshine_current_url( false ) ) );
	}
}

add_filter( 'login_message', 'sunshine_favorites_login_message' );
function sunshine_favorites_login_message( $message ) {
	if ( isset( $_GET['redirect_to'] ) && strpos( $_GET['redirect_to'],'sunshine_favorite' ) !== false ) {
		return $message = sprintf( __( 'A user account is required to track your favorites so you can come back any time to see them. <a href="%s">Register here</a> or login to an existing account below.','sunshine' ), wp_registration_url() );
	}
	return $message;
}

add_action( 'wp_ajax_sunshine_add_to_favorites', 'sunshine_add_to_favorites' );
function sunshine_add_to_favorites() {
	global $current_user;
	if ( isset( $_POST['image_id'] ) ) {
		$favorites = sunshine_get_favorites();
		if ( in_array( $_POST['image_id'], $favorites ) ) {
			sunshine_delete_favorite( $_POST['image_id'] );
			$result = 'DELETE';
		} else {
			sunshine_add_favorite( $_POST['image_id'] );
			$result = 'ADD';
		}
	}
	die( $result );
}

add_action( 'init', 'sunshine_favorites_clear', 100 );
function sunshine_favorites_clear() {
	global $sunshine;
	if ( isset( $_GET['clear_favorites'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'sunshine_clear_favorites' ) ) {
		SunshineUser::delete_user_meta( 'favorite' );
		$sunshine->add_message( __( 'Favorites cleared','sunshine' ) );
		wp_redirect( sunshine_url( 'favorites' ) );
		exit;
	}
}

add_action( 'init', 'sunshine_favorites_submit', 110 );
function sunshine_favorites_submit() {
	global $sunshine, $current_user;
	if ( isset( $_GET['submit_favorites'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'sunshine_submit_favorites' ) ) {

		$content = sprintf( __( '%s has submitted their favorites, <a href="%s">view them here</a>', 'sunshine' ), $current_user->display_name, admin_url( 'user-edit.php?user_id='.$current_user->ID.'#sunshine-favorites' ) );
		$search = array( '[message_content]' );
		$replace = array( $content );

		if ( $sunshine->options['favorite_notifications'] )
			$admin_emails = explode( ',',$sunshine->options['favorite_notifications'] );
		else
			$admin_emails = array( get_bloginfo( 'admin_email' ) );
		foreach ( $admin_emails as $admin_email )
			$mail_result = SunshineEmail::send_email( 'favorites', trim( $admin_email ), sprintf( __( '%s has submitted favorites' ), $current_user->display_name ), '', $search, $replace );

		$sunshine->add_message( __( 'Your favorite images have been sent','sunshine' ) );
		wp_redirect( sunshine_url( 'favorites' ) );
		exit;
	}
}

add_filter( 'sunshine_lightbox_menu', 'sunshine_favorites_lightbox_menu', 10, 2 );
function sunshine_favorites_lightbox_menu( $menu, $image ) {
	global $sunshine;

	if ( $sunshine->options['disable_favorites'] ) {
		return $menu;
	}

	$class = '';
	if ( sunshine_is_image_favorite( $image->ID ) )
		$class = 'sunshine-favorite';
	if ( is_user_logged_in() ) {
		$menu .= ' <a href="#" data-image-id="'.$image->ID.'" onclick="sunshine_add_image_to_favorites(this); return false;" class="'.$class.'"><i class="fa fa-heart"></i></a>';
	} else {
		$menu .= '<a href="' . wp_login_url( add_query_arg( 'sunshine_favorite', $image->ID, sunshine_current_url( false ) . '#' . $image->ID ) ) . '"><i class="fa fa-heart"></i></a>';
	}
	return $menu;
}

/* Helper Functions */
function sunshine_favorite_count( $echo = 1 ) {
	global $sunshine;
	if ( $echo )
		echo count( $sunshine->favorites );
	else
		return count( $sunshine->favorites );
}

function sunshine_set_favorites() {
	global $sunshine;
	$sunshine->favorites = sunshine_get_favorites();
}

function sunshine_get_favorites() {
	return SunshineUser::get_user_meta( 'favorite', false );
}

function sunshine_add_favorite( $image_id ) {
	$image_id = intval( $image_id );
	$favorites = SunshineUser::get_user_meta( 'favorite', false );
	if ( in_array( $image_id, $favorites ) )
		return;
	SunshineUser::add_user_meta( 'favorite', $image_id, false );
	$favorite_count = get_post_meta( $image_id, 'sunshine_favorite_count', true );
	$favorite_count++;
	update_post_meta( $image_id, 'sunshine_favorite_count', $favorite_count );
	do_action( 'sunshine_add_favorite', $image_id );
}

function sunshine_delete_favorite( $image_id ) {
	$image_id = intval( $image_id );
	SunshineUser::delete_user_meta( 'favorite', $image_id );
	$favorite_count = get_post_meta( $image_id, 'sunshine_favorite_count', true );
	$favorite_count--;
	update_post_meta( $image_id, 'sunshine_favorite_count', $favorite_count );
}

function sunshine_is_image_favorite( $image_id ) {
	global $sunshine;
	if ( is_array( $sunshine->favorites ) ) {
		if ( in_array( $image_id, $sunshine->favorites ) )
			return true;
	}
	return false;
}

add_filter( 'user_row_actions', 'sunshine_user_favorites_link_row',5,2 );
function sunshine_user_favorites_link_row( $actions, $user ) {
	if ( current_user_can( 'sunshine_manage_options', $user->ID ) ) {
		$actions['sunshine_favorites'] = '<a href="user-edit.php?user_id='.$user->ID.'#sunshine-favorites">Favorites</a>';
	}
	return $actions;
}

add_action( 'show_user_profile', 'sunshine_admin_user_show_favorites' );
add_action( 'edit_user_profile', 'sunshine_admin_user_show_favorites' );
function sunshine_admin_user_show_favorites( $user ) {
	if ( current_user_can( 'manage_options' ) ) {
		$favorites = get_user_meta( $user->ID, 'sunshine_favorite' );
		if ( $favorites ) {
			echo '<h3 id="sunshine-favorites">'.__( 'Sunshine Favorites','sunshine' ).' ('.count( $favorites ).')</h3>';
			?>
				<p><a href="#sunshine-favorites-file-list" id="sunshine-favorites-file-list-link"><?php _e( 'Image File List', 'sunshine' ); ?></a></p>
				<div id="sunshine-favorites-file-list" style="display: none;">
					<?php
				foreach ( $favorites as $image_id ) {
					$image_file_list[$image_id] = get_post_meta( $image_id, 'sunshine_file_name', true );
				}
				foreach ( $image_file_list as &$file ) {
					$file = str_replace( array( '.jpg','.JPG' ), '', $file );
				}
			?>
					<textarea rows="4" cols="50" onclick="this.focus();this.select()" readonly="readonly"><?php echo join( ', ', $image_file_list ); ?></textarea>
					<p><?php _e( 'Copy and paste the file names above into Lightroom\'s search feature (Library filter) to quickly find and create a new collection to make processing this order easier. Make sure you are using the "Contains" (and not "Contains All") search parameter.', 'sunshine' ); ?></p>
				</div>
				<script>
				jQuery(document).ready(function($){
					$('#sunshine-favorites-file-list-link').click(function(){
						$('#sunshine-favorites-file-list').slideToggle();
						return false;
					});
				});
				</script>

			<?php
			echo '<ul>';
			foreach ( $favorites as $favorite ) {
				$attachment = get_post( $favorite );
				$image = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );
				$url = get_permalink( $attachment->ID );
?>
			<li style="list-style: none; float: left; margin: 0 20px 20px 0;">
				<a href="<?php echo $url; ?>"><img src="<?php echo $image[0]; ?>" height="100" alt="" /></a><br />
				<?php echo get_the_title( $attachment->ID ); ?>
			</li>
		<?php }
			echo '</ul><br clear="all" />';
		}
	}

}


add_action( 'wp', 'sunshine_favorites_check_availability' );
function sunshine_favorites_check_availability() {
	global $sunshine;
	if ( empty( $sunshine->favorites ) ) return;
	if ( is_page( $sunshine->options['page_favorites'] ) ) { // Remove items from favorites if image no longer exists
		$removed_items = false;
		foreach ( $sunshine->favorites as $favorite_id ) {
			$image = get_post( $favorite_id );
			$image_url = get_attached_file( $favorite_id );
			if ( !$image || !file_exists( $image_url ) ) {
				sunshine_delete_favorite( $favorite_id );
				$removed_items = true;
			}
		}
		if ( $removed_items ) {
			$sunshine->add_message( __( 'Images in your favorites have been removed because they are no longer available', 'sunshine' ) );
			wp_redirect( get_permalink( $sunshine->options['page_favorites'] ) );
			exit;
		}
	}
}

/**************************
	EMAIL AUTOMATION
	Add trigger
**************************/
add_filter( 'sunshine_email_triggers', 'sunshine_favorites_email_triggers' );
function sunshine_favorites_email_triggers( $triggers ) {
	$triggers['favorite'] = __( 'After user adds image to favorites', 'sunshine' );
	return $triggers;
}

?>
