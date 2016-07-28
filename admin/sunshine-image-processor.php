<?php
add_action( 'admin_enqueue_scripts', 'sunshine_enqueue_image_processor_ajaxq' );
function sunshine_enqueue_image_processor_ajaxq( $hook ) {
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine_image_processor' ) {
	    wp_enqueue_script( 'ajaxq', SUNSHINE_URL . 'assets/js/ajaxq.js' );
    }
}

function sunshine_image_processor() {
	global $sunshine;
	if ( !isset( $_GET['gallery'] ) ) return;
	
	$gallery_id = intval( $_GET['gallery'] );
	$dir = get_post_meta( $gallery_id, 'sunshine_gallery_images_directory', true );
	$upload_dir = wp_upload_dir();
	$folder = $upload_dir['basedir'].'/sunshine/'.$dir;
	$count = sunshine_image_folder_count( $folder );
?>
	<div class="wrap sunshine">
		<h2><?php _e( 'Image Processor', 'sunshine' ); ?></h2>
		<p><?php _e( 'We are processing your images! Please be patient, especially if you have a lot.','sunshine' ); ?></p>
		<div id="progress-bar" style="background: #000; height: 30px; position: relative;">
			<div id="percentage" style="height: 30px; background-color: green; width: 0%;"></div>
			<div id="processed" style="position: absolute; top: 0; left: 0; width: 100%; color: #FFF; text-align: center; font-size: 18px; height: 30px; line-height: 30px;">
				<span id="processed-count">0</span> of <span id="processed-total"><?php echo $count; ?></span>
			</div>
		</div>
		<p align="center" id="abort"><a href="post.php?post=<?php echo $gallery_id; ?>&action=edit"><?php _e( 'Abort Import', 'sunshine' ); ?></a></p>
		<p align="center" id="return" style="display: none;"><a href="post.php?post=<?php echo $gallery_id; ?>&action=edit"><?php _e( 'Return to Gallery', 'sunshine' ); ?></a></p>
		<ul id="errors"></ul>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var processed = 0;
			var total = <?php echo $count; ?>;
			var percent = 0;
			function sunshine_file_import(item_number) {
				var data = {
					'action': 'sunshine_file_save',
					'gallery_id': <?php echo $gallery_id; ?>,
					'dir': '<?php echo $dir; ?>',
					'item_number': item_number
				};
				$.postq('sunshineimageprocessor', ajaxurl, data, function(response) {
					var obj = $.parseJSON( response );
					processed++;
					if ( processed >= total ) {
						$('#abort').hide();						
						$('#return').show();						
					}
					$('#processed-count').html(processed);
					percent = Math.round( (processed / total) * 100);
					$('#percentage').css('width', percent+'%');
					if ( obj.error ) {
						$( '#errors' ).append( '<li><strong>' + obj.file + '</strong> - ' + obj.error + '</li>' );
					}
				});
			}
			for (i = 1; i <= total; i++) { 
			    sunshine_file_import(i);
			}
		});
		</script>
	</div>
<?php
}

add_action( 'wp_ajax_sunshine_file_save', 'sunshine_ajax_file_save' );
function sunshine_ajax_file_save() {
	global $sunshine;
	
	set_time_limit( 600 );
	
    add_filter( 'upload_dir', 'sunshine_custom_upload_dir' );

	$gallery_id = intval( $_POST['gallery_id'] );
	$item_number = intval( $_POST['item_number'] );
	$dir = $_POST['dir'];
	
	$existing_file_names = array();
	$existing_images = get_children( array( 'post_parent' => $gallery_id, 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
	foreach ( $existing_images as $existing_image ) {
		$existing_file_names[] = get_post_meta( $existing_image->ID, 'sunshine_file_name', true );
	}
	$upload_dir = wp_upload_dir();
	$folder = $upload_dir['basedir'].'/sunshine/'.$dir;
	$images = sunshine_get_images_in_folder( $folder );
	
	$file_path = $images[ $item_number - 1 ];
	$file_name = basename( $file_path );
	
	if ( is_array( $existing_file_names ) && in_array( $file_name, $existing_file_names ) ) {
		echo json_encode( array( 'status' => 'error', 'file' => $file_name, 'error' => __( 'Already uploaded to gallery', 'sunshine' ) ) );
		exit;
	}

	$wp_filetype = wp_check_filetype( $file_name, null );
	extract( $wp_filetype );

	//$file_url = $upload_dir['baseurl'].'/sunshine/'.$dir.'/'.$file;
	//$file_url = apply_filters( 'sunshine_image_save_url', $file_url, $folder );
	
	$new_file_name = wp_unique_filename( $upload_dir['path'], $file_name );

	// copy the file to the uploads dir
	$new_file_path = $upload_dir['path'] . '/' . $new_file_name;
	if ( false === @copy( $file_path, $new_file_path ) )
		return new WP_Error( 'upload_error', sprintf( __( 'The selected file could not be copied to %s.', 'sunshine' ), $upload_dir['path'] ) );

	// Set correct file permissions
	$stat = stat( dirname( $new_file_path ) );
	$perms = $stat['mode'] & 0000666;
	@ chmod( $new_file, $perms );
	$url = $upload_dir['url'] . '/' . $new_file_name;
	
	// Apply upload filters
	$return = apply_filters( 'wp_handle_upload', array( 'file' => $new_file_path, 'url' => $url, 'type' => $type ) );
	$new_file = $return['file'];
	$url = $return['url'];
	$type = $return['type'];

	$title = preg_replace( '!\.[^.]+$!', '', basename( $file_name ) );
	$content = '';

	// use image exif/iptc data for title and caption defaults if possible
	if ( $image_meta = @wp_read_image_metadata( $new_file ) ) {
		if ( '' != trim( $image_meta['title'] ) ) {
			$title = trim( $image_meta['title'] );
		}
		if ( '' != trim( $image_meta['caption'] ) ) {
			$content = trim( $image_meta['caption'] );
		}
	}

	$post_date = current_time( 'mysql' );
	$post_date_gmt = current_time( 'mysql', 1 );

	// Construct the attachment array
	$attachment = array(
		'post_mime_type' => $type,
		'guid' => $url,
		'post_parent' => $gallery_id,
		'post_title' => $title,
		'post_name' => $title,
		'post_content' => $content,
		'post_date' => $post_date,
		'post_date_gmt' => $post_date_gmt
	);

	$new_file = str_replace( wp_normalize_path( $upload_dir['basedir'] ), $upload_dir['basedir'], $new_file );

	// Save the data
	$attachment_id = wp_insert_attachment( $attachment, $new_file, $gallery_id );
	if ( !is_wp_error( $attachment_id ) ) {
		$data = wp_generate_attachment_metadata( $attachment_id, $new_file );
		$attachment_meta_data = wp_update_attachment_metadata( $attachment_id, $data );
		if ( !empty( $image_meta ) ) {
			add_post_meta( $attachment_id, 'created_timestamp', $image_meta['created_timestamp'] );
		}
		add_post_meta( $attachment_id, 'sunshine_file_name', $file_name );

		if ( $attachment_meta_data['image_meta']['title'] )
			wp_update_post( array( 'ID' => $attachment_id, 'post_title' => $attachment_meta_data['image_meta']['title'] ) );

		do_action( 'sunshine_after_image_process', $attachment_id );
		echo json_encode( array( 'status' => 'success', 'file' => $file_name ) );
	}
	
	exit;
	
}
?>