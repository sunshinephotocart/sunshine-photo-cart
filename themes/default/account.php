<?php load_template(SUNSHINE_PATH.'themes/default/header.php'); ?>

<h1><?php _e('Account', 'sunshine'); ?></h1>

<?php
$credits = SunshineUser::get_user_meta('credits');
if ($credits > 0) {
?>
	<h2><?php _e('Credits', 'sunshine'); ?></h2>
	<p>
		<?php printf( __('You have %s in credit', 'sunshine'), sunshine_money_format($credits,false) ); ?>
	</p>
<?php } ?>

<div id="sunshine-account-orders">
	<h2><?php _e('Orders', 'sunshine'); ?></h2>
	<?php
	global $current_user;
	$the_query = new WP_Query( 'post_type=sunshine-order&nopaging=true&meta_key=_sunshine_customer_id&meta_value='.$current_user->ID );
	if ( $the_query->have_posts() ) { ?>
		<table id="sunshine-order-history">
		<tr>
			<th><?php _e( 'Order', 'sunshine' ); ?></th>
			<th><?php _e( 'Order Date', 'sunshine' ); ?></th>
			<th><?php _e( 'Order Total', 'sunshine' ); ?></th>
			<th><?php _e( 'Order Status', 'sunshine' ); ?></th>
		</tr>
		<?php
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$items = maybe_unserialize(get_post_meta($post->ID, '_sunshine_order_items', true));
			$order_data = maybe_unserialize(get_post_meta($post->ID, '_sunshine_order_data', true));
		?>
			<tr>
			<td><a href="<?php the_permalink(); ?>"><?php _e('Order', 'sunshine'); ?> #<?php the_ID(); ?></a></td>
			<td><?php the_time('M j, Y'); ?></td>
			<td><?php sunshine_money_format( $order_data['total'] ); ?></td>
			<td>
				<?php
				$order_status = wp_get_object_terms( $post->ID,  'sunshine-order-status' );
				echo $order_status[0]->name
				?>
			</td>
			</tr>
		<?php endwhile; wp_reset_postdata(); ?>
		</table>
	<?php } ?>
</div>

<form method="post" action="" id="sunshine-account" class="sunshine-form">
<input type="hidden" name="sunshine_update_account" value="1" />

<div id="sunshine-account-info">
	<?php sunshine_checkout_contact_fields(); ?>
	<?php sunshine_checkout_shipping_fields(); ?>
	<?php sunshine_checkout_billing_fields(); ?>
	<p class="sunshine-buttons"><input type="submit" class="sunshine-button" value="<?php _e('Update Account Info', 'sunshine'); ?>" /></p>
</div>

</form>

<?php load_template(SUNSHINE_PATH.'themes/default/footer.php'); ?>
