<?php global $sunshine; ?>
<div id="sunshine" class="sunshine-clearfix <?php sunshine_classes(); ?>">

	<?php do_action('sunshine_before_content'); ?>

	<div id="sunshine-gallery-images" class="sunshine-clearfix">

	<?php
	$images = sunshine_get_search_images();
	if ($images) {
		echo '<ul class="sunshine-col-'.$sunshine->options['columns'].'">';
		foreach ($images as $image) {
			if (post_password_required($image->post_parent))
				$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($image->post_parent), 'sunshine-thumbnail');
			else
				$thumb = wp_get_attachment_image_src($image->ID, 'sunshine-thumbnail');
			$image_html = '<a href="'.get_permalink($image->ID).'"><img src="'.$thumb[0].'" alt="" /></a>';
			$image_html = apply_filters('sunshine_gallery_image_html', $image_html, $image->ID, $thumb);
	?>
			<li id="sunshine-image-<?php echo $image->ID; ?>" class="<?php sunshine_image_class($image->ID, array('sunshine-image-thumbnail')); ?>">
				<?php echo $image_html; ?>
				<div class="sunshine-image-name"><?php echo $image->post_title; ?></div>
				<div class="sunshine-image-menu-container">
					<?php sunshine_image_menu($image); ?>
				</div>
				<?php do_action('sunshine_image_thumbnail', $image); ?>
			</li>
	<?php
		}
		echo '</ul>';
	
		do_action('sunshine_after_search_results');

	} else {
		echo '<p>'.__('Sorry, no images match your search', 'sunshine').'</p>';
	}
	?>
	</div>

	<?php do_action('sunshine_after_content'); ?>

</div>