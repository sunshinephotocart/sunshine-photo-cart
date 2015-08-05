<?php
function sunshine_dashboard_display() {

	// Recent Orders
	ob_start();
?>
	<table>
	<tr>
		<th><?php _e( 'Order #','sunshine' ); ?></th>
		<th><?php _e( 'Customer','sunshine' ); ?></th>
		<th><?php _e( 'Status','sunshine' ); ?></th>
		<th><?php _e( 'Total','sunshine' ); ?></th>
	</tr>
	<?php
	$args = array(
		'post_type' => 'sunshine-order',
		'posts_per_page' => 10
	);
	$the_query = new WP_Query( $args );
	while ( $the_query->have_posts() ) : $the_query->the_post();
	$customer_id = get_post_meta( get_the_ID(), '_sunshine_customer_id', true );
	$customer = get_user_by( 'id', $customer_id );
	$current_status = get_the_terms( get_the_ID(), 'sunshine-order-status' );
	$status = array_values( $current_status );
	$order_data = unserialize( get_post_meta( get_the_ID(), '_sunshine_order_data', true ) );
?>
		<tr>
			<td><a href="post.php?post=<?php the_ID(); ?>&action=edit"><?php the_title(); ?></a></td>
			<td><a href="user-edit.php?user_id=<?php echo $customer_id; ?>"><?php echo $customer->display_name; ?></a></td>
			<td><?php echo $status[0]->name; ?></td>
			<td><?php sunshine_money_format( $order_data['total'] ); ?>
		</tr>
	<?php endwhile; wp_reset_postdata(); ?>
	</table>

<?php
	$content = ob_get_contents();
	ob_end_clean();
	$widgets[] = array(
		'title' => __( 'Recent Orders','sunshine' ),
		'content' => $content
	);
?>
<div class="wrap sunshine">
	<div class="icon32 icon32-sunshine-dashboard" id="icon-sunshine"><br/></div>
	<h2><?php _e( 'Dashboard' ); ?></h2>

	<div id="sunshine-dashboard">
		<div id="dashboard-widgets" class="metabox-holder">

			<?php
				$widgets = apply_filters( 'sunshine_dashboard_widgets', $widgets );
				$i = 1;
				foreach ( $widgets as $widget ) {
			?>
			<div class="postbox-container" style="width:49%; <?php if ( ( $i % 2 ) == 0 ) { echo 'float: right; clear: right;'; } else { echo 'clear: left;'; } ?>">
				<div class="postbox">
					<div style="float: right; margin: 12px 15px 0 0; ">
						<?php echo $widget['links']; ?>
					</div>
					<h3><?php echo $widget['title']; ?></h3>
					<div class="inside">
						<?php echo $widget['content']; ?>
					</div>
				</div>
			</div>
			<?php $i++; } ?>

		</div>

	</div>
</div>
<?php
}
?>
