<?php
add_action( 'edit_user_profile', 'sunshine_admin_user_credits' );
add_action( 'show_user_profile', 'sunshine_admin_user_credits' );
function sunshine_admin_user_credits( $user ) {
	if ( current_user_can( 'sunshine_manage_options' ) ) {
?>
<?php
	}
}

add_action( 'personal_options_update', 'sunshine_admin_user_credits_process' );
add_action( 'edit_user_profile_update', 'sunshine_admin_user_credits_process' );
function sunshine_admin_user_credits_process( $user_id ) {
	SunshineUser::update_user_meta_by_id( $user_id, 'credits', sanitize_text_field( $_POST['sunshine_credits'] ) );
}

add_action( 'show_user_profile', 'sunshine_admin_user_cart' );
add_action( 'edit_user_profile', 'sunshine_admin_user_cart' );
function sunshine_admin_user_cart( $user ) {
	if ( current_user_can( 'sunshine_manage_options' ) ) {
		$items = SunshineUser::get_user_meta_by_id( $user->ID, 'cart', false );
		$orders = get_posts( 'post_type=sunshine-order&meta_key=_sunshine_customer_id&meta_value=' . $user->ID . '&nopaging=1' );
?>
		<h3><?php _e( 'Orders', 'sunshine' ); ?></h3>
		<?php
		if ( $orders ) {
			echo '<ul>';
			foreach ( $orders as $order ) {
				$order_data = maybe_unserialize( get_post_meta( $order->ID, '_sunshine_order_data', true ) );
				$total = sunshine_money_format( $order_data['total'], false );
				echo '<li><a href="post.php?post=' . $order->ID . '&action=edit">' . $order->post_title . '</a>, ' .  date( get_option( 'date_format' ), strtotime( $order->post_date ) ) . ' &mdash; ' . $total . '</li>';
			}
		} else {
			echo '<li><em>' . __( 'User has no orders yet', 'sunshine' ) . '</em></li>';
		}
		?>
		<h3><?php _e( 'Address Information', 'sunshine' ); ?></h3>
		<table class="form-table">
	 	<tr>
	 		<th><?php _e( 'Billing Information', 'sunshine' ); ?></th>
	 		<td>
				<?php
				$address = SunshineUser::get_user_meta_by_id( $user->ID, 'address' );
				$address2 = SunshineUser::get_user_meta_by_id( $user->ID, 'address2' );
				$city = SunshineUser::get_user_meta_by_id( $user->ID, 'city' );
				$state = SunshineUser::get_user_meta_by_id( $user->ID, 'state' );
				$zip = SunshineUser::get_user_meta_by_id( $user->ID, 'zip' );
				$country = SunshineUser::get_user_meta_by_id( $user->ID, 'country' );
				if ( $address ) {
					echo $address . '<br />';
					if ( $address2 ) {
						echo $address2 . '<br />';
					}
					echo $city . ', ' . $state . ' ' . $zip . '<br />' . $country;
				} else {
					echo '<em>' . __( 'No current billing address', 'sunshine' ) . '</em>';
				}
				?>
			</td>
	 	</tr>
	 	<tr>
	 		<th><?php _e( 'Shipping Information', 'sunshine' ); ?></th>
	 		<td>
				<?php
				$address = SunshineUser::get_user_meta_by_id( $user->ID, 'shipping_address' );
				$address2 = SunshineUser::get_user_meta_by_id( $user->ID, 'shipping_address2' );
				$city = SunshineUser::get_user_meta_by_id( $user->ID, 'shipping_city' );
				$state = SunshineUser::get_user_meta_by_id( $user->ID, 'shipping_state' );
				$zip = SunshineUser::get_user_meta_by_id( $user->ID, 'shipping_zip' );
				$country = SunshineUser::get_user_meta_by_id( $user->ID, 'shipping_country' );
				if ( $address ) {
					echo $address . '<br />';
					if ( $address2 ) {
						echo $address2 . '<br />';
					}
					echo $city . ', ' . $state . ' ' . $zip . '<br />' . $country;
				} else {
					echo '<em>' . __( 'No current shipping address', 'sunshine' ) . '</em>';
				}
				?>
			</td>
	 	</tr>
	 	</table>
	 	<h3 id="sunshine-credits"><?php _e( 'Sunshine Gallery Credits for Purchases', 'sunshine' ) ?></h3>
		<table class="form-table">
	 	<tr>
	 		<th><label for="sunshine_credits"><?php _e( 'Credits', 'sunshine' ); ?></label></th>
	 		<td>
				<?php
				$currency_symbol = sunshine_currency_symbol();
				$currency_symbol_format = sunshine_currency_symbol_format();
				$text_field = '<input type="text" name="sunshine_credits" id="sunshine_credits" value="'.esc_attr( SunshineUser::get_user_meta_by_id( $user->ID, 'credits' ) ).'" />';
				echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
				?>
			</td>
	 	</tr>
	 	</table>
	 	<h3 id="sunshine-cart"><?php _e( 'Sunshine Items in Cart', 'sunshine' ) ?></h3>
		<?php if ( $items ) { ?>
			<table id="sunshine-cart-items" width="100%">
			<tr>
				<th class="image"><?php _e( 'Image', 'sunshine' ) ?></th>
				<th class="name"><?php _e( 'Product', 'sunshine' ) ?></th>
				<th class="qty"><?php _e( 'Quantity', 'sunshine' ) ?></th>
				<th class="price"><?php _e( 'Item Price', 'sunshine' ) ?></th>
			</tr>
			<?php foreach ( $items as $item ) { ?>
				<tr class="item">
					<td class="image">
						<?php
				$thumb = wp_get_attachment_image_src( $item['image_id'], 'thumbnail' );
				$image_html = '<a href="'.get_permalink( $item['image_id'] ).'"><img src="'.$thumb[0].'" alt="" class="image-thumb" /></a>';
				echo apply_filters( 'sunshine_cart_image_html', $image_html, $item, $thumb );
?>
					</td>
					<td class="name">
						<?php
				$product = get_post( $item['product_id'] );
				$cat = wp_get_post_terms( $item['product_id'], 'sunshine-product-category' );
?>
						<strong><span class="sunshine-item-cat"><?php echo apply_filters( 'sunshine_cart_item_category', ( isset( $cat[0]->name ) ) ? $cat[0]->name : '', $item ); ?></span> - <span class="sunshine-item-name"><?php echo apply_filters( 'sunshine_cart_item_name', $product->post_title, $item ); ?></span></strong><br />
						<div class="sunshine-item-comments"><?php echo apply_filters( 'sunshine_cart_item_comments', $item['comments'], $item ); ?></div>
					</td>
					<td class="qty">
						<?php echo $item['qty']; ?>
					</td>
					<td class="price">
						<?php sunshine_money_format( $item['price'] ); ?>
					</td>
				</tr>
			<?php } ?>
			</table>
		<?php } else { ?>
			<p><?php _e( 'No items in cart', 'sunshine' ); ?></p>
		<?php } ?>
	<?php }
}


/**
 * Add duplicate link to action list for post_row_actions
 */
add_filter( 'user_row_actions', 'sunshine_user_link_row',10,2 );
function sunshine_user_link_row( $actions, $user ) {
	if ( current_user_can( 'sunshine_manage_options', $user->ID ) ) {
		$actions['sunshine_credits'] = '<a href="user-edit.php?user_id='.$user->ID.'#sunshine-credits">' . __('Credits', 'sunshine') . '</a>';
	}
	return $actions;
}

?>
