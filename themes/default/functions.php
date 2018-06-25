<?php
add_filter('sunshine_options_templates', 'sunshine_template_options');
function sunshine_template_options($options) {
	$options[] = array( 'name' => __('Functionality', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('Gallery Password Box', 'sunshine'),
		'id'   => 'template_gallery_password_box',
		'type' => 'checkbox',
		'tip' => __('Enabling this option will have the gallery password box appear in the left sidebar.','sunshine'),
		'options' => array(1)
	);
	$options[] = array(
		'name' => __('Search Box', 'sunshine'),
		'id'   => 'template_search_box',
		'type' => 'checkbox',
		'tip' => __('Enabling this option will have the search box appear in the left sidebar.','sunshine'),
		'options' => array(1)
	);

	$options[] = array( 'name' => __('Design Elements', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('Background Color', 'sunshine'),
		'id'   => 'template_background_color',
		'type' => 'text',
		'desc' => '
			<script>
			jQuery(document).ready(function($){
			    $("#template_background_color").wpColorPicker();
			});
			</script>
		'
	);
	$attachments = get_posts( array( 'post_type' => 'attachment', 'post_parent' => 0, 'posts_per_page' => 250 ) );
	$media[0] = __('No image', 'sunshine');
	foreach ($attachments as $attachment) {
		$media[$attachment->ID] = $attachment->post_title;
	}
	$options[] = array(
		'name' => __('Background Image', 'sunshine'),
		'id'   => 'template_background_image',
		'type' => 'select',
		'options' => $media,
		'select2' => true,
		'desc' => __('Upload a file to your <a href="upload.php">Media gallery</a>, then select it here','sunshine')
	);
	$options[] = array(
		'name' => __('Background Repeat', 'sunshine'),
		'id'   => 'template_background_repeat',
		'type' => 'select',
		'options' => array('repeat' => __('Horizontally and Vertically','sunshine'), 'repeat-x' => __('Horizontally','sunshine'), 'repeat-y' => __('Vertically','sunshine'), 'no-repeat' => __('No repeat','sunshine'))
	);
	$options[] = array(
		'name' => __('Link Color', 'sunshine'),
		'id'   => 'template_link_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Button Color', 'sunshine'),
		'id'   => 'template_button_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Button Text Color', 'sunshine'),
		'id'   => 'template_button_text_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Header Background Color', 'sunshine'),
		'id'   => 'template_header_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Header Font Color', 'sunshine'),
		'id'   => 'template_header_font_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Header Font', 'sunshine'),
		'id'   => 'template_header_font',
		'type' => 'text',
		'desc' => 'Copy name from <a href="http://www.google.com/webfonts" target="_blank">Google Web Fonts</a> and paste in here'
	);
	$options[] = array(
		'name' => __('Secondary Header Font', 'sunshine'),
		'id'   => 'template_header2_font',
		'type' => 'text',
		'desc' => 'Copy name from <a href="http://www.google.com/webfonts" target="_blank">Google Web Fonts</a> and paste in here'
	);
	$options[] = array(
		'name' => __('Menu Font', 'sunshine'),
		'id'   => 'template_menu_font',
		'type' => 'text',
		'desc' => 'Copy name from <a href="http://www.google.com/webfonts" target="_blank">Google Web Fonts</a> and paste in here'
	);
	$options[] = array(
		'name' => __('Main Body Copy Font', 'sunshine'),
		'id'   => 'template_main_font',
		'type' => 'text',
		'desc' => 'Copy name from <a href="http://www.google.com/webfonts" target="_blank">Google Web Fonts</a> and paste in here'
	);
	$options[] = array( 'name' => __('Custom Styles', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('CSS', 'sunshine'),
		'id'   => 'template_css',
		'type' => 'textarea',
		'css'  => 'height: 300px; width: 600px;'
	);
	return $options;
}

add_action('wp_head', 'sunshine_template_head');
function sunshine_template_head() {
	global $sunshine;
	$css = '';
	if (!empty($sunshine->options['template_header_color']))
		$css .= '#sunshine-main h1, .sunshine-main-menu .sunshine-count { background: '.$sunshine->options['template_header_color'].'; }';
	if (!empty($sunshine->options['template_main_font'])) {
		echo '<link href="https://fonts.googleapis.com/css?family='.urlencode($sunshine->options['template_main_font']).'" rel="stylesheet" type="text/css">';
		$css .= 'p, div, li, h1, h2, h3, h4, td, th, input, select, textarea { font-family: "'.$sunshine->options['template_main_font'].'"; }';
	}
	if (!empty($sunshine->options['template_link_color'])) {
		$css .= '.sunshine a { color: '.$sunshine->options['template_link_color'].'; }';
	}
	if (!empty($sunshine->options['template_button_color'])) {
		$css .= '.sunshine .sunshine-button, .sunshine #sunshine-submit { background-color: '.$sunshine->options['template_button_color'].'; }';
	}
	if (!empty($sunshine->options['template_button_text_color'])) {
		$css .= '.sunshine .sunshine-button { color: '.$sunshine->options['template_button_text_color'].'; }';
	}
	if (!empty($sunshine->options['template_header_font'])) {
		echo '<link href="https://fonts.googleapis.com/css?family='.urlencode($sunshine->options['template_header_font']).'" rel="stylesheet" type="text/css">';
		$css .= '#sunshine-main h1 { font-family: "'.$sunshine->options['template_header_font'].'"; }';
	}
	if (!empty($sunshine->options['template_header_font_color'])) {
		$css .= '#sunshine-main h1, .sunshine-main-menu .sunshine-count { color: '.$sunshine->options['template_header_font_color'].'; }';
	}
	if (!empty($sunshine->options['template_header2_font'])) {
		echo '<link href="https://fonts.googleapis.com/css?family='.urlencode($sunshine->options['template_header2_font']).'" rel="stylesheet" type="text/css">';
		$css .= 'h2 { font-family: "'.$sunshine->options['template_header2_font'].'"; }';
	}
	if (!empty($sunshine->options['template_menu_font'])) {
		echo '<link href="https://fonts.googleapis.com/css?family='.urlencode($sunshine->options['template_menu_font']).'" rel="stylesheet" type="text/css">';
		$css .= '.sunshine-main-menu li { font-family: "'.$sunshine->options['template_menu_font'].'"; letter-spacing: 0; text-transform: none; }';
	}
	if (!empty($sunshine->options['template_background_color'])) {
		$css .= 'body, html { background-color: '.$sunshine->options['template_background_color'].'}';
		$css .= 'body { background-image: none; }';
	}
	if (!empty($sunshine->options['template_background_image'])) {
		$css .= 'body { background-image: url("'.wp_get_attachment_url($sunshine->options['template_background_image']).'"); }';
		$css .= 'body { background-position: center top; }';
	}
	if (!empty($sunshine->options['template_background_repeat']))
		$css .= 'body { background-repeat: '.$sunshine->options['template_background_repeat'].'}';
	echo '<!-- CUSTOM CSS FOR SUNSHINE -->';
	echo '<style type="text/css">';
	echo $css;
	if (!empty($sunshine->options['template_css']))
		echo $sunshine->options['template_css'];
	echo '</style>';
}
?>
