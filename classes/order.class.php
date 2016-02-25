<?php
class SunshineOrder extends SunshineSingleton {

	public static function add_order( $data, $email = true ) {
		global $sunshine;

		$order_items = $sunshine->cart->get_cart_by_user( $data['user_id'] );
		foreach ( $order_items as &$item ) {
			$image_name = get_the_title( $item['image_id'] );
			$product = get_post( $item['product_id'] );
			$cat = wp_get_post_terms( $item['product_id'], 'sunshine-product-category' );
			$product_name = apply_filters( 'sunshine_cart_item_category', ( isset( $cat[0]->name ) ) ? $cat[0]->name : '', $item ).' - '.apply_filters( 'sunshine_cart_item_name', $product->post_title, $item );
			$item['image_name'] = $image_name;
			$item['product_name'] = $product_name;
		}
		$order_id = wp_insert_post( array(
				'post_title' => 'Order &ndash; '.date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ) ),
				'post_content' => '',
				'post_type' => 'sunshine-order',
				'post_status' => 'publish',
				'comment_status' => 'open',
				'post_author' => 1
			) );
		wp_update_post( array(
			'ID' => $order_id,
			'post_title' => 'Order #'.$order_id,
			'post_name' => $order_id,
		) );
		$data = apply_filters( 'sunshine_order_data', $data, $order_id );
		$order_items = apply_filters( 'sunshine_order_items', $order_items, $order_id );
		$discount_items = apply_filters( 'sunshine_order_discounts', $sunshine->cart->discount_items, $order_id );
		update_post_meta( $order_id, '_sunshine_order_data', serialize( $data ) );
		update_post_meta( $order_id, '_sunshine_order_items', serialize( $order_items ) );
		update_post_meta( $order_id, '_sunshine_order_discounts', serialize( $discount_items ) );
		update_post_meta( $order_id, '_sunshine_customer_id', $data['user_id'] );
		update_post_meta( $order_id, 'ip', $_SERVER['REMOTE_ADDR'] );

		// Order status
		$status = ( $data['status'] ) ? $data['status'] : 'pending';
		wp_set_post_terms( $order_id, $status, 'sunshine-order-status' );

		// Decrease credits if used
		if ( $data['credits'] ) {
			$available_credits = SunshineUser::get_user_meta_by_id( $data['user_id'], 'credits', true );
			SunshineUser::update_user_meta_by_id( $data['user_id'], 'credits', $available_credits - $data['credits'] );
		}

		// Update discount code usage
		if ( !empty( $sunshine->cart->discount_items ) ) {
			foreach ( $sunshine->cart->discount_items as $discount ) {
				$current_count = get_post_meta( $discount->ID, 'use_count', true );
				update_post_meta( $discount->ID, 'use_count', $current_count + 1 );
			}
		}

		if ( $email ) {
			self::notify( $order_id, $data );
			$sunshine->cart->empty_cart( $data['user_id'] );
		}

		$sunshine->add_message( __( 'Order completed successfully!','sunshine' ) );

		do_action( 'sunshine_add_order_end', $order_id, $data, $order_items );

		return $order_id;
	}

	public static function process_free_payment() {
		global $current_user, $sunshine;

		// Order details
		$order['user_id'] = $current_user->ID;
		$order['shipping_method'] = $sunshine->cart->shipping_method['id'];
		$order['shipping_cost'] = $sunshine->cart->shipping_method['cost'];
		$order['credits'] = $sunshine->cart->useable_credits;
		$order['discount_total'] = $sunshine->cart->discount_total;
		$order['discount_items'] = $sunshine->cart->discount_items;
		$order['tax'] = $sunshine->cart->tax;
		$order['subtotal'] = $sunshine->cart->subtotal;
		$order['total'] = $sunshine->cart->total;
		$order['payment_method'] = 'free';
		$order['status'] = 'new';

		// Contact Info
		$order['email'] = SunshineUser::get_user_meta( 'email' );
		$order['phone'] = SunshineUser::get_user_meta( 'phone' );

		// Billing Info
		$order['first_name'] = SunshineUser::get_user_meta( 'first_name' );
		$order['last_name'] = SunshineUser::get_user_meta( 'last_name' );
		$order['address'] = SunshineUser::get_user_meta( 'address' );
		$order['address2'] = SunshineUser::get_user_meta( 'address2' );
		$order['city'] = SunshineUser::get_user_meta( 'city' );
		$order['state'] = SunshineUser::get_user_meta( 'state' );
		$order['zip'] = SunshineUser::get_user_meta( 'zip' );
		$order['country'] = SunshineUser::get_user_meta( 'country' );

		// Shipping Info
		$order['shipping_first_name'] = SunshineUser::get_user_meta( 'shipping_first_name' );
		$order['shipping_last_name'] = SunshineUser::get_user_meta( 'shipping_last_name' );
		$order['shipping_address'] = SunshineUser::get_user_meta( 'shipping_address' );
		$order['shipping_address2'] = SunshineUser::get_user_meta( 'shipping_address2' );
		$order['shipping_city'] = SunshineUser::get_user_meta( 'shipping_city' );
		$order['shipping_state'] = SunshineUser::get_user_meta( 'shipping_state' );
		$order['shipping_zip'] = SunshineUser::get_user_meta( 'shipping_zip' );
		$order['shipping_country'] = SunshineUser::get_user_meta( 'shipping_country' );

		$order_id = SunshineOrder::add_order( $order );
		wp_redirect( get_permalink( $order_id ) );
		exit;

	}
	
	public static function notify( $order_id ) {
		global $sunshine;
		
		$data = maybe_unserialize( get_post_meta( $order_id, '_sunshine_order_data', true ) );
		$order_items = maybe_unserialize( get_post_meta( $order_id, '_sunshine_order_items', true ) );
		$discount_items = maybe_unserialize( get_post_meta( $order_id, '_sunshine_order_discounts', true ) );
		
		// Send confirmation email
		$user = get_userdata( $data['user_id'] );

		$th_style = 'padding: 0 20px 5px 0; border-bottom: 1px solid #CCC; text-align: left; font-size: 10px; color: #999; text-decoration: none;';
		$td_style = 'padding: 5px 20px 5px 0; text-align: left; font-size: 12px;';
		$items_html = '';
		$items_html = apply_filters( 'sunshine_before_order_receipt_items', $items_html, $order_id, $order_items );
		$items_html .= '<div class="order-items"><h3 style="font-size: 14px;">' . __('Cart Items','sunshine') . '</h3>';
		$items_html .= ' <table border="0" cellspacing="0" cellpadding="0" width="100%">';
		$items_html .= ' <tr><th style="'.$th_style.'">'.__( 'Image','sunshine' ).'</th><th style="'.$th_style.'">'.__( 'Name','sunshine' ).'</th><th style="'.$th_style.'">'.__( 'Quantity','sunshine' ).'</th><th style="'.$th_style.'">'.__( 'Cost','sunshine' ).'</th></tr>';

		foreach ( $order_items as $key => $order_item ) {

			$items_html .= ' <tr>';
			$thumb = wp_get_attachment_image_src( $order_item['image_id'], array( 50,50 ) );
			$image_html = '<img src="'. $thumb[0].'" alt="" width="75" />';
			$items_html .= ' <td style="'.$td_style.'">'.apply_filters( 'sunshine_order_image_html', $image_html, $order_item, $thumb ).'</td>';
			$items_html .= ' <td style="'.$td_style.'">' . $order_item['product_name'] . '<br />'.apply_filters( 'sunshine_order_line_item_comments', $order_item['comments'], $order_id, $order_item ).'</td>';
			$items_html .= ' <td style="'.$td_style.'">' . $order_item['qty'] . '</td>';
			$items_html .= ' <td style="'.$td_style.'">' . sunshine_money_format( $order_item['total'], false ) . '</td>';
			$items_html .= ' </tr>';

		}

		$td_style .= ' font-weight: bold;';
		$th_style = 'padding: 0 20px 5px 0; text-align: left; font-size: 12px;';
		$items_html .= ' <tr><td colspan="3" style="'.$td_style.' text-align: right; border-top: 2px solid #CCC;">' . __('Subtotal', 'sunshine') . '</td><td style="'.$td_style.' border-top: 2px solid #CCC;">'.sunshine_money_format( $data['subtotal'],false ).'</td></tr>';
		$items_html .= ' <tr><td colspan="3" style="'.$td_style.' text-align: right;">' . __('Shipping', 'sunshine') . ' ('.sunshine_get_shipping_method_name( $data['shipping_method'] ).')</td><td style="'.$td_style.'">'.sunshine_money_format( $data['shipping_cost'], false ).'</td></tr>';
		if ( $sunshine->options['tax_location'] && $sunshine->options['tax_rate'] ) {
			$items_html .= ' <tr><td colspan="3" style="'.$td_style.' text-align: right;">' . __('Tax', 'sunshine') . '</td><td style="'.$td_style.'">'.sunshine_money_format( $data['tax'], false ).'</td></tr>';
		}
		if ( $data['discount_total'] > 0 ) {
					$items_html .= ' <tr><td colspan="3" style="'.$td_style.' text-align: right;">' . __('Discounts', 'sunshine') . '</td><td style="'.$td_style.'">'.sunshine_money_format( $data['discount_total'], false ).'</td></tr>';
		}
		if ( $data['credits'] ) {
			$items_html .= ' <tr><td colspan="3" style="'.$td_style.' text-align: right;">' . __('Credits', 'sunshine') . '</td><td style="'.$td_style.'">-'.sunshine_money_format( $data['credits'], false ).'</td></tr>';			
		}
		$items_html .= ' <tr><td colspan="3" style="'.$td_style.' text-align: right; font-size: 16px;">' . __('Total', 'sunshine') . '</td><td style="'.$td_style.' font-size: 16px;">'.sunshine_money_format( $data['total'],false ).'</td></tr>';
		$items_html .= ' </table></div>';

		if ( $data['shipping_first_name'] ) {
			$shipping_address = '<div class="shipping-address"><h3 style="font-size: 14px;">' . __('Ship To','sunshine') . '</h3><p>';
			$shipping_address .= $data['shipping_first_name'].' '.$data['shipping_last_name'].'<br>';
			$shipping_address .= $data['shipping_address'];
			if ( $data['shipping_address2'] )
				$shipping_address .= '<br>'.$data['shipping_address2'];
			$shipping_address .= '<br>'.$data['shipping_city'].', '.$data['shipping_state'].' '.$data['shipping_zip'];
			if ( $data['shipping_country'] )
				$shipping_address .= '<br>'.$data['shipping_country'];
			if ( $data['email'] )
				$shipping_address .= '<br>'.$data['email'];
			if ( $data['phone'] )
				$shipping_address .= '<br>'.$data['phone'];
			$shipping_address .= '</p></div>';
		}

		$search = array( '[message]', '[items]', '[shipping_address]', '[order_id]', '[order_url]', '[first_name]', '[last_name]' );
		$replace = array( nl2br( $sunshine->options['email_receipt'] ), $items_html, $shipping_address, $order_id, get_permalink( $order_id ), $data['first_name'], $data['last_name'] );
		$mail_result = SunshineEmail::send_email( 'receipt', $user->user_email, $sunshine->options['email_subject_order_receipt'], $sunshine->options['email_subject_order_receipt'], $search, $replace );

		if ( $sunshine->options['order_notifications'] )
			$admin_emails = explode( ',',$sunshine->options['order_notifications'] );
		else
			$admin_emails = array( get_bloginfo( 'admin_email' ) );
		foreach ( $admin_emails as $admin_email )
			$mail_result = SunshineEmail::send_email( 'receipt_admin', trim( $admin_email ), sprintf( __( 'Order #%s placed on %s' ), $order_id, get_option( 'blogname' ) ), sprintf( __( '<a href="%s">Order #%s</a> placed on %s' ), admin_url( '/post.php?post=' . $order_id . '&action=edit' ), $order_id, get_option( 'blogname' ) ), $search, $replace );
		
	}

}

// ADD CRON JOB TO CANCEL PENDING PAYPAL ORDERS AFTER X DAYS




?>