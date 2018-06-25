<?php
class SunshineCart {

	public $item_count = 0;
	public $subtotal = 0;
	public $tax_cart = 0;
	public $tax_shipping = 0;
	public $tax = 0;
	public $shipping_extra = 0;
	public $shipping_method = array();
	public $discounts = array(); // Discount IDs that have been applied
	public $discount_items = array(); // Discount full data
	public $discount_total = 0;
	public $credits = 0;
	public $useable_credits = 0;
	public $use_credits = false;
	public $total = 0;
	public $content = array();
	public $default_price_level = 0;

	function __construct() {

		$this->set_cart();
		add_action( 'wp', array( $this, 'maybe_set_cart_cookies' ), 99 ); // Set cookies
		//add_action( 'shutdown', array( $this, 'maybe_set_cart_cookies' ), 0 ); // Set cookies before shutdown and ob flushing

	}

	function set_cart() {
		$this->set_cart_content();
		$this->set_subtotal();
		$this->set_shipping();
		$this->set_tax();
		$this->set_discounts();
		$this->set_discount_items();
		$this->set_discount_total();
		$this->set_credits();
		$this->apply_final_filters();
		$this->set_total();
		$this->set_item_count();
		$this->set_number_format();
	}

	function add_to_cart( $image_id, $product_id, $qty, $price_level, $comments='', $type='', $extra = '' ) {
		global $sunshine;

		$current_cart = $this->content;

		$image_id = intval( $image_id );
		$product_id = intval( $product_id );
		$qty = intval( $qty );
		$price_level = intval( $price_level );
		if ( $image_id > 0 ) {
			$image = get_post( $image_id );
			$gallery_id = $image->post_parent;
		}

		$item = array(
			'image_id' => $image_id,
			'gallery_id' => $gallery_id,
			'product_id' => $product_id,
			'price_level' => $price_level,
			'qty' => $qty,
			'price' => $this->get_product_price( $product_id, $price_level, false ),
			'shipping' => get_post_meta( $product_id, 'sunshine_product_shipping', true ),
			'comments' => sanitize_text_field( $comments ),
			'type' => ( $type ) ? $type : 'image',
			'hash' => md5( time() )
		);
		if ( is_array( $extra ) ) {
			$item = array_merge( $item, $extra );
		}

		$item = apply_filters( 'sunshine_add_to_cart_item', $item );

		// Check if item is in cart already. If so, increase quantity instead of adding new line item
		if ( is_array( $current_cart ) ) {
			foreach ( $current_cart as $key => &$cart_item ) {
				if ( $image_id == $cart_item['image_id'] && $product_id == $cart_item['product_id'] ) {
					if ( apply_filters( 'sunshine_add_to_cart_increment_qty', true, $cart_item, $item ) ) {
						$item = $cart_item; // Make current item the existing cart item
						$item['qty'] = $cart_item['qty'] + $qty;
						SunshineUser::delete_user_meta( 'cart', $cart_item );
						unset( $current_cart[$key] );
					}
				}
			}
		}

		$item['total'] = $item['price'] * $item['qty'];

		if ( $sunshine->options['display_price'] == 'with_tax' && $sunshine->options['price_has_tax'] == 'no' ) {
			$item['price_with_tax'] = $item['price'] + ( $item['price'] * ( $sunshine->options['tax_rate'] / 100 ) );
			$item['total_with_tax'] = $item['price_with_tax'] * $item['qty'];
		}

		if ( !$item ) {
			return false;
		}

		// Add item to the current cart
		$current_cart[] = $item;

		// Update to current cart
		$this->content = $current_cart;
		$this->set_item_count();
		$this->set_subtotal();

		// Set user cart values
		if ( is_user_logged_in() ) {
			$result = SunshineUser::add_user_meta( 'cart', $item, false );
		} else {
			$this->set_cart_cookies( true, 'add_to_cart' );
		}

		do_action( 'sunshine_add_cart_item', $item );

		return true;
	}

	function remove_from_cart( $key ) {
		$cart = $this->get_cart();
		if ( is_user_logged_in() ) {
			$cart_item = $cart[ $key ];
			SunshineUser::delete_user_meta( 'cart', $cart_item );
			SunshineUser::delete_user_meta( 'shipping_method' );
		} else {
			SunshineSession::instance()->cart = $cart;
			SunshineSession::instance()->shipping_method = array();
		}
		unset( $cart[ $key ] );

		if ( empty( $cart ) ) {
			$this->set_cart_cookies( false, 'remove_from_cart' );
		} elseif ( $_COOKIE['sunshine_cart_hash'] ) {
			update_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'], serialize( $cart ) );
		}
	}

	function empty_products( $user_id = '' ) {
		if ( is_user_logged_in() ) {
			SunshineUser::delete_user_meta( 'cart' );
		} elseif ( $user_id ) {
			SunshineUser::delete_user_meta_by_id( $user_id, 'cart' );
		} else {
			$this->set_cart_cookies( false, 'empty_products' );
		}
		$this->content = '';
	}

	function empty_cart( $user_id = '' ) {
		global $current_user;
		if ( !$user_id )
			$user_id = $current_user->ID;
		if ( $user_id ) {
			SunshineUser::delete_user_meta_by_id( $user_id, 'cart' );
			SunshineUser::delete_user_meta_by_id( $user_id, 'shipping_method' );
			SunshineUser::delete_user_meta_by_id( $user_id, 'discount' );
			SunshineUser::delete_user_meta_by_id( $user_id, 'use_credits' );
			SunshineUser::delete_user_meta_by_id( $user_id, 'payment_method' );
		}
		$this->content = '';
		$this->shipping_method = '';
		$this->discounts = '';
		$this->discount_items = '';
		SunshineSession::instance()->shipping_method = array();
		SunshineSession::instance()->discounts = array();
		SunshineSession::instance()->discount_items = array();
		SunshineSession::instance()->cart = array();
		$this->set_cart_cookies( false, 'empty_cart' );
		if ( isset( $_COOKIE['sunshine_cart_hash'] ) ) {
			delete_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'] );
		}
	}

	function set_cart_content() {
		global $current_user;
		if ( $current_user->ID > 0 ) {
			$this->content = SunshineUser::get_user_meta( 'cart', false );
		} elseif ( isset( $_COOKIE['sunshine_cart_hash'] ) ) {
			$this->content = maybe_unserialize( get_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'] ) );
		}
		sunshine_array_sort_by_column( $this->content, 'image_id' );
	}

	function get_cart() {
		return $this->content;
	}

	function get_cart_by_user( $user_id ) {
		if ( $user_id > 0 ) {
			return SunshineUser::get_user_meta_by_id( $user_id, 'cart', false );
		}
		return;
	}

	/*
	* Original Source: WooCommerce
	*/
	function maybe_set_cart_cookies() {
		if ( ! headers_sent() ) {
			if ( !empty( $this->content ) ) {
				$this->set_cart_cookies( true, 'maybe_set_cart_cookies' );
			}
		}
	}

	/**
	 * Set cart hash cookie and items in cart.
	 *
	 * @param bool $set (default: true)
	 */
	function set_cart_cookies( $set = true, $where = 'unknown' ) {
		if ( empty( $this->content ) ) {
			return;
		}
		if ( $set ) {
			if ( isset( $_COOKIE['sunshine_cart_hash'] ) ) {
				$hash = $_COOKIE['sunshine_cart_hash'];
			} else {
				$hash = md5( time() . json_encode( $this->content ) . uniqid() );
			}
			sunshine_setcookie( 'sunshine_cart_hash', $hash, time() + ( 30 * DAY_IN_SECONDS ) );
			update_option( 'sunshine_cart_hash_' . $hash, $this->content );
		} elseif ( isset( $_COOKIE['sunshine_cart_hash'] ) ) {
			sunshine_setcookie( 'sunshine_cart_hash', '', time() - ( 30 * DAY_IN_SECONDS ) );
			delete_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'] );
			unset( $_COOKIE['sunshine_cart_hash'] );
		}
	}


	public function set_discounts() {
		global $sunshine;

		$this->discounts = SunshineSession::instance()->discounts;
		if ( !empty( $this->content ) ) {
			$auto_discounts = get_posts( array(
				'post_type' => 'sunshine-discount',
				'nopaging' => 1,
				'meta_key' => 'auto',
				'meta_value' => 1
			) );
			if ( is_array( $auto_discounts ) ) {
				foreach( $auto_discounts as $auto_discount ) {
					$code = get_post_meta( $auto_discount->ID, 'code', true );
					if ( empty( $this->discounts ) || !in_array( $auto_discount->ID, $this->discounts ) ) {
						$result = $this->apply_discount( $code, false );
					}
				}
			}
		}

		$this->discounts = apply_filters( 'sunshine_after_set_discounts', $this->discounts );

	}

	public function set_discount_items() {

		if ( !empty( $this->discounts ) ) {
			$this->discount_items = array();
			$ids = array( 0 );
			foreach ( $this->discounts as $discount_id ) {
				if ( is_numeric( $discount_id ) ) {
					$ids[] = $discount_id;
				}
			}
			$discounts = get_posts( 'post_type=sunshine-discount&include='.join( ',',$ids ) );
			foreach ( $discounts as $discount ) {
				$d = array();
				$d['ID'] = $discount->ID;
				$d['name'] = $discount->post_title;
				$d['discount_applied'] = 0;
				$meta = get_post_meta( $discount->ID );
				foreach ( $meta as $key => $m ) {
					$d[ $key ] = maybe_unserialize( $m[ 0 ] );
				}
				$d = (object) $d;
				$this->discount_items[] = $d;
			}
		}

		$this->discount_items = apply_filters( 'sunshine_after_set_discount_items', $this->discount_items );

	}

	function add_free_shipping_method( $methods ) {
		$methods['free'] = array(
			'id' => 'free',
			'title' => __( 'Free shipping via discount code','sunshine' ),
			'taxable' => 0,
			'cost' => 0
		);
		$this->shipping_method = $methods['free'];
		return $methods;
	}

	function set_credits() {
		$this->credits = SunshineUser::get_user_meta( 'credits' );
		$this->use_credits = SunshineUser::get_user_meta( 'use_credits' );
	}

	public function set_item_count() {
		$this->item_count = 0;
		if ( is_array( $this->content ) ) {
			foreach ( $this->content as $item ) {
				$this->item_count += $item['qty'];
			}
		}
	}

	public function set_subtotal() {
		// Subtotal
		$subtotal = 0;
		if ( $this->content ) {
			foreach ( $this->content as $item ) {
				$subtotal += $item['total'];
			}
		}
		$this->subtotal = apply_filters( 'sunshine_after_set_subtotal', $subtotal );
	}

	public function set_tax() {
		global $sunshine;
		// Get tax
		if ( ( isset( $this->shipping_method['id'] ) && $this->shipping_method['id'] == 'pickup' ) || ( !empty( $sunshine->options['tax_location'] ) && !empty( $sunshine->options['tax_rate'] ) ) ) {
			$taxes = $this->get_cart_taxes();
			$this->tax_cart = $taxes['cart'];
			$this->tax_shipping = $taxes['shipping'];
			$this->tax = $taxes['cart'] + $taxes['shipping'];
		}
	}

	public function set_shipping() {
		global $sunshine, $current_user;
		if ( !$current_user ) return;

		if ( !isset( $this->shipping_method['cost'] ) )
			$this->shipping_method['cost'] = 0;

		$user_shipping_method = SunshineUser::get_user_meta( 'shipping_method' );
		$shipping_methods = $sunshine->shipping->methods;
		if ( isset( $shipping_methods[$user_shipping_method] ) )
			$shipping_method = $shipping_methods[$user_shipping_method];
		if ( is_array( $this->content ) ) {
			foreach ( $this->content as $item ) {
				if ( isset( $item['shipping'] ) && $item['shipping'] > 0 )
					$this->shipping_extra += $item['shipping'];
			}
		}
		if ( isset( $shipping_method ) && is_array( $shipping_method ) ) {
			$this->shipping_method = $shipping_method;
			if ( $shipping_method['id'] == 'flat_rate' )
				$this->shipping_method['cost'] += $this->shipping_extra;
		}

		if ( !empty( $this->shipping_method['id'] ) ) {
			$this->shipping_method['cost'] = apply_filters( 'sunshine_shipping_set_shipping_cost', $this->shipping_method['cost'], $this );
		}

		$this->shipping_method = apply_filters( 'sunshine_after_set_shipping', $this->shipping_method );

	}

	function discount_valid_min_amount( $min_amount ) {
		if ( $min_amount > 0 && $this->subtotal < $min_amount )
			return false;
		return true;
	}

	function discount_valid_start_date( $start_date ) {
		$today = date( 'Y-m-d' );
		if ( $start_date != '' && $start_date > $today )
			return false;
		return true;
	}

	function discount_valid_end_date( $end_date ) {
		$today = date( 'Y-m-d' );
		if ( $end_date != '' && $end_date <= $today )
			return false;
		return true;
	}

	function discount_valid_max_uses( $code, $max_uses ) {
		if ( $max_uses > 0 ) {
			// Look for any order that has the discount code in the meta data
			$args = array(
				'post_type' => 'sunshine-order',
				'tax_query' => array(
					array(
						'taxonomy' => 'sunshine-order-status',
						'field' => 'slug',
						'terms' => array( 'cancelled', 'pending' ),
						'operator'  => 'NOT IN'
					)
				),
				'meta_query' => array(
					array(
						'key' => '_sunshine_order_data',
						'value' => $code,
						'compare' => 'LIKE'
					)
				)
			);
			$orders = get_posts( $args );
			if ( count( $orders ) >= $max_uses )
				return false;
		}
		return true;
	}

	function discount_valid_max_uses_per_person( $code, $max_per_person ) {
		global $current_user;
		if ( $max_per_person > 0 ) {
			// Look for any order that has the discount code in the meta data
			$args = array(
				'post_type' => 'sunshine-order',
				'tax_query' => array(
					array(
						'taxonomy' => 'sunshine-order-status',
						'field' => 'slug',
						'terms' => array( 'cancelled', 'pending' ),
						'operator'  => 'NOT IN'
					)
				),
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_sunshine_order_data',
						'value' => $code,
						'compare' => 'LIKE'
					),
					array(
						'key' => '_sunshine_customer_id',
						'value' => $current_user->ID
					)
				)
			);
			$orders = get_posts( $args );
			if ( count( $orders ) >= $max_per_person )
				return false;
		}
		return true;
	}

	public function set_discount_total( $before_tax = false ) {
		global $sunshine;

		$discount_total = 0;
		$redo_taxes = false;
		foreach ( $this->discount_items as $discount ) {

			// Check minimum order amount
			if ( !$this->discount_valid_min_amount( $discount->min_amount ) )
				continue;

			// Check start/end date
			if ( !$this->discount_valid_start_date( $discount->start_date ) )
				continue;
			if ( !$this->discount_valid_end_date( $discount->end_date ) ) {
				$sunshine->add_error( sprintf( __( 'Discount %s has now expired and has been removed from your cart','sunshine' ), $discount->name ) );
				$this->remove_discount( $discount->ID, false );
				continue;
			}

			// Check max uses
			if ( $discount->max_uses > 0 && !$this->discount_valid_max_uses( $discount->code, $discount->max_uses ) ){
				$sunshine->add_error( sprintf( __( 'Discount %s has now exceeded the maximum uses and has been removed from your cart','sunshine' ), $discount->name ) );
				$this->remove_discount( $discount->ID, false );
				continue;
			}

			if ( $discount->max_uses_per_person > 0 && !$this->discount_valid_max_uses_per_person( $discount->code, $discount->max_uses_per_person ) ) {
				continue;
			}

			$galleries = get_post_meta( $discount->ID, 'galleries', true );
			if ( is_array( $galleries ) ) {
				$gallery_match = false;
				foreach ( $this->content as $item ) {
					if ( isset( $item['gallery_id'] ) && in_array( $item['gallery_id'], $galleries ) ) {
						$gallery_match = true;
					}
				}
				if ( !$gallery_match ) {
					$this->remove_discount( $discount->ID, false );
					continue;
				}
			}

			$free_shipping = get_post_meta( $discount->ID, 'free_shipping', true );
			if ( $free_shipping ) {
				add_filter( 'sunshine_shipping_methods', array( $this,'add_free_shipping_method' ), 5 );
			}

			// Passed all the tests!
			switch ( $discount->discount_type ) {
				case 'percent-total':
					if ( $discount->before_tax == 1 )
						$this_discount = $this->subtotal * ( $discount->amount / 100 );
					else
						$this_discount = ( $this->subtotal + $this->tax_cart ) * ( $discount->amount / 100 );
					$discount_total = $discount_total + $this_discount;
					break;
				case 'amount-total':
					if ( $discount_total > $this->subtotal )
						$this_discount = $this->subtotal;
					else
						$this_discount = $discount->amount;
					$discount_total = $discount_total + $this_discount;
					break;
				case 'percent-product':
					$discount_items = array();
					foreach ( $this->content as $item ) {
						if ( is_array( $discount->galleries ) && isset( $item['image_id'] ) ) {
							$image = get_post( $item['image_id'] );
							if ( !in_array( $image->post_parent, $discount->galleries ) )
								continue;
						}
						if ( empty( $discount_items[ $item[ 'product_id' ] ] ) ) {
							$discount_items[ $item[ 'product_id' ] ] = $item[ 'qty' ];
						} else {
							$discount_items[ $item[ 'product_id' ] ] += $item[ 'qty' ];
						}
						$discount_products[ $item[ 'product_id' ] ] = $item;
					}

					if ( !empty( $discount_items ) ) {
						foreach ( $discount_items as $product_id => $item ) {
							if ( $this->product_can_be_discounted( $product_id, $discount ) ) {
								if ( $discount->max_product_quantity > 0 && $discount_items[ $product_id ] > $discount->max_product_quantity ) {
									$price_to_discount = $discount_products[ $product_id ]['price'] * $discount->max_product_quantity;
								} else {
									$price_to_discount = $discount_products[ $product_id ]['price'] * $discount_items[ $product_id ];
								}
								$discount->discount_applied += $price_to_discount * ( $discount->amount / 100 );
								$discount_total = $discount_total + ( $price_to_discount * ( $discount->amount / 100 ) );
							}
						}
					}

					break;
				case 'amount-product':

					$discount_items = array();
					foreach ( $this->content as $item ) {
						if ( is_array( $discount->galleries ) && isset( $item['image_id'] ) ) {
							$image = get_post( $item['image_id'] );
							if ( !in_array( $image->post_parent, $discount->galleries ) )
								continue;
						}
						if ( empty( $discount_items[ $item[ 'product_id' ] ] ) ) {
							$discount_items[ $item[ 'product_id' ] ] = $item[ 'qty' ];
						} else {
							$discount_items[ $item[ 'product_id' ] ] += $item[ 'qty' ];
						}
					}

					if ( !empty( $discount_items ) ) {
						foreach ( $discount_items as $product_id => $qty ) {
							if ( $this->product_can_be_discounted( $product_id, $discount ) ) {
								if ( $discount->max_product_quantity > 0 && $qty > $discount->max_product_quantity ) {
									$discount_item_amount = $discount->max_product_quantity * $discount->amount;
								}
								else {
									$discount_item_amount = $discount->amount * $qty;
								}
								$discount->discount_applied += $price_to_discount * ( $discount->amount / 100 );
								$discount_total += $discount_item_amount;
							}
						}
					}

					break;
				default:
					break;

			}

			if ( $discount->before_tax ) {
				$redo_taxes = true;
			}

		}

		$discount_total = apply_filters( 'sunshine_discount_total', $discount_total, $this );

		if ( $discount_total > ( $this->subtotal + $this->tax ) )
			$discount_total = $this->subtotal + $this->tax;

		$this->discount_total = $discount_total;

		if ( $redo_taxes ) {
			$this->set_tax();
		}

	}

	function product_can_be_discounted( $product_id, $discount ) {
		if ( is_array( $discount->allowed_products ) ) {
			if ( !in_array( $product_id, $discount->allowed_products ) )
				return false;
		}
		if ( is_array( $discount->disallowed_products ) ) {
			if ( in_array( $product_id, $discount->disallowed_products ) )
				return false;
		}
		$categories = get_the_terms( $product_id, 'sunshine-product-category' );
		if ( $categories ) {
			foreach ( $categories as $category ) {
				if ( is_array( $discount->allowed_categories ) && !in_array( $category->term_id, $discount->allowed_categories ) )
					return false;
				if ( is_array( $discount->disallowed_categories ) && in_array( $category->term_id, $discount->disallowed_categories ) )
					return false;
			}
		}

		return true;
	}


	public function set_total() {

		$this->total = $this->subtotal + $this->tax + $this->shipping_method['cost'] - $this->discount_total;

		if ( $this->use_credits ) {
			// Let's make sure we don't apply more credit than the order total
			if ( $this->total > $this->credits ) {
				$this->total = $this->total - $this->credits;
				$this->useable_credits = $this->credits;
			} else {
				$this->useable_credits = $this->total;
				$this->total = 0;
			}
		}

	}

	// Retrieval functions
	public function get_product_price( $product_id, $price_level, $formatted = true ) {
		$price = get_post_meta( $product_id, 'sunshine_product_price_' . $price_level, true );
		$price = str_replace( ',', '.', $price );
		$result = '';

		if ( $price ) {
			if ( $formatted ) {
				$result = sunshine_money_format( $price, false );
			}
			else
				$result = $price;
		} else {
			if ( $formatted )
				$result = '<span class="sunshine-free">' . __('Free', 'sunshine') . '</span>';
			else
				$result = '0';
		}
		return $result;
	}

	public function get_product_display_price( $product_id, $price_level, $formatted = true ) {
		global $sunshine;
		if ( $sunshine->options['display_price'] == 'with_tax' && $sunshine->options['price_has_tax'] == 'no' ) {
			$price = $this->get_product_price( $product_id, $price_level, false );
			$price = $price + ( $price * ( $sunshine->options['tax_rate'] / 100 ) );
			if ( $formatted ) {
				$result = sunshine_money_format( $price, false );
			}
			else {
				$result = $price;
			}
		} else {
			$result = $this->get_product_price( $product_id, $price_level, $formatted );
		}
		if ( !empty( $sunshine->options['price_with_tax_suffix'] ) ) {
			$result .= ' <small class="sunshine-price-tax-suffix">' . $sunshine->options['price_with_tax_suffix'] . '</small>';
		}
		return $result;
	}

	function get_line_item_price( $item, $sign = 1, $echo = 1 ) {
		$price = 0;
		if ( $item['product_id'] > 0 )
			$price = $this->get_product_price( $item['product_id'], $item['price_level'], false );
		if ( is_numeric( $price ) )
			$price = $price * $qty;
		$price = apply_filters( 'sunshine_get_line_item_price', $price, $item );
		$price = str_replace( ',', '.', $price );
		if ( $sign )
			$price = sunshine_money_format( $price, false );
		if ( $echo )
			echo $price;
		else
			return $price;
	}

	function get_cart_taxes() {
		global $sunshine;
		$total = $taxable_total = 0;
		if ( !$sunshine->options['tax_rate'] )
			return 0;
		if ( !$sunshine->options['tax_location'] )
			return 0;

		$tax_location_array = explode( '|', $sunshine->options['tax_location'] );
		$basis = $sunshine->options['tax_basis'];
		$meta_prefix = '';
		if ( $basis && $basis == 'shipping' ) {
			$meta_prefix = $basis . '_';
		}

		$taxable = true;

		if ( ( isset( $this->shipping_method['id'] ) && $this->shipping_method['id'] == 'pickup' ) ) {

		} elseif ( $basis == 'all' ) {

		} elseif ( isset( $tax_location_array[1] ) ) {
			$tax_location = $tax_location_array[1];
			$tax_state = SunshineUser::get_user_meta( $meta_prefix . 'state' );
			if ( empty( $tax_state ) || $tax_state != $tax_location ) {
				$taxable = false;
			}
		} else {
			$tax_location = $sunshine->options['tax_location'];
			$tax_country = SunshineUser::get_user_meta( $meta_prefix . 'country' );
			if ( empty( $tax_country ) || $tax_country != $tax_location ) {
				$taxable = false;
			}
		}

		$taxable = apply_filters( 'sunshine_taxable', $taxable, $this->content );
		if ( !$taxable ) {
			return 0;
		}

		if ( $this->content ) {
			$order_total = $taxable_total = 0;
			foreach ( $this->content as $item ) {
				$order_total += $item['total'];
				if ( get_post_meta( $item['product_id'], 'sunshine_product_taxable', true ) ) {
					if ( $sunshine->options['tax_entire_order_one_item'] ) {
						$taxable_total = $this->subtotal;
						break;
					}
					$taxable_total += $item['total'];
				}
				elseif ( $item['type'] == 'gallery_download' && $sunshine->options['tax_gallery_download'] ) {
					$taxable_total += $item['total'];
				}
			}
		}

		if ( $this->discount_total > 0 ) {
			foreach ( $this->discount_items as $discount ) {
				if ( $discount->before_tax ) {
					$taxable_total -= $discount->discount_applied;
				}
			}
			// Apply discount to non-taxable items first when discount is greater than taxable total
			if ( $this->discount_total > $taxable_total ) {
				$non_taxable = $order_total - $taxable_total;
				$new_val = $non_taxable - $this->discount_total;
				$new_val2 = $taxable_total - abs( $new_val );
				$taxable_total = $new_val2;
			}
		}

		$taxes = array(
			'cart' => $taxable_total,
			'shipping' => 0
		);

		// If shipping method is taxable
		if ( is_array( $this->shipping_method ) && isset( $this->shipping_method['cost'] ) && isset( $this->shipping_method['taxable'] ) && $this->shipping_method['taxable'] == 1 ) {
			$taxes['shipping'] = $this->shipping_method['cost'];
		}

		foreach ( $taxes as $key => $value ) {
			$taxes[ $key ] = max( 0, $value * ( $sunshine->options['tax_rate'] / 100 ) );
		}

		return $taxes;
	}


	// Display Functions
	function show_item_count() {
		echo $this->item_count;
	}


	public function show_item_price( $product_id, $qty ) {
		$price = $this->get_product_price( $product_id, false );
		if ( is_numeric( $price ) )
			$price = $price * $qty;
		sunshine_money_format( $price );
	}

	function can_add_discount() {
		$can_add = true;
		foreach ( $this->discount_items as $discount ) {
			if ( $discount->solo == 1 )
				$can_add = false;
		}
		return $can_add;
	}

	function is_discount_applied( $code ) {
		$result = false;
		foreach ( $this->discount_items as $discount ) {
			if ( $discount->code == $code ) {
				$result = true;
			}
		}
		return $result;
	}

	function apply_discount( $code, $errors = true ) {
		global $current_user, $sunshine;
		if ( $code ) {
			if ( !$this->can_add_discount() ) {
				$sunshine->add_error( __( 'You are not allowed to add any more discounts to your cart','sunshine' ) );
				return false;
			}

			$args = array(
				'post_type' => 'sunshine-discount',
				'meta_key' => 'code',
				'meta_value' => $code
			);
			$discounts = get_posts( $args );
			if ( !empty( $discounts ) ) {
				foreach ( $discounts as $discount ) {
					// Is it already applied
					if ( is_array( $this->discounts ) && in_array( $discount->ID, $this->discounts ) ) {
						if ( $errors ) $sunshine->add_error( __( 'This discount is already applied', 'sunshine' ) );
						break;
					}
					// Check minimum order amount
					$min_amount = get_post_meta( $discount->ID, 'min_amount', true );
					if ( !$this->discount_valid_min_amount( $min_amount ) ) {
						if ( $errors ) $sunshine->add_error( sprintf( __( 'Your order does not yet meet the minimum order amount (%s) for this discount','sunshine' ), sunshine_money_format( $min_amount, false ) ) );
						break;
					}
					// Check start/end date
					$start_date = get_post_meta( $discount->ID, 'start_date', true );
					if ( !$this->discount_valid_start_date( $start_date ) ) {
						if ( $errors ) $sunshine->add_error( __( 'This coupon is not yet valid, please try again later','sunshine' ) );
						break;
					}
					$end_date = get_post_meta( $discount->ID, 'end_date', true );
					if ( !$this->discount_valid_end_date( $end_date ) ) {
						if ( $errors ) $sunshine->add_error( __( 'This coupon has expired','sunshine' ) );
						break;
					}

					$code = get_post_meta( $discount->ID, 'code', true );

					// Check max uses
					$max_uses = get_post_meta( $discount->ID, 'max_uses', true );
					if ( $max_uses > 0 && !$this->discount_valid_max_uses( $code, $max_uses ) ) {
						if ( $errors ) $sunshine->add_error( __( 'This coupon has exceeded the number of uses allowed','sunshine' ) );
						break;
					}

					$max_uses_per_person = get_post_meta( $discount->ID, 'max_uses_per_person', true );
					if ( $max_uses_per_person > 0 && !$this->discount_valid_max_uses_per_person( $code, $max_uses_per_person ) ) {
						if ( $errors ) $sunshine->add_error( __( 'This coupon has exceeded the number of uses allowed per user','sunshine' ) );
						break;
					}

					$galleries = get_post_meta( $discount->ID, 'galleries', true );
					if ( is_array( $galleries ) ) {
						$gallery_match = false;
						foreach ( $this->content as $item ) {
							if ( isset( $item['gallery_id'] ) && in_array( $item['gallery_id'], $galleries ) ) {
								$gallery_match = true;
							}
						}
						if ( !$gallery_match ) {
							if ( $errors ) $sunshine->add_error( __( 'This discount code does not work with any items in your cart','sunshine' ) );
							continue;
						}
					}

					$this->discounts[] = $discount->ID;
					SunshineSession::instance()->discounts = $this->discounts;
					$sunshine->add_message( '"' . $discount->post_title . '" ' . __( 'discount added','sunshine' ) );

					return true;
				}
			} else {
				$sunshine->add_error( __( 'Not a valid discount code','sunshine' ) );
			}
		}
		return false;
	}

	function remove_discount( $discount_id, $add_message = true ) {
		global $current_user, $sunshine;
		if ( $this->discounts ) {
			if( ( $key = array_search( $discount_id, $this->discounts ) ) !== false ) {
				unset( $this->discounts[$key] );
				SunshineSession::instance()->discounts = $this->discounts;
				if ( $add_message ) $sunshine->add_message( sprintf( __( 'Discount "%s" removed','sunshine' ), get_the_title( $discount_id ) ) );
				return true;
			}
		}
		unset( SunshineSession::instance()->discounts );
		unset( $this->discounts );
		$sunshine->add_error( __( 'Discount not applied to your cart and cannot be removed','sunshine' ) );
		return false;
	}

	function toggle_use_credit() {
		global $current_user;
		if ( SunshineUser::get_user_meta( 'use_credits' ) ) {
			SunshineUser::update_user_meta( 'use_credits', '0' );
			return '0';
		} else {
			SunshineUser::update_user_meta( 'use_credits', '1' );
			return '1';
		}
	}

	function apply_final_filters() {
		$this->subtotal = apply_filters( 'sunshine_cart_subtotal', $this->subtotal, $this );
		$this->tax = apply_filters( 'sunshine_cart_tax', $this->tax, $this );
		$this->shipping_method['cost'] = apply_filters( 'sunshine_cart_shipping_method_cost', $this->shipping_method['cost'], $this );
		$this->discount_total = apply_filters( 'sunshine_cart_discount_total', $this->discount_total, $this );
		$this->total = apply_filters( 'sunshine_cart_total', $this->total, $this );
	}

	function set_number_format() {
		$this->subtotal = number_format( floatval( $this->subtotal ), 2, '.', '' );
		$this->tax = number_format( floatval( $this->tax ), 2, '.', '' );
		$this->shipping_method['cost'] = number_format( floatval( $this->shipping_method['cost'] ), 2, '.', '' );
		$this->discount_total = number_format( floatval( $this->discount_total ), 2, '.', '' );
		$this->total = number_format( floatval( $this->total ), 2, '.', '' );
	}

	function get_default_price_level() {
		if ( $this->default_price_level == 0 ) {
			$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
			$this->default_price_level = $price_levels[0]->term_id;
		}
		return $this->default_price_level;
	}

	public function product_in_cart( $image_id, $product_id ) {
		if ( !is_array( $this->content ) ) return 0;
		foreach ( $this->content as $item ) {
			if ( $item['image_id'] == $image_id && $item['product_id'] == $product_id ) {
				return $item['qty'];
			}
		}
		return 0;
	}

	public function remove_item_in_cart( $hash ) {
		foreach ( $this->content as $key => $cart_item ) {
			if ( $hash == $cart_item['hash'] ) {
				if ( is_user_logged_in() )
					SunshineUser::delete_user_meta( 'cart', $cart_item );
				else {
					unset( $this->content[$key] );
					SunshineSession::instance()->cart = $cart;
				}
				break;
			}
		}
	}


}
?>
