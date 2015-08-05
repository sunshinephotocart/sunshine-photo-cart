<?php
class SunshineShare extends SunshineSingleton {

	function __construct() {

		add_filter( 'sunshine_action_menu', array( $this, 'build_action_menu' ) );

	}

	function build_action_menu( $menu ) {
		global $post, $wp_query, $sunshine;

		if ( ( SunshineFrontend::$current_image && $sunshine->options['sharing_image'] ) || ( SunshineFrontend::$current_gallery && $sunshine->options['sharing_gallery'] && !SunshineFrontend::$current_image ) ) {
			
			if ( isset( SunshineFrontend::$current_image ) ) {
				$url = get_permalink( SunshineFrontend::$current_image );
			} else {
				$url = get_permalink( SunshineFrontend::$current_gallery );
			}

			$menu[65] = array(
				'name' => '',
				'class' => 'sunshine-share',
				'after_a' => '
					<!-- AddThis Button BEGIN -->
					<div class="addthis_toolbox addthis_default_style sunshine-share-buttons" addthis:url="' . $url .'">
					<a class="addthis_button_compact"><img src="'.SUNSHINE_URL.'/addons/share/share.png" width="13" height="12" alt="" style="vertical-align: top; margin: 2px 4px 0 0;" /> '.__( 'Share This','sunshine' ).'</a>
					</div>
					<script type="text/javascript">var addthis_config = {"data_track_addressbar":false, "services_compact":"facebook,twitter,pinterest,google_plusone_share","services_exclude":"print"};</script>
					<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js"></script>
					<!-- AddThis Button END -->
				'
			);

		}

		return $menu;

	}

}


if ( !is_admin() ) {
	add_action( 'init', 'sunshine_share_init', 10 );
}

function sunshine_share_init() {
	SunshineShare::instance();
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