<?php
class SunshineEmail extends SunshineSingleton {

	function get_template( $name ) {
		if ( file_exists( TEMPLATEPATH.'/sunshine/email/'.$name.'.php' ) )
			$template = TEMPLATEPATH.'/sunshine/email/'.$name.'.php';
		else
			$template = SUNSHINE_PATH.'/email/'.$name.'.php';
		ob_start();
		include( $template );
		$template_content = ob_get_contents();
		ob_end_clean();
		return $template_content;
	}

	function send_email( $template, $to, $subject, $title, $search = array(), $replace = array(), $params=array() ) {
		global $sunshine;
		add_filter( 'wp_mail_content_type',create_function( '', 'return "text/html";' ) );
		$content = '';
		if ( isset( $sunshine->options['email_'.$template] ) && $sunshine->options['email_'.$template] != '' )
			$content = nl2br( $sunshine->options['email_'.$template] );

		// Get logo
		if ( $sunshine->options['template_logo'] )
			$logo = wp_get_attachment_image( $sunshine->options['template_logo'], 'full' );
		else
			$logo = get_bloginfo( 'name' );

		// Core search/replace variables
		$base_search = array( '[title]', '[siteurl]', '[sitename]', '[logo]', '[sunshineurl]', '[signature]', '[message]' );
		$base_replace = array( $title, get_bloginfo( 'url' ), get_bloginfo( 'name' ), $logo, $sunshine->base_url, nl2br( $sunshine->options['email_signature'] ), apply_filters( 'sunshine_email_'.$template, $content, $params ) );

		// Replace subject variables
		$subject = str_replace( array_merge( $base_search, $search ), array_merge( $base_replace, $replace ), $subject );

		// Main content part
		$email = self::get_template( $template );
		$email = str_replace( array_merge( $base_search, $search ), array_merge( $base_replace, $replace ), $email );
		if ( $sunshine->options['from_name'] != '' && $sunshine->options['from_email'] != '' )
			$headers[] = "From: ".$sunshine->options['from_name']." <".$sunshine->options['from_email'].">\r\n";

		// Put into overall template
		$base_search[] = '[content]';
		$base_replace[] = $email;
		$template = self::get_template( 'template' );
		$template = str_replace( array_merge( $base_search, $search ), array_merge( $base_replace, $replace ), $template );

		return wp_mail( $to, $subject, $template, $headers );
	}
}
?>