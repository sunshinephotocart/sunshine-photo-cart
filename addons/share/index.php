<?php
add_filter( 'sunshine_action_menu', 'sunshine_share_build_action_menu' );
function sunshine_share_build_action_menu( $menu ) {
	global $post, $wp_query, $sunshine;
	
	$gallery_share = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_share', true );
	$image_share = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_image_share', true );

	if ( ( SunshineFrontend::$current_image && ( $sunshine->options['sharing_image'] || $image_share == 'allow' ) && $image_share != 'disallow' ) || ( SunshineFrontend::$current_gallery && ( $sunshine->options['sharing_gallery'] || $gallery_share == 'allow' ) && $gallery_share != 'disallow' && !SunshineFrontend::$current_image ) ) {
		
		if ( isset( SunshineFrontend::$current_image ) ) {
			$title = get_the_title( SunshineFrontend::$current_image ) . ' - ' . get_the_title( SunshineFrontend::$current_gallery );
			$url = get_permalink( SunshineFrontend::$current_image->ID );
			$img = wp_get_attachment_image_src( SunshineFrontend::$current_image->ID, 'full' );
			$img = $img[0];
		} else {
			$title = get_the_title( SunshineFrontend::$current_gallery->ID );
			$url = get_permalink( SunshineFrontend::$current_gallery->ID );
			$post_thumbnail_id = get_post_thumbnail_id( SunshineFrontend::$current_gallery->ID );
			if ( $post_thumbnail_id ) {
				$img = wp_get_attachment_url( $post_thumbnail_id );
			} elseif ( $images = get_children( array(
						'post_parent' => $image->post_parent,
						'post_type' => 'attachment',
						'numberposts' => 1,
						'post_mime_type' => 'image',
						'orderby' => 'menu_order ID',
						'order' => 'ASC' ) ) ) {
				foreach( $images as $image ) {
					$img = wp_get_attachment_image_src( $image->ID, $size );
					$img = $img[0];
				}
			}
		}

		$menu[65] = array(
			'icon' => 'share-square',
			'name' => __('Share This', 'sunshine'),
			'url' => 'http://www.sharethis.com/share?url=' . urlencode( $url ) . '&title=' . urlencode( $title ) . '&img=' . urlencode( $img ),
			'target' => '_blank'
		);

	}

	return $menu;

}

add_filter( 'sunshine_image_menu', 'sunshine_share_build_image_menu', 999, 2 );
function sunshine_share_build_image_menu( $menu, $image ) {
	global $post, $wp_query, $sunshine;

	$image_share = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_image_share', true );

	if ( ( $sunshine->options['sharing_image'] ) && $image_share != 'disallow' || $image_share == 'allow' ) {
		
		$url = get_permalink( $image->ID );
		$title = get_the_title( $image->ID ) . ' - ' . get_the_title( SunshineFrontend::$current_gallery->ID );
		$img = wp_get_attachment_image_src( $image->ID, 'full' );
		$img = $img[0];
		
		$menu[] = array(
			'icon' => 'share-square',
			'name' => __('Share This', 'sunshine'),
			'url' => 'http://www.sharethis.com/share?url=' . urlencode( $url ) . '&title=' . urlencode( $title ) . '&img=' . urlencode( $img ),
			'target' => '_blank'
		);

	}

	return $menu;

}

add_filter( 'sunshine_lightbox_menu', 'sunshine_share_lightbox_menu', 10, 2 );
function sunshine_share_lightbox_menu( $menu, $image ) {
	global $sunshine;
	
	$image_share = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_image_share', true );

	if ( ( $sunshine->options['sharing_image'] ) && $image_share != 'disallow' || $image_share == 'allow' ) {
		$url = get_permalink( $image->ID );
		$title = get_the_title( $image->ID ) . ' - ' . get_the_title( SunshineFrontend::$current_gallery->ID );
		$img = wp_get_attachment_image_src( $image->ID, 'full' );
		$img = $img[0];

		$menu .= ' <a href="http://www.sharethis.com/share?url=' . urlencode( $url ) . '&title=' . urlencode( $title ) . '&img=' . urlencode( $img ) .'" target="_blank"><i class="fa fa-share-square"></i></a>';
	}
	
	return $menu;
}

add_action( 'sunshine_admin_galleries_meta', 'sunshine_share_gallery_meta', 865 );
function sunshine_share_gallery_meta( $post ) {
	$gallery_share = get_post_meta( $post->ID, 'sunshine_gallery_share', true );
	$image_share = get_post_meta( $post->ID, 'sunshine_image_share', true );
	
	echo '<tr><th><label for="sunshine_gallery_share">'.__( 'Gallery Sharing', 'sunshine' ).'</label></th>';
	echo '<td><select name="sunshine_gallery_share">';
	$share_options = array(
		'default' => 'Default',
		'allow' => 'Allow',
		'disallow' => 'Disallow'
	);
	foreach ( $share_options as $key => $option ) {
		echo '<option value="' . $key . '" ' . selected( $key, $gallery_share, false ) . '>' . $option . '</option>';
	}
	echo '</select>';
	echo '</td></tr>';

	echo '<tr><th><label for="sunshine_image_share">'.__( 'Image Sharing', 'sunshine' ).'</label></th>';
	echo '<td><select name="sunshine_image_share">';
	$share_options = array(
		'default' => 'Default',
		'allow' => 'Allow',
		'disallow' => 'Disallow'
	);
	foreach ( $share_options as $key => $option ) {
		echo '<option value="' . $key . '" ' . selected( $key, $image_share, false ) . '>' . $option . '</option>';
	}
	echo '</select>';
	echo '</td></tr>';

}

add_action( 'save_post', 'sunshine_share_save_gallery_meta', 75 );
function sunshine_share_save_gallery_meta( $post_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) )
		return;
	if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'sunshine-gallery' ) {
		update_post_meta( $post_id, 'sunshine_gallery_share', sanitize_text_field( $_POST['sunshine_gallery_share'] ) );
		update_post_meta( $post_id, 'sunshine_image_share', sanitize_text_field( $_POST['sunshine_image_share'] ) );
	}
}


add_filter( 'sunshine_options_galleries', 'sunshine_share_options' );
function sunshine_share_options( $options ) {
	$options[] = array( 'name' => 'Image Sharing', 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __( 'Sharing on Gallery Pages','sunshine' ),
		'tip' => __( 'Let users share a gallery on social networks like Facebook, Twitter, Pinterest','sunshine' ),
		'id'   => 'sharing_gallery',
		'type' => 'checkbox',
		'options' => array( 1 )
	);
	$options[] = array(
		'name' => __( 'Sharing on Image Detail Pages','sunshine' ),
		'tip' => __( 'Let users share an image on social networks like Facebook, Twitter, Pinterest','sunshine' ),
		'id'   => 'sharing_image',
		'type' => 'checkbox',
		'options' => array( 1 )
	);
	return $options;
}
?>