<?php load_template(SUNSHINE_PATH.'themes/2013/header.php'); ?>

<h1><?php _e('Cart', 'sunshine'); ?></h1>

<?php echo apply_filters('the_content', $post->post_content); ?>

<form method="post" action="" id="sunshine-cart">
<input type="hidden" name="sunshine_update_cart" value="1" />
<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'sunshine_update_cart' ); ?>" />

<?php do_action('sunshine_before_cart_items'); ?>

<?php if (sunshine_cart_items()) { ?>
	<table id="sunshine-cart-items">
	<tr>
		<th class="sunshine-cart-image"><?php _e('Image', 'sunshine'); ?></th>
		<th class="sunshine-cart-name"><?php _e('Product', 'sunshine'); ?></th>
		<th class="sunshine-cart-qty"><?php _e('Qty', 'sunshine'); ?></th>
		<th class="sunshine-cart-price"><?php _e('Item Price', 'sunshine'); ?></th>
		<th class="sunshine-cart-total"><?php _e('Item Total', 'sunshine'); ?></th>
	</tr>
	<?php $i = 1; $tabindex = 0; foreach (sunshine_cart_items() as $item) { $tabindex++; ?>
		<tr class="sunshine-cart-item <?php sunshine_product_class($item['product_id']); ?>">
			<td class="sunshine-cart-item-image" data-label="<?php _e('Image', 'sunshine'); ?>">
				<?php
				$thumb = wp_get_attachment_image_src($item['image_id'], 'sunshine-thumbnail');
				$image_html = '<a href="'.get_permalink($item['image_id']).'"><img src="'.$thumb[0].'" alt="" class="sunshine-image-thumb" /></a>';
				echo apply_filters('sunshine_cart_image_html', $image_html, $item, $thumb);
				?>
			</td>
			<td class="sunshine-cart-item-name" data-label="<?php _e('Product', 'sunshine'); ?>">
				<?php 
				$product = get_post($item['product_id']);
				$cat = wp_get_post_terms($item['product_id'], 'sunshine-product-category');
				?>
				<h2><span class="sunshine-item-cat"><?php echo apply_filters('sunshine_cart_item_category', (isset($cat[0]->name)) ? $cat[0]->name : '', $item); ?></span> - <span class="sunshine-item-name"><?php echo apply_filters('sunshine_cart_item_name', $product->post_title, $item); ?></span></h2>
				<div class="sunshine-item-comments"><?php echo apply_filters('sunshine_cart_item_comments', $item['comments'], $item); ?></div>
			</td>
			<td class="sunshine-cart-item-qty" data-label="<?php _e('Qty', 'sunshine'); ?>">
				<input type="number" name="item[<?php echo $i; ?>][qty]" class="sunshine-qty" value="<?php echo $item['qty']; ?>" size="4" tabindex="<?php echo $tabindex; ?>" min="0" />
				<a href="?delete_cart_item=<?php echo $item['hash']; ?>&nonce=<?php echo wp_create_nonce( 'sunshine_delete_cart_item' ); ?>"><?php _e('Remove','sunshine'); ?></a>
			</td>
			<td class="sunshine-cart-item-price" data-label="<?php _e('Price', 'sunshine'); ?>">
				<?php sunshine_money_format($item['price']); ?>
			</td>
			<td class="sunshine-cart-item-total" data-label="<?php _e('Total', 'sunshine'); ?>">
				<?php sunshine_money_format($item['total']); ?>
				<input type="hidden" name="item[<?php echo $i; ?>][image_id]" value="<?php echo $item['image_id']; ?>" />
				<input type="hidden" name="item[<?php echo $i; ?>][product_id]" value="<?php echo $item['product_id']; ?>" />
				<input type="hidden" name="item[<?php echo $i; ?>][comments]" value="<?php echo $item['comments']; ?>" />
				<input type="hidden" name="item[<?php echo $i; ?>][hash]" value="<?php echo $item['hash']; ?>" />
			</td>
		</tr>

	<?php $i++; } ?>
	</table>

	<?php do_action('sunshine_after_cart_items'); ?>

	<div id="sunshine-cart-update-button">
		<input type="submit" value="<?php _e('Update Cart', 'sunshine'); ?>" class="sunshine-button-alt" />
	</div>

	</form>
	
	<?php do_action('sunshine_after_cart_form'); ?>

	<div id="sunshine-cart-totals">
		<?php sunshine_cart_totals(); ?>
		<p id="sunshine-cart-checkout-button"><a href="<?php echo sunshine_url('checkout'); ?>" class="sunshine-button"><?php _e('Continue to checkout', 'sunshine'); ?> &rarr;</a></p>
	</div>

<?php } else { ?>
	<p><?php _e('You do not have anything in your cart yet!', 'sunshine'); ?></p>
<?php } ?>

<?php do_action('sunshine_after_cart'); ?>

<?php load_template(SUNSHINE_PATH.'themes/2013/footer.php'); ?>