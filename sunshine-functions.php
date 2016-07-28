<?php
/******************
COMMON FUNCTIONS
******************/
/**
 * Log errors to debug file
 *
 * @since 1.0
 * @param mixed $message String or array to be written to log file
 * @return void
 */
function sunshine_log( $message, $pre = '' ) {
	if ( WP_DEBUG === true ) {
		if ( $pre ) {
			error_log( $pre );
		}
		if( is_array( $message ) || is_object( $message ) ){
			error_log( print_r( $message, true ) );
		} else {
			error_log( $message );
		}
	}
}

/**
 * Display variables nicely formatted
 *
 * @since 1.0
 * @param mixed $var String or array
 * @return void
 */
function sunshine_dump_var( $var, $echo = true ) {
	if ( $echo ) {
		echo '<pre>';
		print_r( $var );
		echo '</pre>';
	} else {
		$content = '<pre>';
		$content .= print_r( $var, true );
		$content .= '</pre>';
		return $content;
	}
}

/**
 * Are we on a Sunshine related page
 *
 * @since 1.0
 * @param string $from Help determine where this call is being made for debugging
 * @return bool
 */
function is_sunshine( $from='' ) {
	global $post, $sunshine;
	$return = '';

	if ( ( isset( $_GET['sunshine'] ) && $_GET['sunshine'] == 1 ) || ( isset( $_POST['sunshine'] ) && $_POST['sunshine'] == 1 ) )
		$return = 'SUNSHINE';

	if( isset( $post ) && is_array( $sunshine->pages ) && in_array( $post->ID, $sunshine->pages ) )
		$return = 'SUNSHINE PAGE';
	if( get_post_type( $post ) == 'sunshine-gallery' )
		$return = 'SUNSHINE-GALLERY';
	if( is_post_type_archive( 'sunshine-gallery' ) )
		$return = 'SUNSHINE-GALLERY-ARCHIVE';
	if( isset( $post ) && $post->post_parent > 0 && get_post_type( $post->post_parent ) == 'sunshine-gallery' )
		$return = 'SUNSHINE GALLERY ATTACHMENT';
	if( get_post_type( $post ) == 'sunshine-order' )
		$return = 'SUNSHINE ORDER';

	if ( $return ) {
		if ( !defined( 'IS_SUNSHINE' ) )
			define( 'IS_SUNSHINE', $return );
		return $return;
	}
	else
		return false;
}

/**
 * Change letter to number for file size
 *
 * @since 1.0
 * @param string $v string value
 * @return string
 */
function sunshine_let_to_num( $v ) {
	$l = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );
	switch( strtoupper( $l ) ){
	case 'P':
		$ret *= 1024;
	case 'T':
		$ret *= 1024;
	case 'G':
		$ret *= 1024;
	case 'M':
		$ret *= 1024;
	case 'K':
		$ret *= 1024;
		break;
	}
	return $ret;
}

/**
 * Change letter to number for file size
 *
 * @since 2.4
 * @param string $needle string value
 * @param string $haystack array
 * @return boolean
 */
function sunshine_in_array_r( $needle, $haystack, $strict = false ) {
    foreach ( $haystack as $item ) {
        if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && sunshine_in_array_r( $needle, $item, $strict ) ) ) {
            return true;
        }
    }
    return false;
}


/**
 * Customize the_permalink output for custom post types
 *
 * @since 1.6
 * @param string $url URL
 * @param obj $post_obj WP Post Object
 * @return string
 */
add_filter( 'post_type_link', 'sunshine_post_links', 999, 2 );
add_filter( 'attachment_link', 'sunshine_post_links', 999, 2 );
add_filter( 'the_permalink', 'sunshine_post_links', 999 );
function sunshine_post_links( $url, $post_obj = '' ) {
	global $sunshine, $post;
	if ( !is_array( $post_obj ) && is_integer( $post_obj ) )
		$post_obj = get_post( $post_obj );
	elseif ( empty( $post_obj ) )
		$post_obj = $post;
	if ( $post_obj->post_type == 'sunshine-gallery' ) {
		$slug = $post_obj->post_name;
		if ( $post_obj->post_parent > 0 ) {
			$ancestors = get_ancestors( $post_obj->ID, 'sunshine-gallery' );
			foreach ( $ancestors as $parent )
				$slug = get_post( $parent )->post_name . '/' . $slug;
		}
		$url = trailingslashit( get_permalink( $sunshine->options['page'] ) ).$sunshine->options['endpoint_gallery'].'/'.$slug;
	} elseif ( $post_obj->post_type == 'attachment' ) {
		$parent = get_post( $post_obj->post_parent );
		if ( !empty( $parent ) && $parent->post_type == 'sunshine-gallery' )
			$url = trailingslashit( get_permalink( $sunshine->options['page'] ) ).$sunshine->options['endpoint_image'].'/'.$post_obj->post_name;
	} elseif ( $post_obj->post_type == 'sunshine-order' ) {
		$url = trailingslashit( get_permalink( $sunshine->options['page'] ) ).$sunshine->options['endpoint_order'].'/'.$post_obj->ID;
	}
	return $url;
}

/**
 * Sort an array by a specific key/column
 *
 * @since 1.8
 * @return array
 */
function sunshine_array_sort_by_column( &$arr, $col, $dir = SORT_ASC ) {
	$sort_col = array();
	if ( empty( $arr ) ) return;
	foreach ( $arr as $key=> $row ) {
		$sort_col[$key] = $row[$col];
	}
	array_multisort( $sort_col, $dir, $arr );
}


/**********************
	CART
***********************/
/**
 * Listening for add to cart request, adding item to cart
 *
 * @since 1.0
 * @return void
 */
add_action( 'init', 'sunshine_add_to_cart', 99 );
function sunshine_add_to_cart() {
	global $sunshine;
	if ( isset( $_POST['sunshine_add_to_cart'] ) && $_POST['sunshine_add_to_cart'] == 1 ) {
		if ( is_numeric( $_POST['sunshine_product'] ) ) {
			if ( !is_numeric( $_POST['sunshine_qty'] ) )
				$quantity = 1;
			else
				$quantity = intval( $_POST['sunshine_qty'] );
			$image = get_post( intval( $_POST['sunshine_image'] ) );
			$price_level = get_post_meta( $image->post_parent, 'sunshine_gallery_price_level', true );
			$result = $sunshine->cart->add_to_cart( $_POST['sunshine_image'], $_POST['sunshine_product'], $quantity, $price_level, $_POST['sunshine_comments'] );
			$gallery_return_url = get_permalink( $image->post_parent );
			if ( SunshineSession::instance()->current_gallery_page )
				$gallery_return_url .= '?pagination='.SunshineSession::instance()->current_gallery_page[1];

			$message = sprintf( __( 'Item added to cart! <a href="%s" target="_top">View cart</a> or <a href="%s">Return to %s</a>', 'sunshine' ), sunshine_url( 'cart' ), esc_url( $gallery_return_url ), get_the_title( $image->post_parent ) );

			$sunshine->add_message( $message );
			//$sunshine->add_message('Item added to cart');
		} else {
			$sunshine->add_error( __( 'Sorry, something went wrong with adding the item to cart.','sunshine' ) );
		}
		wp_redirect( sunshine_current_url( false ) );
		exit;
	}
}

/**
 * Listening to update cart request, updating cart
 *
 * @since 1.0
 * @return void
 */
add_action( 'init', 'sunshine_update_cart', 99 );
function sunshine_update_cart() {
	global $current_user, $sunshine;
	if ( isset( $_POST['sunshine_update_cart'] ) && $_POST['sunshine_update_cart'] == 1  && wp_verify_nonce( $_POST['nonce'], 'sunshine_update_cart' ) ) {
		$i = 0;
		$cart = $sunshine->cart->get_cart();
		foreach ( $cart as &$cart_item ) {
			foreach ( $_POST['item'] as $key => $item ) {
				if ( $item['hash'] == $cart_item['hash'] ) {
					if ( !isset( $item['qty'] ) )
						$item['qty'] = 1;
					if ( $item['qty'] <= 0 ) {
						if ( is_user_logged_in() )
							SunshineUser::delete_user_meta( 'cart', $cart_item );
						else {
							unset( $cart[$i] );
						}
					} elseif ( $item['qty'] != $cart_item['qty'] ) {
						$new_item = $cart_item;
						$new_item['qty'] = $item['qty'];
						$new_item['total'] = $new_item['qty'] * $new_item['price'];
						if ( is_user_logged_in() )
							SunshineUser::update_user_meta( 'cart', $new_item, $cart_item );
						else {
							$cart[$i] = $new_item;
						}
					}
					$i++;
				}
			}
		}
		if ( empty( $cart ) ) {
			$sunshine->cart->set_cart_cookies( false );
		} elseif ( $_COOKIE['sunshine_cart_hash'] ) {
			update_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'], serialize( $cart ) );
		}
		
		$sunshine->add_message( __( 'Cart updated','sunshine' ) );
		wp_redirect( sunshine_url( 'cart' ) );
		exit;
	}
}

/**
 * Listening for delete cart item request, deleting item
 *
 * @since 1.0
 * @return void
 */
add_action( 'wp', 'sunshine_delete_cart_item', 999 );
function sunshine_delete_cart_item() {
	global $sunshine, $current_user;
	if ( isset( $_GET['delete_cart_item'] ) && wp_verify_nonce( $_GET['nonce'], 'sunshine_delete_cart_item' ) ) {
		$cart = $sunshine->cart->get_cart();
		foreach ( $cart as $key => $cart_item ) {
			if ( $_GET['delete_cart_item'] == $cart_item['hash'] ) {
				$sunshine->cart->remove_from_cart( $key );
				break;
			}
		}
		$sunshine->add_message( __( 'Item removed from cart','sunshine' ) );
		wp_redirect( sunshine_url( 'cart' ) );
		exit;
	}
}

/**********************
	CHECKOUT
***********************/

/**
 * Listening for complete checkout, doing error checking
 *
 * @since 1.0
 * @return void
 */
add_action( 'wp', 'sunshine_checkout', 999 );
function sunshine_checkout() {
	global $current_user, $sunshine;
	if ( isset( $_POST['sunshine_checkout'] ) && $_POST['sunshine_checkout'] == 1 ) {

		$order_data = array();
		
		$create_new_user = $valid_email = $valid_password = false;

		if ( !is_user_logged_in() ) {

			if ( $_POST['email'] == '' || !is_email( $_POST['email'] ) ) {
				$sunshine->add_error( __( 'Valid email is required','sunshine' ) );
			} elseif ( email_exists( sanitize_email( $_POST['email'] ) ) ) {
				$sunshine->add_error( sprintf( __( 'Email already exists, <a href="%s">please login first</a>','sunshine' ), wp_login_url( sunshine_current_url( false ) ) ) );
			} else {
				$valid_email = true;
			}

			if ( $sunshine->options['allow_guest_checkout'] != 1 && ( !isset( $_POST['password'] ) || $_POST['password'] == '' ) ) {
				$sunshine->add_error( __( 'Password is required','sunshine' ) );
			} else {
				$valid_password = true;
			}

			if ( $valid_email && $valid_password && isset( $_POST['password'] ) && $_POST['password'] != '' ) {
				$cart = maybe_unserialize( get_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'] ) );
				$user_id = wp_create_user( sanitize_email( $_POST['email'] ), $_POST['password'], sanitize_email( $_POST['email'] ) );
				$user = get_user_by( 'id', $user_id );
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id );
				do_action( 'wp_login', $user->user_login, $user );
				$create_new_user = true;
			}

		} else {
			if ( $_POST['email'] == '' || !is_email( sanitize_email( $_POST['email'] ) ) )
				$sunshine->add_error( __( 'Valid email required','sunshine' ) );
		}
		
		$order_data['country'] = isset( $_POST['country'] ) ? $_POST['country'] : '';
		$order_data['first_name'] = isset( $_POST['first_name'] ) ? $_POST['first_name'] : '';
		$order_data['last_name'] = isset( $_POST['last_name'] ) ? $_POST['last_name'] : '';
		$order_data['address'] = isset( $_POST['address'] ) ? $_POST['address'] : '';
		$order_data['address2'] = isset( $_POST['address2'] ) ? $_POST['address2'] : '';
		$order_data['city'] = isset( $_POST['city'] ) ? $_POST['city'] : '';
		$order_data['state'] = isset( $_POST['state'] ) ? $_POST['state'] : '';
		$order_data['zip'] = isset( $_POST['zip'] ) ? $_POST['zip'] : '';
		
		$order_data['shipping_country'] = isset( $_POST['shipping_country'] ) ? $_POST['shipping_country'] : '';
		$order_data['shipping_first_name'] = isset( $_POST['shipping_first_name'] ) ? $_POST['shipping_first_name'] : '';
		$order_data['shipping_last_name'] = isset( $_POST['shipping_last_name'] ) ? $_POST['shipping_last_name'] : '';
		$order_data['shipping_address'] = isset( $_POST['shipping_address'] ) ? $_POST['shipping_address'] : '';
		$order_data['shipping_address2'] = isset( $_POST['shipping_address2'] ) ? $_POST['shipping_address2'] : '';
		$order_data['shipping_city'] = isset( $_POST['shipping_city'] ) ? $_POST['shipping_city'] : '';
		$order_data['shipping_state'] = isset( $_POST['shipping_state'] ) ? $_POST['shipping_state'] : '';
		$order_data['shipping_zip'] = isset( $_POST['shipping_zip'] ) ? $_POST['shipping_zip'] : '';

		if ( isset( $_POST['billing_as_shipping'] ) && $_POST['billing_as_shipping'] == 1 ) {
			$order_data['country'] = isset( $_POST['shipping_country'] ) ? $_POST['shipping_country'] : '';
			$order_data['first_name'] = isset( $_POST['shipping_first_name'] ) ? $_POST['shipping_first_name'] : '';
			$order_data['last_name'] = isset( $_POST['shipping_last_name'] ) ? $_POST['shipping_last_name'] : '';
			$order_data['address'] = isset( $_POST['shipping_address'] ) ? $_POST['shipping_address'] : '';
			$order_data['address2'] = isset( $_POST['shipping_address2'] ) ? $_POST['shipping_address2'] : '';
			$order_data['city'] = isset( $_POST['shipping_city'] ) ? $_POST['shipping_city'] : '';
			$order_data['state'] = isset( $_POST['shipping_state'] ) ? $_POST['shipping_state'] : '';
			$order_data['zip'] = isset( $_POST['shipping_zip'] ) ? $_POST['shipping_zip'] : '';
		}
		$order_data['billing_as_shipping'] = isset( $_POST['billing_as_shipping'] ) ? $_POST['billing_as_shipping'] : '';
		$order_data['email'] = $_POST['email'];
		$order_data['phone'] = isset( $_POST['phone'] ) ? $_POST['phone'] : '';
		$order_data['shipping_method'] = isset( $_POST['shipping_method'] ) ? $_POST['shipping_method'] : '';
		
		if ( is_user_logged_in() ) {
			$order_data['user_id'] = $current_user->ID;
			foreach ( $order_data as $key => $item ) {
				SunshineUser::update_user_meta_by_id( $current_user->ID, $key, sanitize_text_field( $item ) );
			}
			$userdata['ID'] = $current_user->ID;
			$userdata['user_email'] = sanitize_email( $_POST['email'] );
			$userdata['first_name'] = sanitize_text_field( $_POST['first_name'] );
			$userdata['last_name'] = sanitize_text_field( $_POST['last_name'] );
			wp_update_user( $userdata );
		} else {
			foreach ( $order_data as $key => $item ) {
				SunshineSession::instance()->$key = $item;
			}
		}
		
		// Validate that we have all required fields

		if ( $_POST['shipping_method'] == 'local' || $_POST['shipping_method'] == 'flat_rate' ) {
			if ( $_POST['shipping_country'] == '' )
				$sunshine->add_error( __( 'Shipping country required','sunshine' ) );
			if ( $_POST['shipping_first_name'] == '' )
				$sunshine->add_error( __( 'Shipping first name required','sunshine' ) );
			if ( $_POST['shipping_last_name'] == '' )
				$sunshine->add_error( __( 'Shipping last name required','sunshine' ) );
			if ( $_POST['shipping_address'] == '' )
				$sunshine->add_error( __( 'Shipping address required','sunshine' ) );
			if ( $_POST['shipping_city'] == '' )
				$sunshine->add_error( __( 'Shipping city required','sunshine' ) );
			if ( $_POST['shipping_state'] == '' )
				$sunshine->add_error( __( 'Shipping state required','sunshine' ) );
			if ( $_POST['shipping_zip'] == '' )
				$sunshine->add_error( __( 'Shipping zip required','sunshine' ) );
		} 
		
		if ( empty( $_POST['billing_as_shipping'] ) || $_POST['shipping_method'] == 'pickup' ) {
			if ( $_POST['country'] == '' )
				$sunshine->add_error( __( 'Billing country required','sunshine' ) );
			if ( $_POST['first_name'] == '' )
				$sunshine->add_error( __( 'Billing first name required','sunshine' ) );
			if ( $_POST['last_name'] == '' )
				$sunshine->add_error( __( 'Billing last name required','sunshine' ) );
			if ( $_POST['address'] == '' )
				$sunshine->add_error( __( 'Billing address required','sunshine' ) );
			if ( $_POST['city'] == '' )
				$sunshine->add_error( __( 'Billing city required','sunshine' ) );
			if ( $_POST['state'] == '' )
				$sunshine->add_error( __( 'Billing state required','sunshine' ) );
			if ( $_POST['zip'] == '' )
				$sunshine->add_error( __( 'Billing zip required','sunshine' ) );
		}
		if ( !isset( $_POST['shipping_method'] ) )
			$sunshine->add_error( __( 'Shipping method required','sunshine' ) );
		if ( !isset( $_POST['payment_method'] ) && $sunshine->cart->total > 0 )
			$sunshine->add_error( __( 'Payment method required','sunshine' ) );
		
		if ( $sunshine->options['require_terms'] && empty( $_POST['terms'] ) ) {
			$sunshine->add_error( __( 'You must approve our terms', 'sunshine' ) );
		}
		
		if ( $create_new_user ) {
			$sunshine->cart->set_cart();
		}
		
		$order_items = $sunshine->cart->get_cart();
		foreach ( $order_items as &$item ) {
			$image_name = get_the_title( $item['image_id'] );
			$product = get_post( $item['product_id'] );
			$cat = wp_get_post_terms( $item['product_id'], 'sunshine-product-category' );
			$product_name = apply_filters( 'sunshine_cart_item_category', ( isset( $cat[0]->name ) ) ? $cat[0]->name : '', $item ).' - '.apply_filters( 'sunshine_cart_item_name', $product->post_title, $item );
			$item['image_name'] = $image_name;
			$item['product_name'] = $product_name;
		}
		$order_data['items'] = $order_items;
		
		$order_data['payment_method'] = sanitize_text_field( $_POST['payment_method'] );
		if ( $sunshine->cart->total <= 0 ) {
			$order_data['payment_method'] = 'free';
		} 
		
		$order_data['shipping_cost'] = $sunshine->cart->shipping_method['cost'];
		$order_data['credits'] = isset( $_POST['use_credits'] ) ? 1 : '';
		$order_data['discount_total'] = $sunshine->cart->discount_total;
		$order_data['discount_items'] = $sunshine->cart->discount_items;
		$order_data['tax'] = $sunshine->cart->tax;
		$order_data['subtotal'] = $sunshine->cart->subtotal;
		$order_data['total'] = $sunshine->cart->total;

		do_action( 'sunshine_checkout_validation', $order_data );

		if ( !$sunshine->has_errors() ) {
			
			$order_data = apply_filters( 'sunshine_order_pre_process', $order_data );
			$order_id = SunshineOrder::add_order( $order_data, apply_filters( 'sunshine_add_order_notify', true, $order_data ) );
			if ( apply_filters( 'sunshine_add_order_clear_cart', true, $order_data ) ) {
				$sunshine->cart->empty_cart();
			}
			do_action( 'sunshine_order_post_process', $order_id, $order_data );
			
		}
		
		if ( !$sunshine->has_errors() ) {
			SunshineSession::instance()->order_id = $order_id;
			$thank_you_url = apply_filters( 'sunshine_purchase_redirect', get_permalink( $order_id ), $order_id, $order_data );
			wp_redirect( $thank_you_url );
			exit;
		} else {
			$sunshine->messages = array();
			wp_delete_post( $order_id, true );
		}		

	}
}

add_filter( 'sunshine_order_pre_process', 'sunshine_process_free_payment' );
function sunshine_process_free_payment( $order_data ) {
	if ( $order_data['payment_method'] == 'free' ) {
		$order_data['status'] = 'new';
	}
	return $order_data;
}

/**
 * Ajax request when billing country is selected, return HTML for selecting available states
 *
 * @since 1.0
 * @return void
 */
add_action( 'wp_ajax_sunshine_checkout_update_state', 'sunshine_checkout_update_state' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_update_state', 'sunshine_checkout_update_state' );
function sunshine_checkout_update_state() {
	if ( isset( $_POST['country'] ) ) {	
		if ( isset( SunshineCountries::$states[ $_POST['country'] ] ) && is_array( SunshineCountries::$states[ $_POST['country'] ] ) ) {
			$states = SunshineCountries::$states[ sanitize_text_field( $_POST['country'] ) ];
			$return['state_options'] = '<select name="state"><option value="">'.__( 'Select state','sunshine' ).'</option>';
			foreach ( $states as $state_code => $state )
				$return['state_options'] .= '<option value="'.esc_attr( $state_code ).'">'.esc_html( $state ).'</option>';
			$return['state_options'] .= '</select>';

		} else
			$return['state_options'] = '<input type="text" name="state" />';
	}

	die( json_encode( $return ) );
}

add_action( 'wp_ajax_sunshine_checkout_email_exists', 'sunshine_checkout_email_exists' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_email_exists', 'sunshine_checkout_email_exists' );
function sunshine_checkout_email_exists() {
	if ( !is_user_logged_in() && isset( $_POST['email'] ) && email_exists( $_POST['email'] ) ) {	
		$return['exists'] = true;
	} else {
		$return['exists'] = false;
	}

	die( json_encode( $return ) );
}


/**
 * Ajax request when shipping country is selected, return HTML for selecting available states
 *
 * @since 1.0
 * @return void
 */
add_action( 'wp_ajax_sunshine_checkout_update_shipping_state', 'sunshine_checkout_update_shipping_state' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_update_shipping_state', 'sunshine_checkout_update_shipping_state' );
function sunshine_checkout_update_shipping_state() {
	if ( isset( $_POST['shipping_country'] ) ) {
		$states = SunshineCountries::$states[$_POST['shipping_country']];
		if ( is_array( $states ) ) {
			$return['state_options'] = '<select name="shipping_state"><option value="">'.__( 'Select state','sunshine' ).'</option>';
			foreach ( $states as $state_code => $state )
				$return['state_options'] .= '<option value="'.esc_attr( $state_code ).'">'.esc_html( $state ).'</option>';
			$return['state_options'] .= '</select>';

		} else
			$return['state_options'] = '<input type="text" name="shipping_state" />';
	}

	die( json_encode( $return ) );
}

/**
 * Ajax request when anything is done on checkout page that could update totals, return new totals
 *
 * @since 1.0
 * @return void
 */
add_action( 'wp_ajax_sunshine_checkout_update_totals', 'sunshine_checkout_update_totals' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_update_totals', 'sunshine_checkout_update_totals' );
function sunshine_checkout_update_totals() {
	global $current_user;
	
	if ( is_user_logged_in() ) {
		
		SunshineUser::update_user_meta( 'shipping_method', isset( $_POST['shipping_method'] ) ? sanitize_text_field( $_POST['shipping_method'] ) : '' );
		SunshineUser::update_user_meta( 'use_credits', isset( $_POST['use_credits'] ) ? 1 : '' );
		SunshineUser::update_user_meta( 'billing_as_shipping', isset( $_POST['billing_as_shipping'] ) ? sanitize_text_field( $_POST['billing_as_shipping'] ) : '' );

		SunshineUser::update_user_meta( 'shipping_country', sanitize_text_field( $_POST['shipping_country'] ) );
		SunshineUser::update_user_meta( 'shipping_state', sanitize_text_field( $_POST['shipping_state'] ) );
		SunshineUser::update_user_meta( 'country', sanitize_text_field( $_POST['country'] ) );
		SunshineUser::update_user_meta( 'state', sanitize_text_field( $_POST['state'] ) );

		if ( isset( $_POST['billing_as_shipping'] ) && $_POST['billing_as_shipping'] == 1 ) {
			if ( $_POST['shipping_country'] ) {
				SunshineUser::update_user_meta( 'country', sanitize_text_field( $_POST['shipping_country'] ) );
				SunshineUser::update_user_meta( 'state', sanitize_text_field( $_POST['shipping_state'] ) );
			} else {
				SunshineUser::update_user_meta( 'shipping_country', sanitize_text_field( $_POST['country'] ) );
				SunshineUser::update_user_meta( 'shipping_state', sanitize_text_field( $_POST['state'] ) );
			}
		} 
		
	} else {
		
		SunshineSession::instance()->billing_as_shipping = isset( $_POST['billing_as_shipping'] ) ? sanitize_text_field( $_POST['billing_as_shipping'] ) : '';
		SunshineSession::instance()->shipping_method = isset( $_POST['shipping_method'] ) ? sanitize_text_field( $_POST['shipping_method'] ) : '';

		SunshineSession::instance()->shipping_country = sanitize_text_field( $_POST['shipping_country'] );
		SunshineSession::instance()->shipping_state = sanitize_text_field( $_POST['shipping_state'] );
		SunshineSession::instance()->country = sanitize_text_field( $_POST['country'] );
		SunshineSession::instance()->state = sanitize_text_field( $_POST['state'] );

		if ( isset( $_POST['billing_as_shipping'] ) && $_POST['billing_as_shipping'] == 1 ) {
			if ( $_POST['shipping_country'] ) {
				SunshineSession::instance()->country = sanitize_text_field( $_POST['shipping_country'] );
				SunshineSession::instance()->state = sanitize_text_field( $_POST['shipping_state'] );
			} else {
				SunshineSession::instance()->shipping_country = sanitize_text_field( $_POST['country'] );
				SunshineSession::instance()->shipping_state = sanitize_text_field( $_POST['state'] );
			}
		} 
		
	}
	
	$sunshine = new Sunshine();
	$sunshine->shipping = new SunshineShipping();
	$sunshine->cart = new SunshineCart();

	if ( isset( $_POST['state'] ) ) {
		if ( isset( $_POST['shipping_method'] ) )
			$return['shipping_method'] = sanitize_text_field( $_POST['shipping_method'] );
		$return['shipping'] = sunshine_money_format( $sunshine->cart->shipping_method['cost'],false );
		$return['tax'] = sunshine_money_format( $sunshine->cart->tax,false );
		$return['credits'] = '-'.sunshine_money_format( $sunshine->cart->useable_credits,false );
		$return['total'] = sunshine_money_format( $sunshine->cart->total,false );
		if ( $sunshine->cart->total == 0 )
			$return['free'] = 1;
	}

	die( json_encode( $return ) );
}

/**
 * Ajax request when user chooses to use available credits
 *
 * @since 1.4
 * @return void
 */
add_action( 'wp_ajax_sunshine_checkout_use_credits', 'sunshine_checkout_use_credits' );
function sunshine_checkout_use_credits() {
	global $sunshine;
	$status = $sunshine->cart->toggle_use_credit();
	$sunshine->cart->set_cart();
	$return['status'] = $status;
	$return['credits'] = '-'.sunshine_money_format( $sunshine->cart->useable_credits,false );
	$return['total'] = sunshine_money_format( $sunshine->cart->total,false );
	if ( $sunshine->cart->total == 0 )
		$return['free'] = 1;
	else
		$return['free'] = 0;
	die( json_encode( $return ) );
}

/**
 * Listening for when checkout is submitted and order is $0
 *
 * @since 1.2
 * @return void
 */
//add_action( 'sunshine_checkout_validation', 'sunshine_process_free_order' );
function sunshine_process_free_order() {
	global $sunshine;
	if ( isset( $_POST['sunshine_checkout'] ) && $_POST['sunshine_checkout'] == 1 && empty( $sunshine->errors ) && $sunshine->cart->total <= 0 ) {
		SunshineOrder::process_free_payment();
	}
}

/**
 * Add Terms of User field if enabled
 *
 * @since 2.1
 * @return void
 */
add_action( 'sunshine_after_checkout_steps', 'sunshine_terms_checkout_fields' );
function sunshine_terms_checkout_fields() {
	global $sunshine;
	if ( $sunshine->options['require_terms'] ) {
?>	
	<li id="sunshine-checkout-step-terms">
		<fieldset id="sunshine-payment-methods">
			<h2><?php _e( 'Agree to Terms', 'sunshine' ); ?></h2>
			<div style="height: 200px; overflow-y: scroll; border: 1px solid #EFEFEF; padding: 10px;">
				<?php echo nl2br( $sunshine->options['terms'] ); ?>
			</div>
			<label><input type="checkbox" name="terms" value="1" <?php checked( ( isset( $_POST['terms'] ) ? $_POST['terms'] : '' ), 1 ); ?> /> <?php _e( 'I agree to the above terms', 'sunshine' ); ?></label>
		</fieldset>
	</li>
<?php	
	}
}


/**********************
	REGISTRATION / LOGIN
***********************/

/**
 * Customize new user registration only when registering from Sunshine related registration page
 *
 * @since 1.0
 * @param int $user_id User ID
 * @param string $plaintext_pass Password in plaintext
 * @return void
 */
if ( isset( $_GET['sunshine'] ) ) {
	if ( !function_exists( 'wp_new_user_notification' ) && $_GET['sunshine'] == 1 ) {
		function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
			global $sunshine;
			$user = new WP_User( $user_id );
			$user_login = stripslashes( $user->user_login );
			$user_email = stripslashes( $user->user_email );

			$message  = __( 'New user registration on your Sunshine Photo Cart','sunshine' ) . "\r\n\r\n";
			$message .= sprintf( __( 'E-mail: %s' ), $user_email ) . "\r\n";
			$message .= sprintf( __( 'Name: %s' ), $user->first_name.' '.$user->last_name ) . "\r\n";

			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), get_option( 'blogname' ) ), $message );

			if ( empty( $_POST['password'] ) )
				return;

			$search = array( '[username]', '[password]', '[email]' );
			$replace = array( $user_login, $_POST['password'], $user_email );
			$mail_result = SunshineEmail::send_email( 'register', $user_email, $sunshine->options['email_subject_register'], $sunshine->options['email_subject_register'], $search, $replace );

		}
	}
}

/**
 * After user registers, log then in automatically and change all session cart values to meta data
 *
 * @since 1.0
 * @param int $user_id User ID
 * @return void
 */
add_action( 'user_register','sunshine_after_register' );
function sunshine_after_register( $user_id ) {
	global $sunshine;

	$userdata = array();
	$userdata['ID'] = $user_id;
	if ( isset( $_POST['password'] ) && $_POST['password'] != '' ) {
		$userdata['user_pass'] = sanitize_text_field( $_POST['password'] );
		wp_update_user( $userdata );
	}

	if ( !is_admin() ) {
		$user = new WP_User( $user_id );
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );
		$sunshine->add_message( __( 'Thank you for registering! You have been automatically logged into your new account','sunshine' ) );
		$cart = SunshineSession::instance()->cart;
		if ( is_array( $cart ) ) {
			$sunshine->cart->empty_cart();
			foreach ( $cart as $item )
				SunshineUser::add_user_meta_by_id( $user_id, 'cart', $item, false );
			$discounts = SunshineSession::instance()->discounts;
			if ( is_array( $discounts ) ) {
				foreach ( $discounts as $discount )
					SunshineUser::add_user_meta_by_id( $user_id, 'discount', $discount, false );
			}
		}
	}
}

/**
 * After logging in, adjust cart cookie as needed
 * If items in cart, keep those items in cart regardless of what was saved to user account
 * If no items in cart but items in user's saved cart, add those back into the current cart
 *
 * @since 1.0
 * @param string $user_login Username
 * @param object $user WP_User
 * @return void
 */
add_action( 'wp_login', 'sunshine_after_login', 10, 2 );
function sunshine_after_login( $user_login, $user ) {
	global $sunshine;
	if ( !is_admin() ) {
		if ( isset( $_COOKIE['sunshine_cart_hash'] ) ) {
			$cart = get_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'] );
		}
		if ( !empty( $cart ) ) {
			$sunshine->cart->empty_products( $user->ID );
			foreach ( $cart as $item )
				SunshineUser::add_user_meta_by_id( $user->ID, 'cart', $item, false );
				
			$shipping_method = SunshineSession::instance()->shipping_method;
			SunshineUser::add_user_meta_by_id( $user->ID, 'shipping_method', $shipping_method );
			
			$discounts = SunshineSession::instance()->discounts;
			if ( is_array( $discounts ) ) {
				foreach ( $discounts as $discount ) {
					SunshineUser::add_user_meta_by_id( $user->ID, 'discount', $discount, false );
				}
			}
			//$sunshine->add_message( sprintf( __( 'You are now logged in as <strong>%s</strong>','sunshine' ), $user->user_login ) );
		}
	}
}

/**
 * After logging out, clear the cart come cookies
 *
 * @since 1.0
 * @param string $user_login Username
 * @param object $user WP_User
 * @return void
 */

add_action( 'wp_logout', 'sunshine_after_logout' );
function sunshine_after_logout() {
	global $sunshine, $current_user;
	$sunshine->cart->set_cart_cookies( false, 'empty_products' );
}


/**
 * Add password field on registration form
 *
 * @since 1.0
 * @return void
 */
add_action( 'register_form', 'sunshine_show_extra_register_fields' );
function sunshine_show_extra_register_fields(){
?>
	<p>
	<label for="password"><?php _e( 'Password','sunshine' ); ?><br/>
	<input id="password" class="input" type="password" size="25" value="" name="password" />
	</label>
	</p>
	<input type="hidden" value="<?php echo $_GET['redirect_to']; ?>" name="redirect_to" />

<?php
	do_action( 'sunshine_register_fields' );
}

/**
 * Error checking for new password field on registration form
 *
 * @since 1.0
 * @param string $login Username
 * @param string $email Email address
 * @param array $errors Errors
 * @return void
 */
add_action( 'register_post', 'sunshine_check_extra_register_fields', 10, 3 );
function sunshine_check_extra_register_fields( $login, $email, $errors ) {
	if ( strlen( $_POST['password'] ) < 6 ) {
		$errors->add( 'password_too_short', '<strong>ERROR</strong>: '.__( 'Passwords must be at least six characters long','sunshine' ) );
	}
}

/**
 * Add hidden field on login form to identify when it is for Sunshine
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_form', 'sunshine_login_form' );
function sunshine_login_form() {
	if ( ( isset( $_GET['sunshine'] ) && $_GET['sunshine'] == 1 ) || ( isset( $_POST['sunshine'] ) && $_POST['sunshine'] == 1 ) )
		echo '<input type="hidden" name="sunshine" value="1" />';
}

/**
 * Add custom logo image to login/registration form when Sunshine related
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_head', 'sunshine_custom_login_logo' );
add_action( 'login_enqueue_scripts', array( 'SunshineFrontend','frontend_cssjs' ), 1 );
function sunshine_custom_login_logo() {
	global $sunshine;

	if ( ( isset( $_GET['sunshine'] ) && $_GET['sunshine'] == 1 ) || ( isset( $_POST['sunshine'] ) && $_POST['sunshine'] == 1 ) ) {

		if ( $sunshine->options['template_logo'] > 0 ) {
			$logo = wp_get_attachment_image_src( $sunshine->options['template_logo'], 'full' );
			if ( $logo[1] > 320 ) {
				$logo[2] = $logo[2] * ( 320 / $logo[1] );
				$logo[1] = 320;
			}
			echo '<style type="text/css">
			h1 a { background: url('.$logo[0].') center top no-repeat !important; width: 100% !important; height: '.$logo[2].'px !important; background-size: contain !important;  }
			</style>';
		}

	}

}

add_filter( 'login_headerurl', 'sunshine_custom_login_url' );
function sunshine_custom_login_url( $url ) {
	if ( ( isset( $_GET['sunshine'] ) && $_GET['sunshine'] == 1 ) || ( isset( $_POST['sunshine'] ) && $_POST['sunshine'] == 1 ) ) {
		return sunshine_url( 'home' );
	}
	return $url;
}


/**
 * Hide the username text when registering, we want to show username or email text
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_head', 'sunshine_register_hide_username' );
function sunshine_register_hide_username() {
?>
    <style>
        #registerform > p:first-child{
            display:none;
        }
    </style>

    <script type="text/javascript" src="<?php echo site_url( '/wp-includes/js/jquery/jquery.js' ); ?>"></script>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('#registerform > p:first-child').css('display', 'none');
        });
    </script>
<?php
}

/**
 * Disable username errors on registration, we will handle it ourselves
 *
 * @since 1.0
 * @return WP_Error
 */
add_filter( 'registration_errors', 'sunshine_register_errors' );
function sunshine_register_errors( $wp_error ) {
	if( isset( $wp_error->errors['empty_username'] ) ){
		unset( $wp_error->errors['empty_username'] );
	}

	if( isset( $wp_error->errors['username_exists'] ) ){
		unset( $wp_error->errors['username_exists'] );
	}
	return $wp_error;
}

/**
 * When user registers, make their login their email address
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_form_register', 'sunshine_login_form_register' );
function sunshine_login_form_register() {
	if( isset( $_POST['user_login'] ) && isset( $_POST['user_email'] ) && !empty( $_POST['user_email'] ) ){
		$_POST['user_login'] = $_POST['user_email'];
	}
}

/**
 * Show text saying they can login with email or username
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_form', 'sunshine_username_or_email_login' );
function sunshine_username_or_email_login() {
	if ( 'wp-login.php' != basename( $_SERVER['SCRIPT_NAME'] ) && !isset( $_GET['sunshine'] ) )
		return;

	?><script type="text/javascript">
	// Form Label
	if ( document.getElementById('loginform') )
		document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = '<?php echo esc_js( __( 'Username or Email', 'email-login' ) ); ?>';

	// Error Messages
	if ( document.getElementById('login_error') )
		document.getElementById('login_error').innerHTML = document.getElementById('login_error').innerHTML.replace( '<?php echo esc_js( __( 'username' ) ); ?>', '<?php echo esc_js( __( 'Username or Email' , 'email-login' ) ); ?>' );
	</script><?php
}

/**
 * Allow users to login with their email address
 *
 * @since 1.0
 * @return WP_User or WP_Error
 */
add_filter( 'authenticate', 'sunshine_allow_email_login', 40, 3 );
function sunshine_allow_email_login( $user, $username, $password ) {
	if ( !empty( $username ) ) {
		$user = get_user_by( 'email', $username );
		if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
			$username = $user->user_login;
	}
	return wp_authenticate_username_password( null, $username, $password );
}


/**********************
	ACCOUNT AREA
***********************/
/**
 * Listen for account update request, update account
 *
 * @since 1.0
 * @return void
 */
add_action( 'init', 'sunshine_update_account' );
function sunshine_update_account() {
	global $current_user, $sunshine;
	if ( isset( $_POST['sunshine_update_account'] ) && $_POST['sunshine_update_account'] == 1 ) {
		
		$vars['country'] = isset( $_POST['country'] ) ? $_POST['country'] : '';
		$vars['first_name'] = isset( $_POST['first_name'] ) ? $_POST['first_name'] : '';
		$vars['last_name'] = isset( $_POST['last_name'] ) ? $_POST['last_name'] : '';
		$vars['address'] = isset( $_POST['address'] ) ? $_POST['address'] : '';
		$vars['address2'] = isset( $_POST['address2'] ) ? $_POST['address2'] : '';
		$vars['city'] = isset( $_POST['city'] ) ? $_POST['city'] : '';
		$vars['state'] = isset( $_POST['state'] ) ? $_POST['state'] : '';
		$vars['zip'] = isset( $_POST['zip'] ) ? $_POST['zip'] : '';
		
		$vars['shipping_country'] = isset( $_POST['shipping_country'] ) ? $_POST['shipping_country'] : '';
		$vars['shipping_first_name'] = isset( $_POST['shipping_first_name'] ) ? $_POST['shipping_first_name'] : '';
		$vars['shipping_last_name'] = isset( $_POST['shipping_last_name'] ) ? $_POST['shipping_last_name'] : '';
		$vars['shipping_address'] = isset( $_POST['shipping_address'] ) ? $_POST['shipping_address'] : '';
		$vars['shipping_address2'] = isset( $_POST['shipping_address2'] ) ? $_POST['shipping_address2'] : '';
		$vars['shipping_city'] = isset( $_POST['shipping_city'] ) ? $_POST['shipping_city'] : '';
		$vars['shipping_state'] = isset( $_POST['shipping_state'] ) ? $_POST['shipping_state'] : '';
		$vars['shipping_zip'] = isset( $_POST['shipping_zip'] ) ? $_POST['shipping_zip'] : '';
		
		if ( isset( $_POST['billing_as_shipping'] ) && $_POST['billing_as_shipping'] == 1 ) {
			$vars['country'] = isset( $_POST['shipping_country'] ) ? $_POST['shipping_country'] : '';
			$vars['first_name'] = isset( $_POST['shipping_first_name'] ) ? $_POST['shipping_first_name'] : '';
			$vars['last_name'] = isset( $_POST['shipping_last_name'] ) ? $_POST['shipping_last_name'] : '';
			$vars['address'] = isset( $_POST['shipping_address'] ) ? $_POST['shipping_address'] : '';
			$vars['address2'] = isset( $_POST['shipping_address2'] ) ? $_POST['shipping_address2'] : '';
			$vars['city'] = isset( $_POST['shipping_city'] ) ? $_POST['shipping_city'] : '';
			$vars['state'] = isset( $_POST['shipping_state'] ) ? $_POST['shipping_state'] : '';
			$vars['zip'] = isset( $_POST['shipping_zip'] ) ? $_POST['shipping_zip'] : '';
		}

		$vars['billing_as_shipping'] = isset( $_POST['billing_as_shipping'] ) ? $_POST['billing_as_shipping'] : '';
		$vars['phone'] = isset( $_POST['phone'] ) ? $_POST['phone'] : '';

		foreach ( $vars as $key => $item ) {
			SunshineUser::update_user_meta_by_id( $current_user->ID, $key, sanitize_text_field( $item ) );
		}
		$userdata['ID'] = $current_user->ID;
		$userdata['user_email'] = sanitize_email( $_POST['email'] );
		$userdata['first_name'] = sanitize_text_field( $_POST['first_name'] );
		$userdata['last_name'] = sanitize_text_field( $_POST['last_name'] );
		wp_update_user( $userdata );

		$sunshine->add_message( __( 'Account updated','sunshine' ) );
		wp_redirect( sunshine_current_url( false ) );
		exit;
	}
}

/**
 * Notify admin when an order has a comment
 *
 * @since 1.0
 * @return void
 */
if ( !function_exists( 'wp_notify_postauthor' ) ) {
	function wp_notify_postauthor( $comment_id ) {
		global $sunshine;
		$comment = get_comment( $comment_id );
		$order = get_post( $comment->comment_post_ID );
		if ( !is_admin() && get_post_type( $order ) == 'sunshine-order' ) {
			$customer_id = get_post_meta( $order->ID, '_sunshine_customer_id', true );
			$customer = get_user_by( 'id', $customer_id );
			$search = array( '[comment]', '[order_id]', '[customer_name]' );
			$replace = array( nl2br( $comment->comment_content ), $order->ID, $customer->first_name.' '.$customer->last_name );
			SunshineEmail::send_email( 'order_comment_admin', get_bloginfo( 'admin_email' ), $sunshine->options['email_subject_order_comment'], $sunshine->options['email_subject_order_comment'], $search, $replace );
		}
	}
}

/**
 * Get all images from a folder
 *
 * @since 1.0
 * @return array of file names
 */
function sunshine_get_images_in_folder( $folder ) {
	$images = glob( $folder.'/*.[jJ][pP][gG]' );
	$images = apply_filters( 'sunshine_images_in_folder', $images, $folder );
	$i = 0;
	if ( $images ) {
		// ProPhoto hack because they regenerate the Featured Image every time a new PP Theme is activated and save it in our folder
		foreach ( $images as &$image ) {
			if ( strpos( $image, '(pp_' ) !== false )
				unset( $images[$i] );
			$i++;
		}
	}
	return $images;
}

/**
 * Count how many images are in a folder
 *
 * @since 1.0
 * @return number
 */
function sunshine_image_folder_count( $folder ) {
	return count( sunshine_get_images_in_folder( $folder ) );
}

/**********************
GALLERY PASSWORD BOX
***********************/

/**
 * Check if valid password, redirect to gallery if exists
 * From gallery password widget
 *
 * @since 1.0
 * @return void
 */
add_action( 'init', 'sunshine_gallery_password_redirect' );
function sunshine_gallery_password_redirect() {
	global $wpdb, $post;
	if ( !is_admin() && isset( $_POST['sunshine_gallery_password'] ) ) {
		$password = sanitize_text_field( $_POST['sunshine_gallery_password'] );
		$querystr = "
		    SELECT $wpdb->posts.*
		    FROM $wpdb->posts
		    WHERE
				$wpdb->posts.post_status = 'publish'
		    	AND $wpdb->posts.post_type = 'sunshine-gallery'
		    	AND $wpdb->posts.post_password = '$password'
	 	";
		$pageposts = $wpdb->get_results( $querystr, OBJECT );
		if ( $pageposts ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			foreach ( $pageposts as $post ) {
				$hasher = new PasswordHash( 8, true );
				setcookie( 'wp-postpass_' . COOKIEHASH, $hasher->HashPassword( wp_unslash( $password ) ), time() + 10 * DAY_IN_SECONDS, COOKIEPATH );
				wp_safe_redirect( get_permalink( $post->ID ) );
				exit();
			}
		}

		wp_die( __( 'Sorry, no galleries matched that password.','sunshine' ),'No galleries matched password', 'back_link=true' );
		exit();
	}
}

/**********************
	GALLERY EMAIL BOX
***********************/
/**
 * Redirect user back to gallery if successfully providing their email address
 *
 * @since 1.0
 * @return void
 */
add_action( 'template_redirect', 'sunshine_gallery_email_redirect' );
function sunshine_gallery_email_redirect() {
	global $sunshine;
	if ( isset( $_POST['sunshine_gallery_email'] ) ) {
		$email = sanitize_email( $_POST['sunshine_gallery_email'] );
		$gallery_id = intval( $_POST['sunshine_gallery_id'] );
		if ( is_email( $_POST['sunshine_gallery_email'] ) ) {
			$gallery_emails = SunshineSession::instance()->gallery_emails;
			$gallery_emails[] = $gallery_id;
			SunshineSession::instance()->gallery_emails = $gallery_emails;
			$existing_emails = get_post_meta( $gallery_id, 'sunshine_gallery_email' );
			if ( !in_array( $email, $existing_emails ) ) {
				add_post_meta( $gallery_id, 'sunshine_gallery_email', $email );
				do_action( 'sunshine_gallery_email', $email, $gallery_id );
			}
		} else {
			$sunshine->add_error( __( 'Not a valid email address', 'sunshine' ) );
		}
		
		wp_safe_redirect( get_permalink( $gallery_id ) );
		exit();
	}
}


/**********************
IMAGE PAGE
***********************/
/**
 * Force the Client Galleries page to have comments closed when viewing an image
 * We then use comment_form in Sunshine's image.php template because most themes do not have comments setup for the page template
 *
 * @since 1.0
 * @return void
 */
add_filter( 'comments_open', 'sunshine_comments_open', 10, 2 );
function sunshine_comments_open( $open, $post_id ) {
	global $sunshine;
	if ( isset( SunshineFrontend::$current_image->ID ) ) {
		if ( $sunshine->comment_status == 'IN_SUNSHINE' )
			return true;
		else
			return false;
	}
	return $open;
}


/**
 * Allow file extensions 
 *
 * @since 1.8
 * @return array
 */
function sunshine_allowed_file_extensions() {
	$extensions = array( 'jpg' );
	return apply_filters( 'sunshine_allowed_file_extensions', $extensions );
}



/**********************
ADMIN TOOLBAR
***********************/
add_action( 'wp_before_admin_bar_render', 'sunshine_customize_admin_toolbar' );
function sunshine_customize_admin_toolbar() {
    global $wp_admin_bar, $sunshine;
	if ( is_page( $sunshine->options['page'] ) && isset( SunshineFrontend::$current_gallery ) ) {
	    $wp_admin_bar->add_menu( array(
	        'id' => 'edit',
	        'parent' => false,
	        'title' => __( 'Edit Gallery', 'sunshine' ),
	        'href' => admin_url( 'post.php?post=' . SunshineFrontend::$current_gallery->ID . '&action=edit' ),
			'class' => 'ab-item'
	    ) );
	
	}
}

/**********************
CUSTOM IMAGE UPLOAD LOCATION
***********************/
function sunshine_custom_upload_dir( $param ) {
    $id = $_REQUEST['gallery_id'];
    if( !empty( $id ) && 'sunshine-gallery' == get_post_type( $id ) ) {
        $mydir         = '/sunshine/' . $id;
        $param['path'] = $param['basedir'] . $mydir;
        $param['url']  = $param['baseurl'] . $mydir;
    }
    return $param;
}

?>