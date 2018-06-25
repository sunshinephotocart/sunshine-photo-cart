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

		$menu[5] = array(
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

	if ( empty( SunshineFrontend::$current_gallery ) ) {
		return;
	}

	$gallery_share = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_share', true );
	$image_share = get_post_meta( SunshineFrontend::$current_gallery->ID, 'sunshine_image_share', true );

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

		$url = urlencode( trailingslashit( $url ) );

		$services = maybe_unserialize( $sunshine->options['sharing_services'] );
		if ( !is_array( $services ) ) {
			return;
		}
		$html = '<ul class="sunshine-share-links sunshine-action-share-links">';
		$image_html = '<ul class="sunshine-share-links sunshine-image-share-links">';
		foreach ( $services as $service => $active ) {
			if ( $active == 0 ) continue;
			switch ( $service ) {
				case 'facebook':
					$service_url = 'http://www.facebook.com/sharer.php?u=' . $url;
					$image_service_url = "http://www.facebook.com/sharer.php?u='+url+'";
					$service_name = 'Facebook';
					break;
				case 'twitter':
					$service_url = 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title;
					$image_service_url = "https://twitter.com/intent/tweet?url='+url+'&text='+title+'";
					$service_name = 'Twitter';
					break;
				case 'pinterest':
					$service_url = 'http://pinterest.com/pin/create/button/?url=' . $url . '&media=' . $img . '&description=' . $title;
					$image_service_url = "http://pinterest.com/pin/create/button/?url='+url+'&media='+img+'&title='+title+'";
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
			var img = $( this ).children( 'a' ).data( 'img' );
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
	$services = maybe_unserialize( $sunshine->options['sharing_services'] );

	if ( is_array( $services ) && ( $sunshine->options['sharing_image'] || $image_share == 'allow' ) && $image_share != 'disallow' ) {

		$url = trailingslashit( get_permalink( $image->ID ) );
		//$title = get_the_title( $image->ID ) . ' - ' . get_the_title( $image->post_parent );
		$size = apply_filters( 'sunshine_image_size', 'full' );
		$img = wp_get_attachment_image_src( $image->ID, $size );
		$img = $img[0];

		$menu[] = array(
			'icon' => 'share-square',
			//'name' => __('Share This', 'sunshine'),
			//'url' => 'http://www.sharethis.com/share?url=' . urlencode( $url ) . '&title=' . urlencode( $title ) . '&img=' . urlencode( $img ),
			'url' => '#',
			//'target' => '_blank',
			'class' => 'sunshine-image-share',
			'attr' => array(
				'data-url' => urlencode( esc_attr( $url ) ),
				//'data-title' => urlencode( esc_attr( $title ) ),
				'data-img' => urlencode( esc_attr( $img ) )
			)
		);

	}

	return $menu;

}

add_filter( 'sunshine_lightbox_menu', 'sunshine_share_lightbox_menu', 10, 2 );
function sunshine_share_lightbox_menu( $menu, $image ) {
	global $sunshine;

	$image_share = get_post_meta( $image->post_parent, 'sunshine_image_share', true );
	$services = maybe_unserialize( $sunshine->options['sharing_services'] );

	if ( is_array( $services ) && ( $sunshine->options['sharing_image'] || $image_share == 'allow' ) && $image_share != 'disallow' ) {

		$size = apply_filters( 'sunshine_image_size', 'full' );
		$title = get_the_title( $image ) . ' - ' . get_the_title( $image->post_parent );
		$url = trailingslashit( get_permalink( $image->ID ) );
		$img = wp_get_attachment_image_src( $image->ID, $size );
		$img = $img[0];

		$menu .= ' <div class="sunshine-lightbox-share"><i class="fa fa-share-square"></i> <ul class="sunshine-lightbox-share-links">';
		foreach ( $services as $service => $active ) {
			if ( $active == 0 ) continue;
			switch ( $service ) {
				case 'facebook':
					$service_url = 'http://www.facebook.com/sharer.php?u=' . $url;
					$image_service_url = "http://www.facebook.com/sharer.php?u='+url+'";
					$service_name = 'Facebook';
					break;
				case 'twitter':
					$service_url = 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title;
					$image_service_url = "https://twitter.com/intent/tweet?url='+url+'&text='+title+'";
					$service_name = 'Twitter';
					break;
				case 'pinterest':
					$service_url = 'http://pinterest.com/pin/create/button/?url=' . $url . '&media=' . $img . '&description=' . $title;
					$image_service_url = "http://pinterest.com/pin/create/button/?url='+url+'&media='+img+'&title='+title+'";
					$service_name = 'Pinterest';
					break;
				case 'google':
					$service_url = 'http://plus.google.com/share?url=' . $url;
					$image_service_url = "http://plus.google.com/share?url='+url+'";
					$service_name = 'Google+';
					break;
			}
			$menu .= '<li><a href="' . $service_url . '" target="_blank">' . $service_name . '</a></li>';
		}
		$menu .= '</ul></div>';
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
		'default' => __( 'Default', 'sunshine' ),
		'allow' => __( 'Allow', 'sunshine' ),
		'disallow' => __( 'Disallow', 'sunshine' )
	);
	foreach ( $share_options as $key => $option ) {
		echo '<option value="' . $key . '" ' . selected( $key, $gallery_share, false ) . '>' . $option . '</option>';
	}
	echo '</select>';
	echo '</td></tr>';

	echo '<tr><th><label for="sunshine_image_share">'.__( 'Image Sharing', 'sunshine' ).'</label></th>';
	echo '<td><select name="sunshine_image_share">';
	$share_options = array(
		'default' => __( 'Default', 'sunshine' ),
		'allow' => __( 'Allow', 'sunshine' ),
		'disallow' => __( 'Disallow', 'sunshine' )
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
