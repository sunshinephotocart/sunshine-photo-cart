<?php
add_filter('sunshine_options_templates', 'sunshine_theme_options');
function sunshine_theme_options($options) {
	$options[] = array(
		'name' => __( 'Auto-include main menu', 'sunshine' ),
		'id'   => 'main_menu',
		'type' => 'checkbox',
		'desc' => __( 'Automatically have the Sunshine Main Menu appear above the Sunshine content','sunshine' ),
		'options' => array( 1 )
	);
	$options[] = array( 'name' => __('Custom Code', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('Disable Sunshine CSS', 'sunshine'),
		'id'   => 'disable_sunshine_css',
		'desc' => __( 'Checking this will prevent the default sunshine CSS file from being loaded', 'sunshine' ),
		'type' => 'checkbox',
	);
	$options[] = array(
		'name' => __('Custom CSS', 'sunshine'),
		'id'   => 'theme_css',
		'type' => 'textarea',
		'css'  => 'height: 300px; width: 600px;'
	);
	$options[] = array(
		'name' => __('Before Sunshine', 'sunshine'),
		'id'   => 'theme_post_header',
		'type' => 'wysiwyg',
		'tip'  => 'This HTML code will get added immediately before Sunshine code is output in the page template',
		'css'  => 'height: 300px; width: 600px;'
	);
	$options[] = array(
		'name' => __('After Sunshine', 'sunshine'),
		'id'   => 'theme_pre_footer',
		'type' => 'wysiwyg',
		'tip'  => 'This HTML code will get added immediately after Sunshine code is output in the page template',
		'css'  => 'height: 300px; width: 600px;'
	);
	return $options;
}

add_action('wp_head', 'sunshine_template_head');
function sunshine_template_head() {
	global $sunshine;
	if (isset($sunshine->options['theme_css'])) {
		echo '<!-- CUSTOM CSS FOR SUNSHINE -->';
		echo '<style type="text/css">';
		echo $sunshine->options['theme_css'];
		echo '</style>';
	}
}

add_action('sunshine_before_content', 'sunshine_template_before_content', 999);
function sunshine_template_before_content( ) {
	global $sunshine;
	if ( isset( $sunshine->options['main_menu'] ) && $sunshine->options['main_menu'] ) {
		echo do_shortcode( '[sunshine-menu]' );
	}
	if ( isset( $sunshine->options['theme_post_header'] ) && $sunshine->options['theme_post_header'] ) {
		echo do_shortcode( $sunshine->options['theme_post_header'] );
	}
}

add_action('sunshine_after_content', 'sunshine_template_after_content', 999);
function sunshine_template_after_content( ) {
	global $sunshine;
	if ( isset( $sunshine->options['theme_pre_footer'] ) && $sunshine->options['theme_pre_footer'] ) {
		echo do_shortcode( $sunshine->options['theme_pre_footer'] );
	}
}
?>
