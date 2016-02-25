<?php
class SunshineUser extends SunshineSingleton {

	function __construct() {

		/*
		if (is_admin()) {
			add_action('show_user_profile', array($this, 'show_user_meta'));
			add_action('edit_user_profile', array($this, 'show_user_meta'));
		}
		*/

	}

	public static function show_user_meta( $user ) {
		$all_meta_for_user = get_user_meta( $user->ID );
		dump_var( $all_meta_for_user );
	}

	public static function add_user_meta( $option, $value, $single=true ) {
		global $current_user;
		return self::add_user_meta_by_id( $current_user->ID, $option, $value, $single );
	}

	public static function add_user_meta_by_id( $user_id, $option, $value, $single=true ) {
		return add_user_meta( $user_id, 'sunshine_'.$option, $value, $single );
	}

	public static function get_user_meta( $option, $single=true ) {
		global $current_user;
		return self::get_user_meta_by_id( $current_user->ID, $option, $single );
	}

	public static function get_user_meta_by_id( $user_id, $option, $single=true ) {
		global $current_user;
		if ( !$user_id )
			$user_id = $current_user->ID;
		if ( !$user_id ) {
			$value = SunshineSession::instance()->$option;
		} else {
			if ( $option == 'email' ) {
				$value = $current_user->user_email;
			} else {
				$value = get_user_meta( $user_id, 'sunshine_'.$option, $single );
			}				
		}
		return $value;
	}

	public static function update_user_meta( $option, $value, $prev_value='' ) {
		global $current_user;
		return self::update_user_meta_by_id( $current_user->ID, $option, $value, $prev_value );
	}

	public static function update_user_meta_by_id( $user_id, $option, $value, $prev_value = '' ) {
		return update_user_meta( $user_id, 'sunshine_'.$option, $value, $prev_value );
	}

	public static function delete_user_meta( $option, $value='' ) {
		global $current_user;
		return self::delete_user_meta_by_id( $current_user->ID, $option, $value );
	}

	public static function delete_user_meta_by_id( $user_id, $option, $value='' ) {
		return delete_user_meta( $user_id, 'sunshine_'.$option, $value );
	}

}
?>