<?php
/**
 * Adds Main Menu widget.
 */
class Sunshine_Widget_Gallery_Password_Box extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'sunshine_widget_gallery_password_box', // Base ID
			'Sunshine Gallery Password Box', // Name
			array( 'description' => __( 'Display a gallery password box', 'sunshine' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		if ( $instance['visibility'] == 'sunshine_only' && !is_sunshine() )
			return;

		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		sunshine_gallery_password_form();
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['visibility'] = strip_tags( $new_instance['visibility'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		if ( isset( $instance[ 'visibility' ] ) ) {
			$visibility = $instance[ 'visibility' ];
		}
?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (Optional):' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Visibility (Optional):' ); ?></label>
		<br /><input type="checkbox" value="sunshine_only" name="<?php echo $this->get_field_name( 'visibility' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" <?php checked( 'sunshine_only', $visibility ); ?> /> Only visible when on Sunshine pages
		</p>
		<?php
	}

} // class Foo_Widget
add_action( 'widgets_init', create_function( '', 'register_widget( "sunshine_widget_gallery_password_box" );' ) );

class Sunshine_Widget_Main_Menu extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'sunshine_widget_main_menu', // Base ID
			'Sunshine Main Menu', // Name
			array( 'description' => __( 'Display Sunshine\'s main menu links', 'sunshine' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $sunshine;

		if ( $instance['visibility'] == 'sunshine_only' && !is_sunshine() )
			return;

		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		sunshine_main_menu();
		echo $after_widget;

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['visibility'] = strip_tags( $new_instance['visibility'] );
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		if ( isset( $instance[ 'visibility' ] ) ) {
			$visibility = $instance[ 'visibility' ];
		}
?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (Optional):' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Visibility (Optional):' ); ?></label>
		<br /><input type="checkbox" value="sunshine_only" name="<?php echo $this->get_field_name( 'visibility' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" <?php checked( 'sunshine_only', $visibility ); ?> /> Only visible when on Sunshine pages
		</p>
		<?php
	}

}
add_action( 'widgets_init', create_function( '', 'register_widget( "sunshine_widget_main_menu" );' ) );


class Sunshine_Widget_Search extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'sunshine_widget_search', // Base ID
			'Sunshine Search', // Name
			array( 'description' => __( 'Display a search box', 'sunshine' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $sunshine;

		if ( $instance['visibility'] == 'sunshine_only' && !is_sunshine() )
			return;

		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		sunshine_search();
		echo $after_widget;

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['visibility'] = strip_tags( $new_instance['visibility'] );
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		if ( $instance[ 'visibility' ] == 'sunshine_only' ) {
			$visibility = $instance[ 'visibility' ];
		}
?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (Optional):' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Visibility (Optional):' ); ?></label>
		<br /><input type="checkbox" value="sunshine_only" name="<?php echo $this->get_field_name( 'visibility' ); ?>" id="<?php echo $this->get_field_id( 'visibility' ); ?>" <?php checked( 'sunshine_only', $visibility ); ?> /> Only visible when on Sunshine pages
		</p>
		<?php
	}

}
add_action( 'widgets_init', create_function( '', 'register_widget( "sunshine_widget_search" );' ) );


?>