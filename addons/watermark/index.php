<?php
/*
Plugin Name: Sunshine Photo Cart - Watermarks
Plugin URI: http://www.sunshinephotocart.com/download/watermarks
Description: Add-on for Sunshine Photo Cart - Dynamically add watermarks to images uploaded to Sunshine
Version: 1.0
Author: Sunshine Photo Cart
Author URI: http://www.sunshinephotocart.com
*/

add_filter( 'sunshine_options_galleries', 'sunshine_watermark_options', 45 );
function sunshine_watermark_options( $options ) {
	$options[] = array( 'name' => __( 'Watermark','sunshine' ), 'type' => 'title', 'desc' => __( 'Add image to all files uploaded to Sunshine (except digital download files)','sunshine' ) );
	$attachments = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image/png', 'post_parent' => 0, 'posts_per_page' => -1 ) );
	$media[0] = __( 'No image', 'sunshine' );
	foreach ( $attachments as $attachment ) {
		$media[$attachment->ID] = $attachment->post_title;
	}
	$options[] = array(
		'name' => __( 'Image', 'sunshine' ),
		'id'   => 'watermark_image',
		'type' => 'select',
		'options' => $media,
		'select2' => true,
		'desc' => __( '<strong>Image must be a transparent PNG.</strong> Upload a file to your <a href="upload.php">Media gallery</a>, then select it here','sunshine' )
	);
	$options[] = array(
		'name' => __( 'Position', 'sunshine' ),
		'id'   => 'watermark_position',
		'type' => 'select',
		'options' => array(
			'topleft' => __( 'Top Left','sunshine' ),
			'topright' => __( 'Top Right','sunshine' ),
			'bottomleft' => __( 'Bottom Left','sunshine' ),
			'bottomright' => __( 'Bottom Right','sunshine' ),
			'center' => __( 'Center','sunshine' ),
			'repeat' => __( 'Repeat','sunshine' )
		)
	);
	$options[] = array(
		'name' => __( 'Margin from edge', 'sunshine' ),
		'desc' => __( 'Number only, not used for "Center" position', 'sunshine' ),
		'id'   => 'watermark_margin',
		'type' => 'text',
		'css' => 'width: 50px;'
	);
	$options[] = array(
		'name' => __( 'Watermark Thumbnails', 'sunshine' ),
		'id'   => 'watermark_thumbnail',
		'type' => 'checkbox',
		'options' => array( 1 )
	);


	return $options;
}

function sunshine_watermark_image( $attachment_id, $metadata = array(), $passed_image_size = '' ) {
	global $sunshine;
	$attachment = get_post( $attachment_id );
	if ( get_post_type( $attachment->post_parent ) == 'sunshine-gallery' && $sunshine->options['watermark_image'] ) {

		$watermark_image = get_attached_file( $sunshine->options['watermark_image'] );
		$watermark_file_type = wp_check_filetype( $watermark_image );

		if ( file_exists( $watermark_image ) && $watermark_file_type['ext'] == 'png' ) {
			$image = get_attached_file( $attachment_id, 'full' );
			$image_size = $passed_image_size;
			if ( !$image_size ) {
				$image_size = apply_filters( 'sunshine_image_size', 'full' );
			}
			if ( empty( $metadata['sizes'][ $image_size ]['file'] ) ) {
				$metadata = wp_get_attachment_metadata( $attachment_id );
			}
			if ( $image_size != 'full' && !empty( $metadata['sizes'][$image_size]['file'] ) ) {
				$image_basename = basename( $image );
				$image_path = str_replace( $image_basename, '', $image );
				$image = $image_path . $metadata['sizes'][$image_size]['file'];
			}

			if ( !file_exists( $image ) ) {
				return;
			}

			$watermark = imagecreatefrompng( $watermark_image );
			$new_image = imagecreatefromjpeg( $image );

			$margin = ( $sunshine->options['watermark_margin'] != '' ) ? $sunshine->options['watermark_margin'] : 30;
			$watermark_width = imagesx( $watermark );
			$watermark_height = imagesy( $watermark );
			$new_image_width = imagesx( $new_image );
			$new_image_height = imagesy( $new_image );

			if ( $sunshine->options['watermark_position'] == 'topleft' ) {
				$x_pos = $margin;
				$y_pos = $margin;
			} elseif ( $sunshine->options['watermark_position'] == 'topright' ) {
				$x_pos = $new_image_width - $watermark_width - $margin;
				$y_pos = $margin;
			} elseif ( $sunshine->options['watermark_position'] == 'bottomleft' ) {
				$x_pos = $margin;
				$y_pos = $new_image_height - $watermark_height - $margin;
			} elseif ( $sunshine->options['watermark_position'] == 'bottomright' ) {
				$x_pos = $new_image_width - $watermark_width - $margin;
				$y_pos = $new_image_height - $watermark_height - $margin;
			} else {
				$x_pos = ( $new_image_width/2 ) - ( $watermark_width/2 );
				$y_pos = ( $new_image_height/2 ) - ( $watermark_height/2 );
			}

			if ( $sunshine->options['watermark_position'] == 'repeat' ) {
				imagesettile( $new_image, $watermark );
				imagefilledrectangle( $new_image, 0, 0, $new_image_width, $new_image_height, IMG_COLOR_TILED );
			} else {
				imagecopy( $new_image, $watermark, $x_pos, $y_pos, 0, 0, $watermark_width, $watermark_height );
			}

			// Output and free memory
			imagejpeg( $new_image, $image, 100 );
			imagedestroy( $new_image );

			// Watermark the thumbnail if needed
			if ( $sunshine->options['watermark_thumbnail'] && !$passed_image_size ) {
				$image_editor = wp_get_image_editor( $image );
				$image_editor->resize( $sunshine->options['thumbnail_width'], $sunshine->options['thumbnail_height'], $sunshine->options['thumbnail_crop'] );
				$image_editor->save( $image_path . $metadata['sizes']['sunshine-thumbnail']['file'] );
			}
		}
	}
}

// Add the watermark
add_filter( 'wp_generate_attachment_metadata', 'sunshine_watermark_media_upload', 999, 2 );
function sunshine_watermark_media_upload( $metadata, $attachment_id ) {
	sunshine_watermark_image( $attachment_id, $metadata );
	return $metadata;
}

?>
