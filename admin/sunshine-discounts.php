<?php
/* SETUP META BOXES */
add_action( 'add_meta_boxes', 'sunshine_discount_meta_boxes' );

function sunshine_discount_meta_boxes() {
	add_meta_box(
		'sunshine_discount_options',
		__( 'Options', 'sunshine' ),
		'sunshine_discount_options_inner',
		'sunshine-discount',
		'normal',
		'high'
	);

	remove_meta_box( 'trackbacksdiv','sunshine-discount','normal' );
	remove_meta_box( 'commentstatusdiv', 'sunshine-discount' , 'normal' );
	remove_meta_box( 'slugdiv', 'sunshine-discount' , 'normal' );
}

/* Do something with the data entered */
add_action( 'save_post', 'sunshine_discount_save_postdata' );
function sunshine_discount_save_postdata( $post_id ) {
	// verify if this is an auto save routine.
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if ( !isset( $_POST['sunshine_noncename'] ) || !wp_verify_nonce( $_POST['sunshine_noncename'], plugin_basename( __FILE__ ) ) )
		return;

	// Check permissions
	if ( 'sunshine-discount' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return;
	} else {
		return;
	}

	// OK, we're authenticated: we need to find and save the data
	update_post_meta( $post_id, 'code', ( isset( $_POST['code'] ) ) ? sanitize_text_field( $_POST['code'] ) : '' );
	update_post_meta( $post_id, 'discount_type', ( isset( $_POST['discount_type'] ) ) ? sanitize_text_field( $_POST['discount_type'] ) : '' );
	update_post_meta( $post_id, 'amount', ( isset( $_POST['amount'] ) ) ? sanitize_text_field( $_POST['amount'] ) : '' );
	update_post_meta( $post_id, 'start_date', ( isset( $_POST['start_date'] ) ) ? sanitize_text_field( $_POST['start_date'] ) : '' );
	update_post_meta( $post_id, 'end_date', ( isset( $_POST['end_date'] ) ) ? sanitize_text_field( $_POST['end_date'] ) : '' );
	update_post_meta( $post_id, 'max_product_quantity', ( isset( $_POST['max_product_quantity'] ) ) ? sanitize_text_field( $_POST['max_product_quantity'] ) : '' );
	update_post_meta( $post_id, 'max_uses', ( isset( $_POST['max_uses'] ) ) ? sanitize_text_field( $_POST['max_uses'] ) : '' );
	update_post_meta( $post_id, 'max_uses_per_person', ( isset( $_POST['max_uses_per_person'] ) ) ? sanitize_text_field( $_POST['max_uses_per_person'] ) : '' );
	update_post_meta( $post_id, 'solo', ( isset( $_POST['solo'] ) ) ? sanitize_text_field( $_POST['solo'] ) : '' );
	update_post_meta( $post_id, 'free_shipping', ( isset( $_POST['free_shipping'] ) ) ? sanitize_text_field( $_POST['free_shipping'] ) : '' );
	update_post_meta( $post_id, 'min_amount', ( isset( $_POST['min_amount'] ) ) ? sanitize_text_field( $_POST['min_amount'] ) : '' );
	update_post_meta( $post_id, 'before_tax', ( isset( $_POST['before_tax'] ) ) ? sanitize_text_field( $_POST['before_tax'] ) : '' );
	update_post_meta( $post_id, 'allowed_products', ( isset( $_POST['allowed_products'] ) ) ? sanitize_text_field( $_POST['allowed_products'] ) : '' );
	update_post_meta( $post_id, 'disallowed_products', ( isset( $_POST['disallowed_products'] ) ) ? sanitize_text_field( $_POST['disallowed_products'] ) : '' );
	update_post_meta( $post_id, 'allowed_categories', ( isset( $_POST['allowed_categories'] ) ) ? sanitize_text_field( $_POST['allowed_categories'] ) : '' );
	update_post_meta( $post_id, 'disallowed_categories', ( isset( $_POST['disallowed_categories'] ) ) ? sanitize_text_field( $_POST['disallowed_categories'] ) : '' );

}


function sunshine_discount_options_inner( $post ) {

	wp_nonce_field( plugin_basename( __FILE__ ), 'sunshine_noncename' );
	$currency_symbol_format = sunshine_currency_symbol_format();
	$currency_symbol = sunshine_currency_symbol();

?>
	<table class="sunshine-meta">
	<tr valign="top">
		<th scope="row"><?php _e( 'Discount Code', 'sunshine' ); ?></th>
		<td>
			<input type="text" name="code" value="<?php echo esc_attr( get_post_meta( $post->ID, 'code', true ) ); ?>" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Discount Type', 'sunshine' ); ?></th>
		<td>
			<label><input type="radio" name="discount_type" value="percent-total" <?php checked( 'percent-total', get_post_meta( $post->ID, 'discount_type', true ) ); ?> /> % <?php _e( 'off order', 'sunshine' ); ?></label><br />
			<label><input type="radio" name="discount_type" value="amount-total" <?php checked( 'amount-total', get_post_meta( $post->ID, 'discount_type', true ) ); ?> /> <?php echo $currency_symbol; ?> <?php _e( 'off order', 'sunshine' ); ?></label><br />
			<label><input type="radio" name="discount_type" value="percent-product" <?php checked( 'percent-product', get_post_meta( $post->ID, 'discount_type', true ) ); ?> /> % <?php _e( 'off product(s)', 'sunshine' ); ?></label><br />
			<label><input type="radio" name="discount_type" value="amount-product" <?php checked( 'amount-product', get_post_meta( $post->ID, 'discount_type', true ) ); ?> /> <?php echo $currency_symbol; ?> <?php _e( 'off product(s)', 'sunshine' ); ?></label><br />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Discount Amount', 'sunshine' ); ?></th>
		<td>
			<?php echo $currency_symbol; ?> or %<input type="text" name="amount" value="<?php echo esc_attr( get_post_meta( $post->ID, 'amount', true ) ); ?>" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Start Date', 'sunshine' ); ?></th>
		<td>
			<input type="text" name="start_date" class="datepicker" value="<?php echo esc_attr( get_post_meta( $post->ID, 'start_date', true ) ); ?>" /> (Optional)
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'End Date', 'sunshine' ); ?></th>
		<td>
			<input type="text" name="end_date" class="datepicker" value="<?php echo esc_attr( get_post_meta( $post->ID, 'end_date', true ) ); ?>" /> (Optional)
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Max Quantity', 'sunshine' ); ?></th>
		<td>
			<input type="text" name="max_product_quantity" value="<?php echo esc_attr( get_post_meta( $post->ID, 'max_product_quantity', true ) ); ?>" />
			<?php _e( 'The maximum number of any one item this can be applied towards', 'sunshine' ); ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Total Max Uses', 'sunshine' ); ?></th>
		<td>
			<input type="text" name="max_uses" value="<?php echo esc_attr( get_post_meta( $post->ID, 'max_uses', true ) ); ?>" /> (Optional)
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Max Uses Per Person', 'sunshine' ); ?></th>
		<td>
			<input type="text" name="max_uses_per_person" value="<?php echo esc_attr( get_post_meta( $post->ID, 'max_uses_per_person', true ) ); ?>" /> (Optional)
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Only This Discount', 'sunshine' ); ?></th>
		<td>
			<label><input type="checkbox" name="solo" value="1" <?php checked( 1, get_post_meta( $post->ID, 'solo', true ) ); ?> /> <?php _e( 'Only allow customer to use this coupon, disallow all other coupons', 'sunshine' ); ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Free Shipping', 'sunshine' ); ?></th>
		<td>
			<label><input type="checkbox" name="free_shipping" value="1" <?php checked( 1, get_post_meta( $post->ID, 'free_shipping', true ) ); ?> /> <?php _e( 'Make shipping free on entire order', 'sunshine' ); ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Minimum Purchase Amount', 'sunshine' ); ?></th>
		<td>
			<?php
	$text_field = '<input type="text" name="min_amount" value="'.esc_attr( get_post_meta( $post->ID, 'min_amount', true ) ).'" />';
	echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
?>
			 (Optional)
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Apply Before Tax', 'sunshine' ); ?></th>
		<td>
			<label><input type="checkbox" name="before_tax" value="1" <?php checked( 1, get_post_meta( $post->ID, 'before_tax', true ) ); ?> /> <?php _e( 'Should this coupon be applied before calculating tax', 'sunshine' ); ?></label>
		</td>
	</tr>

	<?php $product_categories = get_terms( 'sunshine-product-category' ); ?>

	<tr valign="top">
		<th scope="row"><?php _e( 'Allowed Products', 'sunshine' ); ?></th>
		<td>
			<div style="height: 100px; overflow-y: scroll; border: 1px solid #CCC; padding: 10px;">
				<?php
	foreach ( $product_categories as $product_category ) {
		$args = array(
			'post_type' => 'sunshine-product',
			'nopaging' => true,
			'tax_query' => array( array(
					'taxonomy' => 'sunshine-product-category',
					'field' => 'id',
					'terms' => array( $product_category->term_id )
				) )
		);
		$products = new WP_Query( $args );
		if ( $products->have_posts() ) {
			$allowed_products = get_post_meta( $post->ID, 'allowed_products', true );
			echo '<strong>'.$product_category->name.'</strong><br />';
			while ( $products->have_posts() ) : $products->the_post();
			$checked = 0;
			if ( $allowed_products ) {
				if ( in_array( get_the_ID(), $allowed_products ) )
					$checked = 1;
			}
?>
						<label><input type="checkbox" name="allowed_products[]" value="<?php the_ID(); ?>" <?php checked( 1,$checked ); ?> /> <?php the_title(); ?></label><br />
						<?php endwhile; wp_reset_postdata(); ?>
				<?php } } ?>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Disallowed Products', 'sunshine' ); ?></th>
		<td>
			<div style="height: 100px; overflow-y: scroll; border: 1px solid #CCC; padding: 10px;">
				<?php
	foreach ( $product_categories as $product_category ) {
		$args = array(
			'post_type' => 'sunshine-product',
			'nopaging' => true,
			'tax_query' => array( array(
					'taxonomy' => 'sunshine-product-category',
					'field' => 'id',
					'terms' => array( $product_category->term_id )
				) )
		);
		//var_dump($args);
		$products = new WP_Query( $args );
		if ( $products->have_posts() ) {
			$disallowed_products = get_post_meta( $post->ID, 'disallowed_products', true );
			echo '<strong>'.$product_category->name.'</strong><br />';
			while ( $products->have_posts() ) : $products->the_post();
			$checked = 0;
			if ( $disallowed_products ) {
				if ( in_array( get_the_ID(), $disallowed_products ) )
					$checked = 1;
			}
?>
						<label><input type="checkbox" name="disallowed_products[]" value="<?php the_ID(); ?>" <?php checked( 1, $checked ); ?> /> <?php the_title(); ?></label><br />
						<?php endwhile; wp_reset_postdata(); ?>
				<?php } } ?>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Allowed Categories', 'sunshine' ); ?></th>
		<td>
			<div style="height: 100px; overflow-y: scroll; border: 1px solid #CCC; padding: 10px;">
				<?php
	$allowed_categories = get_post_meta( $post->ID, 'allowed_categories', true );
	foreach ( $product_categories as $product_category ) {
		$checked = 0;
		if ( $allowed_categories ) {
			if ( in_array( $product_category->term_id, $allowed_categories ) )
				$checked = 1;
		}
?>
					<label><input type="checkbox" name="allowed_categories[]" value="<?php echo $product_category->term_id; ?>" <?php checked( 1, $checked ); ?> /> <?php echo $product_category->name; ?></label><br />
				<?php } ?>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Disallowed Categories', 'sunshine' ); ?></th>
		<td>
			<div style="height: 100px; overflow-y: scroll; border: 1px solid #CCC; padding: 10px;">
				<?php
	$disallowed_categories = get_post_meta( $post->ID, 'disallowed_categories', true );
	foreach ( $product_categories as $product_category ) {
		$checked = 0;
		if ( $disallowed_categories ) {
			if ( in_array( $product_category->term_id, $disallowed_categories ) )
				$checked = 1;
		}
?>
					<label><input type="checkbox" name="disallowed_categories[]" value="<?php echo $product_category->term_id; ?>" <?php checked( 1, $checked ); ?> /> <?php echo $product_category->name; ?></label><br />
				<?php } ?>
			</div>
		</td>
	</tr>

	</table>

	<script>
	jQuery(document).ready(function(){
		jQuery('.datepicker').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
	});
	</script>

<?php
}

add_action( 'admin_head', 'sunshine_discounts_remove_permalink' );
function sunshine_discounts_remove_permalink() {
	if ( isset( $_GET['post'] ) ) {
		$post_type = get_post_type( intval( $_GET['post'] ) );
		if( $post_type == 'sunshine-discount' && $_GET['action'] == 'edit' )
			echo '<style>#edit-slug-box{display:none;}</style>';
	}
}

?>