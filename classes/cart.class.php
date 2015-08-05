<?php
class SunshineCart {

	public $item_count = 0;
	public $subtotal = 0;
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

	}

	function set_cart() {
		$this->set_cart_content();
		$this->set_subtotal();
		$this->set_shipping();
		$this->set_credits();
		$this->set_discounts();
		$this->set_discount_items();
		$this->set_discount_total();
		$this->set_tax();
		$this->set_total();
		$this->set_item_count();
		$this->set_number_format();
	}

	function add_to_cart( $image_id, $product_id, $qty, $price_level, $comments='', $type='', $extra = '' ) {

		$current_cart = $this->content;
		
		$image_id = intval( $image_id );
		$product_id = intval( $product_id );
		$qty = intval( $qty );
		$price_level = intval( $price_level );

		$item = array(
			'image_id' => $image_id,
			'product_id' => $product_id,
			'price_level' => $price_level,
			'qty' => $qty,
			'price' => $this->get_product_price( $product_id, $price_level, false ),
			'shipping' => get_post_meta( $product_id, 'sunshine_product_shipping', true ),
			'comments' => $comments,
			'type' => ( $type ) ? $type : 'image',
			'hash' => md5( time() )
		);
		if ( is_array( $extra ) )
			$item = array_merge( $item, $extra );

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

		// Add item to the current cart
		$current_cart[] = $item;

		// Set user cart values
		if ( is_user_logged_in() )
			$result = SunshineUser::add_user_meta( 'cart', $item, false );
		else
			SunshineSession::instance()->cart = $current_cart;

		// Update to current cart
		$this->content = $current_cart;
		$this->set_item_count();

		do_action( 'sunshine_add_cart_item', $item );

		return true;
	}

	function empty_products() {
		if ( is_user_logged_in() )
			SunshineUser::delete_user_meta( 'cart' );
		else
			unset( SunshineSession::instance()->cart );
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
		SunshineSession::instance()->cart = array();
	}

	function set_cart_content() {
		global $current_user;
		if ( $current_user->ID > 0 ) {
			$this->content = SunshineUser::get_user_meta( 'cart', false );
		} else
			$this->content = SunshineSession::instance()->cart;

		sunshine_array_sort_by_column( $this->content, 'type' );
	}

	function get_cart() {
		return $this->content;
	}

	function get_cart_by_user( $user_id ) {
		if ( $user_id > 0 )
			$cart = SunshineUser::get_user_meta_by_id( $user_id, 'cart', false );
		else
			$cart = SunshineSession::instance()->cart;
		return $cart;
	}

	function set_discounts() {
		global $current_user;
		if ( is_user_logged_in() )
			$this->discounts = SunshineUser::get_user_meta( 'discount', false );
		else
			$this->discounts = SunshineSession::instance()->discounts;
	}

	function set_discount_items() {
		if ( !empty( $this->discounts ) ) {
			$ids = array( 0 );
			foreach ( $this->discounts as $discount_id )
				$ids[] = $discount_id;
			$discounts = get_posts( 'post_type=sunshine-discount&include='.join( ',',$ids ) );
			foreach ( $discounts as $discount ) {
				$d = new stdClass;
				$d->ID = $discount->ID;
				$d->name = $discount->post_title;
				$d->code = get_post_meta( $discount->ID, 'code', true );
				$d->discount_type = get_post_meta( $discount->ID, 'discount_type', true );
				$d->amount = get_post_meta( $discount->ID, 'amount', true );
				$d->start_date = get_post_meta( $discount->ID, 'start_date', true );
				$d->end_date = get_post_meta( $discount->ID, 'end_date', true );
				$d->max_uses = get_post_meta( $discount->ID, 'max_uses', true );
				$d->use_count = get_post_meta( $discount->ID, 'use_count', true );
				$d->max_product_quantity = get_post_meta( $discount->ID, 'max_product_quantity', true );
				$d->max_uses_per_person = get_post_meta( $discount->ID, 'max_uses_per_person', true );
				$d->solo = get_post_meta( $discount->ID, 'solo', true );
				$d->free_shipping = get_post_meta( $discount->ID, 'free_shipping', true );
				$d->min_amount = get_post_meta( $discount->ID, 'min_amount', true );
				$d->before_tax = get_post_meta( $discount->ID, 'before_tax', true );
				$d->allowed_products = get_post_meta( $discount->ID, 'allowed_products', true );
				$d->disallowed_products = get_post_meta( $discount->ID, 'disallowed_products', true );
				$d->allowed_categories = get_post_meta( $discount->ID, 'allowed_categories', true );
				$d->disallowed_categories = get_post_meta( $discount->ID, 'disallowed_categories', true );
				$this->discount_items[] = $d;

				if ( $d->free_shipping ) {
					add_filter( 'sunshine_shipping_methods', array( $this,'add_free_shipping_method' ), 5 );
				}
			}
		}
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
			foreach ( $this->content as $item )
				$subtotal = $subtotal + $item['total'];
		}
		$this->subtotal = $subtotal;
	}

	public function set_tax() {
		global $sunshine;
		// Get tax
		$this->tax = 0;
		if ( $sunshine->options['tax_location'] && $sunshine->options['tax_rate'] ) {
			$this->tax = $this->get_cart_taxes();
		}
	}

	public function set_shipping() {
		global $sunshine, $current_user;
		if ( !$current_user ) return;
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
		if ( !isset( $this->shipping_method['cost'] ) )
			$this->shipping_method['cost'] = 0;
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

	function discount_valid_max_uses( $use_count, $max_uses ) {
		if ( $max_uses > 0 && $use_count >= $max_uses )
			return false;
		return true;
	}

	function discount_valid_max_uses_per_person( $code, $max_per_person ) {
		global $current_user;
		if ( $max_per_person > 0 ) {
			// Look for any order that has the discount code in the meta data
			$args = array(
				'post_type' => 'sunshine-order',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_sunshine_order_discounts',
						'value' => '"%'.$code.'%"',
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

	public function set_discount_total() {
		global $sunshine;
		$discount_total = 0;
		foreach ( $this->discount_items as $discount ) {
			// Check minimum order amount
			if ( !$this->discount_valid_min_amount( $discount->min_amount ) )
				break;

			// Check start/end date
			if ( !$this->discount_valid_start_date( $discount->start_date ) )
				break;
			if ( !$this->discount_valid_end_date( $discount->end_date ) ) {
				$sunshine->add_error( sprintf( __( 'Discount %s has now expired and has been removed from your cart','sunshine' ), $discount->name ) );
				$this->remove_discount( $discount->ID, false );
				break;
			}

			// Check max uses
			if ( !$this->discount_valid_max_uses( $discount->use_count, $discount->max_uses ) ){
				$sunshine->add_error( sprintf( __( 'Discount %s has now exceeded the maximum uses and has been removed from your cart','sunshine' ), $discount->name ) );
				$this->remove_discount( $discount->ID, false );
				break;
			}

			if ( !$this->discount_valid_max_uses_per_person( $discount->code, $discount->max_uses_per_person ) )
				break;

			// Passed all the tests!
			switch ( $discount->discount_type ) {
			case 'percent-total':
				if ( $discount->before_tax == 1 )
					$discount_total = $this->subtotal * ( $discount->amount / 100 );
				else
					$discount_total = ( $this->subtotal + $this->tax ) * ( $discount->amount / 100 );
				break;
			case 'amount-total':
				if ( $discount_total > $this->subtotal )
					$discount_total = $this->subtotal;
				else
					$discount_total = $discount->amount;
				break;
			case 'percent-product':
				$product_count = array();
				foreach ( $this->content as $item ) {
					if ( !isset( $product_count[$item['product_id']] ) )
						$product_count[$item['product_id']] = 1;
					if ( $this->product_can_be_discounted( $item['product_id'], $discount ) ) {
						if ( $discount->max_product_quantity > 0 && $product_count[$item['product_id']] > $discount->max_product_quantity ) {
							$price_to_discount = 0;
						} elseif ( $discount->max_product_quantity > 0 && $item['qty'] > $discount->max_product_quantity ) {
							$price_to_discount = $item['price'] * $discount->max_product_quantity;
						} else {
							$price_to_discount = $item['total'];
						}
						if ( $discount->before_tax != 1 && $this->tax > 0 && get_post_meta( $item['product_id'], 'sunshine_product_taxable', true ) ) {
							$tax_discount = ( $sunshine->options['tax_rate'] / 100 ) * $price_to_discount;
							$price_to_discount += $tax_discount;
						}
						$discount_total = $discount_total + ( $price_to_discount * ( $discount->amount / 100 ) );
					}
					$product_count[$item['product_id']] += $item['qty'];
				}
				break;
			case 'amount-product':
				$product_count = array();
				foreach ( $this->content as $item ) {
					if ( !isset( $product_count[$item['product_id']] ) )
						$product_count[$item['product_id']] = 1;

					//echo 'Product Count: '.$product_count[$item['product_id']].' / Max Product Quantity: '.$discount->max_product_quantity.'<br />';

					if ( $this->product_can_be_discounted( $item['product_id'], $discount ) ) {
						if ( $discount->max_product_quantity > 0 && $product_count[$item['product_id']] > $discount->max_product_quantity )
							$discount_item = 0;
						elseif ( $discount->max_product_quantity > 0 && $item['qty'] > $discount->max_product_quantity )
							$discount_item = $discount->max_product_quantity * $discount->amount;
						elseif ( $item['total'] > $discount->amount )
							$discount_item = $discount->amount;
						else
							$discount_item = $discount->amount;
						$discount_total += $discount_item;
					}
					$product_count[$item['product_id']] += $item['qty'];
				}
				break;
			default:
				break;
			}

			$this->discount_total = $this->discount_total + $discount_total;

		}

		if ( $this->discount_total > ( $this->subtotal + $this->shipping_method['cost'] + $this->tax ) )
			$this->discount_total = $this->subtotal + $this->shipping_method['cost'] + $this->tax;

		$this->discount_total = apply_filters( 'sunshine_discount_total', $this->discount_total, $this );

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
		$price = get_post_meta( $product_id, 'sunshine_product_price_'.$price_level, true );
		$result = '';
		if ( $price ) {
			if ( $formatted )
				$result = sunshine_money_format( $price,false );
			else
				$result = $price;
		} else {
			if ( $formatted )
				$result = '<span class="sunshine-free">Free</span>';
			else
				$result = '0';
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
		if ( $sign )
			$price = sunshine_money_format( $price,false );
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
		if ( isset( $tax_location_array[1] ) ) {
			$tax_location = $tax_location_array[1];
			$tax_state = SunshineUser::get_user_meta( 'shipping_state' );
			if ( empty( $tax_state ) || $tax_state != $tax_location )
				return 0;
		} else {
			$tax_location = $sunshine->options['tax_location'];
			$tax_country = SunshineUser::get_user_meta( 'shipping_country' );
			if ( empty( $tax_country ) || $tax_country != $tax_location )
				return 0;
		}

		if ( $this->content ) {
			$order_total = $taxable_total = 0;
			foreach ( $this->content as $item ) {
				$order_total += $item['total'];
				if ( get_post_meta( $item['product_id'], 'sunshine_product_taxable', true ) )
					$taxable_total += $item['total'];
				elseif ( $item['type'] == 'gallery_download' && $sunshine->options['tax_gallery_download'] )
					$taxable_total += $item['total'];
			}
		}

		if ( $this->discount_total > 0 ) {
			// Apply discount to non-taxable items first when discount is greater than taxable total
			if ( $this->discount_total > $taxable_total ) {
				$non_taxable = $order_total - $taxable_total;
				$new_val = $non_taxable - $this->discount_total;
				$new_val2 = $taxable_total - abs( $new_val );
				$taxable_total = $new_val2;
			} else
				$taxable_total -= $this->discount_total;
		}

		if ( $this->credits > 0 && $this->use_credits )
			$taxable_total -= $this->credits;

		// If shipping method is taxable
		if ( is_array( $this->shipping_method ) && isset( $this->shipping_method['cost'] ) && isset( $this->shipping_method['taxable'] ) && $this->shipping_method['taxable'] == 1 )
			$taxable_total += $this->shipping_method['cost'];

		return max( 0, $taxable_total * ( $sunshine->options['tax_rate']/100 ) );
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

	function apply_discount( $code ) {
		global $current_user, $sunshine;
		if ( $code ) {
			if ( !$this->can_add_discount() ) {
				$sunshine->add_error( __( 'You are not allowed to add any more discounts to your cart','sunshine' ) );
				return;
			}
			if ( $this->is_discount_applied( $code ) ) {
				$sunshine->add_error( __( 'This discount is already applied to your cart','sunshine' ) );
				return;
			}

			$args = array(
				'post_type' => 'sunshine-discount',
				'meta_key' => 'code',
				'meta_value' => $code
			);
			$discounts = get_posts( $args );
			if ( $discounts ) {
				foreach ( $discounts as $discount ) {
					// Check minimum order amount
					$min_amount = get_post_meta( $discount->ID, 'min_amount', true );
					if ( !$this->discount_valid_min_amount( $min_amount ) ) {
						$sunshine->add_error( __( 'Your order does not yet meet the minimum order amount for this discount','sunshine' ) );
						break;
					}
					// Check start/end date
					$start_date = get_post_meta( $discount->ID, 'start_date', true );
					if ( !$this->discount_valid_start_date( $start_date ) ) {
						$sunshine->add_error( __( 'This coupon is not yet valid, please try again later','sunshine' ) );
						break;
					}
					$end_date = get_post_meta( $discount->ID, 'end_date', true );
					if ( !$this->discount_valid_end_date( $end_date ) ) {
						$sunshine->add_error( __( 'This coupon has expired','sunshine' ) );
						break;
					}

					// Check max uses
					$use_count = get_post_meta( $discount->ID, 'use_count', true );
					$max_uses = get_post_meta( $discount->ID, 'max_uses', true );
					if ( !$this->discount_valid_max_uses( $use_count, $max_uses ) ) {
						$sunshine->add_error( __( 'This coupon has exceeded the number of uses allowed','sunshine' ) );
						break;
					}

					$code = get_post_meta( $discount->ID, 'code', true );
					$max_uses_per_person = get_post_meta( $discount->ID, 'max_uses_per_person', true );
					if ( !$this->discount_valid_max_uses_per_person( $code, $max_uses_per_person ) ) {
						$sunshine->add_error( __( 'This coupon has exceeded the number of uses allowed per user','sunshine' ) );
						break;
					}

					$this->discounts[] = $discount->ID;
					if ( is_user_logged_in() )
						SunshineUser::add_user_meta( 'discount', $discount->ID, false );
					else
						SunshineSession::instance()->discounts = $this->discounts;
					$sunshine->add_message( '"'.$discount->post_title.'" '.__( 'discount added','sunshine' ) );

					return true;
				}
			} else
				$sunshine->add_error( __( 'Not a valid discount code','sunshine' ) );
		}
		return false;
	}

	function remove_discount( $discount_id, $add_message=true ) {
		global $current_user, $sunshine;
		if ( $this->discounts ) {
			if( ( $key = array_search( $discount_id, $this->discounts ) ) !== false ) {
				unset( $this->discounts[$key] );
				if ( is_user_logged_in() )
					SunshineUser::delete_user_meta( 'discount', $discount_id );
				else
					SunshineSession::instance()->discounts = $this->discounts;
				$sunshine->add_message( __( 'Discount removed','sunshine' ) );
				return true;
			}
		}
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

	function set_number_format() {
		$this->subtotal = number_format( $this->subtotal,2,'.','' );
		$this->tax = number_format( $this->tax,2,'.','' );
		$this->shipping_method['cost'] = number_format( $this->shipping_method['cost'],2,'.','' );
		$this->discount_total = number_format( $this->discount_total,2,'.','' );
		$this->total = number_format( $this->total,2,'.','' );
	}

	function apply_price_filters() {
		$this->subtotal = apply_filters( 'sunshine_cart_subtotal', $this->subtotal );
		$this->tax = apply_filters( 'sunshine_cart_tax', $this->tax );
		$this->shipping_method['cost'] = apply_filters( 'sunshine_cart_shipping_method_cost', $this->shipping_method['cost'] );
		$this->discount_total = apply_filters( 'sunshine_cart_discount_total', $this->discount_total );
		$this->total = apply_filters( 'sunshine_cart_total', $this->total );
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