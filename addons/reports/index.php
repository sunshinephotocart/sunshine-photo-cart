<?php
add_filter( 'sunshine_admin_menu', 'sunshine_reports_admin_menu' );
function sunshine_reports_admin_menu( $submenu ) {
	$submenu[100] = array( __( 'Reports','sunshine' ), __( 'Reports','sunshine' ), 'sunshine_manage_options', 'sunshine_reports', 'sunshine_reports_page' );
	return $submenu;
}

function sunshine_reports_page() {
	global $sunshine;

?>
	<div class="wrap sunshine">
		<h2><?php _e( 'Reports', 'sunshine' ); ?></h2>
		<h3>Sales Taxes Collected</h3>
		<?php
			$tax_years = array();
			$args = array(
				'post_type' => 'sunshine-order',
				'nopaging' => true,
				'tax_query' => array(
					array(
						'taxonomy' => 'sunshine-order-status',
						'field'    => 'slug',
						'terms'    => array( 'shipped' )
					)
				)
			);
			$orders = get_posts( $args );
			foreach ( $orders as $order ) {
				$order_data = maybe_unserialize( get_post_meta( $order->ID, '_sunshine_order_data', true ) );
				$year = date( 'Y',strtotime( $order->post_date ) );
				$tax_years[$year] += $order_data['tax'];
			}
			if ( !empty( $tax_years ) ) {
				foreach ( $tax_years as $tax_year => $tax_amount ) {
					echo '<p><strong>'.$tax_year.'</strong>: '.sunshine_money_format( $tax_amount,false ).'</p>';
				}
			} else {
				_e( 'No taxes have been collected thus far', 'sunshine' );
			}
		?>
	</div>
<?php
}

?>
