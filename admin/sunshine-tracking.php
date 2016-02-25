<?php
add_action( 'admin_init', 'sunshine_send_tracking_data' );
function sunshine_send_tracking_data() {

	if( !is_admin() || get_option( 'sunshine_tracking' ) != 'yes' ) return; // Only run in admin

	$transient_key = 'sunshine_tracking_cache';
	$data          = get_transient( $transient_key );

	// bail if transient is set and valid
	if ( $data !== false ) {
		return;
	}

	// Make sure to only send tracking data once a week
	set_transient( $transient_key, 1, WEEK_IN_SECONDS );

	// Start of Metrics
	global $wpdb;

	$hash = get_option( 'sunshine_tracking_hash', false );

	if ( ! $hash || empty( $hash ) ) {
		// create and store hash
		$hash = md5( site_url() );
		update_option( 'sunshine_tracking_hash', $hash );
	}

	$post_counts = array();
	$post_types = array( 'sunshine-gallery','sunshine-order' );
	if ( is_array( $post_types ) && $post_types !== array() ) {
		foreach ( $post_types as $post_type ) {
			$post_counts[$post_type] = wp_count_posts( $post_type );
		}
	}
	unset( $post_types );

	$comments_count = wp_count_comments();

	$theme_data     = wp_get_theme();
	$theme          = array(
		'name'       => $theme_data->display( 'Name', false, false ),
		'theme_uri'  => $theme_data->display( 'ThemeURI', false, false ),
		'version'    => $theme_data->display( 'Version', false, false ),
		'author'     => $theme_data->display( 'Author', false, false ),
		'author_uri' => $theme_data->display( 'AuthorURI', false, false ),
	);
	$theme_template = $theme_data->get_template();
	if ( $theme_template !== '' && $theme_data->parent() ) {
		$theme['template'] = array(
			'version'    => $theme_data->parent()->display( 'Version', false, false ),
			'name'       => $theme_data->parent()->display( 'Name', false, false ),
			'theme_uri'  => $theme_data->parent()->display( 'ThemeURI', false, false ),
			'author'     => $theme_data->parent()->display( 'Author', false, false ),
			'author_uri' => $theme_data->parent()->display( 'AuthorURI', false, false ),
		);
	} else {
		$theme['template'] = '';
	}
	unset( $theme_template );


	$plugins       = array();
	$active_plugin = get_option( 'active_plugins' );
	foreach ( $active_plugin as $plugin_path ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );

		$slug             = str_replace( '/' . basename( $plugin_path ), '', $plugin_path );
		if ( strpos( $slug, 'sunshine' ) === false ) {
			$plugins[$slug] = array(
				'version'    => $plugin_info['Version'],
				'name'       => $plugin_info['Name'],
				'plugin_uri' => $plugin_info['PluginURI'],
				'author'     => $plugin_info['AuthorName'],
				'author_uri' => $plugin_info['AuthorURI'],
			);
		}
	}
	unset( $active_plugins, $plugin_path );

	$data = array(
		'hash'      => $hash,
		'url'    => get_bloginfo( 'url' ),
		'name'    => get_bloginfo( 'name' ),
		'wp_version'   => get_bloginfo( 'version' ),
		'sunshine_version' => SUNSHINE_VERSION,
		'lang'      => get_locale(),
		'php_version'  => phpversion(),
		'theme'     => $theme,
		'plugins'   => $plugins,
		'post_counts' => $post_counts
	);

	$args = array(
		'body'      => $data,
		'blocking'  => false,
		'sslverify' => false,
	);

	wp_remote_post( 'http://www.sunshinephotocart.com/?tracking=1', $args );

}


$sunshine_tracking = get_option( 'sunshine_tracking' );
if ( !$sunshine_tracking ) {
	add_action( 'admin_enqueue_scripts', 'sunshine_tracking_enqueue_scripts' );
	add_action( 'admin_print_footer_scripts', 'sunshine_tracking_add_pointer_scripts' );
}

function sunshine_tracking_enqueue_scripts() {
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'wp-pointer' );
}

function sunshine_tracking_add_pointer_scripts() {
	$content = '<h3>Help improve Sunshine Photo Cart</h3>';
	$content .= '<p>Please help us improve the plugin by allowing us to gather usage stats so we know which configurations, plugins and themes to test with.';
	$content .= '<p style="display: inline;"><a id="sunshine-tracking-approve" class="button-primary">Sure thing!</a> <a id="sunshine-tracking-decline" class="button-secondary">No thanks</a></p>';
?>

	<script>
	jQuery(document).ready( function($) {
	    $('#wpadminbar').pointer({
			pointer_id: 'sunshine_tracking',
	        content: '<?php echo $content; ?>',
	        position: {
                edge: 'top',
                align: 'center'
            },
	        close: function() { }
	    }).pointer('open');
		$('#sunshine-tracking-approve').click(function(){
			sunshine_tracking_save_answer('yes');
		});
		$('#sunshine-tracking-decline').click(function(){
			sunshine_tracking_save_answer('no');
		});
		function sunshine_tracking_save_answer(answer) {
			var sunshine_tracking_data = {
				action        	: 'sunshine_tracking_save_answer',
				allow_tracking	: answer,
				_wpnonce        : '<?php echo wp_create_nonce( 'sunshine_tracking_save_answer' ); ?>'
			};
			jQuery.post(ajaxurl, sunshine_tracking_data, function () {
				jQuery('#wp-pointer-0').remove();
			});
		}
	});
	</script>

<?php
}

function sunshine_tracking_save_answer() {
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'sunshine_tracking_save_answer' ) ) {
		die();
	}
	update_option( 'sunshine_tracking', sanitize_text_field( $_POST['allow_tracking'] ) );
	die;
}
add_action( 'wp_ajax_sunshine_tracking_save_answer', 'sunshine_tracking_save_answer' );
