<?php global $sunshine; ?>
<div id="sunshine" class="sunshine-clearfix <?php sunshine_classes(); ?>">

	<?php do_action('sunshine_before_content'); ?>

	<div id="sunshine-breadcrumb">
		<?php sunshine_breadcrumb(); ?>
	</div>
	<h2><?php echo SunshineFrontend::$current_gallery->post_title; ?></h2>
	<?php
	$child_galleries = sunshine_get_child_galleries();
	?>

	<div id="sunshine-main">

		<div id="sunshine-action-menu" class="sunshine-clearfix">
			<?php sunshine_action_menu(); ?>
		</div>
		<?php
		if ( !sunshine_is_gallery_expired() ) {
			sunshine_gallery_expiration_notice();
			if ( SunshineFrontend::$current_gallery->post_content ) { ?>
				<div id="sunshine-content">
					<?php echo do_shortcode( wpautop( SunshineFrontend::$current_gallery->post_content ) ); ?>
				</div>
			<?php }
			if ( $child_galleries->have_posts() ) {
			?>
			<div id="sunshine-gallery-list" class="sunshine-clearfix">
				<ul class="sunshine-col-<?php echo $sunshine->options['columns']; ?> sunshine-clearfix">
				<?php while ( $child_galleries->have_posts() ) : $child_galleries->the_post(); ?>
					<li class="<?php sunshine_gallery_class(); ?>">
						<a href="<?php the_permalink(); ?>"><?php sunshine_featured_image(); ?></a><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					</li>
				<?php endwhile; ?>
				</ul>
			</div>
			<?php }	else {
				$images = sunshine_get_gallery_images();
				if ($images) {
					echo '<div id="sunshine-image-list">';
					echo '<ul class="sunshine-clearfix sunshine-col-'.$sunshine->options['columns'].'">';
					foreach ($images as $image) {
						$thumb = wp_get_attachment_image_src( $image->ID, 'sunshine-thumbnail');
						$image_html = '<a href="' . get_permalink( $image->ID ) . '"><img src="' . $thumb[0] . '" alt="" /></a>';
						$image_html = apply_filters( 'sunshine_gallery_image_html', $image_html, $image->ID, $thumb );
		?>
						<li id="sunshine-image-<?php echo $image->ID; ?>" class="<?php sunshine_image_class( $image->ID, array( 'sunshine-image-thumbnail' ) ); ?>">
							<?php echo $image_html; ?>
							<?php if ( $sunshine->options['show_image_names'] ) { ?>
								<div class="sunshine-image-name"><?php echo apply_filters( 'sunshine_image_name', $image->post_title, $image ); ?></div>
							<?php } ?>
							<div class="sunshine-image-menu-container">
								<?php sunshine_image_menu( $image ); ?>
							</div>
							<?php do_action( 'sunshine_image_thumbnail', $image ); ?>
						</li>
		<?php
					}
					echo '</ul>';
					echo '</div>';

					do_action( 'sunshine_after_gallery', SunshineFrontend::$current_gallery );

					sunshine_pagination();

				} else {
					echo '<p>'.__('Sorry, no images have been added to this gallery yet', 'sunshine').'</p>';
				}
			}
		} else {
			echo '<p>'.__('Sorry, this gallery has expired.','sunshine').'</p>';
		}
		?>

	</div>

	<?php do_action('sunshine_after_content'); ?>

</div>
