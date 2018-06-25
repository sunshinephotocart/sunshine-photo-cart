<?php global $sunshine; load_template(SUNSHINE_PATH.'themes/default/header.php'); ?>

<h1><?php _e('Favorites', 'sunshine'); ?></h1>
<?php echo apply_filters('the_content', $post->post_content); ?>

<div id="sunshine-action-menu" class="sunshine-clearfix">
	<?php sunshine_action_menu(); ?>
</div>
<div id="sunshine-image-list">
	<?php
	if (!empty($sunshine->favorites)) {
		echo '<ul class="sunshine-col-'.$sunshine->options['columns'].'">';
		foreach ( $sunshine->favorites as $favorite_id ) {
			$image = get_post( $favorite_id );
			$thumb = wp_get_attachment_image_src($image->ID, 'sunshine-thumbnail');
			$image_html = '<a href="'.get_permalink($image->ID).'"><img src="'.$thumb[0].'" alt="" class="sunshine-image-thumb" /></a>';
			$image_html = apply_filters('sunshine_gallery_image_html', $image_html, $image->ID, $thumb);
	?>
		<li id="sunshine-image-<?php echo $image->ID; ?>" class="<?php sunshine_image_class($image->ID, array('sunshine-image-thumbnail')); ?>">
			<?php echo $image_html; ?>
			<?php if ($sunshine->options['show_image_names']) { ?>
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
	} else {
		echo '<p>'.__('You have no images marked as a favorite', 'sunshine').'</p>';
	}
	?>
</div>

<?php load_template(SUNSHINE_PATH.'themes/default/footer.php'); ?>
