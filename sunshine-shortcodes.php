<?php
add_shortcode( 'sunshine-gallery-password', 'sunshine_gallery_password_shortcode' );
add_shortcode( 'sunshine_gallery_password', 'sunshine_gallery_password_shortcode' );
function sunshine_gallery_password_shortcode() {
	return sunshine_gallery_password_form( false );
}

add_shortcode( 'sunshine-menu', 'sunshine_menu_shortcode' );
add_shortcode( 'sunshine_menu', 'sunshine_menu_shortcode' );
function sunshine_menu_shortcode() {
	return sunshine_main_menu();
}
?>