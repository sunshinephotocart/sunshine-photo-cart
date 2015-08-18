<?php load_template(SUNSHINE_PATH.'themes/2013/header.php'); ?>

<h1><?php _e('Packages', 'sunshine'); ?></h1>

<?php
$gallery_id = intval( $_GET['gallery_id'] );
$image_id = (isset($_GET['image_id'])) ? intval( $_GET['image_id'] ) : '';
$price_level = get_post_meta($gallery_id, 'sunshine_gallery_price_level', true);
$args = array(
	'post_type' => 'sunshine-product',
	'orderby' => 'menu_order',
	'order' => 'ASC',
	'nopaging' => true,
	'meta_query' => array(
		array(
			'key' => 'sunshine_product_price_'.$price_level,
			'value' => '',
			'compare' => '!='
		),
		array(
			'key' => 'sunshine_product_package',
			'value' => '1'
		)
	)
);
$packages = new WP_Query( $args );
if ($packages->have_posts()) : while ( $packages->have_posts() ) : $packages->the_post();
?>
	<div class="sunshine-package">
	<h2><?php the_title(); ?></h2>
	<p class="sunshine-package-price"><?php echo $sunshine->cart->get_product_price(get_the_ID(), $price_level); ?> </p>
	<?php the_content(); ?>
	<p><a href="?package_add_to_cart=1&amp;package_id=<?php the_ID(); ?>&amp;gallery_id=<?php echo $gallery_id; ?>&amp;image_id=<?php echo $image_id; ?>" class="sunshine-button">Add to cart</a></p>
	</div>
<?php endwhile; else: ?>
	<p>Sorry, no packages are available</p>
<?php endif; wp_reset_postdata(); ?>	

<?php load_template(SUNSHINE_PATH.'themes/2013/footer.php'); ?>
