<?php
set_time_limit( 600 );

function sunshine_image_processor() {
	global $sunshine;
	if ( !isset( $_GET['gallery'] ) ) return;
	
	$gallery_id = intval( $_GET['gallery'] );
?>
	<div class="wrap sunshine">
		<h2><?php _e( 'Image Processor' ); ?></h2>
		<p><?php _e( 'We are processing your images! Please be patient, especially if you have a lot.','sunshine' ); ?></p>
<?php
	$args = array(
		'post_type' => 'attachment',
		'post_parent' => $gallery_id,
		'nopaging' => true
	);
	$attachments = get_posts( $args );
	$existing_guids = array();
	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$existing_guids[] = $attachment->guid;
		}
	}

	$dir = get_post_meta( $gallery_id, 'sunshine_gallery_images_directory', true );
	$upload_dir = wp_upload_dir();

	// Check for special characters in folder and file names
	if ( !ctype_alnum( $dir ) ) {
		$sanitized_folder_name = sanitize_file_name( $dir );
		if ( $sanitized_folder_name != $dir ) {
			if ( rename( $upload_dir['basedir'].'/sunshine/'.$dir, $upload_dir['basedir'].'/sunshine/'.$sanitized_folder_name ) ) {
				if ( update_post_meta( $_GET['gallery'], 'sunshine_gallery_images_directory', $sanitized_folder_name ) ) {
					$updated_dir = 1;
					$dir = $sanitized_folder_name;
				}
			} else {
				echo '<div class="error"><p>The folder selected contains special characters (Example: spaces, apostrophes, ampersands). We tried to rename the folder for you but failed. Please rename your folder to something like "'.$sanitized_folder_name.'" and try again.</p></div>';
			}
			$sanitize_folder = true;
		}
	}

	$images_processed = 0;
	$image_size_total = 0;
	$count = 0;
	$offset = ( isset( $_GET['offset'] ) ) ? intval( $_GET['offset'] ) : 0;
	$folder = $upload_dir['basedir'].'/sunshine/'.$dir;
	$images = sunshine_get_images_in_folder( $folder );

	$existing_images = get_children( array( 'post_parent' => $gallery_id, 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
	foreach ( $existing_images as $existing_image ) {
		$existing_file_names[] = get_post_meta( $existing_image->ID, 'sunshine_file_name', true );
	}

	foreach ( $images as $filename ) { // Loop through all files
		$count++;
		$file_info = pathinfo( $filename );
		$guid = $upload_dir['baseurl'].'/sunshine/'.$dir.'/'.$file_info['basename'];;
		if ( $count > $offset ) {
			if ( $images_processed >= 25 )
				break;

			if ( !empty( $existing_file_names ) && in_array( basename( $filename ), $existing_file_names ) )
				continue;

			// Rename the image file name if it has special characters
			$sanitized_basename = sanitize_file_name( $file_info['basename'] );
			if ( $sanitized_basename != $file_info['basename'] ) {
				rename( $upload_dir['basedir'].'/sunshine/'.$dir.'/'.$file_info['basename'], $upload_dir['basedir'].'/sunshine/'.$dir.'/'.$sanitized_basename );
				$file_info['basename'] = $sanitized_basename;
			}

			$file_path = $upload_dir['basedir'].'/sunshine/'.$dir.'/'.$file_info['basename'];
			$file_url = $upload_dir['baseurl'].'/sunshine/'.$dir.'/'.$file_info['basename'];

			$tmp = download_url( $file_url );

			if ( is_wp_error( $tmp ) ) {
				$error_string = $tmp->get_error_message();
				echo '<div id="message" class="error"><p>' . $file_info['basename'].': '.$error_string . '</p></div>';
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
				continue;
			}

			preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $file_url, $matches );
			$file_array['name'] = basename( $matches[0] );
			$file_array['tmp_name'] = $tmp;

			$attachment_id = media_handle_sideload( $file_array, $gallery_id );
			if ( is_wp_error( $attachment_id ) ) {
				$error_string = $attachment_id->get_error_message();
				echo '<div id="message" class="error"><p>' . $file_info['basename'].': '.$error_string . '</p></div>';
				continue;
			}
			$attachment_meta_data = wp_get_attachment_metadata( $attachment_id );
			add_post_meta( $attachment_id, 'sunshine_file_name', basename( $filename ) );

			if ( $attachment_meta_data['image_meta']['title'] )
				wp_update_post( array( 'ID' => $attachment_id, 'post_title' => $attachment_meta_data['image_meta']['title'] ) );

			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $file_array['tmp_name'] );
			}

			$images_processed++;
			$thumb_img = wp_get_attachment_image_src( $attachment_id,'sunshine-thumbnail' );
			echo '<img src="'.$thumb_img[0].'" alt="" height="50" />';

			do_action( 'sunshine_after_image_process', $attachment_id, $file_info, $gallery_id );

		}
	}

	// After processing, see how many attachments we have now and compare to images in directory
	$attachments = get_posts( array(
			'post_type' => 'attachment',
			'post_parent' => $gallery_id,
			'posts_per_page' => -1
		) );
	$file_count = count( $attachments );
	$file_count_in_dir = sunshine_image_folder_count( $upload_dir['basedir'].'/sunshine/'.$dir );

	echo '<p>'.$file_count.' / '.$file_count_in_dir.'</p>';
	if ( $file_count_in_dir > $file_count && !$error_string ) {
		$offset += 25;
		echo '<a href="'.get_admin_url().'admin.php?page=sunshine_image_processor&gallery='.$gallery_id.'&offset='.$offset.'&child_gallery='.$child_gallery_id.'&folder='.$folder.'"  id="sunshine-image-processor-next" style="display: none;">'.__( 'Next page','sunshine' ).'</a>';
		echo '<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#sunshine-image-processor-next").bind("click", function() {
				  window.location.href = this.href;
				  return false;
				}).delay(5000).trigger("click");
			});
			</script>';
	} else {
		do_action( 'sunshine_after_image_processor', $gallery_id );
		echo '<p style="font-weight: bold;">All done! ';
		echo '<a href="'.get_admin_url().'post.php?post='.$gallery_id.'&action=edit">'.__( 'Edit this gallery','sunshine' ).'</a></p>';
	}
?>
	</div>
<?php
}
?>