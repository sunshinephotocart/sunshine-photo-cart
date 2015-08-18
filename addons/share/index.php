<?php
add_filter( 'sunshine_action_menu', 'sunshine_share_build_action_menu' );
function sunshine_share_build_action_menu( $menu ) {
	global $post, $wp_query, $sunshine;

	if ( ( SunshineFrontend::$current_image && $sunshine->options['sharing_image'] ) || ( SunshineFrontend::$current_gallery && $sunshine->options['sharing_gallery'] && !SunshineFrontend::$current_image ) ) {
		
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

	if ( $sunshine->options['sharing_image'] ) {
		
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
	
	if ( !$sunshine->options['sharing_image'] ) {
		return $menu;
	}

	$url = get_permalink( $image->ID );
	$title = get_the_title( $image->ID ) . ' - ' . get_the_title( SunshineFrontend::$current_gallery->ID );
	$img = wp_get_attachment_image_src( $image->ID, 'full' );
	$img = $img[0];

	$menu .= ' <a href="http://www.sharethis.com/share?url=' . urlencode( $url ) . '&title=' . urlencode( $title ) . '&img=' . urlencode( $img ) .'" target="_blank"><i class="fa fa-share-square"></i></a>';
	
	return $menu;
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