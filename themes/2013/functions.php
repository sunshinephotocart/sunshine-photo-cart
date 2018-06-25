<?php
add_filter('sunshine_options_templates', 'sunshine_2013_options');
function sunshine_2013_options($options) {

	$options[] = array( 'name' => __('Functionality', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('Gallery Password Box', 'sunshine'),
		'id'   => '2013_gallery_password_box',
		'type' => 'checkbox',
		'tip' => __('Enabling this option will have the gallery password box appear in the left sidebar.','sunshine'),
		'options' => array(1)
	);
	$options[] = array(
		'name' => __('Search Box', 'sunshine'),
		'id'   => '2013_search_box',
		'type' => 'checkbox',
		'tip' => __('Enabling this option will have the search box appear in the left sidebar.','sunshine'),
		'options' => array(1)
	);

	$options[] = array( 'name' => __('Main Area', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('Background Color', 'sunshine'),
		'id'   => '2013_main_background_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Header Font', 'sunshine'),
		'id'   => '2013_header_font',
		'type' => 'text',
		'desc' => 'Copy name from <a href="http://www.google.com/webfonts" target="_blank">Google Web Fonts</a> and paste in here'
	);
	$options[] = array(
		'name' => __('Header Text Color', 'sunshine'),
		'id'   => '2013_header_text_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Body Text Font', 'sunshine'),
		'id'   => '2013_main_font',
		'type' => 'text',
		'desc' => 'Copy name from <a href="http://www.google.com/webfonts" target="_blank">Google Web Fonts</a> and paste in here'
	);
	$options[] = array(
		'name' => __('Body Text Color', 'sunshine'),
		'id'   => '2013_main_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Link Color', 'sunshine'),
		'id'   => '2013_link_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Secondary Color', 'sunshine'),
		'id'   => '2013_secondary_color',
		'type' => 'color',
	);

	$options[] = array( 'name' => __('Left Sidebar', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('Background Color', 'sunshine'),
		'id'   => '2013_sidebar_background_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Font', 'sunshine'),
		'id'   => '2013_menu_font',
		'type' => 'text',
		'desc' => 'Copy name from <a href="http://www.google.com/webfonts" target="_blank">Google Web Fonts</a> and paste in here'
	);
	$options[] = array(
		'name' => __('Link Color', 'sunshine'),
		'id'   => '2013_menu_link_color',
		'type' => 'color',
	);

	$options[] = array( 'name' => __('Buttons', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('Background Color', 'sunshine'),
		'id'   => '2013_button_color',
		'type' => 'color',
	);
	$options[] = array(
		'name' => __('Text Color', 'sunshine'),
		'id'   => '2013_button_text_color',
		'type' => 'color',
	);

	$options[] = array( 'name' => __('Custom Styles', 'sunshine'), 'type' => 'title', 'desc' => '' );
	$options[] = array(
		'name' => __('CSS', 'sunshine'),
		'id'   => '2013_css',
		'type' => 'textarea',
		'css'  => 'height: 300px; width: 600px;'
	);
	return $options;
}

add_action('wp_head', 'sunshine_2013_head');
function sunshine_2013_head() {
	global $sunshine;
	$css = '';
	if (!empty($sunshine->options['2013_link_color']))
		$css .= '#sunshine-main a { color: '.$sunshine->options['2013_link_color'].'; }';
	if (!empty($sunshine->options['2013_main_font'])) {
		echo '<link href="https://fonts.googleapis.com/css?family='.urlencode($sunshine->options['2013_main_font']).'" rel="stylesheet" type="text/css">';
		$css .= '#sunshine-main p, #sunshine-main div, #sunshine-main li, #sunshine-main h1, #sunshine-main h2, #sunshine-main h3, #sunshine-main h4, #sunshine-main td, #sunshine-main th, #sunshine-main input, #sunshine-main select, #sunshine-main textarea { font-family: "'.$sunshine->options['2013_main_font'].'"; }';
	}
	if (!empty($sunshine->options['2013_main_color']))
		$css .= '#sunshine-main p, #sunshine-main div, #sunshine-main li, #sunshine-main h1, #sunshine-main h2, #sunshine-main h3, #sunshine-main h4, #sunshine-main td, #sunshine-main th, #sunshine-main input, #sunshine-main select, #sunshine-main textarea { color: '.$sunshine->options['2013_main_color'].'; }';
	if (!empty($sunshine->options['2013_header_font'])) {
		echo '<link href="https://fonts.googleapis.com/css?family='.urlencode($sunshine->options['2013_header_font']).'" rel="stylesheet" type="text/css">';
		$css .= '#sunshine-main h1 { font-family: "'.$sunshine->options['2013_header_font'].'"; }';
	}
	if (!empty($sunshine->options['2013_header_text_color']))
		$css .= '#sunshine-main h1 { color: '.$sunshine->options['2013_header_text_color'].'; }';
	if (!empty($sunshine->options['2013_secondary_color'])) {
		$css .= '#sunshine .sunshine-action-menu li a, #sunshine-main .sunshine-action-menu li, #sunshine-applied-discounts li span, #sunshine-applied-discounts li span a, #sunshine-checkout .sunshine-payment-method-description, #sunshine-order-comments .comment-meta, #sunshine-order-comments .comment-meta a, #sunshine-main h1 span a, #sunshine-content, #sunshine-content p, .sunshine-gallery-password-hint, #sunshine .sunshine-action-menu li a { color: '.$sunshine->options['2013_secondary_color'].'; }';
		$css .= '#sunshine-next-prev a { background-color: '.$sunshine->options['2013_secondary_color'].'; }';
	}


	if (!empty($sunshine->options['2013_sidebar_background_color']))
		$css .= '#sunshine-header { background-color: '.$sunshine->options['2013_sidebar_background_color'].'; }';
	if (!empty($sunshine->options['2013_menu_font'])) {
		echo '<link href="https://fonts.googleapis.com/css?family='.urlencode($sunshine->options['2013_menu_font']).'" rel="stylesheet" type="text/css">';
		$css .= '#sunshine .sunshine-main-menu li { font-family: "'.$sunshine->options['2013_menu_font'].'"; letter-spacing: 0; text-transform: none; }';
	}
	if (!empty($sunshine->options['2013_menu_link_color']))
		$css .= '#sunshine .sunshine-main-menu a { color: '.$sunshine->options['2013_menu_link_color'].'; }';
	if (!empty($sunshine->options['2013_menu_hover_color']))
		$css .= '#sunshine .sunshine-main-menu a:hover { color: '.$sunshine->options['2013_menu_hover_color'].'; }';
	if (!empty($sunshine->options['2013_main_background_color']))
		$css .= 'body, #sunshine-main { background-color: '.$sunshine->options['2013_main_background_color'].'; }';

	if (!empty($sunshine->options['2013_button_color'])) {
		$css .= '#sunshine .sunshine-button, #sunshine #sunshine-submit { background-color: '.$sunshine->options['2013_button_color'].'; }';
	}
	if (!empty($sunshine->options['2013_button_text_color'])) {
		$css .= '#sunshine input.sunshine-button, #sunshine input#sunshine-submit { color: '.$sunshine->options['2013_button_text_color'].'; }';
	}

	echo '<!-- CUSTOM CSS FOR SUNSHINE -->';
	echo '<style type="text/css">';
	echo $css;
	if (isset($sunshine->options['2013_css']))
		echo $sunshine->options['2013_css'];
	echo '</style>';
}

function sunshine_2013_login() {
	global $sunshine;
	if (is_sunshine()) {
		$css = '';
		if (!empty($sunshine->options['2013_sidebar_background_color']))
			$css .= ' body { background-color: '.$sunshine->options['2013_sidebar_background_color'].' !important; }';
		else
			$css .= ' body { background-color: #21282e !important; }';

		if (!empty($sunshine->options['2013_menu_link_color']))
			$css .= ' .login #nav a, .login #backtoblog a, .login #nav a:hover, .login #backtoblog a:hover { color: '.$sunshine->options['2013_menu_link_color'].' !important; text-shadow: none !important; }';
		else
			$css .= ' .login #nav a, .login #backtoblog a, .login #nav a:hover, .login #backtoblog a:hover { color: #86888a !important; text-shadow: none !important; }';

		echo '<!-- CUSTOM CSS FOR SUNSHINE -->';
		echo '<style type="text/css">';
		echo $css;
		echo '</style>';
	}
}
add_action('login_head', 'sunshine_2013_login');


?>
