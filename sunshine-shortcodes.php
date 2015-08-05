<?php
/**
 * Display variables nicely formatted
 *
 * @since 1.0
 * @return html
 */
add_shortcode( 'sunshine-gallery-password', 'sunshine_gallery_password_shortcode' );
function sunshine_gallery_password_shortcode() {
	return sunshine_gallery_password_form( false );
}

add_shortcode( 'sunshine-menu', 'sunshine_menu_shortcode' );
function sunshine_menu_shortcode() {
	return sunshine_main_menu();
}

?>