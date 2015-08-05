<?php
class SunshineFrontend extends SunshineSingleton {
	
	public static $current_gallery;
	public static $current_image;
	public static $current_order;
	private static $output;
	
	function __construct() {
		global $sunshine, $post;
		add_action('wp', array($this,'get_view_values'));
		add_action('wp', array($this,'require_login'));
		add_action('wp', array($this, 'frontend_cssjs'));
		add_action('wp', array($this, 'redirect_to_endpoint_urls'));
		add_action('wp', array($this, 'admin_bar'));
		add_action('wp_print_styles', array($this, 'clear_queue'));
		add_filter('body_class',array($this, 'body_class'));
		add_filter('the_password_form', array($this, 'gallery_password_form'));
		add_filter('the_title', array($this, 'the_title'), 10, 2);
		add_filter('wp_title', array($this, 'wp_title'));
		add_filter('pre_comment_approved', array($this, 'order_comment_auto_approve') , 99, 2 );
		//add_filter('comment_post_redirect', array($this, 'order_comment_post_redirect'), 10, 2);
		add_filter('sunshine_main_menu', array($this, 'build_main_menu'), 10, 1);
		add_filter('sunshine_action_menu', array($this, 'build_action_menu'), 10, 1);
		add_filter('sunshine_image_menu', array($this, 'build_image_menu'), 10, 2);
		add_filter('login_url', array($this, 'login_url'));
		add_filter('lostpassword_url', array($this, 'login_url'));
		add_filter('site_url', array($this, 'site_url'), 99, 3);
		add_action('login_message', array($this, 'login_message')); 
		add_action('wp_head', array($this, 'head'), 999);
		add_action('template_redirect', array($this, 'can_view_gallery'));
		//add_action('template_redirect', array($this, 'can_view_image'));
		add_action('template_redirect', array($this, 'can_view_order'));
		add_action('template_redirect', array($this, 'can_use_cart'));
		//add_filter('comments_open', array($this, 'hide_image_comments'), 10 , 2);
		add_filter('nav_menu_css_class', array($this, 'add_class_to_wp_nav_menu'), 10, 2);
				
		if ($sunshine->options['theme'] == 'theme') 
			add_filter('the_content', array($this, 'sunshine_content'), 999);
		else
			add_action('template_include', array($this, 'sunshine_theme'), 999);
		
	}
	
	function sunshine_content($content) {
		global $post, $wp_query, $sunshine;
		
		if (!is_sunshine())
			return $content;
		
		if (isset($_GET['sunshine_search'])) {
			$content = self::get_template('search-results');
		} elseif (isset(self::$current_image)) {
			if ( post_password_required(self::$current_gallery->ID) )
				$content = get_the_password_form();
			else
				$content = self::get_template('image');
		} elseif (isset(self::$current_gallery)) {
			if ( post_password_required(self::$current_gallery->ID) )
				$content = get_the_password_form();
			elseif (sunshine_gallery_requires_email(self::$current_gallery->ID)) {
				$content = sunshine_gallery_email_form();
			} else {
				$content = self::get_template('gallery');
			}
		} elseif (isset(self::$current_order)) {
			$content = self::get_template('order');
		} elseif (is_page($sunshine->options['page'])) {
			$content = $content.self::get_template('home');
		} elseif (is_page($sunshine->options['page_cart'])) {
			$content = $content.self::get_template('cart');
		} elseif (is_page($sunshine->options['page_checkout'])) {
			$content = $content.self::get_template('checkout');
		} elseif (is_page($sunshine->options['page_account'])) {
			$content = $content.self::get_template('account');
		} 
					
		return apply_filters('sunshine_content', $content);
	}
	
	function sunshine_theme($template) {
		global $sunshine;
		$theme_path = SUNSHINE_PATH.'themes/'.$sunshine->options['theme'].'/';
		if (isset($_GET['sunshine_search'])) {
			if (file_exists(get_stylesheet_directory().'/sunshine/search-results.php'))
				$template = get_stylesheet_directory().'/sunshine/search-results.php';
			else
				$template = $theme_path.'search-results.php';
		} elseif (self::$current_image) {
			if (file_exists(get_stylesheet_directory().'/sunshine/image.php'))
				$template = get_stylesheet_directory().'/sunshine/image.php';
			else
				$template = $theme_path.'image.php';
		} elseif (self::$current_gallery) {
			if (file_exists(get_stylesheet_directory().'/sunshine/gallery.php'))
				$template = get_stylesheet_directory().'/sunshine/gallery.php';
			else
				$template = $theme_path.'gallery.php';
		} elseif (self::$current_order) {
			if (file_exists(get_stylesheet_directory().'/sunshine/order.php'))
				$template = get_stylesheet_directory().'/sunshine/order.php';
			else
				$template = $theme_path.'order.php';
		} elseif (is_page($sunshine->options['page_cart'])) {
			if (file_exists(get_stylesheet_directory().'/sunshine/cart.php'))
				$template = get_stylesheet_directory().'/sunshine/cart.php';
			else
				$template = $theme_path.'cart.php';
		} elseif (is_page($sunshine->options['page_checkout'])) {
			if (file_exists(get_stylesheet_directory().'/sunshine/checkout.php'))
				$template = get_stylesheet_directory().'/sunshine/checkout.php';
			else
				$template = $theme_path.'checkout.php';
		} elseif (is_page($sunshine->options['page_account'])) {
			if (file_exists(get_stylesheet_directory().'/sunshine/account.php'))
				$template = get_stylesheet_directory().'/sunshine/account.php';
			else
				$template = $theme_path.'account.php';
		} elseif (is_page($sunshine->options['page'])) {
			if (file_exists(get_stylesheet_directory().'/sunshine/home.php'))
				$template = get_stylesheet_directory().'/sunshine/home.php';
			else
				$template = $theme_path.'home.php';
		}
		$template = apply_filters('sunshine_template', $template);
		return $template;
	}
	
	/* 
		Remove all theme CSS/JS enqueued files if we are using a Sunshine theme
	*/
	function clear_queue() {
		global $wp_styles, $wp_scripts, $sunshine;
		//dump_var($wp_scripts);
		if (is_sunshine() && $sunshine->options['theme'] != 'theme') {
			$allowed_css_names = apply_filters('sunshine_allowed_css', array('sunshine','admin-bar'));
			$allowed_js_names = apply_filters('sunshine_allowed_js', array('jquery','admin-bar','sunshine'));
			$remove_css = array();
			$remove_js = array();
			foreach ($wp_styles->queue as $key => $value) {
				$remove_css[$key] = true;
				foreach ($allowed_css_names as $allowed_name) {
					if (strpos($value, $allowed_name) !== false) {
						$remove_css[$key] = false;
						break;
					}
				}
			}
			foreach ($remove_css as $key => $ok_to_remove) {
				if ($ok_to_remove)
					unset($wp_styles->queue[$key]);
			}
			foreach ($wp_scripts->queue as $key => $value) {
				$remove_js[$key] = true;
				foreach ($allowed_js_names as $allowed_name) {
					if (strpos($value, $allowed_name) !== false) {
						$remove_js[$key] = false;
						break;
					}
				}
			}
			foreach ($remove_js as $key => $ok_to_remove) {
				if ($ok_to_remove)
					unset($wp_scripts->queue[$key]);
			}
		}
	}	
	
	function get_view_values($wp) {
		global $sunshine, $post, $wp_query, $wpdb;
		if (is_page($sunshine->options['page']) && isset( $wp_query->query_vars[$sunshine->options['endpoint_image']] ) ) {
			$sql = "
				 SELECT ID
				 FROM $wpdb->posts
				 WHERE post_name = '".$wp_query->query_vars[$sunshine->options['endpoint_image']]."'
				 AND post_type = 'attachment'
				 LIMIT 1
			";
			$images = $wpdb->get_results( $sql, OBJECT );
			$image = get_post($images[0]->ID);
			$parent = get_post($image->post_parent);
			if ($parent->post_type == 'sunshine-gallery') {
				self::$current_image = $image;
				self::$current_gallery = $parent;
			}
		} elseif (is_page($sunshine->options['page']) && isset( $wp_query->query_vars[$sunshine->options['endpoint_gallery']] )) {
			$gallery = get_page_by_path($wp_query->query_vars[$sunshine->options['endpoint_gallery']], 'OBJECT', 'sunshine-gallery');
			if ($gallery)
				self::$current_gallery = $gallery;
		} elseif (is_page($sunshine->options['page_account']) && isset( $wp_query->query_vars[$sunshine->options['endpoint_order']] )) {
			self::$current_order = get_post($wp_query->query_vars[$sunshine->options['endpoint_order']]);
		} 
	}
	
	function admin_bar() {
		if (!current_user_can('manage_options'))
			show_admin_bar(apply_filters('sunshine_admin_bar', false));
	}
	
	function require_login() {
		global $sunshine;
		if (is_page($sunshine->options['page_account']) && !is_user_logged_in()) {
			wp_redirect(apply_filters('sunshine_login_url', wp_login_url(sunshine_current_url(false))));
			exit;
		} 
		/*
		elseif (is_page($sunshine->options['page_checkout']) && !is_user_logged_in()) {
			$url = add_query_arg('redirect_to', sunshine_current_url(false), wp_registration_url());
			$url = add_query_arg('checkout_needs_account', 1, $url);
			wp_redirect(apply_filters('sunshine_registration_url', $url));
			exit;
		}
		*/
	}

	function redirect_to_endpoint_urls() {
		global $sunshine, $post;
		if (is_singular('sunshine-gallery')) {
			if ($post->post_status == 'private') { // Don't redirect private galleries, we need to handle some display things elsewhere
				echo 'PRIVATE GALLERY';
				return;
			}
			$url = trailingslashit(get_permalink($sunshine->options['page'])).$sunshine->options['endpoint_gallery'].'/'.$post->post_name;
			wp_redirect($url);
			exit;
		} elseif (is_singular('sunshine-order')) {
			$url = trailingslashit(get_permalink($sunshine->options['page_account'])).$sunshine->options['endpoint_gallery'].'/'.$post->ID;
			wp_redirect($url);
			exit;
		} elseif (is_singular('attachment')) {
			$parent = get_post($post->post_parent);
			if ($parent->post_type == 'sunshine-gallery')
				$url = trailingslashit(get_permalink($sunshine->options['page'])).$sunshine->options['endpoint_image'].'/'.$post->post_name;
			wp_redirect($url);
			exit;
		}
	}
	
	function get_template($template, $file='') {
		global $sunshine;
		if (self::$output)
			return self::$output;
		if (!$file) {
			if (file_exists(get_stylesheet_directory().'/sunshine/'.$template.'.php'))
				$file = get_stylesheet_directory().'/sunshine/'.$template.'.php';
			else
				$file = SUNSHINE_PATH.'themes/'.$sunshine->options['theme'].'/'.$template.'.php';
		}
		ob_start();
			load_template($file);
			$output = ob_get_contents();
		ob_end_clean();
		self::$output = $output;
		return $output;
	}
	
	function the_title($title, $id = '') {
		global $sunshine;

		$findthese = array(
			'#Protected:#',
			'#Private:#'
		);
		$replacewith = array(
			'', // What to replace "Protected:" with
			'' // What to replace "Private:" with
		);
		$title = preg_replace($findthese, $replacewith, $title);
		
		if (!in_the_loop() && $id == $sunshine->options['page_cart'])
			$title = $title . ' ('.$sunshine->cart->item_count.')';
		
		if (isset($_GET['sunshine_search']) && in_the_loop() && $id == $sunshine->options['page'])
			$title = __('Search for','sunshine').' "'.$_GET['sunshine_search'].'"';
		
		return $title;
	}
	
	function wp_title($title) {
		if (isset(SunshineFrontend::$current_image->ID))
			$title = SunshineFrontend::$current_image->post_title.' - '.SunshineFrontend::$current_gallery->post_title.' - '.get_bloginfo('name');
		elseif (isset(SunshineFrontend::$current_gallery->ID))
			$title = SunshineFrontend::$current_gallery->post_title.' - '.get_bloginfo('name');
		elseif (isset(SunshineFrontend::$current_order->ID))
			$title = __('Order','sunshine').' #'.SunshineFrontend::$current_order->ID.' - '.get_bloginfo('name');

		return apply_filters('sunshine_wp_title', $title);
	}
		
	function hide_image_comments($open, $post_id) {
		global $post;
		if ( $post->post_type == 'attachment' && get_post_type($post->post_parent) == 'sunshine-gallery') {
			return false;
		} 
		return $open;
	}

	// Include CSS/JS
	function frontend_cssjs () {
		global $sunshine;
		if ( is_admin() || !is_sunshine()) return false;
		if (!$sunshine->options['disable_sunshine_css']) {
			wp_enqueue_style('sunshine-fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
			wp_enqueue_style('sunshine', SUNSHINE_URL.'themes/'.$sunshine->options['theme'].'/style.css');
		}
		wp_enqueue_script('jquery');
	}

	// Add to body_class
	function body_class($classes) {
		if (is_sunshine())
			$classes[] = 'sunshine';
		if (is_active_sidebar('sunshine-sidebar'))
			$classes[] = 'sunshine-sidebar';
		return $classes;
	}
	

	function gallery_end_date($format='M j, Y') {
		global $post;
		$enddate = get_post_meta($post->ID, 'sunshine_gallery_end_date', true);
		if ($enddate)
			echo __( 'Expires on','sunshine') . ' '.date($format, $enddate);
	}

	function gallery_password_form($form) {
		
		if (isset(self::$current_gallery->ID)) {
			$label = 'pwbox-'.( empty( self::$current_gallery ) ? rand() : self::$current_gallery->ID );
			$form = '<form class="sunshine-gallery-password-form" action="'.esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ).'" method="post">';
			$form .= '<div class="sunshine-gallery-password-description"><p>' . __( "This gallery is password protected. To view it please enter your password below:", 'sunshine' ) . '</p></div>';
			$form .= '<div class="sunshine-gallery-password-input"><label for="' . $label . '">' . __( "Password", 'sunshine' ) . ': </label><input name="post_password" id="' . $label . '" type="password" size="20" /></div>';
			if ($hint = get_post_meta(self::$current_gallery->ID, 'sunshine_gallery_password_hint', true))
				$form .= '<div class="sunshine-gallery-password-hint"><span>' . __( "Hint", 'sunshine' ) . ':</span> '.$hint.'</div>';
			$form .= '<div class="sunshine-gallery-password-submit"><input type="submit" name="Submit" value="' . esc_attr__( "Submit", 'sunshine' ) . '" class="sunshine-button" /></div>';
			$form .= '</form>';
		}
		
		return $form;
	}
			
	function pluralize($count, $singular, $plural, $echo=true) {
		if ($count == 1)
			$output = $singular;
		else
			$output = $plural;
		if ($echo)
			echo $output;
		else
			return $output;
	}
		
	function order_comment_auto_approve($approved, $commentdata) {
		if (get_post_type($commentdata['comment_post_ID']) == 'sunshine-order')
			$approved = TRUE;
		return $approved;
	}
	
	function build_main_menu($menu) {
		global $sunshine;
		if (is_user_logged_in()) {
			$menu[110] = array(
				'name' => __('Logout','sunshine'),
				'url' => wp_logout_url( $sunshine->base_url ),
				'class' => 'sunshine-logout'
			);
			
			$menu[100] = array(
				'name' => __('Account','sunshine'),
				'url' => sunshine_url('account'),
				'class' => 'sunshine-account'
			);

		} else {
			$menu[100] = array(
				'name' => __('Login','sunshine'),
				'url' => wp_login_url(sunshine_current_url(false)),
				'class' => 'sunshine-login'
			);
			$menu[110] = array(
				'name' => __('Register','sunshine'),
				'url' => wp_registration_url().'&redirect_to='.sunshine_current_url(false),
				//'url' => wp_login_url(sunshine_current_url(false)),
				'class' => 'sunshine-register'
			);
		}
		
		$menu[10] = array(
			'name' => __('Galleries','sunshine'),
			'url' => sunshine_url('home'),
			'class' => 'sunshine-galleries'
		);
		
		if (!$sunshine->options['proofing']) {
			$cart_count = '';
			
			if (!empty($sunshine->cart->content))
				$cart_count = '<span class="sunshine-count sunshine-cart-count">'.$sunshine->cart->item_count.'</span>';
			
			$menu[40] = array(
				'name' => __('Cart','sunshine'),
				'url' => sunshine_url('cart'),
				'class' => 'sunshine-cart',
				'after_a' => $cart_count
			);

			$menu[50] = array(
				'name' => __('Checkout','sunshine'),
				'url' => sunshine_url('checkout'),
				'class' => 'sunshine-checkout'
			);
		}

		return $menu;
	}
	
	
	function build_action_menu($menu) {
		global $wp_query, $post;
		
		// Single gallery page
		if (isset($wp_query->query_vars['sunshine_gallery'])) {
			if ($post->post_parent != 0) { // If sub gallery
				$menu[10] = array(
					'name' => __('Return to','sunshine').' '.get_the_title($post->post_parent),
					'url' => get_permalink($post->post_parent),
					'class' => 'sunshine-return-gallery'
				);
			}
		}
		
		return $menu;
	}
	
	function build_image_menu($menu, $image) {
		global $sunshine;
		$disable_products = get_post_meta(SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_disable_products', true);
		if (!$disable_products && !$sunshine->options['proofing']) {
			$menu[10] = array(
				'icon' => 'shopping-cart',
				'url' => get_permalink($image->ID),
				'class' => 'sunshine-purchase',
			);
		}
		$allow_comments = get_post_meta(SunshineFrontend::$current_gallery->ID, 'sunshine_gallery_image_comments', true);
		if ($allow_comments) {
			$menu[30] = array(
				'icon' => 'comments',
				'url' => get_permalink($image->ID).'#respond',
				'class' => 'sunshine-comments',
			);
		}
		
		return $menu;
	}

	function head() {
		global $post, $sunshine;
		
		// Image page
		if (!empty(self::$current_image)) {
			
			$image = wp_get_attachment_image_src(self::$current_image->ID, 'large');
			
			echo '<meta property="og:title" content="'.self::$current_image->post_title.' by '.get_bloginfo('name').'"/>
		    <meta property="og:type" content="product"/>
		    <meta property="og:url" content="'.trailingslashit(get_permalink(self::$current_image->ID)).'"/>
		    <meta property="og:image" content="'.$image[0].'"/>
		    <meta property="og:image:width" content="' . $image[1] . '"/>
		    <meta property="og:image:height" content="' . $image[2] . '"/>
		    <meta property="og:site_name" content="' . get_bloginfo('name') . '"/>
		    <meta property="og:description" content="' . sprintf( __( 'A photo from the gallery %s by %s', 'sunshine' ), strip_tags( get_the_title( self::$current_image->post_parent ) ), get_bloginfo( 'name' ) ) . '"/>';
			
	    } elseif (!empty(self::$current_gallery)) {
		
			$image = sunshine_featured_image('full', 0);
		
			echo '<meta property="og:title" content="'.self::$current_gallery->post_title.' by '.get_bloginfo('name').'"/>
		    <meta property="og:type" content="product"/>
		    <meta property="og:url" content="'.trailingslashit(get_permalink(self::$current_gallery->ID)).'"/>
		    <meta property="og:image" content="'.$image.'"/>
		    <meta property="og:site_name" content="'.get_bloginfo('name').'"/>
		    <meta property="og:description" content="' . sprintf( __( 'A photo in the gallery %s by %s', 'sunshine' ), get_the_title(self::$current_gallery->post_parent), get_bloginfo( 'name' ) ) . '"/>';
		
		} elseif (!empty(self::$current_order)) {
			echo '<meta name="googlebot" content="noindex" />';
		}
		
		if (is_sunshine() && $sunshine->options['disable_right_click']) {
		?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery(document).bind("contextmenu",function(e){ return false; });
				jQuery("img").mousedown(function(){ return false; });
			});
			</script>
		<?php
		}
		
	}	
	
	function can_view_gallery() {
		global $post, $current_user;
		if (self::$current_gallery && self::$current_gallery->post_status == 'private' && !current_user_can('manage_options')) {
			$allowed_users = get_post_meta(self::$current_gallery->ID, 'sunshine_gallery_private_user');
			if (!in_array($current_user->ID, $allowed_users)) {
				wp_redirect(add_query_arg('sunshine_login_notice','private_gallery',wp_login_url(sunshine_current_url(false))));
				exit;	
			}
		}
		if (self::$current_gallery && get_post_meta(self::$current_gallery->ID, 'sunshine_gallery_access', true) == 'account' && !is_user_logged_in()) {
			wp_redirect(add_query_arg('sunshine_login_notice','gallery_requires_login',wp_login_url(sunshine_current_url(false))));
			exit;
		}
	}
	
	function can_view_image() {
		global $post, $current_user;
		//echo ' ** CAN VIEW IMAGE NEEDS WORK ** ';
		if (get_post_type($post) == 'attachment' && get_post_type($post->post_parent) == 'sunshine-gallery' && !current_user_can('manage_options')) {
			$gallery = get_post($post->post_parent);
			if ($gallery->post_status == 'private') {
				$allowed_users = get_post_meta($post->post_parent, 'sunshine_gallery_private_user');
				if (!in_array($current_user->ID, $allowed_users))
					wp_die(__('Sorry, you are not allowed to view this image','sunshine'), __('Access denied','sunshine'), array('back_link'=>true));
			}
		}
	}
	
	function can_view_order() {
		global $wp_query, $current_user;
		if (isset(self::$current_order->ID)) {
			$order_customer_id = get_post_meta(self::$current_order->ID, '_sunshine_customer_id', true);
			if (!isset($current_user->ID) || $current_user->ID != $order_customer_id)
				wp_die(__('Sorry, you are not allowed to access this order information','sunshine'), __('Access denied','sunshine'), array('back_link'=>true));
		}
	}
	
	function can_use_cart() {
		global $sunshine;
		if ($sunshine->options['proofing'] && (is_page($sunshine->options['page_cart']) || is_page($sunshine->options['page_checkout']))) {
			wp_redirect(get_permalink($sunshine->options['page']));
			exit;
		}
	}
	
	function hide_order_comments( $template ) {
		global $post;
		if ($post->post_type == 'sunshine-order')
			return;
		return $template;
	}	
	
	function order_comment_post_redirect($url, $comment) {
		if(get_post_type($comment->comment_post_ID) == 'sunshine-order')
			return get_permalink($sunshine->options['page']).'order/'.$comment->comment_post_ID.'/#comment-'.$comment->comment_ID;
		return $url;
	}
	
	/*
	*	Adds sunshine=1 to all requests for login, password retrieval links
	*	Only adds if we are within Sunshine
	*/
	function login_url($login_url) {
		if (is_sunshine()) {
			$login_url = add_query_arg('sunshine', 1, $login_url);
			if (isset($_GET['redirect_to']))
				$login_url = add_query_arg('redirect_to', sanitize_text_field( $_GET['redirect_to'] ), $login_url);
			if (isset($_POST['redirect_to']))
				$login_url = add_query_arg('redirect_to', sanitize_text_field( $_POST['redirect_to'] ), $login_url);
		}
		return $login_url;
	}
	
	/* changes the "Register For This Site" text on the Wordpress login screen (wp-login.php) */
	function login_message($message) {
		if (is_sunshine() && isset($_GET['checkout_needs_account'])) {
			$message = __('To continue to checkout, please enter your email address and password to establish an account. You can complete your purchase on the next page.', 'sunshine');
			return '<p class="message register">' . $message . '</p>';
		} elseif (isset($_GET['sunshine_login_notice']) && $_GET['sunshine_login_notice'] == 'gallery_requires_login' && $_GET['action'] == 'register') {
			$message = sprintf(__('The gallery you are accessing requires you to have a user account to access. Please register below or <a href="%s">login here</a>', 'sunshine'), add_query_arg('sunshine_login_notice','gallery_requires_login',wp_login_url()));
			return '<p class="message login">' . $message . '</p>';
		} elseif (isset($_GET['sunshine_login_notice']) && $_GET['sunshine_login_notice'] == 'gallery_requires_login') {
			$message = sprintf(__('The gallery you are accessing requires you to have a user account to access. Please login below or <a href="%s">register here</a>', 'sunshine'), add_query_arg('sunshine_login_notice','gallery_requires_login',wp_registration_url()));
			return '<p class="message login">' . $message . '</p>';
		} elseif (isset($_GET['sunshine_login_notice']) && $_GET['sunshine_login_notice'] == 'private_gallery') {
			$message = sprintf(__('The gallery you are accessing is private and only allowed for specific users. Please login below:', 'sunshine'), add_query_arg('sunshine_login_notice','private_gallery',wp_registration_url()));
			return '<p class="message login">' . $message . '</p>';
		}
		return $message;
	}		
	
	/*
	*	Checks if we are asking for the registration URL and we are in Sunshine
	*	If so, add the sunshine=1 to the URL so we know this is a Sunshine registration
	* 	Used for navigating between login/register links on wp-login.php
	*/
	function site_url($login_url, $path, $scheme) {
		//echo $login_url.$path.$scheme.' **** ';
		if (is_sunshine() && $login_url == get_option( 'siteurl' ).'/wp-login.php?action=register') {
			$login_url = add_query_arg('sunshine', 1, $login_url);
			if (isset($_GET['redirect_to']))
				$login_url = add_query_arg('redirect_to', $_GET['redirect_to'], $login_url);
			if (isset($_POST['redirect_to']))
				$login_url = add_query_arg('redirect_to', $_POST['redirect_to'], $login_url);
		}
		return $login_url;
	}

	function remove_image_commenting( $open, $post_id ) {
		global $post;
		if( $post->post_type == 'attachment' ) {
			return false;
		}
		return $open;
	}
	
	function remove_parent_classes($class) {
		return ($class == 'current_page_item' || $class == 'current_page_parent' || $class == 'current_page_ancestor'  || $class == 'current-menu-item') ? FALSE : TRUE;
	}

	function add_class_to_wp_nav_menu($classes, $item) {
		global $sunshine;
		switch (get_post_type()) {
			case 'sunshine-gallery':
	     		$classes = array_filter($classes, array($this, 'remove_parent_classes'));
	     		if ($item->object_id == $sunshine->options['page'])
					$classes[] = 'current_page_parent';
	     		break;
	     	case 'sunshine-order':
     			$classes = array_filter($classes, array($this, 'remove_parent_classes'));
	     		if ($item->object_id == $sunshine->options['page'])
					$classes[] = 'current_page_parent';
	     		break;
	    }
		return $classes;
	}
		
}

?>