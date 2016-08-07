<?php
add_filter( 'sunshine_action_menu', 'sunshine_share_build_action_menu' );
function sunshine_share_build_action_menu( $menu ) {
	global $post, $wp_query, $sunshine;

	if ( empty( SunshineFrontend::$current_gallery ) ) {
		return $menu;
	}

	$gallery_share = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_share', true );
	$image_share = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_image_share', true );

	if ( ( SunshineFrontend::$current_image && ( $sunshine->options['sharing_image'] || $image_share == 'allow' ) && $image_share != 'disallow' ) || ( SunshineFrontend::$current_gallery && ( $sunshine->options['sharing_gallery'] || $gallery_share == 'allow' ) && $gallery_share != 'disallow' && !SunshineFrontend::$current_image ) ) {

		if ( isset( SunshineFrontend::$current_image ) ) {
			$share_text = __( 'Share Image', 'sunshine' );
		} else {
			$share_text = __( 'Share Gallery', 'sunshine' );
		}

		$menu[65] = array(
			'icon' => 'share-square',
			'name' => $share_text,
			//'url' => 'http://www.sharethis.com/share?url=' . urlencode( $url ) . '&title=' . urlencode( $title ) . '&img=' . urlencode( $img ),
			'url' => '#',
			'target' => '_blank',
			'class' => 'sunshine-action-share'
		);

	}

	return $menu;

}

add_action( 'wp_footer', 'sunshine_share_javascript' );
function sunshine_share_javascript() {
	global $sunshine;
	if ( ( SunshineFrontend::$current_image && ( $sunshine->options['sharing_image'] || $image_share == 'allow' ) && $image_share != 'disallow' ) || ( SunshineFrontend::$current_gallery && ( $sunshine->options['sharing_gallery'] || $gallery_share == 'allow' ) && $gallery_share != 'disallow' && !SunshineFrontend::$current_image ) ) {

		$size = apply_filters( 'sunshine_image_size', 'full' );
		if ( isset( SunshineFrontend::$current_image ) ) {
			$title = get_the_title( SunshineFrontend::$current_image ) . ' - ' . get_the_title( SunshineFrontend::$current_gallery );
			$url = get_permalink( SunshineFrontend::$current_image->ID );
			$img = wp_get_attachment_image_src( SunshineFrontend::$current_image->ID, $size );
			$img = $img[0];
		} else {
			$title = get_the_title( SunshineFrontend::$current_gallery->ID );
			$url = get_permalink( SunshineFrontend::$current_gallery->ID );
			$post_thumbnail_id = get_post_thumbnail_id( SunshineFrontend::$current_gallery->ID );
			if ( $post_thumbnail_id ) {
				$img = wp_get_attachment_image_src( $post_thumbnail_id, $size );
				$img = $img[0];
			} elseif ( $images = get_children( array(
						'post_parent' => SunshineFrontend::$current_gallery->ID,
						'post_type' => 'attachment',
						'numberposts' => 55,
						'post_mime_type' => 'image',
						'orderby' => 'menu_order ID',
						'order' => 'ASC' ) ) ) {
				foreach( $images as $image ) {
					$img = wp_get_attachment_image_src( $image->ID, $size );
					$img = $img[0];
				}
			}
		}

		$services = maybe_unserialize( $sunshine->options['sharing_services'] );
		$html = '<ul class="sunshine-share-links sunshine-action-share-links">';
		$image_html = '<ul class="sunshine-share-links sunshine-image-share-links">';
		foreach ( $services as $service => $active ) {
			if ( $active == 0 ) continue;
			switch ( $service ) {
				case 'facebook':
					$service_url = 'http://www.facebook.com/sharer.php?u=' . $url . '&title=' . $title;
					$image_service_url = "http://www.facebook.com/sharer.php?u='+url+'&title='+title+'";
					$service_name = 'Facebook';
					break;
				case 'twitter':
					$service_url = 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title;
					$image_service_url = "https://twitter.com/intent/tweet?url='+url+'&text='+title+'";
					$service_name = 'Twitter';
					break;
				case 'pinterest':
					$service_url = 'http://www.pinterest.com/pin/find/?url=' . $url;
					$image_service_url = "http://www.pinterest.com/pin/find/?url='+url+'";
					$service_name = 'Pinterest';
					break;
				case 'google':
					$service_url = 'http://plus.google.com/share?url=' . $url;
					$image_service_url = "http://plus.google.com/share?url='+url+'";
					$service_name = 'Google+';
					break;
			}
			$html .= '<li><a href="' . $service_url . '" target="_blank">' . $service_name . '</a></li>';
			$image_html .= '<li><a href="' . $image_service_url . '" target="_blank">' . $service_name . '</a></li>';
		}
		$html .= '</ul>';
		$image_html .= '</ul>';
?>
	<script>
	// SHARING JAVASCRIPT
	jQuery( document ).ready( function($){
		$( '.sunshine-action-share' ).hover(function(){
			$( this ).append( '<?php echo $html; ?>' );
		}, function() {
			$( '.sunshine-share-links' ).remove();
		});
		$( '.sunshine-image-share' ).hover(function(){
			var url = $( this ).children( 'a' ).data( 'url' );
			var title = $( this ).children( 'a' ).data( 'title' );
			$( this ).append( '<?php echo $image_html; ?>' );
		}, function() {
			$( '.sunshine-share-links' ).remove();
		});
	});
	</script>
<?php
	}
}

add_filter( 'sunshine_image_menu', 'sunshine_share_build_image_menu', 999, 2 );
function sunshine_share_build_image_menu( $menu, $image ) {
	global $post, $wp_query, $sunshine;

	$image_share = get_post_meta( $image->post_parent, 'sunshine_image_share', true );

	if ( ( $sunshine->options['sharing_image'] ) && $image_share != 'disallow' || $image_share == 'allow' ) {

		$url = get_permalink( $image->ID );
		$title = get_the_title( $image->ID ) . ' - ' . get_the_title( $image->post_parent );
		$size = apply_filters( 'sunshine_image_size', 'full' );
		$img = wp_get_attachment_image_src( $image->ID, $size );
		$img = $img[0];

		$menu[] = array(
			'icon' => 'share-square',
			'name' => __('Share This', 'sunshine'),
			//'url' => 'http://www.sharethis.com/share?url=' . urlencode( $url ) . '&title=' . urlencode( $title ) . '&img=' . urlencode( $img ),
			'url' => '#',
			//'target' => '_blank',
			'class' => 'sunshine-image-share',
			'attr' => array(
				'data-url' => urlencode( $url ),
				'data-title' => urlencode( $title ),
				'data-img' => urlencode( $img )
			)
		);

	}

	return $menu;

}

add_filter( 'sunshine_lightbox_menu', 'sunshine_share_lightbox_menu', 10, 2 );
function sunshine_share_lightbox_menu( $menu, $image ) {
	global $sunshine;

	$image_share = get_post_meta( $image->post_parent, 'sunshine_image_share', true );

	if ( ( $sunshine->options['sharing_image'] ) && $image_share != 'disallow' || $image_share == 'allow' ) {
		$url = get_permalink( $image->ID );
		$title = get_the_title( $image->ID ) . ' - ' . get_the_title( $image->post_parent );
		$size = apply_filters( 'sunshine_image_size', 'full' );
		$img = wp_get_attachment_image_src( $image->ID, $size );
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
	$options[] = array(
		'name' => __( 'Services','sunshine' ),
		'tip' => __( 'Choose which services to make available','sunshine' ),
		'id'   => 'sharing_services',
		'type' => 'checkbox',
		'multiple' => true,
		'options' => array(
			'facebook' => 'Facebook',
			'twitter' => 'Twitter',
			'pinterest' => 'Pinterest',
			'google' => 'Google+'
		)
	);

	return $options;
}

add_action( 'wp_head', 'sunshine_share_head' );
function sunshine_share_head() {
	global $sunshine, $post;
	if ( ( isset( SunshineFrontend::$current_gallery ) && $sunshine->options['sharing_gallery'] ) || ( isset( SunshineFrontend::$current_image ) && $sunshine->options['sharing_image'] ) ) {
		echo '<meta name="pinterest" content="nopin" />';
		return;
	}
	$gallery_share = get_post_meta( $post->ID, 'sunshine_gallery_share', true );
	if ( isset( SunshineFrontend::$current_gallery ) && $gallery_share == 'disallow' ) {
		echo '<meta name="pinterest" content="nopin" />';
	}
	$image_share = get_post_meta( $post->ID, 'sunshine_image_share', true );
	if ( isset( SunshineFrontend::$current_image ) && $image_share == 'disallow' ) {
		echo '<meta name="pinterest" content="nopin" />';
	}
}
?>
