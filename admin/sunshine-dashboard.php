<?php
function sunshine_dashboard_display() {

	// Recent Orders
	ob_start();
?>
	<table width="100%;">
	<tr>
		<th><?php _e( 'Order #','sunshine' ); ?></th>
		<th><?php _e( 'Customer','sunshine' ); ?></th>
		<th><?php _e( 'Status','sunshine' ); ?></th>
		<th><?php _e( 'Total','sunshine' ); ?></th>
	</tr>
	<?php
	$args = array(
		'post_type' => 'sunshine-order',
		'posts_per_page' => 10,
		'post_status' => 'publish'
	);
	$the_query = new WP_Query( $args );
	while ( $the_query->have_posts() ) : $the_query->the_post();
		$customer_id = get_post_meta( get_the_ID(), '_sunshine_customer_id', true );
		if ( $customer_id ) {
			$customer = get_user_by( 'id', $customer_id );
		}
		$current_status = get_the_terms( get_the_ID(), 'sunshine-order-status' );
		$status = array_values( $current_status );
		$order_data = maybe_unserialize( get_post_meta( get_the_ID(), '_sunshine_order_data', true ) );
?>
		<tr>
			<td><a href="post.php?post=<?php the_ID(); ?>&action=edit"><?php the_title(); ?></a></td>
			<td>
				<?php if ( $customer_id ) { ?>
					<a href="user-edit.php?user_id=<?php echo $customer_id; ?>"><?php echo $customer->display_name; ?></a>
				<?php } else {
					 echo __( 'Guest', 'sunshine' ) . ' &mdash; ' . $order_data['first_name'] . ' ' . $order_data['last_name'];
				} ?>
			</td>
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
	<h2><?php _e( 'Dashboard' ); ?></h2>

	<?php do_action( 'sunshine_dashboard_before' ); ?>

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
						<?php if( !empty( $widget['links'] ) ) { echo $widget['links']; } ?>
					</div>
					<h2 class="hndle"><span><?php echo $widget['title']; ?></span></h2>
					<div class="inside">
						<?php echo $widget['content']; ?>
					</div>
				</div>
			</div>
			<?php $i++; } ?>

		</div>
	</div>

	<?php do_action( 'sunshine_dashboard_after' ); ?>

</div>
<?php
}

/*
MAIN DASHBOARD
*/
add_action( 'wp_dashboard_setup', 'add_sunshine_dashboard_widgets', 1 );
function add_sunshine_dashboard_widgets() {
	if ( current_user_can( 'sunshine_manage_options' ) ) {
		wp_add_dashboard_widget( 'dashboard_widget', __( 'Sunshine Photo Cart Overview', 'sunshine' ), 'sunshine_dashboard_widget_stats' );
	}
}

function sunshine_dashboard_widget_stats( $post, $callback_args ) {
	echo '<ul id="sunshine-dashboard-widget-stats" class="sunshine-clearfix">';
	global $wpdb;

	/* Provided by Rex Valkering */
	$image_sql = "SELECT COUNT(*) as total FROM {$wpdb->posts}
				  WHERE post_type = 'attachment' AND post_parent IN (
				  	SELECT ID FROM {$wpdb->posts}
				  	WHERE post_type = 'sunshine-gallery'
				  );";
	$image_total = $wpdb->get_row($image_sql)->total;

	$gallery_sql = "SELECT COUNT(*) as total FROM {$wpdb->posts}
				    WHERE post_type = 'sunshine-gallery' AND post_status='publish';";
	$gallery_total = $wpdb->get_row($gallery_sql)->total;
	/* */

	if ( $gallery_total ) {
		echo '<li><span class="data">' . $gallery_total . '</span><a href="edit.php?post_type=sunshine-gallery">' . __( 'Galleries', 'sunshine' ) . '</a></li>';
	}
	if ( $image_total ) {
		echo '<li><span class="data">' . $image_total . '</span>' . __( 'Images', 'sunshine' ) . '</li>';
	}

	$args = array(
		'post_type' => 'sunshine-order',
		'nopaging' => true,
		'tax_query' => array(
			array(
				'taxonomy' => 'sunshine-order-status',
				'field'    => 'slug',
				'terms'    => array( 'new', 'processing', 'shipped' ),
			),
		)
	);
	$orders = get_posts( $args );
	if ( is_array( $orders ) ) {
		$order_total = 0;
		foreach ( $orders as $order ) {
			$order_data = maybe_unserialize( get_post_meta( $order->ID, '_sunshine_order_data', true ) );
			$order_total += $order_data['total'];
		}
	}
	if ( $order_total ) {
		echo '<li><span class="data">' . sunshine_money_format( $order_total, false ) . '</span><a href="edit.php?post_type=sunshine-order">' . __( 'Orders', 'sunshine' ) . '</a></li>';
	}
	echo '</ol>';
}



?>
