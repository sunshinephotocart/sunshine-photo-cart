<?php global $sunshine; ?>
<div id="sunshine" class="sunshine-clearfix <?php sunshine_classes(); ?>">

	<?php do_action('sunshine_before_content'); ?>

	<div id="sunshine-main">

		<div id="sunshine-action-menu" class="sunshine-clearfix">
			<?php sunshine_action_menu(); ?>
		</div>
		<div id="sunshine-image-list">
		<?php
		if (!empty($sunshine->favorites)) {
			echo '<ul class="sunshine-clearfix sunshine-col-'.$sunshine->options['columns'].'">';
			foreach ($sunshine->favorites as $image_id) {
				$image = get_post($image_id);
				$thumb = wp_get_attachment_image_src($image->ID, 'sunshine-thumbnail');
				$image_html = '<a href="'.get_permalink($image->ID).'"><img src="'.$thumb[0].'" alt="" /></a>';
				$image_html = apply_filters('sunshine_gallery_image_html', $image_html, $image->ID, $thumb);
?>
				<li id="sunshine-image-<?php echo $image->ID; ?>" class="<?php sunshine_image_class($image->ID, array('sunshine-image-thumbnail')); ?>">
					<?php echo $image_html; ?>
					<?php if ($sunshine->options['show_image_names']) { ?>
						<div class="sunshine-image-name"><?php echo apply_filters('sunshine_image_name', $image->post_title, $image); ?></div>
					<?php } ?>
					<div class="sunshine-image-menu-container">
						<?php sunshine_image_menu($image); ?>
					</div>
					<?php do_action('sunshine_image_thumbnail', $image); ?>
				</li>
<?php
			}
			echo '</ul>';
		} else {
			echo '<p>'.__('You have no images marked as a favorite', 'sunshine').'</p>';
		}

		do_action('sunshine_after_favorites');
		?>
		</div>

	</div>

	<?php do_action('sunshine_after_content'); ?>

</div>
