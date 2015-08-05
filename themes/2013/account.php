<?php load_template(SUNSHINE_PATH.'themes/2013/header.php'); ?>

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
	while ( $the_query->have_posts() ) : $the_query->the_post();
		$items = unserialize(get_post_meta($post->ID, '_sunshine_order_items', true));
	?>
		<a href="<?php the_permalink(); ?>"><?php _e('Order', 'sunshine'); ?> #<?php the_ID(); ?> - 
		<?php printf( _n('%d item', '%d items', count($items), 'sunshine'), count($items) ); ?>
		- 
		<?php the_time('M j, Y'); ?></a><br />
	<?php endwhile; wp_reset_postdata(); ?>		
</div>

<form method="post" action="" id="sunshine-account" class="sunshine-form">
<input type="hidden" name="sunshine_update_account" value="1" />

<div id="sunshine-account-info">
	<?php sunshine_checkout_contact_fields(); ?>
	<?php sunshine_checkout_billing_fields(); ?>
	<?php sunshine_checkout_shipping_fields(); ?>
	<p class="sunshine-buttons"><input type="submit" class="sunshine-button" value="<?php _e('Update Account Info', 'sunshine'); ?>" /></p>
</div>

</form>

<?php load_template(SUNSHINE_PATH.'themes/2013/footer.php'); ?>
