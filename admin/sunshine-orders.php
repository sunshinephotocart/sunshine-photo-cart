<?php
/* SETUP META BOXES */
add_action( 'add_meta_boxes', 'sunshine_order_meta_boxes' );
function sunshine_order_meta_boxes() {
	global $post;
	
	$screen = get_current_screen();
	//sunshine_dump_var( $screen );
	
	if ( $screen->action == 'add' ) {
		add_meta_box(
			'sunshine_order_data',
			__( 'Add Order', 'sunshine' ),
			'sunshine_add_order_data',
			'sunshine-order',
			'normal',
			'high'
		);
	} else {
		add_meta_box(
			'sunshine_order_data',
			sprintf( __( 'Order #%s', 'sunshine' ), $post->ID ),
			'sunshine_order_data_inner',
			'sunshine-order',
			'normal',
			'high'
		);
		add_meta_box(
			'sunshine_order_notes',
			__( 'Order Notes', 'sunshine' ),
			'sunshine_order_notes_inner',
			'sunshine-order',
			'normal',
			'high'
		);
		add_meta_box(
			'sunshine_order_status',
			__( 'Order Status', 'sunshine' ),
			'sunshine_order_status_inner',
			'sunshine-order',
			'side',
			'high'
		);
	}

	remove_meta_box( 'trackbacksdiv','sunshine-order','normal' );
	remove_meta_box( 'commentstatusdiv', 'sunshine-order' , 'normal' );
	remove_meta_box( 'slugdiv', 'sunshine-order' , 'normal' );
	remove_meta_box( 'tagsdiv-sunshine-order-status', 'sunshine-order', 'side' );
}

add_action( 'admin_enqueue_scripts', 'sunshine_order_admin_enqueue_scripts' );
function sunshine_order_admin_enqueue_scripts( $page ){
	if ( get_post_type() != 'sunshine-order' ) {
		return;
	}
	wp_enqueue_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js', array( 'jquery' ) );
	wp_enqueue_style( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css' );
	wp_enqueue_script( 'jquery-ui' );
}

function sunshine_add_order_data() {
	
?>
	<form method="post">
	<p>
		<?php _e( 'Customer', 'sunshine' ); ?>:<br />
		<select name="customer" class="sunshine-multiselect">
			<option value=""><?php _e( 'Guest', 'sunshine' ); ?></option>
			<?php
			$users = get_users();
			foreach ( $users as $user ) {
				echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
			}
			?>
		</select>
	</p>
	<p id="email">
		<?php _e( 'Email', 'sunshine' ); ?><br />
		<input type="email" name="email" />
	</p>
	<p>
		<?php _e( 'Order Date', 'sunshine' ); ?>:<br />
		<input type="text" name="date" class="datepicker" />
	</p>
	<p>
		<?php _e( 'Order Status', 'sunshine' ); ?><br />
		<select name="status">
			<?php
			$statuses = get_terms( 'sunshine-order-status', 'hide_empty=0&orderby=id&order=ASC' );
			foreach ( $statuses as $status ) {
				echo '<option value="' . $status->term_id . '">' . $status->name . '</option>';
			}
			?>
		</select>
	</p>
	<div style="width: 45%; float: left;">
		<h4><?php _e( 'Billing Information', 'sunshine' ); ?></h4>
		<p>
			<?php _e( 'Country', 'sunshine' ); ?><br />
			<?php SunshineCountries::country_only_dropdown( 'country', '' ); ?>
		</p>
		<p>
			<?php _e( 'First Name', 'sunshine' ); ?><br />
			<input type="text" name="first_name" />
		</p>
		<p>
			<?php _e( 'Last Name', 'sunshine' ); ?><br />
			<input type="text" name="last_name" />
		</p>
		<p>
			<?php _e( 'Address', 'sunshine' ); ?><br />
			<input type="text" name="address" />
		</p>
		<p>
			<?php _e( 'Address 2', 'sunshine' ); ?><br />
			<input type="text" name="address2" />
		</p>
		<p>
			<?php _e( 'City', 'sunshine' ); ?><br />
			<input type="text" name="city" />
		</p>
		<p id="sunshine-billing-state">
			<?php _e( 'State', 'sunshine' ); ?><br />
			<?php SunshineCountries::state_dropdown( '', 'state', '' ); ?>
		</p>
	</div>
	<div style="width: 45%; float: right;">
		<h4><?php _e( 'Shipping Information', 'sunshine' ); ?></h4>
	<p>
		<?php _e( 'Country', 'sunshine' ); ?><br />
		<?php SunshineCountries::country_only_dropdown( 'shipping_country', '' ); ?>
	</p>
	<p>
		<?php _e( 'First Name', 'sunshine' ); ?><br />
		<input type="text" name="shipping_first_name" />
	</p>
	<p>
		<?php _e( 'Last Name', 'sunshine' ); ?><br />
		<input type="text" name="shipping_last_name" />
	</p>
	<p>
		<?php _e( 'Address', 'sunshine' ); ?><br />
		<input type="text" name="shipping_address" />
	</p>
	<p>
		<?php _e( 'Address 2', 'sunshine' ); ?><br />
		<input type="text" name="shipping_address2" />
	</p>
	<p>
		<?php _e( 'City', 'sunshine' ); ?><br />
		<input type="text" name="shipping_city" />
	</p>
	<p id="sunshine-shipping-state">
		<?php _e( 'State', 'sunshine' ); ?><br />
		<?php SunshineCountries::state_dropdown( '', 'shipping_state', '' ); ?>
	</p>
	</div>
	<br clear="both" />
	
	<h4><?php _e( 'Order Items', 'sunshine' ); ?></h4>
	<table style="width: 100%;">
	<tr>
		<th><?php _e( 'Gallery', 'sunshine' ); ?></th>
		<th><?php _e( 'Image', 'sunshine' ); ?></th>
		<th><?php _e( 'Product', 'sunshine' ); ?></th>
		<th><?php _e( 'Qty', 'sunshine' ); ?></th>
		<th><?php _e( 'Item Total', 'sunshine' ); ?></th>
	</tr>
	<tr>
		<td>
			<select name="gallery">
				<option value=""><?php _e( 'Select gallery', 'sunshine' ); ?></option>
				<?php
				$galleries = get_posts( 'post_type=sunshine-gallery&nopaging=true' );
				foreach ( $galleries as $gallery ) {
					echo '<option value="' . $gallery->ID . '">' . $gallery->post_title . '</option>';
				}
				?>
			</select>
		</td>
		<td>
			<select name="image[]">
			</select>
		</td>
		<td>
			<select name="product[]">
			
			</select>
		</td>
		<td>
		</td>
	</tr>
	</table>
	
	</form>
	
	<script>
	jQuery( document ).ready( function($) {
		$(".sunshine-multiselect").select2({
		    width: '100%',
			allowClear: true
		});
		
		$( 'select[name="customer"]' ).change(function(){
			if ( $( this ).val() == '' ) {
				$( '#email' ).show();
			} else {
				$( '#email' ).hide();
			}
		});
		
		jQuery('.datepicker').datepicker( {
			dateFormat: '<?php echo sunshine_date_format_php_to_js( get_option( 'date_format' ) ); ?>', 
			gotoCurrent: true,
		}).keyup(function(e) {
		    if(e.keyCode == 8 || e.keyCode == 46) {
		        $.datepicker._clearDate(this);
		    }
		});
		
		// Changing state selection
		jQuery('form').on('change', 'select[name="country"]', function(){
			var country = jQuery(this).val();
			setTimeout(function () {
				jQuery.ajax({
				  	type: 'POST',
				  	url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				  	data: {
				  		action: 'sunshine_checkout_update_state',
						country: country,
					},
				  	success: function(data, textStatus, XMLHttpRequest) {
						var obj = jQuery.parseJSON(data);
						if (obj.state_options)
							jQuery('#sunshine-billing-state').html('<label><?php _e( 'State / Province','sunshine' ); ?> '+obj.state_options+'</label>');
				  	},
				  	error: function(MLHttpRequest, textStatus, errorThrown) {
						alert('Sorry, there was an error with your request');
				  	}
				});
			}, 500);
			return false;
		});
		
		jQuery('form').on('change', 'select[name="shipping_country"]', function(){
			var shipping_country = jQuery(this).val();
			setTimeout(function () {
				jQuery.ajax({
				  	type: 'POST',
				  	url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				  	data: {
				  		action: 'sunshine_checkout_update_shipping_state',
						shipping_country: shipping_country
					},
				  	success: function(data, textStatus, XMLHttpRequest) {
						var obj = jQuery.parseJSON(data);
						if (obj.state_options)
							jQuery('#sunshine-shipping-state').html('<label>State / Province '+obj.state_options+'</label>');
				  	},
				  	error: function(MLHttpRequest, textStatus, errorThrown) {
						alert('Sorry, there was an error with your request');
				  	}
				});
			}, 500);
			return false;
		});
		
	});
	</script>

<?php
}


function sunshine_order_data_inner( $post ) {
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'sunshine_noncename' );
	// The actual fields for data entry
	$order_data = unserialize( get_post_meta( $post->ID, '_sunshine_order_data', true ) );
	$items = unserialize( get_post_meta( $post->ID, '_sunshine_order_items', true ) );
	sunshine_array_sort_by_column( $items, 'type' );
?>
	<p><strong><a href="<?php echo admin_url( 'admin.php?page=sunshine_invoice_display&order=' . $post->ID . '&nonce=' .wp_create_nonce( 'sunshine_invoice' ) ); ?>"><?php _e( 'Invoice', 'sunshine' ); ?></a> | <a href="#sunshine-lightroom-file-list" id="sunshine-file-list-link"><?php _e( 'Image File List', 'sunshine' ); ?></a></strong></p>
	<div id="sunshine-file-list" style="display: none;">
		<?php
	foreach ( $items as $item ) {
		if ( $item['type'] == 'image' ) {
			$image_file_list[$item['image_id']] = get_post_meta( $item['image_id'], 'sunshine_file_name', true );
		} elseif ( $item['type'] == 'package' ) {
			foreach ( $item['package_products'] as $package_product ) {
				$image_file_list[$package_product['image_id']] = get_post_meta( $package_product['image_id'], 'sunshine_file_name', true );
			}
		}
	}
	foreach ( $image_file_list as &$file ) {
		$file = str_replace( array( '.jpg','.JPG' ), '', $file );
	}
?>
		<textarea rows="4" cols="50" onclick="this.focus();this.select()" readonly="readonly"><?php echo join( ', ', $image_file_list ); ?></textarea>
		<p><?php _e( 'Copy and paste the file names above into Lightroom\'s search feature (Library filter) to quickly find and create a new collection to make processing this order easier. Make sure you are using the "Contains" (and not "Contains All") search parameter.', 'sunshine' ); ?></p>
	</div>
	<script>
	jQuery(document).ready(function($){
		$('#sunshine-file-list-link').click(function(){
			$('#sunshine-file-list').slideToggle();
			return false;
		});
	});
	</script>
	<p><strong><?php _e( 'Order Date', 'sunshine' ); ?>:</strong> <?php echo date( get_option( 'date_format' ), strtotime( $post->post_date ) ); ?></p>
	<?php if ( $ip = get_post_meta( $post->ID, 'ip', true ) ) { ?>
		<p><strong>IP Address:</strong> <?php echo $ip; ?></p>
	<?php } ?>
	<table width="100%" cellspacing="0" cellpadding="0" id="sunshine-order-data">
	<tr><th><?php _e( 'Billing Info', 'sunshine' ); ?></th><th><?php if ( isset( $order_data['shipping_first_name'] ) ) { _e( 'Shipping Info', 'sunshine' );  } ?></th></tr>
	<tr>
		<td>
			<?php
			echo $order_data['first_name'].' '.$order_data['last_name'].'<br />'.$order_data['address'];
			if ( $order_data['address2'] )
				echo '<br />'.$order_data['address2'];
			echo '<br />'.$order_data['city'].', '.$order_data['state'].' '.$order_data['zip'].'<br />'.$order_data['country'].'<br />'.$order_data['email'].'<br />'.$order_data['phone']; 
			?>
		</td>
		<td>
			<?php
			if ( isset( $order_data['shipping_first_name'] ) ) {
				echo $order_data['shipping_first_name'].' '.$order_data['shipping_last_name'].'<br />'.$order_data['shipping_address'];
				if ( $order_data['shipping_address2'] )
					echo '<br />'.$order_data['shipping_address2'];
				echo '<br />'.$order_data['shipping_city'].', '.$order_data['shipping_state'].' '.$order_data['shipping_zip'].'<br />'.$order_data['shipping_country'];
			}
			?>
		</td>
	</tr>
	</table>
	<br /><br /><table id="sunshine-cart-items" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<th class="image"><?php _e( 'Type', 'sunshine' ); ?></th>
		<th class="image"><?php _e( 'Image', 'sunshine' ); ?></th>
		<th class="name"><?php _e( 'Product', 'sunshine' ); ?></th>
		<th class="qty"><?php _e( 'Qty', 'sunshine' ); ?></th>
		<th class="price"><?php _e( 'Item Price', 'sunshine' ); ?></th>
		<th class="total"><?php _e( 'Item Total', 'sunshine' ); ?></th>
	</tr>
	<?php $i = 1; foreach ( $items as $item ) { ?>
		<tr class="item">
			<td class="type">
				<?php echo ucwords( str_replace( '_', ' ', $item['type'] ) ); ?>
			</td>
			<td class="image">
				<?php
		if ( $item['image_id'] > 0 ) {
			$image = get_post( $item['image_id'] );
			$gallery = get_post( $image->post_parent );
			if( $thumb = wp_get_attachment_image_src( $item['image_id'], array( 50,50 ) ) )
				$image_html = '<img src="'.$thumb[0].'" alt="'.$item['image_name'].'" />';
			else
				$image_html = '<img src="http://placehold.it/100&text=Image+deleted" alt="Image has been deleted" />';
		}
		echo apply_filters( 'sunshine_cart_image_html', $image_html, $item, $thumb );
?>
			</td>
			<td class="name">
				<strong><?php echo $item['product_name']; ?></strong><br />
				<div class="comments"><?php echo apply_filters( 'sunshine_cart_item_comments', $item['comments'], $item ); ?></div>
			</td>
			<td class="qty">
				<?php echo $item['qty']; ?>
			</td>
			<td class="price">
				<?php sunshine_money_format( $item['price'] ); ?>
			</td>
			<td class="total">
				<?php sunshine_money_format( $item['total'] ); ?>
			</td>
		</tr>

	<?php $i++; } ?>
		<tr class="subtotal totals">
			<th colspan="5" align="right"><?php _e( 'Subtotal', 'sunshine' ); ?></th>
			<td><?php sunshine_money_format( $order_data['subtotal'] ); ?></td>
		</tr>
		<tr class="tax totals">
			<th colspan="5" align="right"><?php _e( 'Tax', 'sunshine' ); ?></th>
			<td><?php sunshine_money_format( $order_data['tax'] ); ?></td>
		</tr>
		<tr class="shipping totals">
			<th colspan="5" align="right"><?php _e( 'Shipping', 'sunshine' ); ?> (<?php echo sunshine_get_shipping_method_name( $order_data['shipping_method'] ); ?>)</th>
			<td>
				<?php sunshine_money_format( $order_data['shipping_cost'] ); ?>
			</td>
		</tr>
		<tr class="discounts totals">
			<th colspan="5" align="right">
				<?php _e( 'Discounts', 'sunshine' ); ?>
				<?php
	if ( !empty( $order_data['discount_items'] ) ) {
		$discount_names = array();
		foreach ( $order_data['discount_items'] as $discount_item ) {
			$discount_names[] = $discount_item->name;
		}
		echo '<br />('.join( ', ', $discount_names ).')';
	}
?>
			</th>
			<td>-<?php sunshine_money_format( $order_data['discount_total'] ); ?></td>
		</tr>
		<?php if ( $order_data['credits'] > 0 ) { ?>
		<tr class="credits totals">
			<th colspan="5" align="right"><?php _e( 'Credits', 'sunshine' ); ?></th>
			<td>-<?php sunshine_money_format( $order_data['credits'] ); ?></td>
		</tr>
		<?php } ?>

		<tr class="total totals">
			<th colspan="5" align="right"><?php _e( 'Total', 'sunshine' ); ?></th>
			<td><?php sunshine_money_format( $order_data['total'] ); ?></td>
		</tr>
		<tr class="payment-method totals">
			<th colspan="5" align="right"><?php _e( 'Payment Method', 'sunshine' ); ?></th>
			<td><?php echo $order_data['payment_method']; ?></td>
		</tr>
	</table>
<?php
}

function sunshine_order_notes_inner( $post ) {
?>
	<textarea rows="8" style="width: 100%;" name="sunshine_order_notes"><?php echo get_post_meta( $post->ID, 'sunshine_order_notes', true ); ?></textarea>
	<p class="description"><?php _e( 'This is for internal use only and is not visible to your customer', 'sunshine' ); ?></p>
<?php
}

add_action( 'save_post', 'sunshine_orders_save_postdata' );
function sunshine_orders_save_postdata( $post_id ) {
	global $sunshine;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( !isset( $_POST['sunshine_noncename'] ) || !wp_verify_nonce( $_POST['sunshine_noncename'], plugin_basename( __FILE__ ) ) )
		return;

	if ( isset( $_POST['post_type'] ) && 'sunshine-order' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_sunshine_order', $post_id ) )
			return;
	} else {
		return;
	}

	update_post_meta( $post_id, 'sunshine_order_notes', sanitize_text_field( $_POST['sunshine_order_notes'] ) );
	wp_set_post_terms( $post_id, sanitize_text_field( $_POST['sunshine_order_status'] ), 'sunshine-order-status' );

	if ( isset( $_POST['sunshine_email_customer_order_change'] ) && $_POST['sunshine_email_customer_order_change'] != '' ) {
		$data = maybe_unserialize( get_post_meta( $post_id, '_sunshine_order_data', true ) );
		$search = array( '[status]', '[status_description]', '[order_id]', '[order_url]', '[first_name]', '[last_name]' );
		$status = get_term_by( 'slug', sanitize_text_field( $_POST['sunshine_order_status'] ), 'sunshine-order-status' );
		$replace = array( $status->name, $status->description, $post_id, get_permalink( $post_id ), $data['first_name'], $data['last_name'] );
		SunshineEmail::send_email( 'order_status', $data['email'], $sunshine->options['email_subject_order_status'], $sunshine->options['email_subject_order_status'], $search, $replace );
	}

}

add_action( 'restrict_manage_posts', 'sunshine_show_order_customer_filter' );
function sunshine_show_order_customer_filter() {
	if ( isset( $_GET['post_type'] ) && post_type_exists( $_GET['post_type'] ) && in_array( strtolower( $_GET['post_type'] ), array( 'sunshine-order' ) ) ) {
		wp_dropdown_users( array(
			'show_option_all' => __( 'Show all Customers', 'sunshine' ),
			'show_option_none' => false,
			'name'   => 'customer',
			'selected'  => !empty( $_GET['customer'] ) ? intval( $_GET['customer'] ) : 0,
			'include_selected' => false
		) );
	}
}

add_filter( 'parse_query', 'sunshine_orders_show_by_user' );
function sunshine_orders_show_by_user( $query ) {
    global $pagenow;
    if ( isset( $_GET['customer'] ) && $pagenow == 'edit.php' && isset( $query->query_vars['post_type']) && $query->query_vars['post_type']=='sunshine-order' ) {
        $query->query_vars['meta_key'] = '_sunshine_customer_id';
		$query->query_vars['meta_value'] = sanitize_text_field( $_GET['customer'] );
    }
}


add_action( 'comment_post', 'sunshine_order_comment_post' );
function sunshine_order_comment_post( $comment_id ) {
	global $sunshine;
	if ( is_admin() ) {
		$comment = get_comment( $comment_id );
		$order = get_post( $comment->comment_post_ID );
		$data = maybe_unserialize( get_post_meta( $order->ID, '_sunshine_order_data', true ) );
		if ( get_post_type( $order ) == 'sunshine-order' ) {
			$search = array( '[comment]', '[order_id]', '[order_url]', '[first_name]', '[last_name]' );
			$replace = array( nl2br( $comment->comment_content ), $order->ID, get_permalink( $order->ID ), $data['first_name'], $data['last_name'] );
			SunshineEmail::send_email( 'order_comment', $data['email'], $sunshine->options['email_subject_order_comment'], $sunshine->options['email_subject_order_comment'], $search, $replace );
		}
	}
}

function sunshine_order_status_inner( $post ) {
	$statuses = get_terms( 'sunshine-order-status', 'hide_empty=0&orderby=id&order=ASC' );
	$current_status = get_the_terms( $post->ID, 'sunshine-order-status' );
	$current_status = array_merge( $current_status ); // Reset array indexes to 0
	echo '<select name="sunshine_order_status">';
	foreach ( $statuses as $status ) {
		echo '<option value="'.$status->slug.'" '.selected( $current_status[0]->slug,$status->slug, false ).'>'.$status->name.'</option>';
	}
	echo '</select>';
?>
	<p id="sunshine-email-customer" style="display: none;"><label><input type="checkbox" name="sunshine_email_customer_order_change" value="1" /> <?php _e( 'Email customer about status change', 'sunshine' ); ?></label></p>
	<style type="text/css">
	#minor-publishing { display: none; }
	</style>
	<script>
	jQuery(document).ready(function() {
		jQuery('select[name="sunshine_order_status"]').change(function() {
			if (jQuery('select[name="sunshine_order_status"]').val() != '<?php echo $current_status[0]->slug; ?>')
				jQuery('#sunshine-email-customer').show();
			else
				jQuery('#sunshine-email-customer').hide();
		});
	});
	</script>
	<?php
}

add_filter( 'manage_edit-sunshine-order_columns', 'sunshine_order_columns' ) ;
function sunshine_order_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Order #', 'sunshine' ),
		'customer' => __( 'Customer', 'sunshine' ),
		'status' => __( 'Status', 'sunshine' ),
		'total' => __( 'Order Total', 'sunshine' ),
		'date_ordered' => __( 'Date', 'sunshine' ),
		'galleries' => __( 'Galleries', 'sunshine' )
	);
	return $columns;
}

add_action( 'manage_sunshine-order_posts_custom_column', 'sunshine_order_columns_content', 10, 2 );
function sunshine_order_columns_content( $column, $post_id ) {
	global $post;

	switch( $column ) {
	case 'order_id':
		echo $post_id;
		break;
	case 'customer':
		$customer_id = get_post_meta( $post_id, '_sunshine_customer_id', true );
		if ( $customer_id ) {
			$customer = get_user_by( 'id', $customer_id );
			if ( !empty( $customer ) )
				echo '<a href="user-edit.php?user_id='.$customer_id.'">'.$customer->display_name.'</a>';
		} else {
			$data = maybe_unserialize( get_post_meta( $post_id, '_sunshine_order_data', true ) );
			echo $data['first_name'] . ' ' . $data['last_name'];
		}
		break;
	case 'status':
		$current_status = array_values( get_the_terms( $post_id, 'sunshine-order-status' ) );
		echo $current_status[0]->name;
		break;
	case 'total':
		$order_data = unserialize( get_post_meta( $post_id, '_sunshine_order_data', true ) );
		sunshine_money_format( $order_data['total'] );
		break;
	case 'date_ordered':
		echo date( get_option( 'date_format' ), strtotime( $post->post_date ) );
		break;
	case 'galleries':
		$order_items = unserialize( get_post_meta( $post_id, '_sunshine_order_items', true ) );
		$links = array();
		$galleries = array();
		foreach ( $order_items as $item ) {
			if ( $item['gallery_id'] && !in_array( $item['gallery_id'], $galleries ) ) {
				$galleries[ $item['gallery_id'] ] = get_the_title( $item['gallery_id'] );
			} elseif ( $item['image_id'] ) {
				$image = get_post( $item['image_id'] );
				if ( $image->post_parent && !in_array( $image->post_parent, $galleries ) ) {
					$galleries[ $image->post_parent ] = get_the_title( $image->post_parent );
				}
			}
		}
		foreach( $galleries as $gallery_id => $gallery_title ) {
			$links[] = '<a href="post.php?post=' . $gallery_id . '&action=edit">' . $gallery_title . '</a>';
		}
		echo ( $links ) ? join( ', ', $links )  : '&mdash;';
		unset( $links );
		unset( $galleries );
		break;
	default:
		break;
	}
}

add_filter( 'views_edit-sunshine-order', 'sunshine_custom_order_views' );
function sunshine_custom_order_views( $views ) {

	$statuses = get_terms( 'sunshine-order-status', 'hide_empty=0' );
	foreach ( $statuses as $status ) {
		if ( $status->slug == 'pending' ) {
			$pending_count = $status->count;
		} else if ( $status->slug == 'new' ) {
			$new_count = $status->count;
		} else if ( $status->slug == 'processing' ) {
			$processing_count = $status->count;
		} else if ( $status->slug == 'shipped' ) {
			$shipped_count = $status->count;
		} else if ( $status->slug == 'cancelled' ) {
			$cancelled_count = $status->count;
		}
	}

	$pending = ( isset( $_GET['sunshine-order-status'] ) && $_GET['sunshine-order-status'] == 'pending' ) ? 'current' : '';
	$new = ( isset( $_GET['sunshine-order-status'] ) && $_GET['sunshine-order-status'] == 'new' ) ? 'current' : '';
	$processing = ( isset( $_GET['sunshine-order-status'] ) && $_GET['shop_order_status'] == 'processing' ) ? 'current' : '';
	$shipped = ( isset( $_GET['sunshine-order-status'] ) && $_GET['sunshine-order-status'] == 'shipped' ) ? 'current' : '';
	$cancelled = ( isset( $_GET['sunshine-order-status'] ) && $_GET['sunshine-order-status'] == 'cancelled' ) ? 'current' : '';

	$views['pending'] = '<a class="' . esc_attr( $pending ) . '" href="?post_type=sunshine-order&amp;sunshine-order-status=pending">' . __( 'Pending', 'sunshine' ) . ' <span class="count">(' . $pending_count . ')</span></a>';
	$views['new'] = '<a class="' . esc_attr( $new ) . '" href="?post_type=sunshine-order&amp;sunshine-order-status=new">' . __( 'New', 'sunshine' ) . ' <span class="count">(' . $new_count . ')</span></a>';
	$views['processing'] = '<a class="' . esc_attr( $processing ) . '" href="?post_type=sunshine-order&amp;sunshine-order-status=processing">' . __( 'Processing', 'sunshine' ) . ' <span class="count">(' . $processing_count . ')</span></a>';
	$views['shipped'] = '<a class="' . esc_attr( $shipped ) . '" href="?post_type=sunshine-order&amp;sunshine-order-status=shipped">' . __( 'Shipped/Completed', 'sunshine' ) . ' <span class="count">(' . $shipped_count . ')</span></a>';
	$views['cancelled'] = '<a class="' . esc_attr( $cancelled ) . '" href="?post_type=sunshine-order&amp;sunshine-order-status=cancelled">' . __( 'Cancelled', 'sunshine' ) . ' <span class="count">(' . $cancelled_count . ')</span></a>';

	if ( $pending || $new || $processing || $shipped || $cancelled )
		$views['all'] = str_replace( 'current', '', $views['all'] );

	unset( $views['publish'] );

	if ( isset( $views['trash'] ) ) :
		$trash = $views['trash'];
	unset( $views['draft'] );
	unset( $views['trash'] );
	$views['trash'] = $trash;
	endif;

	return $views;
}

function sunshine_orders_post_row_invoice( $actions, $post ) {
	if ( $post->post_type == 'sunshine-order' ) {
		$actions['sunshine_invoice'] = '<a href="' . admin_url( 'admin.php?page=sunshine_invoice_display&order=' . $post->ID . '&nonce=' .wp_create_nonce( 'sunshine_invoice' ) ) .'">'.__( 'Invoice','sunshine' ).'</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'sunshine_orders_post_row_invoice', 10, 2 );


add_action( 'admin_init', 'sunshine_invoice_display' );
function sunshine_invoice_display() {

if ( !isset( $_GET['page'] ) || $_GET['page'] != 'sunshine_invoice_display' ) {
	return;
}

if (  !isset( $_GET['order'] ) || ! wp_verify_nonce( $_GET['nonce'], 'sunshine_invoice' ) ) {
	wp_die( __('Sorry, no order specified', 'sunshine' ) );
	exit;
}

$order_id = intval( $_GET['order'] );
$order = get_post( $order_id );
$order_data = unserialize( get_post_meta( $order_id, '_sunshine_order_data', true ) );
$items = unserialize( get_post_meta( $order_id, '_sunshine_order_items', true ) );
?>
<!DOCTYPE html>
<html>
<head>
	<title>Invoice for Order #<?php echo $order_id; ?></title>
	<meta charset="UTF-8" />
	<style type="text/css">
		*
		{
			border: 0;
			box-sizing: content-box;
			color: inherit;
			font-family: inherit;
			font-size: inherit;
			font-style: inherit;
			font-weight: inherit;
			line-height: inherit;
			list-style: none;
			margin: 0;
			padding: 0;
			text-decoration: none;
			vertical-align: top;
		}

		/* content editable */

		/* heading */

		h1 { font: bold 100% sans-serif; letter-spacing: 0.5em; text-align: center; text-transform: uppercase; }

		/* table */

		table { font-size: 75%; table-layout: fixed; width: 100%; }
		th, td { border-bottom: 1px solid #DDD; padding: 0.5em; position: relative; text-align: left; }
		th { background: #EEE; }
		td { border-color: #DDD; }

		td td { border: none; }


		/* page */

		html { font: 16px/1 'Open Sans', sans-serif; overflow: auto; padding: 0.5in; }
		html { background: #999; cursor: default; }

		body { box-sizing: border-box; height: 11in; margin: 0 auto; /*overflow: hidden;*/ padding: 0.5in; width: 8.5in; }
		body { background: #FFF; border-radius: 1px; box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5); }

		/* header */

		header { margin: 0 0 3em; }
		header:after { clear: both; content: ""; display: table; }

		header { text-align: center; margin: 0 0 25px 0; }
		header h1 { background: #000; border-radius: 0.25em; color: #FFF; margin: 0 0 1em; padding: 0.5em 0; }
		header img { margin: 0 auto; height: auto; width: auto; max-height: 75px; }

		/* article */

		article, article address, table.meta, table.inventory { margin: 0 0 3em; }
		article:after { clear: both; content: ""; display: table; }
		article h1 { clip: rect(0 0 0 0); position: absolute; }

		article address { float: left; font-weight: bold; }

		/* table meta & balance */

		table.meta, table.balance { float: right; width: 36%; }
		table.meta:after, table.balance:after { clear: both; content: ""; display: table; }

		/* table meta */

		table.meta th { width: 40%; }
		table.meta td { width: 60%; }

		/* table items */

		table.inventory { clear: both; width: 100%; }
		table.inventory th { font-weight: bold; text-align: center; }

		table.inventory td { padding: 20px 0; }
		table.inventory td td { padding: 5px 0; }
		table.inventory td:nth-child(1) { width: 26%; }
		table.inventory td:nth-child(2) { width: 38%; }
		table.inventory td:nth-child(3) { text-align: right; width: 12%; }
		table.inventory td:nth-child(4) { text-align: right; width: 12%; }
		table.inventory td:nth-child(5) { text-align: right; width: 12%; }

		table.inventory td img { max-width: 100px; height: auto; }
		table.inventory td td img { max-width: 40px; height: auto; }
		/* table balance */

		table.balance th, table.balance td { width: 50%; white-space: nowrap;  }
		table.balance td { text-align: right;}

		/* aside */

		aside h1 { border: none; border-width: 0 0 1px; margin: 0 0 1em; }
		aside h1 { border-color: #999; border-bottom-style: solid; }


		@media print {
			* { -webkit-print-color-adjust: exact; }
			html { background: none; padding: 0; }
			body { box-shadow: none; margin: 0; }
			span:empty { display: none; }
			.add, .cut { display: none; }
		}

		@page { margin: 0; }
	</style>
</head>
<body>

	<header>
		<?php sunshine_logo(); ?>
	</header>
	<article>
		<h1><?php _e( 'Recipient', 'sunshine' ); ?></h1>
		<address>
			<p>
				<?php
echo $order_data['first_name'].' '.$order_data['last_name'].'<br />'.$order_data['address'];
if ( $order_data['address2'] )
	echo '<br />'.$order_data['address2'];
echo '<br />'.$order_data['city'].', '.$order_data['state'].' '.$order_data['zip'].'<br />'.$order_data['country'].'<br />'.$order_data['email'].'<br />'.$order_data['phone']; ?>
			</p>
		</address>
		<table class="meta" cellspacing="0" cellpadding="0">
			<tr>
				<th><span><?php _e( 'Order #', 'sunshine' ); ?></span></th>
				<td><span><?php echo $order_id; ?></span></td>
			</tr>
			<tr>
				<th><span><?php _e( 'Date', 'sunshine' ); ?></span></th>
				<td><span><?php echo get_the_date( get_option( 'date_format' ), $order_id ); ?></span></td>
			</tr>
			<tr>
				<th><span><?php _e( 'Order Total', 'sunshine' ); ?></span></th>
				<td><span><?php sunshine_money_format( $order_data['total'] ); ?></span></td>
			</tr>
		</table>
		<table class="inventory">
			<thead>
				<tr>
					<th><span><?php _e( 'Image', 'sunshine' ); ?></span></th>
					<th><span><?php _e( 'Product', 'sunshine' ); ?></span></th>
					<th><span><?php _e( 'Quantity', 'sunshine' ); ?></span></th>
					<th><span><?php _e( 'Price', 'sunshine' ); ?></span></th>
					<th><span><?php _e( 'Item Total', 'sunshine' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 1; foreach ( $items as $item ) { ?>
					<tr class="item">
						<td class="image">
							<?php
								if ( $item['image_id'] > 0 ) {
									$image = get_post( $item['image_id'] );
									$gallery = get_post( $image->post_parent );
									if( $thumb = wp_get_attachment_image_src( $item['image_id'], 'sunshine-thumbnail' ) )
										$image_html = '<img src="'.$thumb[0].'" alt="'.$item['image_name'].'" width="100" />';
									else
										$image_html = '<img src="http://placehold.it/100&text=Image+deleted" alt="Image has been deleted" />';
								}
								echo apply_filters( 'sunshine_cart_image_html', $image_html, $item, $thumb );
							?>
						</td>
						<td class="name">
							<strong><?php echo $item['product_name']; ?></strong><br />
							<div class="comments"><?php echo apply_filters( 'sunshine_cart_item_comments', $item['comments'], $item ); ?></div>
						</td>
						<td class="qty">
							<?php echo $item['qty']; ?>
						</td>
						<td class="price">
							<?php sunshine_money_format( $item['price'] ); ?>
						</td>
						<td class="total">
							<?php sunshine_money_format( $item['total'] ); ?>
						</td>
					</tr>

				<?php $i++; } ?>
			</tbody>
		</table>
		<table class="balance">
			<tr class="subtotal totals">
				<th colspan="4" align="right"><?php _e( 'Subtotal', 'sunshine' ); ?></th>
				<td><?php sunshine_money_format( $order_data['subtotal'] ); ?></td>
			</tr>
			<tr class="tax totals">
				<th colspan="4" align="right"><?php _e( 'Tax', 'sunshine' ); ?></th>
				<td><?php sunshine_money_format( $order_data['tax'] ); ?></td>
			</tr>
			<tr class="shipping totals">
				<th colspan="4" align="right"><?php _e( 'Shipping', 'sunshine' ); ?> (<?php echo $order_data['shipping_method']; ?>)</th>
				<td>
					<?php sunshine_money_format( $order_data['shipping_cost'] ); ?>
				</td>
			</tr>
			<tr class="discounts totals">
				<th colspan="4" align="right">
					<?php _e( 'Discounts', 'sunshine' ); ?>
					<?php
					if ( !empty( $order_data['discount_items'] ) ) {
						$discount_names = array();
						foreach ( $order_data['discount_items'] as $discount_item ) {
							$discount_names[] = $discount_item->name;
						}
						echo '<br />('.join( ', ', $discount_names ).')';
					}
					?>
				</th>
				<td>-<?php sunshine_money_format( $order_data['discount_total'] ); ?></td>
			</tr>
			<?php if ( $order_data['credits'] > 0 ) { ?>
			<tr class="credits totals">
				<th colspan="4" align="right"><?php _e( 'Credits', 'sunshine' ); ?></th>
				<td>-<?php sunshine_money_format( $order_data['credits'] ); ?></td>
			</tr>
			<?php } ?>

			<tr class="total totals">
				<th colspan="4" align="right"><?php _e( 'Total', 'sunshine' ); ?></th>
				<td><?php sunshine_money_format( $order_data['total'] ); ?></td>
			</tr>
			<tr class="payment-method totals">
				<th colspan="4" align="right"><?php _e( 'Payment Method', 'sunshine' ); ?></th>
				<td><?php echo $order_data['payment_method']; ?></td>
			</tr>
		</table>
	</article>
</body>
</html>

<?php 
exit;
} ?>
