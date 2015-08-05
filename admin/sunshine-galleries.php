<?php
function sunshine_gallery_meta_boxes() {
	global $post;
	$image_count = 0;
	$images = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
	$image_count = count( $images );

	$meta_boxes[10] = array(
		'sunshine_gallery_images',
		__( 'Photos', 'sunshine' ). ' (<span class="sunshine-gallery-image-count">'.$image_count.'</span> images)',
		'sunshine_gallery_upload_meta_box',
		'sunshine-gallery',
		'advanced',
		'high'
	);
	$meta_boxes[20] = array(
		'sunshine_gallery_options',
		__( 'Gallery Options', 'sunshine' ),
		'sunshine_gallery_options_box',
		'sunshine-gallery',
		'advanced',
		'high'
	);
	if ( get_post_meta( $post->ID, 'sunshine_gallery_access', true ) == 'email' ) {
		$meta_boxes[30] = array(
			'sunshine_gallery_emails',
			__( 'Gallery Emails', 'sunshine' ),
			'sunshine_gallery_emails_box',
			'sunshine-gallery',
			'advanced',
			'low'
		);
	}
	$meta_boxes = apply_filters( 'sunshine_gallery_meta_boxes', $meta_boxes );
	ksort( $meta_boxes );
	foreach ( $meta_boxes as $meta_box ) {
		add_meta_box( $meta_box[0], $meta_box[1], $meta_box[2], $meta_box[3], $meta_box[4], $meta_box[5] );
	}
}

add_action( 'admin_enqueue_scripts', 'sunshine_gallery_admin_enqueue_scripts' );
function sunshine_gallery_admin_enqueue_scripts( $page ){
	if( ( $page !== 'post.php' ) || ( get_post_type() !== 'sunshine-gallery' ) )
		return;
	wp_enqueue_script( 'plupload-all' );
	wp_enqueue_script( 'jquery-ui' );
}

function sunshine_remove_add_media(){
	global $post;
	if ( !empty( $post ) && $post->post_type == 'sunshine-gallery' )
		remove_action( 'media_buttons', 'media_buttons' );
}
add_action( 'admin_head', 'sunshine_remove_add_media' );

function sunshine_gallery_upload_meta_box(){
	global $post;
?>
	<div id="sunshine-gallery-images-processing"><div class="status"></div></div>
   	<div id="plupload-upload-ui" class="hide-if-no-js">
     	<div id="drag-drop-area">
       		<div class="drag-drop-inside">
        		<p class="drag-drop-info"><?php _e( 'Drop files here', 'sunshine' ); ?></p>
        		<p><?php _e( 'or', 'sunshine' ); ?></p>
        		<p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php esc_attr_e( 'Select Files', 'sunshine' ); ?>" class="button" /></p>
      		</div>
     	</div>
  	</div>

	<div id="sunshine-gallery-images">
		<ul class="sunshine-clearfix">
			<?php
	$images = get_posts( 'post_type=attachment&post_parent='.$post->ID.'&nopaging=true&orderby=menu_order&order=ASC' );
	foreach ( $images as $image ) {
		$thumbnail = wp_get_attachment_image_src( $image->ID, 'sunshine-thumbnail' );
		echo '<li id="image-'.$image->ID.'"><img src="'.$thumbnail[0].'" alt="" /> <span class="sunshine-image-actions"><a href="media.php?attachment_id='.$image->ID.'&action=edit" class="sunshine-image-edit dashicons dashicons-edit edit" target="_blank"></a> <a href="#" class="sunshine-image-delete dashicons dashicons-no-alt remove" data-image-id="'.$image->ID.'"></a></span></li>';
	}
?>
		</ul>
	</div>
	<script>
	jQuery(document).ready(function($) {
	    var itemList = $('#sunshine-gallery-images ul');

	    itemList.sortable({
	        update: function(event, ui) {
		        $('#sunshine-gallery-images-processing div.status').html('Saving image order...');
	            $('#sunshine-gallery-images-processing').show();
				var image_order = itemList.sortable('toArray').toString();
	            opts = {
	                url: ajaxurl,
	                type: 'POST',
	                async: true,
	                cache: false,
	                dataType: 'json',
	                data:{
	                    action: 'sunshine_gallery_image_sort',
	                    images: image_order
	                },
	                success: function(response) {
	                    $('#sunshine-gallery-images-processing').hide();
	                    return;
	                },
	                error: function(xhr,textStatus,e) {
	                    // alert('There was an error saving the updates');
	                    $('#sunshine-gallery-images-processing').hide();
	                    return;
	                }
	            };
	            $.ajax(opts);
	        }
	    });

		$('a.sunshine-image-delete').click(function(){
			var image_id = $(this).data('image-id');
			var data = {
				'action': 'sunshine_gallery_image_delete',
				'image_id': image_id
			};

			$.post(ajaxurl, data, function(response) {
				if (response == 'SUCCESS')
					$('li#image-'+image_id).fadeOut();
				else
					alert('<?php _e( 'Sorry, the image could not be deleted for some reason','sunshine' ); ?>');
			});
			return false;
		});

	});
	</script>

  	<?php
	$plupload_init = array(
		'runtimes'            => 'html5,silverlight,flash,html4',
		'browse_button'       => 'plupload-browse-button',
		'container'           => 'plupload-upload-ui',
		'drop_element'        => 'drag-drop-area',
		'file_data_name'      => 'sunshine_gallery_image',
		'multiple_queues'     => true,
		'max_file_size'       => wp_max_upload_size().'b',
		'url'                 => admin_url( 'admin-ajax.php' ),
		'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
		'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
		'filters'             => array( array( 'title' => __( 'Allowed Files' ), 'extensions' => 'jpg' ) ),
		'multipart'           => true,
		'urlstream_upload'    => true,

		// additional post data to send to our ajax hook
		'multipart_params'    => array(
			'_ajax_nonce' => wp_create_nonce( 'sunshine_gallery_upload' ),
			'action'      => 'sunshine_gallery_upload',            // the ajax action name
			'gallery_id'      => $post->ID,
		),
	);
?>

  	<script type="text/javascript">

	jQuery(document).ready(function($){

      	// create the uploader and pass the config from above
      	var uploader = new plupload.Uploader(<?php echo json_encode( $plupload_init ); ?>);

      	// checks if browser supports drag and drop upload, makes some css adjustments if necessary
      	uploader.bind('Init', function(up){
        	var uploaddiv = $('#plupload-upload-ui');

	        if(up.features.dragdrop){
	          	uploaddiv.addClass('drag-drop');
	            	$('#drag-drop-area')
	              		.bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
	              		.bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });
	   		} else{
	          	uploaddiv.removeClass('drag-drop');
	          	$('#drag-drop-area').unbind('.wp-uploader');
	        }

		});

		uploader.init();

		uploader.bind('UploadComplete', function(){
			$('#sunshine-gallery-images-processing div.status').html('Images uploaded successfully!');
			$('#sunshine-gallery-images-processing').addClass('success').delay( 2000 ).fadeOut( 400 );
			var elem = document.getElementById('sunshine-gallery-images');
			elem.scrollTop = elem.scrollHeight;
		});

		// a file was added in the queue
		var current_image_count = 0;
		uploader.bind('FilesAdded', function(up, files){
			var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

			var total_images = files.length;
			plupload.each(files, function(file){
				if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){
	  				alert('Your file was too large');
				} else {
					current_image_count = 0;
					$('#sunshine-gallery-images-processing').removeClass('success');
					$('#sunshine-gallery-images-processing div.status').html('Uploading <span class="processed">0</span> of <span class="total-files">'+total_images+'</span> images...<span class="current-file"></span>');
					$('#sunshine-gallery-images-processing').show();
				}
			});

			up.refresh();
			up.start();
		});

    	// a file was uploaded
    	uploader.bind('FileUploaded', function(up, file, response) {
			var result = $.parseJSON(response.response);

			current_image_count++;
 			$('#sunshine-gallery-images-processing span.processed').html(current_image_count);
 			$('#sunshine-gallery-images-processing div.status span.current-file').html(file.name+' uploaded');
			$('#sunshine-gallery-images ul').append(
				$('<li/>', {
					'class': 'uploading-image',
					'id': result.image_id,
					html: $('<img/>', {
 						src: result.thumbnail
					})
				})
			);

			var image_count = $('.sunshine-gallery-image-count').html();
			image_count++;
 			$('.sunshine-gallery-image-count').html(image_count);

    	});

    });
  </script>
  <?php
}

add_action( 'wp_ajax_sunshine_gallery_upload', 'sunshine_gallery_admin_ajax_upload' );
function sunshine_gallery_admin_ajax_upload(){

	check_ajax_referer( 'sunshine_gallery_upload' );
	
	$gallery_id = intval( $_POST['gallery_id'] );

	$last_image = get_posts( 'post_type=attachment&post_parent='.$gallery_id.'&posts_per_page=1&orderby=menu_order&order=DESC' );
	$menu_order = $last_image[0]->menu_order;
	$menu_order++;
	$result['menu_order'] = $menu_order;

	$file = $_FILES['sunshine_gallery_image'];
	$result['file'] = $file;
	$file_upload = wp_handle_upload( $file, array( 'test_form'=>true, 'action' => 'sunshine_gallery_upload' ) );
	$post_parent_id = $gallery_id;

	//Adds file as attachment to WordPress
	$attachment_id = wp_insert_attachment( array(
			'post_mime_type' => $file_upload['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $file['name'] ) ),
			'post_content' => '',
			'post_status' => 'inherit',
			'comment_status' => 'inherit',
			'ping_status' => 'inherit',
			'menu_order' => $menu_order
		), $file_upload['file'], $post_parent_id );

	if ( $attachment_id ) {
		$attachment_meta_data = wp_generate_attachment_metadata( $attachment_id, $file_upload['file'] );
		if ( !empty( $attachment_meta_data['image_meta']['title'] ) ) {
			wp_update_post( array(
				'ID' => $attachment_id, 
				'post_title' => $attachment_meta_data['image_meta']['title']
			) );
		}
		if ( !empty( $attachment_meta_data['image_meta']['created_timestamp'] ) ) {
			add_post_meta( $attachment_id, 'created_timestamp', $attachment_meta_data['image_meta']['created_timestamp'] );
		}
		wp_update_attachment_metadata( $attachment_id, $attachment_meta_data );
		add_post_meta( $attachment_id, 'sunshine_file_name', strtolower( sanitize_file_name( basename( $file['name'] ) ) ) );
		$result['result'] = 'success';
		$result['image_id'] = $attachment_id;
		$thumbnail = wp_get_attachment_image_src( $attachment_id, 'sunshine-thumbnail' );
		$result['thumbnail'] = $thumbnail[0];
		do_action( 'sunshine_after_image_process', $attachment_id, $file );
	} else
		$result['result'] = 'fail';

	echo json_encode( $result );
	exit;
}

add_action( 'wp_ajax_sunshine_gallery_image_sort', 'sunshine_gallery_image_sort' );
function sunshine_gallery_image_sort(){
	$images = explode( ',', $_POST['images'] );
	$counter = 0;
	foreach ( $images as $image_id ) {
		$image_data = explode( '-', $image_id );
		wp_update_post( array( 'ID' => $image_data[1], 'menu_order' => $counter ) );
		$counter++;
	}
	exit;
}

add_action( 'wp_ajax_sunshine_gallery_image_delete', 'sunshine_gallery_image_delete' );
function sunshine_gallery_image_delete() {
	global $current_user;
	$image_id = intval( $_POST['image_id'] );
	if ( isset( $image_id ) ) {
		if ( wp_delete_attachment( $image_id, true ) )
			$result = 'SUCCESS';
		else
			$result = 'FAIL';
	}
	die( $result );
}


function sunshine_gallery_options_box( $post ) {
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'sunshine_noncename' );
	// The actual fields for data entry
	echo '<table class="sunshine-meta">';

	$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
	if ( count( $price_levels ) > 1 ) {
		echo '<tr><th><label for="sunshine_gallery_price_level">'.__( 'Price Level', 'sunshine' ).'</label></th>';
		echo '<td>';
		if ( count( $price_levels ) > 1 ) {
			echo '<select id="sunshine_gallery_price_level" name="sunshine_gallery_price_level">';
			$current_price_level = get_post_meta( $post->ID, 'sunshine_gallery_price_level', true );
			foreach ( $price_levels as $price_level ) {
				echo '<option value="'.$price_level->term_id.'"'.selected( $current_price_level,$price_level->term_id,false ).'>'.$price_level->name.'</option>';
			}
			echo '</select>';
		} else {
			echo '<input type="hidden" value="'.$price_levels[0]->term_id.'" /> '.$price_levels[0]->name;
			echo '<br /><small><a href="edit-tags.php?taxonomy=sunshine-product-price-level&post_type=sunshine-product">Add new price level</a></small>';
		}
		echo '</td></tr>';
	} else
		echo '<input type="hidden" name="sunshine_gallery_price_level" value="'.$price_levels[0]->term_id.'" />';


	echo '<tr><th><label for="sunshine_gallery_access">Access</label></th>';
	echo '<td><select name="sunshine_gallery_access">';
	echo '<option value="">'.__( 'Normal access','sunshine' ).'</option>';
	echo '<option value="account" '.selected( get_post_meta( $post->ID, 'sunshine_gallery_access', true ), 'account', 0 ).'>'.__( 'Registered and logged in','sunshine' ).'</option>';
	echo '<option value="email" '.selected( get_post_meta( $post->ID, 'sunshine_gallery_access', true ), 'email', 0 ).'>'.__( 'Provide email address','sunshine' ).'</option>';
	echo '</select></td></tr>';

	/*
	echo '<tr><th><label for="sunshine_gallery_require_account">Require Account</label></th>';
	echo '<td><label><input type="checkbox" name="sunshine_gallery_require_account" value="1" '.checked(get_post_meta($post->ID, 'sunshine_gallery_require_account', true), 1, 0).' /> Require user have an account and be logged in to view this gallery</label></td></tr>';

	echo '<tr><th><label for="sunshine_gallery_require_email">Require Email</label></th>';
	echo '<td><label><input type="checkbox" name="sunshine_gallery_require_email" value="1" '.checked(get_post_meta($post->ID, 'sunshine_gallery_require_email', true), 1, 0).' /> Require user to enter their email address in order to view gallery</label></td></tr>';
	*/

	echo '<tr><th><label for="sunshine_gallery_disable_products">Disable products</label></th>';
	echo '<td><label><input type="checkbox" name="sunshine_gallery_disable_products" value="1" '.checked( get_post_meta( $post->ID, 'sunshine_gallery_disable_products', true ), 1, 0 ).' /> Users will not be able to purchase any products for this gallery</label></td></tr>';

	echo '<tr><th><label for="sunshine_gallery_image_comments">Allow image comments</label></th>';
	echo '<td><label><input type="checkbox" name="sunshine_gallery_image_comments" value="1" '.checked( get_post_meta( $post->ID, 'sunshine_gallery_image_comments', true ), 1, 0 ).' /> Allow users to make comments on images</label></td></tr>';

	echo '<tr><th><label for="sunshine_gallery_images_directory">FTP Images Folder</label></th>';
	echo '<td><select id="sunshine_gallery_images_directory" name="sunshine_gallery_images_directory"><option value="">Please select folder</option>';
	$upload_dir = wp_upload_dir();
	$selected_dir = get_post_meta( $post->ID, 'sunshine_gallery_images_directory', true );
	$folders = scandir( $upload_dir['basedir'].'/sunshine' );
	$folders = apply_filters( 'sunshine_gallery_images_directory', $folders );
	foreach ( $folders as $item ) {
		if ( $item != '.' && $item != '..' ) {
			$count = sunshine_image_folder_count( $upload_dir['basedir'].'/sunshine/'.$item );
			if ( $selected_dir == $item )
				$selected = ' selected="selected"';
			else
				$selected = '';
			if ( $count > 0 )
				echo '<option value="'.$item.'"'.$selected.'>'.$item.' ('.$count.' images)</option>';
		}
	}
	echo '</select></td></tr>';

	do_action( 'sunshine_admin_galleries_meta', $post );

	echo '</table>';


	// Add password hint field
	$sunshine_gallery_password_hint = get_post_meta( $post->ID, 'sunshine_gallery_password_hint', true );
	// Add clients for private galleries
	$clients = get_users();
	if ( $clients ) {
		$selected_users = get_post_meta( $post->ID, 'sunshine_gallery_private_user' );
		$client_list = '';
		foreach ( $clients as $client ) {
			$checked = ( in_array( $client->ID, $selected_users ) ) ? ' checked="checked"' : '';
			$client_list .= '<label><input type="checkbox" name="sunshine_gallery_private_user[]" value="'.$client->ID.'"'.$checked.' /> '.esc_attr( $client->display_name ) . '</label><br />';
		}
	}
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('#password-span').append('<label for="sunshine_gallery_password_hint">Password Hint:</label><br /><input type="text" name="sunshine_gallery_password_hint" id="sunshine_gallery_password_hint" value="<?php echo addslashes( htmlspecialchars( $sunshine_gallery_password_hint ) ); ?>" style="width: 100%;"/><br />');
			jQuery('label[for="visibility-radio-private"]').append('<div id="sunshine-gallery-private-users">Select users to give access:<br /><div style="height: 75px; overflow-y: scroll; font-size: 11px;"><?php echo $client_list; ?></div></div>');
			jQuery('input[name="visibility"]').change(function() {
				if (jQuery(this).val() == 'private')
					jQuery('#sunshine-gallery-private-users').show();
				else
					jQuery('#sunshine-gallery-private-users').hide();
			});
			<?php if ( get_post_status( $post->ID ) != 'private' ) { ?>
				jQuery('#sunshine-gallery-private-users').hide();
			<?php } ?>
		});
	</script>
<?php
}

function sunshine_gallery_emails_box( $post ) {
	$emails = get_post_meta( $post->ID, 'sunshine_gallery_email' );
	foreach ( $emails as $email ) {
		echo $email.'<br />';
	}
}

add_action( 'post_submitbox_misc_actions', 'sunshine_gallery_publish_box' );
function sunshine_gallery_publish_box() {
	global $post, $wp_locale;
	if ( $post->post_type == 'sunshine-gallery' || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sunshine-gallery' ) ) {
		$end_date = get_post_meta( $post->ID, 'sunshine_gallery_end_date', true );
		if ( $end_date ) {
			$datef = __( 'M j, Y @ G:i' );
			$endstamp = date_i18n( $datef, $end_date );
		} else {
			$endstamp = 'No end date set';
		}
?>
	<div class="misc-pub-section curtime">
		<span id="timestamp">Ends on: <b><?php echo $endstamp; ?></b></span>
		<a href="#edit_endtimestamp" class="edit-endtimestamp hide-if-no-js" tabindex="4" onclick="jQuery('#endtimestampdiv').show(); jQuery('a.edit-endtimestamp').hide();">Edit</a>
		<div id="endtimestampdiv" class="hide-if-js">

			<?php
		$end_day = ( $end_date ) ? date( 'd', $end_date ) : '';
		$end_month = ( $end_date ) ? date( 'm', $end_date ) : '';
		$end_year = ( $end_date ) ? date( 'Y', $end_date ) : '';
		$end_hour = ( $end_date ) ? date( 'H', $end_date ) : '';
		$end_min = ( $end_date ) ? date( 'i', $end_date ) : '';

		$month = '<select name="end_month"><option value=""></option>';
		for ( $i = 1; $i < 13; $i = $i +1 ) {
			$monthnum = zeroise( $i, 2 );
			$month .= "\t\t\t" . '<option value="' . $monthnum . '"';
			if ( $i == $end_month )
				$month .= ' selected="selected"';
			/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
			$month .= '>' . sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
		}
		$month .= '</select>';

		$day = '<input type="text" name="end_day" value="' . $end_day . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
		$year = '<input type="text" name="end_year" value="' . $end_year . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" />';
		$hour = '<input type="text" name="end_hour" value="' . $end_hour . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
		$minute = '<input type="text" name="end_min" value="' . $end_min . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';

		echo '<div class="timestamp-wrap">';
		/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
		printf( __( '%1$s%2$s, %3$s @ %4$s : %5$s' ), $month, $day, $year, $hour, $minute );

		echo '</div>';
?>

			<p>
			<a href="#edit_endtimestamp" class="save-endtimestamp hide-if-no-js button" onclick="jQuery('#endtimestampdiv').hide();  jQuery('a.edit-endtimestamp').show();"><?php _e( 'OK' ); ?></a>
			<a href="#edit_endtimestamp" class="cancel-endtimestamp hide-if-no-js" onclick="jQuery('#endtimestampdiv').hide();  jQuery('a.edit-endtimestamp').show();"><?php _e( 'Cancel' ); ?></a>
			</p>

		</div>
	</div>
<?php
	}
}

add_action( 'save_post', 'sunshine_gallery_save_postdata' );
function sunshine_gallery_save_postdata( $post_id ) {
	global $wpdb;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	if ( !isset( $_POST['sunshine_noncename'] ) || !wp_verify_nonce( $_POST['sunshine_noncename'], plugin_basename( __FILE__ ) ) )
		return;

	if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'sunshine-gallery' ) {

		if ( $_POST['sunshine_gallery_image_comments'] ) {
			$wpdb->query( "UPDATE $wpdb->posts SET comment_status = 'open' WHERE post_parent = $post_id" );
		}

		// Update post meta data
		update_post_meta( $post_id, 'sunshine_gallery_access', sanitize_text_field( $_POST['sunshine_gallery_access'] ) );
		update_post_meta( $post_id, 'sunshine_gallery_image_comments', ( isset( $_POST['sunshine_gallery_image_comments'] ) ) ? sanitize_text_field( $_POST['sunshine_gallery_image_comments'] ) : '' );
		update_post_meta( $post_id, 'sunshine_gallery_images_directory', ( isset( $_POST['sunshine_gallery_images_directory'] ) ) ? sanitize_text_field( $_POST['sunshine_gallery_images_directory'] ) : '' );
		update_post_meta( $post_id, 'sunshine_gallery_password_hint', ( isset( $_POST['sunshine_gallery_password_hint'] ) ) ? sanitize_text_field( $_POST['sunshine_gallery_password_hint'] ) : '' );
		update_post_meta( $post_id, 'sunshine_gallery_disable_products', ( isset( $_POST['sunshine_gallery_disable_products'] ) ) ? sanitize_text_field( $_POST['sunshine_gallery_disable_products'] ) : '' );
		update_post_meta( $post_id, 'sunshine_gallery_price_level', ( isset( $_POST['sunshine_gallery_price_level'] ) ) ? sanitize_text_field( $_POST['sunshine_gallery_price_level'] ) : '' );

		// Update users for private post
		delete_post_meta( $post_id, 'sunshine_gallery_private_user' ); // Remove all previous users, then reassign
		if ( $_POST['post_status'] == 'private' ) {
			if ( !empty( $_POST['sunshine_gallery_private_user'] ) ) {
				foreach ( $_POST['sunshine_gallery_private_user'] as $user ) {
					add_post_meta( $post_id, 'sunshine_gallery_private_user', intval( $user ) );
				}
			}
		} else
			add_post_meta( $post_id, 'sunshine_gallery_private_user', '0' ); // Add 0 for every gallery to make OR query possible on home page

		// Update end date
		if ( $_POST['end_month'] ) {
			$end_hour = ( $_POST['end_hour'] ) ? $_POST['end_hour'] : 0;
			$end_min = ( $_POST['end_min'] ) ? $_POST['end_min'] : 0;
			$end_month = ( $_POST['end_month'] ) ? $_POST['end_month'] : 0;
			$end_day = ( $_POST['end_day'] ) ? $_POST['end_day'] : 0;
			$end_year = ( $_POST['end_year'] ) ? $_POST['end_year'] : 0;
			$end_date = mktime( $end_hour, $end_min, 0, $end_month, $end_day, $end_year );
		} else
			$end_date = '';
		update_post_meta( $post_id, 'sunshine_gallery_end_date', sanitize_text_field( $end_date ) );

	}
}

add_action( 'admin_notices', 'sunshine_gallery_notice_more_images' );
function sunshine_gallery_notice_more_images(){
	if ( isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'sunshine-gallery' ) {
		$post_id = intval( $_GET['post'] );
		$selected_dir = get_post_meta( $post_id, 'sunshine_gallery_images_directory', true );
		// See if there are new images in selected directory
		if ( $selected_dir ) {
			$upload_dir = wp_upload_dir();
			$attachments = get_posts( array(
					'post_type' => 'attachment',
					'post_parent' => $post_id,
					'posts_per_page' => -1
				) );
			$file_count = count( $attachments );
			$file_count_in_dir = sunshine_image_folder_count( $upload_dir['basedir'].'/sunshine/'.$selected_dir );
			if ( $file_count_in_dir > $file_count ) {
				$unprocessed = $file_count_in_dir - $file_count;
				echo '<div class="updated">
			       <p>There '.SunshineFrontend::pluralize( $unprocessed, 'is', 'are', 0 ).' <strong>'.$unprocessed.' unprocessed '.SunshineFrontend::pluralize( $unprocessed, 'photo', 'photos', 0 ).'</strong> in your selected folder. Please click the "Update" button to process these images and add them to your gallery.</p>
			    </div>';
			}
		}
	}
}

add_filter( 'redirect_post_location', 'sunshine_redirect_to_gallery_image_processor' );
function sunshine_redirect_to_gallery_image_processor( $location ) {
	global $post;

	$images_to_process = false;
	// See if there are new images in selected directory
	if ( !empty( $_POST['sunshine_gallery_images_directory'] ) ) {
		$upload_dir = wp_upload_dir();
		$attachments = get_posts( array(
				'post_type' => 'attachment',
				'post_parent' => $post->ID,
				'posts_per_page' => -1
			) );
		$file_count = count( $attachments );
		$file_count_in_dir = sunshine_image_folder_count( $upload_dir['basedir'].'/sunshine/'.sanitize_text_field( $_POST['sunshine_gallery_images_directory'] ) );
		if ( $file_count_in_dir > $file_count )
			$images_to_process = true;
	}

	if ( !empty( $_POST['sunshine_gallery_images_directory'] ) && $images_to_process ) {
		$location = get_admin_url().'admin.php?page=sunshine_image_processor&gallery='.$post->ID;
	}

	return $location;

}

function touch_time_end( $edit = 1, $for_post = 1, $tab_index = 0, $multi = 0 ) {
	global $wp_locale, $post, $comment;

	if ( $for_post )
		$edit = ! ( in_array( $post->post_status, array( 'draft', 'pending' ) ) && ( !$post->post_date_gmt || '0000-00-00 00:00:00' == $post->post_date_gmt ) );

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )
		$tab_index_attribute = " tabindex=\"$tab_index\"";

	// echo '<label for="timestamp" style="display: block;"><input type="checkbox" class="checkbox" name="edit_date" value="1" id="timestamp"'.$tab_index_attribute.' /> '.__( 'Edit timestamp' ).'</label><br />';

	$time_adj = current_time( 'timestamp' );
	$post_date = ( $for_post ) ? $post->post_date : $comment->comment_date;
	$jj = ( $edit ) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
	$mm = ( $edit ) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
	$aa = ( $edit ) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
	$hh = ( $edit ) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
	$mn = ( $edit ) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
	$ss = ( $edit ) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );

	$cur_jj = gmdate( 'd', $time_adj );
	$cur_mm = gmdate( 'm', $time_adj );
	$cur_aa = gmdate( 'Y', $time_adj );
	$cur_hh = gmdate( 'H', $time_adj );
	$cur_mn = gmdate( 'i', $time_adj );

	$month = "<select " . ( $multi ? '' : 'id="mm" ' ) . "name=\"end_mm\"$tab_index_attribute>\n";
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise( $i, 2 );
		$month .= "\t\t\t" . '<option value="' . $monthnum . '"';
		if ( $i == $mm )
			$month .= ' selected="selected"';
		/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
		$month .= '>' . sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
	}
	$month .= '</select>';

	$day = '<input type="text" ' . ( $multi ? '' : 'id="end_jj" ' ) . 'name="end_jj" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
	$year = '<input type="text" ' . ( $multi ? '' : 'id="end_aa" ' ) . 'name="end_aa" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" />';
	$hour = '<input type="text" ' . ( $multi ? '' : 'id="end_hh" ' ) . 'name="end_hh" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
	$minute = '<input type="text" ' . ( $multi ? '' : 'id="end_mn" ' ) . 'name="end_mn" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';

	echo '<div class="timestamp-wrap">';
	/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
	printf( __( '%1$s%2$s, %3$s @ %4$s : %5$s' ), $month, $day, $year, $hour, $minute );

	echo '</div><input type="hidden" id="end_ss" name="end_ss" value="' . $ss . '" />';

	if ( $multi ) return;

	echo "\n\n";
	foreach ( array( 'mm', 'jj', 'aa', 'hh', 'mn' ) as $timeunit ) {
		echo '<input type="hidden" id="end_hidden_' . $timeunit . '" name="end_hidden_' . $timeunit . '" value="' . $$timeunit . '" />' . "\n";
		$cur_timeunit = 'cur_' . $timeunit;
		echo '<input type="hidden" id="end_'. $cur_timeunit . '" name="end_'. $cur_timeunit . '" value="' . $$cur_timeunit . '" />' . "\n";
	}
?>

<p>
<a href="#edit_endtimestamp" class="save-endtimestamp hide-if-no-js button" onclick="jQuery('#endtimestampdiv').hide();  jQuery('a.edit-endtimestamp').show();"><?php _e( 'OK' ); ?></a>
<a href="#edit_endtimestamp" class="cancel-endtimestamp hide-if-no-js" onclick="jQuery('#endtimestampdiv').hide();  jQuery('a.edit-endtimestamp').show();"><?php _e( 'Cancel' ); ?></a>
</p>
<?php
}

function sunshine_galleries_product_columns( $columns ) {
	$columns['expires'] = 'Expires';
	$columns['images'] = 'Images';
	//$columns['profit'] = 'Profit';
	return $columns;
}
add_filter( 'manage_edit-sunshine-gallery_columns', 'sunshine_galleries_product_columns', 99 );

function sunshine_galleries_columns_content( $column, $post_id ) {
	global $post;
	switch( $column ) {
	case 'images':
		$total_images = get_children( array( 'post_parent'=>$post_id ) );
		$image_count = count( $total_images );
		echo $image_count;
		break;
	case 'expires':
		$end_date = get_post_meta( $post_id, 'sunshine_gallery_end_date', true );
		if ( $end_date ) {
			echo date( 'M j, Y', $end_date );
			if ( $end_date < current_time( 'timestamp' ) ) // Don't let people see expired galleries
				echo ' - <em>Expired</em>';
		} else
			echo '&mdash;';
		break;
	default:
		break;
	}
}
add_action( 'manage_sunshine-gallery_posts_custom_column', 'sunshine_galleries_columns_content', 10, 2 );

add_action( 'before_delete_post', 'sunshine_galleries_delete_attachments' );
function sunshine_galleries_delete_attachments( $post_id ){
	global $sunshine;
	if ( $sunshine->options['delete_images'] && get_post_type( $post_id ) == 'sunshine-gallery' ) {
		$attachments = get_posts( array( 'post_type' => 'attachment', 'post_parent' => $post_id, 'nopaging' => 1 ) );
		foreach ( $attachments as $attachment ) {
			wp_delete_attachment( $attachment->ID, true );
		}
	}
	// Delete the FTP folder, if it exists
	if ( $sunshine->options['delete_images_folder'] ) {
		$dir = get_post_meta( $post_id, 'sunshine_gallery_images_directory', true );
		if ( $dir ) {
			$upload_dir = wp_upload_dir();
			$dir = $upload_dir['basedir'].'/sunshine/'.$dir;
			if ( is_dir( $dir ) ) {
				$it = new RecursiveDirectoryIterator( $dir );
				$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );
				foreach( $files as $file ) {
					if ( $file->getFilename() === '.' || $file->getFilename() === '..' ) {
						continue;
					}
					if ( $file->isDir() ){
						rmdir( $file->getRealPath() );
					} else {
						unlink( $file->getRealPath() );
					}
				}
				rmdir( $dir );
			}
		}
	}
};

add_filter( 'post_updated_messages', 'sunshine_gallery_post_updated_messages' );
function sunshine_gallery_post_updated_messages( $messages ) {
	global $post;
	if ( $post->post_type == 'sunshine-gallery' ) {
		$messages["post"][1] = sprintf( __( '<strong>Gallery updated</strong>, <a href="%s">view gallery</a>', 'sunshine' ), get_permalink( $post->ID ) );
		$messages["post"][6] = sprintf( __( '<strong>Gallery created</strong>, <a href="%s">view gallery</a>', 'sunshine' ), get_permalink( $post->ID ) );
	}
	return $messages;
}

add_filter( 'sanitize_file_name', 'sunshine_sanitize_file_name' );
function sunshine_sanitize_file_name( $filename ) {
	$special_chars = array( "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}" );
	$filename = str_replace( $special_chars, '', $filename );
	$filename = preg_replace( '/[\s-]+/', '-', $filename );
	$filename = trim( $filename, '.-_' );
	return $filename;
}

function page_attributes_metabox_add_parents( $dropdown_args, $post = NULL ) {
	if ( $post->post_type == 'sunshine-gallery' )
		$dropdown_args['post_status'] = array( 'publish', 'draft', 'pending', 'future', 'private' );
	return $dropdown_args;
}

add_filter( 'page_attributes_dropdown_pages_args', 'page_attributes_metabox_add_parents', 10, 2 );
add_filter( 'quick_edit_dropdown_pages_args', 'page_attributes_metabox_add_parents', 10 );


?>