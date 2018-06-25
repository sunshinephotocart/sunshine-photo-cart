<?php
add_action( 'admin_head', 'sunshine_product_bulk_add_link' );
function sunshine_product_bulk_add_link() {
	$screen = get_current_screen();
	if ( $screen->id == 'edit-sunshine-product' ) {
?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.wp-header-end').before('<a href="<?php echo get_admin_url().'admin.php?page=sunshine_bulk_add_products'; ?>" class="page-title-action"><?php _e( 'Bulk add products', 'sunshine' ); ?></a>');
	});
	</script>
<?php
	}
}

function sunshine_bulk_add_products() {
?>
	<div class="wrap sunshine">
		<h2><?php _e( 'Bulk Add Products','sunshine' ); ?></h2>

		<?php
	if ( isset( $_POST['sunshine_bulk_add_products'] ) && $_POST['sunshine_bulk_add_products'] == 1 ) {
		$added = 0;
		for ( $i = 0; $i < count( $_POST['name'] ); $i++ ) {
			$name = sanitize_text_field( $_POST['name'][$i] );
			$category = sanitize_text_field( $_POST['category'][$i] );
			$taxable = intval( $_POST['taxable'][$i] );
			if ( $name != '' ) {
				$current_user = wp_get_current_user();
				$product_id = wp_insert_post( array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => $current_user->ID,
						'post_title' => $name,
						'post_status' => 'publish',
						'post_type' => 'sunshine-product'
					) );

				wp_set_object_terms( $product_id, $category, 'sunshine-product-category' );

				add_post_meta( $product_id, 'sunshine_product_taxable', $taxable );

				$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
				foreach ( $price_levels as $price_level ) {
					$price = $_POST['price_'.$price_level->term_id][$i];
					if ( $price )
						update_post_meta( $product_id, 'sunshine_product_price_'.$price_level->term_id, $price );
				}

				do_action( 'sunshine_bulk_add_product', $product_id, $_POST, $i );

				$added++;
			}
		}
		echo '<div id="message" class="updated"><p>Bulk import complete! '.$added.' products added. <a href="edit.php?post_type=sunshine-product">View products</a></p></div>';
	}
?>

		<form method="post">
			<input type="hidden" name="sunshine_bulk_add_products" value="1" />
		<?php
	$categories = get_terms( 'sunshine-product-category', array( 'hide_empty' => false ) );
	$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
	$currency_symbol_format = sunshine_currency_symbol_format();
	$currency_symbol = sunshine_currency_symbol();
?>
		<table id="sunshine-bulk-add-products">
			<tr>
				<th><?php _e( 'Name','sunshine' ); ?>:</th>
				<td><input type="text" name="name[]" /></td>
				<th><?php _e( 'Category','sunshine' ); ?>:</th>
				<td>
					<select name="category[]">
						<option value=""></option>
					<?php
	foreach ( $categories as $category ) {
		echo '<option value="'.$category->slug.'">'.$category->name.'</option>';
	}
?>
					</select>
				</td>
				<th><?php _e( 'Price Level','sunshine' ); ?>:</th>
				<td>
					<?php foreach ( $price_levels as $price_level ) {
		$text_field = '<input type="text" name="price_'.$price_level->term_id.'[]" size="6" />';
		echo $price_level->name.': '.sprintf( $currency_symbol_format, $currency_symbol, $text_field ).'<br />';
	} ?>
				</td>
				<th><?php _e( 'Taxable','sunshine' ); ?>:</th>
				<td><input type="checkbox" value="1" name="taxable[]" /></td>
				<?php do_action( 'sunshine_bulk_add_product_item' ); ?>
				<td><a href="#" class="sunshine-remove-product"><?php _e( 'Remove','sunshine' ); ?></a></td>
			</tr>
		</table>
		<a href="#" id="sunshine-add-product"><?php _e( 'Add another product','sunshine' ); ?></a>

		<p><strong><?php _e( 'Before you add these, please double check everything is accurate!','sunshine' ); ?></strong></p>

		<p><input type="submit" value="<?php _e( 'Add these products','sunshine' ); ?>" class="button-primary" /></p>

		</form>

		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#sunshine-add-product").live('click', function() {
			    jQuery('#sunshine-bulk-add-products tr:first').clone(true,true).appendTo('#sunshine-bulk-add-products');
			    jQuery('#sunshine-bulk-add-products tr:last input, #sunshine-bulk-add-products tr:last select').val('');
			    jQuery('#sunshine-bulk-add-products tr:last input[type=checkbox]').removeAttr('checked');
			    return false;
			});
			jQuery(".sunshine-remove-product").live('click', function(event) {
				jQuery(this).parent().parent().remove();
				return false;
			});
		});
		</script>

	</div>
<?php
}
?>
